<?php

namespace App\Console\Commands;

use App\Messages\Consumers\OrderMessageConsumer;
use App\Services\OrderProcessingService;
use App\DataTransferObjects\OrderProcessingData;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ConsumeOrdersCommand extends Command
{
    protected $signature = 'orders:consume';
    protected $description = 'Consume orders from RabbitMQ queue';

    private const MESSAGE_TYPES = [
        'order.created',
        'order.status_changed'
    ];

    public function __construct(
        private readonly OrderMessageConsumer $consumer,
        private readonly OrderProcessingService $processor
    ) {
        parent::__construct();
    }

    public function handle(): void
    {
        $this->info('Starting orders consumer...');

        try {
            $this->startConsuming();
        } catch (\Exception $e) {
            $this->handleConsumerError($e);
            $this->retryConsuming();
        }
    }

    private function startConsuming(): void
    {
        $this->consumer->consume(function ($message) {
            try {
                $data = $this->parseMessage($message);
                if (!$data) {
                    return;
                }

                $this->processMessage($data, $message);
            } catch (\Exception $e) {
                $this->handleMessageError($e);
                $message->reject();
            }
        });
    }

    private function parseMessage($message): ?array
    {
        $data = json_decode($message->getBody(), true);
        
        if (!$data) {
            Log::warning('Invalid message format: empty or invalid JSON');
            $message->reject();
            return null;
        }

        if (!in_array($data['type'] ?? '', self::MESSAGE_TYPES)) {
            Log::warning('Unknown message type: ' . ($data['type'] ?? 'undefined'));
            $message->reject();
            return null;
        }

        return $data;
    }

    private function processMessage(array $data, $message): void
    {
        $processingData = $this->createProcessingData($data);

        switch ($data['type']) {
            case 'order.created':
                $this->processor->processOrderCreated($processingData);
                break;
            case 'order.status_changed':
                $this->processor->processOrderStatusChanged($processingData);
                break;
        }

        $message->ack();
    }

    private function createProcessingData(array $data): OrderProcessingData
    {
        return OrderProcessingData::forStatusChange(
            orderId: $data['order_id'],
            oldStatus: $data['type'] === 'order.created' ? 'pending' : $data['old_status'],
            newStatus: $data['type'] === 'order.created' ? 'processing' : $data['new_status']
        );
    }

    private function handleMessageError(\Exception $e): void
    {
        Log::error('Error processing message: ' . $e->getMessage(), [
            'exception' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]);
    }

    private function handleConsumerError(\Exception $e): void
    {
        Log::error('Consumer error: ' . $e->getMessage(), [
            'exception' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]);
        $this->error('Consumer error: ' . $e->getMessage());
    }

    private function retryConsuming(): void
    {
        sleep(5);
        $this->handle();
    }
} 