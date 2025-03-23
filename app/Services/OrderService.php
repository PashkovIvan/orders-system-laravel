<?php

namespace App\Services;

use App\DataTransferObjects\OrderData;
use App\DataTransferObjects\OrderItemData;
use App\Jobs\ProcessOrderJob;
use App\Jobs\UpdateOrderStatusJob;
use App\Jobs\CreateOrderJob;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderService
{
    public function createOrder(OrderData $orderData): Order
    {
        try {
            DB::beginTransaction();

            $order = Order::create([
                'customer_name' => $orderData->customer_name,
                'customer_email' => $orderData->customer_email,
                'status' => $orderData->status,
                'total_amount' => $orderData->total_amount
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

    public function updateOrderStatus(Order $order, string $newStatus): Order
    {
        try {
            DB::beginTransaction();

            $oldStatus = $order->status;
            $order->update(['status' => $newStatus]);

            DB::commit();

            UpdateOrderStatusJob::dispatch($order, $oldStatus, $newStatus);

            return $order->load('items');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating order status', [
                'order_id' => $order->id,
                'old_status' => $oldStatus ?? null,
                'new_status' => $newStatus,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function getOrder(int $orderId): Order
    {
        return Order::with('items')->findOrFail($orderId);
    }

    public function listOrders(): array
    {
        return Order::with('items')
            ->latest()
            ->get()
            ->toArray();
    }
} 