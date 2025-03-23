<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;

abstract class AbstractResponse
{
    protected static function errorResponse(
        string $message,
        ?int $code = null,
        int $httpCode = 500
    ): JsonResponse 
    {
        return response()->json(
            [
                'error' => [
                    'message' => $message,
                    'code' => $code,
                ],
            ], 
            $httpCode
        );
    }
} 