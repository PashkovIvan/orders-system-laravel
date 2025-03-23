<?php

namespace App\Services;

use App\DataTransferObjects\OrderData;
use App\DataTransferObjects\OrderItemData;
use App\Jobs\CreateOrderJob;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Collection;

class OrderService
{
    public function createOrder(OrderData $orderData): Order
    {
        try {
            DB::beginTransaction();

            $totalAmount = 0;
            foreach ($orderData->items as $itemData) {
                $totalAmount += $itemData->total;
            }

            $order = Order::create([
                'customer_name' => $orderData->customer_name,
                'customer_email' => $orderData->customer_email,
                'status' => 'pending',
                'total_amount' => $totalAmount
            ]);

            foreach ($orderData->items as $itemData) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_name' => $itemData->product_name,
                    'quantity' => $itemData->quantity,
                    'price' => $itemData->price,
                    'total' => $itemData->total
                ]);
            }

            DB::commit();

            CreateOrderJob::dispatch($order);

            return $order->load('items');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating order', [
                'error' => $e->getMessage(),
                'data' => $orderData
            ]);
            throw $e;
        }
    }

    public function getOrder(int $orderId): Order
    {
        return Order::with('items')->findOrFail($orderId);
    }

    public function listOrders(): Collection
    {
        return Order::with('items')
            ->latest()
            ->get();
    }
} 