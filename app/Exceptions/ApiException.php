<?php

namespace App\Exceptions;

use Exception;

class ApiException extends Exception
{
    protected string $errorCode;
    protected int $statusCode;

    public function __construct(string $message = "", string $errorCode = "", int $statusCode = 400, \Throwable $previous = null)
    {
        $this->errorCode = $errorCode;
        $this->statusCode = $statusCode;
        parent::__construct($message, 0, $previous);
    }

    public function getErrorCode(): string
    {
        return $this->errorCode;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function render()
    {
        return response()->json([
            'success' => false,
            'message' => $this->getMessage(),
            'error_code' => $this->errorCode
        ], $this->statusCode);
    }
}