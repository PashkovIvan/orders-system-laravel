<?php

namespace App\Jobs;

use App\Contracts\Messages\Producer\MessageProducerInterface;
use App\Messages\Orders\OrderStatusChangedMessage;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class UpdateOrderStatusJob extends AbstractOrderJob
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        Order $order,
        private readonly string $oldStatus,
        private readonly string $newStatus
    ) {
        parent::__construct($order);
    }

    public function handle(MessageProducerInterface $producer): void
    {
        try {
            $this->logInfo('Processing order status update', [
                'old_status' => $this->oldStatus,
                'new_status' => $this->newStatus
            ]);

            $message = new OrderStatusChangedMessage(
                orderId: $this->order->id,
                oldStatus: $this->oldStatus,
                newStatus: $this->newStatus
            );

            $producer->publishMessage($message);

            $this->logInfo('Order status update processed successfully', [
                'old_status' => $this->oldStatus,
                'new_status' => $this->newStatus
            ]);
        } catch (\Exception $e) {
            $this->logError('Error processing order status update', $e, [
                'old_status' => $this->oldStatus,
                'new_status' => $this->newStatus
            ]);
            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("Failed to process order status update", [
            'order_id' => $this->order->id,
            'old_status' => $this->oldStatus,
            'new_status' => $this->newStatus,
            'error' => $exception->getMessage()
        ]);
    }
} 