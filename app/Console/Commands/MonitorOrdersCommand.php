<?php

namespace App\Console\Commands;

use App\Models\Order;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MonitorOrdersCommand extends Command
{
    protected $signature = 'orders:monitor';
    protected $description = 'Monitor orders processing';

    public function handle(): void
    {
        $this->info('Starting orders monitoring...');
        $this->info('Press Ctrl+C to stop');

        while (true) {
            $this->line("\n" . str_repeat('-', 50));
            $this->info('Orders Status Summary:');
            
            // Статистика по статусам
            $stats = DB::table('orders')
                ->select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->get();

            $total = 0;
            foreach ($stats as $stat) {
                $total += $stat->count;
                $this->line("- {$stat->status}: {$stat->count}");
            }
            $this->info("Total orders: {$total}");

            // Последние обработанные заказы
            $this->info("\nLast 5 processed orders:");
            $lastOrders = Order::latest()->take(5)->get();
            
            foreach ($lastOrders as $order) {
                $this->line(sprintf(
                    "ID: %d | Status: %s | Updated: %s",
                    $order->id,
                    $order->status,
                    $order->updated_at->diffForHumans()
                ));
            }

            // Проверка очереди RabbitMQ
            $this->info("\nQueue Status:");
            $queueStatus = $this->getQueueStatus();
            $this->line("Messages in queue: {$queueStatus['messages']}");
            $this->line("Processing rate: {$queueStatus['rate']} msg/sec");

            sleep(5); // Обновление каждые 5 секунд
            $this->output->write("\033[H\033[2J"); // Очистка экрана
        }
    }

    private function getQueueStatus(): array
    {
        // Здесь должен быть код для получения статистики из RabbitMQ
        // Пока возвращаем тестовые данные
        return [
            'messages' => rand(0, 10),
            'rate' => number_format(rand(1, 100) / 10, 1)
        ];
    }
} 