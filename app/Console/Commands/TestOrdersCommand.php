<?php

namespace App\Console\Commands;

use App\Messages\Producers\OrderMessageProducer;
use App\Models\Order;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class TestOrdersCommand extends Command
{
    protected $signature = 'orders:test';
    protected $description = 'Test orders processing system';

    public function __construct(
        private readonly OrderMessageProducer $producer
    ) {
        parent::__construct();
    }

    public function handle(): void
    {
        $this->info('Starting orders system test...');

        try {
            // Тест 1: Создание заказа
            $this->info('Test 1: Creating new order...');
            $orderId = 1;
            $this->producer->publishMessage([
                'type' => 'order.created',
                'order_id' => $orderId,
                'timestamp' => now()->toIso8601String()
            ]);
            $this->info('✓ Order creation message sent');
            sleep(2); // Ждем обработки

            // Тест 2: Изменение статуса
            $this->info('Test 2: Changing order status...');
            $this->producer->publishMessage([
                'type' => 'order.status_changed',
                'order_id' => $orderId,
                'old_status' => 'pending',
                'new_status' => 'processing',
                'timestamp' => now()->toIso8601String()
            ]);
            $this->info('✓ Status change message sent');
            sleep(2);

            // Тест 3: Некорректное сообщение
            $this->info('Test 3: Sending invalid message...');
            $this->producer->publishMessage([
                'type' => 'unknown.action',
                'data' => 'some data'
            ]);
            $this->info('✓ Invalid message sent');
            sleep(2);

            // Тест 4: Пакетная обработка
            $this->info('Test 4: Batch processing...');
            for ($i = 2; $i <= 5; $i++) {
                $this->producer->publishMessage([
                    'type' => 'order.created',
                    'order_id' => $i,
                    'timestamp' => now()->toIso8601String()
                ]);
            }
            $this->info('✓ Batch messages sent');

            $this->info('All test messages sent successfully!');
            $this->info('Check the logs for processing results.');
            
        } catch (\Exception $e) {
            Log::error('Test error: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            $this->error('Test failed: ' . $e->getMessage());
        }
    }
} 