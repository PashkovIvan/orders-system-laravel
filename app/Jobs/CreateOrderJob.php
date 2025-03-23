<?php

namespace App\Jobs;

use App\Contracts\MessageProducerInterface;
use App\Messages\Orders\OrderCreatedMessage;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CreateOrderJob extends AbstractOrderJob
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(Order $order)
    {
        parent::__construct($order);
    }

    public function handle(MessageProducerInterface $producer): void
    {
        try {
            $this->logInfo("Processing order creation");

            // Создаем сообщение о создании заказа
            $message = new OrderCreatedMessage(
                orderId: $this->order->id,
                customerName: $this->order->customer_name,
                customerEmail: $this->order->customer_email,
                totalAmount: $this->order->total_amount,
                status: $this->order->status,
                items: $this->order->items->toArray()
            );

            // Отправляем сообщение в очередь
            $producer->publishMessage($message);

            $this->logInfo("Order creation processed successfully");
        } catch (\Exception $e) {
            $this->logError("Error processing order creation", $e);
            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("Failed to process order creation", [
            'order_id' => $this->order->id,
            'error' => $exception->getMessage()
        ]);
    }
} 