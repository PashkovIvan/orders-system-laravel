<?php

namespace App\Http\Responses;

use App\Models\Order;
use Illuminate\Http\JsonResponse;

class OrderResponse
{
    public static function make(Order $order): JsonResponse
    {
        return response()->json([
            'data' => [
                'id' => $order->id,
                'customer_name' => $order->customer_name,
                'customer_email' => $order->customer_email,
                'status' => $order->status,
                'total_amount' => $order->total_amount,
                'items' => $order->items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'product_name' => $item->product_name,
                        'quantity' => $item->quantity,
                        'price' => $item->price,
                        'total' => $item->total,
                    ];
                }),
                'created_at' => $order->created_at,
                'updated_at' => $order->updated_at,
            ],
        ]);
    }

    public static function collection(array $orders): JsonResponse
    {
        return response()->json([
            'data' => collect($orders)->map(function ($order) {
                return [
                    'id' => $order['id'],
                    'customer_name' => $order['customer_name'],
                    'customer_email' => $order['customer_email'],
                    'status' => $order['status'],
                    'total_amount' => $order['total_amount'],
                    'items' => collect($order['items'])->map(function ($item) {
                        return [
                            'id' => $item['id'],
                            'product_name' => $item['product_name'],
                            'quantity' => $item['quantity'],
                            'price' => $item['price'],
                            'total' => $item['total'],
                        ];
                    }),
                    'created_at' => $order['created_at'],
                    'updated_at' => $order['updated_at'],
                ];
            }),
        ]);
    }
} 