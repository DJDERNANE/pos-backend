<?php

namespace App\Traits;

trait ApiResponse
{
    protected function successResponse($data = [], string $message = '', int $statusCode = 200)
    {
        return response()->json([
            'success' => true,
            'data' => $data,
            'message' => $message,
        ], $statusCode);
    }

    protected function errorResponse(string $message = '', int $statusCode = 400, $data = [])
    {
        return response()->json([
            'success' => false,
            'data' => $data,
            'message' => $message,
        ], $statusCode);
    }
}
