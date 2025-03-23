<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\OrderProcessingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessOrderJob extends AbstractOrderJob
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(Order $order)
    {
        parent::__construct($order);
    }

    public function handle(OrderProcessingService $processingService): void
    {
        try {
            $this->logInfo('Starting order processing');

            $processingService->processOrderCreated($this->order->id);

            $this->logInfo('Order processed successfully');
        } catch (\Exception $e) {
            $this->logError('Error processing order', $e);
            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("Failed to process order", [
            'order_id' => $this->order->id,
            'status' => $this->order->status,
            'error' => $exception->getMessage()
        ]);
    }
} 