<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    /**
     * Success response
     */
    protected function successResponse($data = null, string $message = 'Success', int $code = 200): array
    {
        return [
            'success' => true,
            'message' => $message,
            'data' => $data,
        ];
    }

    /**
     * Error response
     */
    protected function errorResponse(string $message = 'Error', $errors = null, int $code = 400): array
    {
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
} 