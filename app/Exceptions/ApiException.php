<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class ApiException extends Exception
{
    public function __construct(
        public readonly string $message,
        public readonly int $statusCode = 400,
        public readonly array $data = [],
        ?Exception $previous = null
    ) {
        parent::__construct($message, 0, $previous);
    }

    public function render(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $this->message,
            'data' => $this->data,
        ], $this->statusCode);
    }
}
