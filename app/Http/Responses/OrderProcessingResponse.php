<?php

namespace App\Http\Responses;

use App\Models\Order;
use Illuminate\Http\JsonResponse;

class OrderProcessingResponse extends AbstractResponse
{
    public static function success(Order $order, string $message = 'Order processed successfully'): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => [
                'order_id' => $order?->id,
                'status' => $order?->status,
                'processed_at' => now()->toIso8601String(),
            ]
        ]);
    }

    public static function statusChanged(
        Order $order,
        string $oldStatus,
        string $newStatus,
        string $message = 'Order status changed successfully'
    ): JsonResponse {
        return response()->json([
            'data' => [
                'id' => $order?->id,
                'status' => $newStatus,
                'message' => $message,
            ]
        ]);
    }

    public static function error(\Exception|string $error, int $httpCode = 500): JsonResponse
    {
        $hasException = $error instanceof \Exception;

        return self::errorResponse(
            message: $hasException ? $error?->getMessage() : $error,
            code: $hasException ? $error?->getCode() : null,
            httpCode: $httpCode
        );
    }
} 