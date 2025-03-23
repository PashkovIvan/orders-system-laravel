<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use Database\Seeders\OrderSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class OrderLoadTest extends TestCase
{
    use RefreshDatabase;

    public function test_handle_large_number_of_orders(): void
    {
        // Запускаем сидер для создания 10000 заказов
        $this->seed(OrderSeeder::class);

        // Проверяем количество созданных заказов
        $this->assertEquals(10000, Order::count());

        // Проверяем время выполнения запроса списка заказов
        $startTime = microtime(true);
        $response = $this->getJson('/api/v1/orders');
        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        $response->assertStatus(200);
        $this->assertLessThan(2.0, $executionTime); // Должно выполняться менее чем за 2 секунды

        // Проверяем количество запросов к базе данных
        DB::enableQueryLog();
        $this->getJson('/api/v1/orders');
        $queries = DB::getQueryLog();
        $this->assertLessThan(10, count($queries)); // Должно быть менее 10 запросов

        // Проверяем использование памяти
        $memoryBefore = memory_get_usage(true);
        $this->getJson('/api/v1/orders');
        $memoryAfter = memory_get_usage(true);
        $memoryUsed = $memoryAfter - $memoryBefore;
        $this->assertLessThan(100 * 1024 * 1024, $memoryUsed); // Должно использовать менее 100MB памяти

        // Проверяем пагинацию
        $response = $this->getJson('/api/v1/orders?page=1&per_page=100');
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'meta' => [
                    'current_page',
                    'from',
                    'last_page',
                    'per_page',
                    'to',
                    'total'
                ]
            ]);

        // Проверяем поиск по большому набору данных
        $startTime = microtime(true);
        $response = $this->getJson('/api/v1/orders?search=test');
        $endTime = microtime(true);
        $searchTime = $endTime - $startTime;
        $this->assertLessThan(1.0, $searchTime); // Поиск должен выполняться менее чем за 1 секунду

        // Проверяем фильтрацию по статусу
        $startTime = microtime(true);
        $response = $this->getJson('/api/v1/orders?status=pending');
        $endTime = microtime(true);
        $filterTime = $endTime - $startTime;
        $this->assertLessThan(1.0, $filterTime); // Фильтрация должна выполняться менее чем за 1 секунду

        // Проверяем сортировку
        $startTime = microtime(true);
        $response = $this->getJson('/api/v1/orders?sort=created_at&order=desc');
        $endTime = microtime(true);
        $sortTime = $endTime - $startTime;
        $this->assertLessThan(1.0, $sortTime); // Сортировка должна выполняться менее чем за 1 секунду
    }

    public function test_concurrent_requests_with_large_dataset(): void
    {
        // Запускаем сидер для создания 10000 заказов
        $this->seed(OrderSeeder::class);

        $startTime = microtime(true);
        $responses = [];

        // Выполняем 10 параллельных запросов
        for ($i = 0; $i < 10; $i++) {
            $responses[] = $this->getJson('/api/v1/orders');
        }

        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        foreach ($responses as $response) {
            $response->assertStatus(200);
        }

        $this->assertLessThan(5.0, $executionTime); // Все запросы должны выполниться менее чем за 5 секунд
    }

    public function test_cache_performance_with_large_dataset(): void
    {
        // Запускаем сидер для создания 10000 заказов
        $this->seed(OrderSeeder::class);

        // Первый запрос (без кэша)
        $startTime = microtime(true);
        $response1 = $this->getJson('/api/v1/orders');
        $endTime = microtime(true);
        $firstRequestTime = $endTime - $startTime;

        // Второй запрос (с кэшем)
        $startTime = microtime(true);
        $response2 = $this->getJson('/api/v1/orders');
        $endTime = microtime(true);
        $secondRequestTime = $endTime - $startTime;

        $response1->assertStatus(200);
        $response2->assertStatus(200);
        $this->assertLessThan($firstRequestTime, $secondRequestTime); // Второй запрос должен быть быстрее
    }
} 