<?php

namespace App\Http\Responses;

use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\Collection;

/**
 * @OA\Schema(
 *     schema="OrderResponse",
 *     type="object",
 *     @OA\Property(
 *         property="data",
 *         type="object",
 *         @OA\Property(property="id", type="integer", description="ID заказа"),
 *         @OA\Property(property="customer_name", type="string", description="Имя клиента"),
 *         @OA\Property(property="customer_email", type="string", format="email", description="Email клиента"),
 *         @OA\Property(
 *             property="status",
 *             type="string",
 *             enum={"pending", "processing", "completed", "cancelled"},
 *             description="Статус заказа"
 *         ),
 *         @OA\Property(property="total_amount", type="number", format="float", description="Общая сумма заказа"),
 *         @OA\Property(
 *             property="items",
 *             type="array",
 *             @OA\Items(
 *                 type="object",
 *                 @OA\Property(property="id", type="integer", description="ID позиции заказа"),
 *                 @OA\Property(property="product_name", type="string", description="Название товара"),
 *                 @OA\Property(property="quantity", type="integer", description="Количество"),
 *                 @OA\Property(property="price", type="number", format="float", description="Цена за единицу"),
 *                 @OA\Property(property="total", type="number", format="float", description="Общая стоимость позиции")
 *             )
 *         ),
 *         @OA\Property(property="created_at", type="string", format="date-time", description="Дата создания"),
 *         @OA\Property(property="updated_at", type="string", format="date-time", description="Дата обновления")
 *     )
 * )
 */
class OrderResponse extends AbstractResponse
{
    public static function make(Order $order): JsonResponse
    {
        return response()->json([
            'data' => [
                'id' => $order?->id,
                'customer_name' => $order?->customer_name,
                'customer_email' => $order?->customer_email,
                'status' => $order?->status,
                'total_amount' => $order?->total_amount,
                'items' => $order?->items?->map(function ($item) {
                    return [
                        'id' => $item?->id,
                        'product_name' => $item?->product_name,
                        'quantity' => $item?->quantity,
                        'price' => $item?->price,
                        'total' => $item?->total,
                    ];
                }),
                'created_at' => $order?->created_at,
                'updated_at' => $order?->updated_at,
            ],
        ]);
    }

    public static function collection(Collection $orders): JsonResponse
    {
        return response()->json([
            'data' => collect($orders)->map(function ($order) {
                return [
                    'id' => $order['id'] ?? null,
                    'customer_name' => $order['customer_name'] ?? null,
                    'customer_email' => $order['customer_email'] ?? null,
                    'status' => $order['status'] ?? null,
                    'total_amount' => $order['total_amount'] ?? null,
                    'items' => collect($order['items'] ?? [])->map(function ($item) {
                        return [
                            'id' => $item['id'] ?? null,
                            'product_name' => $item['product_name'] ?? null,
                            'quantity' => $item['quantity'] ?? null,
                            'price' => $item['price'] ?? null,
                            'total' => $item['total'] ?? null,
                        ];
                    }),
                    'created_at' => $order['created_at'] ?? null,
                    'updated_at' => $order['updated_at'] ?? null,
                ];
            }),
        ]);
    }

    public static function error(\Exception $e): JsonResponse
    {
        return self::errorResponse(
            message: $e?->getMessage(),
            code: $e?->getCode()
        );
    }
} 