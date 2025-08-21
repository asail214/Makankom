<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function render($request, Throwable $exception)
    {
        if ($request->expectsJson()) {
            return $this->handleApiException($request, $exception);
        }

        return parent::render($request, $exception);
    }

    private function handleApiException(Request $request, Throwable $exception)
    {
        // Log the exception with context
        $this->logExceptionWithContext($request, $exception);

        if ($exception instanceof ValidationException) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $exception->errors(),
                'error_code' => 'VALIDATION_ERROR'
            ], 422);
        }

        if ($exception instanceof AuthenticationException) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required',
                'error_code' => 'AUTHENTICATION_REQUIRED'
            ], 401);
        }

        if ($exception instanceof ModelNotFoundException) {
            return response()->json([
                'success' => false,
                'message' => 'Resource not found',
                'error_code' => 'RESOURCE_NOT_FOUND'
            ], 404);
        }

        if ($exception instanceof NotFoundHttpException) {
            return response()->json([
                'success' => false,
                'message' => 'Endpoint not found',
                'error_code' => 'ENDPOINT_NOT_FOUND'
            ], 404);
        }

        if ($exception instanceof MethodNotAllowedHttpException) {
            return response()->json([
                'success' => false,
                'message' => 'Method not allowed',
                'error_code' => 'METHOD_NOT_ALLOWED'
            ], 405);
        }

        // For production, don't expose internal errors
        if (app()->environment('production')) {
            return response()->json([
                'success' => false,
                'message' => 'Internal server error',
                'error_code' => 'INTERNAL_ERROR'
            ], 500);
        }

        // For development, include debug info
        return response()->json([
            'success' => false,
            'message' => $exception->getMessage(),
            'error_code' => 'INTERNAL_ERROR',
            'debug' => [
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString()
            ]
        ], 500);
    }

    private function logExceptionWithContext(Request $request, Throwable $exception)
    {
        \Log::error('API Exception', [
            'exception' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'user_id' => auth()->id(),
            'guard' => $this->getActiveGuard($request),
            'request_id' => $this->generateRequestId(),
        ]);
    }

    private function getActiveGuard(Request $request): ?string
    {
        if (auth('customer')->check()) return 'customer';
        if (auth('organizer')->check()) return 'organizer';
        if (auth('admin')->check()) return 'admin';
        if (auth('scan_point')->check()) return 'scan_point';
        return null;
    }

    private function generateRequestId(): string
    {
        return uniqid('req_', true);
    }
}