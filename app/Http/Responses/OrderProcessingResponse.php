<?php

namespace App\Http\Responses;

use App\Models\Order;
use Illuminate\Http\JsonResponse;

class OrderProcessingResponse
{
    public static function success(Order $order, string $message = 'Order processed successfully'): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => [
                'order_id' => $order->id,
                'status' => $order->status,
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
            'success' => true,
            'message' => $message,
            'data' => [
                'order_id' => $order->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'processed_at' => now()->toIso8601String(),
            ]
        ]);
    }

    public static function error(\Exception $e): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Error processing order',
            'error' => $e->getMessage()
        ], 500);
    }
} 