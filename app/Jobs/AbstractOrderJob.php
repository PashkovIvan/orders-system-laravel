<?php

namespace App\Jobs;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

abstract class AbstractOrderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected function __construct(
        protected Order $order
    ) {}

    protected function logInfo(string $message, array $context = []): void
    {
        Log::info($message, array_merge([
            'order_id' => $this->order->id,
            'status' => $this->order->status
        ], $context));
    }

    protected function logError(string $message, \Throwable $exception = null, array $context = []): void
    {
        Log::error($message, array_merge([
            'order_id' => $this->order->id,
            'status' => $this->order->status,
            'error' => $exception?->getMessage()
        ], $context));
    }

    public function failed(\Throwable $exception): void
    {
        $this->logError('Job execution failed', $exception);
    }
} 