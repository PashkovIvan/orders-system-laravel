<?php

namespace App\Services;

use App\DataTransferObjects\OrderProcessingData;
use App\Contracts\Messages\Producer\MessageProducerInterface;
use App\Http\Responses\OrderProcessingResponse;
use App\Jobs\ProcessOrderJob;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderProcessingService
{
    private const STATUS_TRANSITIONS = [
        'pending' => ['processing', 'cancelled'],
        'processing' => ['completed', 'failed'],
        'completed' => [],
        'failed' => ['processing'],
        'cancelled' => []
    ];

    public function __construct(
        private readonly MessageProducerInterface $messageProducer
    ) {}

    public function processOrderCreated(OrderProcessingData $data): Order
    {
        try {
            DB::beginTransaction();

            $order = Order::findOrFail($data->orderId);
            $this->logOrderProcessing($order, 'creation');

            // Обновляем статус заказа на "processing"
            $this->updateOrderStatus($order, $data->newStatus);

            // Запускаем job для обработки заказа
            ProcessOrderJob::dispatch($order);

            DB::commit();
            $this->logOrderProcessingSuccess($order, 'creation');

            return $order;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logOrderProcessingError($data->orderId, 'creation', $e);
            throw $e;
        }
    }

    public function processOrderStatusChanged(OrderProcessingData $data): Order
    {
        try {
            DB::beginTransaction();

            $order = Order::findOrFail($data->orderId);
            $this->logOrderProcessing($order, 'status change', [
                'old_status' => $data->oldStatus,
                'new_status' => $data->newStatus
            ]);

            $this->validateStatusTransition($data->oldStatus, $data->newStatus);
            $this->updateOrderStatus($order, $data->newStatus);

            DB::commit();
            $this->logOrderProcessingSuccess($order, 'status change', [
                'new_status' => $data->newStatus
            ]);

            return $order;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logOrderProcessingError($data->orderId, 'status change', $e, [
                'old_status' => $data->oldStatus,
                'new_status' => $data->newStatus
            ]);
            throw $e;
        }
    }

    private function validateStatusTransition(?string $oldStatus, string $newStatus): void
    {
        if (
            !isset(self::STATUS_TRANSITIONS[$oldStatus]) 
            || !in_array($newStatus, self::STATUS_TRANSITIONS[$oldStatus])
            ) {
            throw new \InvalidArgumentException(
                "Invalid status transition from {$oldStatus} to {$newStatus}"
            );
        }
    }

    private function updateOrderStatus(Order $order, string $newStatus): void
    {
        $order->update(['status' => $newStatus]);
    }

    private function logOrderProcessing(Order $order, string $operation, array $context = []): void
    {
        Log::info("Processing order {$operation}", array_merge([
            'order_id' => $order->id
        ], $context));
    }

    private function logOrderProcessingSuccess(Order $order, string $operation, array $context = []): void
    {
        Log::info("Order {$operation} processed successfully", array_merge([
            'order_id' => $order->id
        ], $context));
    }

    private function logOrderProcessingError(int $orderId, string $operation, \Exception $e, array $context = []): void
    {
        Log::error("Error processing order {$operation}", array_merge([
            'order_id' => $orderId,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], $context));
    }
} 