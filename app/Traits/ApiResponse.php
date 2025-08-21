<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

trait ApiResponse
{
    /**
     * Success response with logging
     */
    protected function successResponse($data = null, string $message = 'Success', int $code = 200): array
    {
        $this->logApiResponse('success', $message, $code);
        
        return [
            'success' => true,
            'message' => $message,
            'data' => $data,
        ];
    }

    /**
     * Error response with logging
     */
    protected function errorResponse(string $message = 'Error', $errors = null, int $code = 400): array
    {
        $this->logApiResponse('error', $message, $code, $errors);
        
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors) {
            $response['errors'] = $errors;
        }

        return $response;
    }

    /**
     * JSON success response for controllers
     */
    protected function jsonSuccess($data = null, string $message = 'Success', int $code = 200): JsonResponse
    {
        return response()->json(
            $this->successResponse($data, $message),
            $code
        );
    }

    /**
     * JSON error response for controllers
     */
    protected function jsonError(string $message = 'Error', $errors = null, int $code = 400): JsonResponse
    {
        return response()->json(
            $this->errorResponse($message, $errors),
            $code
        );
    }

    /**
     * Validation error response
     */
    protected function validationError($errors, string $message = 'Validation failed'): JsonResponse
    {
        return $this->jsonError($message, $errors, 422);
    }

    /**
     * Unauthorized response
     */
    protected function unauthorized(string $message = 'Unauthorized'): JsonResponse
    {
        return $this->jsonError($message, null, 401);
    }

    /**
     * Forbidden response
     */
    protected function forbidden(string $message = 'Forbidden'): JsonResponse
    {
        return $this->jsonError($message, null, 403);
    }

    /**
     * Not found response
     */
    protected function notFound(string $message = 'Resource not found'): JsonResponse
    {
        return $this->jsonError($message, null, 404);
    }

    /**
     * Server error response
     */
    protected function serverError(string $message = 'Internal server error'): JsonResponse
    {
        return $this->jsonError($message, null, 500);
    }

    /**
     * Log API responses for monitoring
     */
    private function logApiResponse(string $type, string $message, int $code, $errors = null): void
    {
        $context = [
            'type' => $type,
            'status_code' => $code,
            'url' => request()->fullUrl(),
            'method' => request()->method(),
            'user_id' => auth()->id(),
            'ip' => request()->ip(),
        ];

        if ($errors) {
            $context['errors'] = $errors;
        }

        if ($type === 'error' && $code >= 500) {
            Log::error("API Error: {$message}", $context);
        } elseif ($type === 'error') {
            Log::warning("API Warning: {$message}", $context);
        } else {
            Log::info("API Success: {$message}", $context);
        }
    }
}