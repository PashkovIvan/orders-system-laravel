<?php

namespace App\Services;

use App\DataTransferObjects\OrderProcessingData;
use App\Contracts\Messages\Producer\MessageProducerInterface;
use App\Http\Responses\OrderProcessingResponse;
use App\Messages\Orders\OrderCreatedMessage;
use App\Messages\Orders\OrderStatusChangedMessage;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderProcessingService extends AbstractOrderService
{
    public function __construct(
        private readonly MessageProducerInterface $messageProducer
    ) {}

    public function processOrderCreated(OrderProcessingData $data): Order
    {
        try {
            DB::beginTransaction();

            $order = Order::findOrFail($data?->orderId);

            $this->logOrderProcessing($order, 'creation');
            $this->updateOrderStatus($order, $data?->newStatus);
            $this->messageProducer->publishMessage(
                new OrderCreatedMessage(
                    orderId: $order?->id,
                    customerName: $order?->customer_name,
                    customerEmail: $order?->customer_email,
                    totalAmount: $order?->total_amount,
                    status: $order?->status,
                    items: $order?->items->toArray()
                )
            );

            DB::commit();
            $this->logOrderProcessingSuccess($order, 'creation');

            return $order;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logOrderProcessingError($data?->orderId, 'creation', $e);
            throw $e;
        }
    }

    public function processOrderStatusChanged(OrderProcessingData $data): Order
    {
        try {
            DB::beginTransaction();

            $order = Order::findOrFail($data?->orderId);

            $this->logOrderProcessing($order, 'status change', [
                'old_status' => $data?->oldStatus,
                'new_status' => $data?->newStatus
            ]);
            $this->validateStatusTransition($data?->oldStatus, $data?->newStatus);
            $this->updateOrderStatus($order, $data?->newStatus);
            $this->messageProducer->publishMessage(
                new OrderStatusChangedMessage(
                    orderId: $order?->id,
                    oldStatus: $data?->oldStatus,
                    newStatus: $data?->newStatus
                )
            );

            DB::commit();
            $this->logOrderProcessingSuccess($order, 'status change', [
                'new_status' => $data?->newStatus
            ]);

            return $order;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logOrderProcessingError($data?->orderId, 'status change', $e, [
                'old_status' => $data?->oldStatus,
                'new_status' => $data?->newStatus
            ]);
            throw $e;
        }
    }

    private function updateOrderStatus(Order $order, string $newStatus): void
    {
        $order?->update(['status' => $newStatus]);
    }

    private function logOrderProcessing(Order $order, string $operation, array $context = []): void
    {
        Log::info(
            "Processing order {$operation}", 
            array_merge(
                ['order_id' => $order?->id], 
                $context
            )
        );
    }

    private function logOrderProcessingSuccess(Order $order, string $operation, array $context = []): void
    {
        Log::info(
            "Order {$operation} processed successfully", 
            array_merge(
                ['order_id' => $order?->id], 
                $context
            )
        );
    }

    private function logOrderProcessingError(int $orderId, string $operation, \Exception $e, array $context = []): void
    {
        Log::error(
            "Error processing order {$operation}", 
            array_merge(
                [
                    'order_id' => $orderId,
                    'error' => $e?->getMessage(),
                    'trace' => $e?->getTraceAsString()
                ], 
                $context
            )
        );
    }
} 