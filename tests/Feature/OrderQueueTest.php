<?php

namespace Tests\Feature;

use App\Jobs\ProcessOrderJob;
use App\Jobs\UpdateOrderStatusJob;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\OrderProcessingService;
use App\DataTransferObjects\OrderProcessingData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class OrderQueueTest extends TestCase
{
    use RefreshDatabase;

    private Order $order;

    protected function setUp(): void
    {
        parent::setUp();
        $this->order = Order::factory()->create();
        OrderItem::factory()->count(3)->create(['order_id' => $this->order->id]);
    }

    public function test_order_processing_queue(): void
    {
        Bus::fake();

        ProcessOrderJob::dispatch($this->order);

        Bus::assertDispatched(ProcessOrderJob::class, function ($job) {
            return $job->order->id === $this->order->id;
        });
    }

    public function test_order_status_update_queue(): void
    {
        Bus::fake();

        $this->order->update(['status' => 'pending']);
        UpdateOrderStatusJob::dispatch($this->order, 'processing');

        Bus::assertDispatched(UpdateOrderStatusJob::class, function ($job) {
            return $job->order->id === $this->order->id && $job->status === 'processing';
        });
    }

    public function test_queue_retry_on_failure(): void
    {
        Queue::fake();

        ProcessOrderJob::dispatch($this->order)->onQueue('orders')->retry(3);

        Queue::assertPushedOn('orders', ProcessOrderJob::class, function ($job) {
            return $job->order->id === $this->order->id && $job->attempts === 0;
        });
    }

    public function test_queue_delay(): void
    {
        Queue::fake();

        ProcessOrderJob::dispatch($this->order)->delay(now()->addMinutes(5));

        Queue::assertPushed(ProcessOrderJob::class, function ($job) {
            return $job->order->id === $this->order->id && $job->delay->diffInMinutes(now()) === 5;
        });
    }

    public function test_queue_chaining(): void
    {
        Bus::fake();

        ProcessOrderJob::withChain([
            new UpdateOrderStatusJob($this->order, 'processing'),
            new UpdateOrderStatusJob($this->order, 'completed')
        ])->dispatch($this->order);

        Bus::assertChained([
            ProcessOrderJob::class,
            UpdateOrderStatusJob::class,
            UpdateOrderStatusJob::class
        ]);
    }

    public function test_queue_batch(): void
    {
        Bus::fake();

        $orders = Order::factory()->count(5)->create();
        foreach ($orders as $order) {
            OrderItem::factory()->count(3)->create(['order_id' => $order->id]);
        }

        $batch = Bus::batch([
            new ProcessOrderJob($orders[0]),
            new ProcessOrderJob($orders[1]),
            new ProcessOrderJob($orders[2]),
            new ProcessOrderJob($orders[3]),
            new ProcessOrderJob($orders[4])
        ])->dispatch();

        Bus::assertBatchCount(1);
        Bus::assertBatchSize(5);
    }

    public function test_queue_rate_limiting(): void
    {
        Queue::fake();

        ProcessOrderJob::dispatch($this->order)->throttle(10);

        Queue::assertPushed(ProcessOrderJob::class, function ($job) {
            return $job->order->id === $this->order->id && $job->throttle === 10;
        });
    }

    public function test_queue_middleware(): void
    {
        Queue::fake();

        ProcessOrderJob::dispatch($this->order)->middleware(['throttle:10']);

        Queue::assertPushed(ProcessOrderJob::class, function ($job) {
            return $job->order->id === $this->order->id && in_array('throttle:10', $job->middleware);
        });
    }

    public function test_queue_processing_service_integration(): void
    {
        $this->order->update(['status' => 'pending']);

        $processingService = $this->app->make(OrderProcessingService::class);
        $processingData = OrderProcessingData::forStatusChange(
            orderId: $this->order->id,
            oldStatus: 'pending',
            newStatus: 'processing'
        );
        
        $processedOrder = $processingService->processOrderCreated($processingData);

        $this->assertEquals('processing', $processedOrder->status);
        $this->assertEquals($this->order->id, $processedOrder->id);
    }

    public function test_invalid_status_transition(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $processingService = $this->app->make(OrderProcessingService::class);
        $processingData = OrderProcessingData::forStatusChange(
            orderId: $this->order->id,
            oldStatus: 'completed',
            newStatus: 'processing'
        );
        
        $processingService->processOrderStatusChanged($processingData);
    }
} 