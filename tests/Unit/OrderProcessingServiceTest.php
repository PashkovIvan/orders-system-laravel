<?php

namespace Tests\Unit;

use App\Contracts\Messages\Producer\MessageProducerInterface;
use App\DataTransferObjects\OrderProcessingData;
use App\Models\Order;
use App\Services\OrderProcessingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class OrderProcessingServiceTest extends TestCase
{
    use RefreshDatabase;

    private OrderProcessingService $processingService;
    private MessageProducerInterface $messageProducer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->messageProducer = Mockery::mock(MessageProducerInterface::class);
        $this->processingService = new OrderProcessingService($this->messageProducer);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_can_process_order_creation(): void
    {
        $order = Order::factory()->create(['status' => 'pending']);
        
        $this->messageProducer
            ->shouldReceive('publishMessage')
            ->once()
            ->withArgs(function ($message) use ($order) {
                return $message->getOrderId() === $order->id
                    && $message->getType() === 'order.created';
            });

        $processingData = OrderProcessingData::forStatusChange(
            orderId: $order->id,
            oldStatus: 'pending',
            newStatus: 'processing'
        );

        $processedOrder = $this->processingService->processOrderCreated($processingData);

        $this->assertEquals('processing', $processedOrder->status);
        $this->assertEquals($order->id, $processedOrder->id);
    }

    public function test_can_process_order_status_change(): void
    {
        $order = Order::factory()->create(['status' => 'pending']);
        
        $this->messageProducer
            ->shouldReceive('publishMessage')
            ->once()
            ->withArgs(function ($message) use ($order) {
                return $message->getOrderId() === $order->id
                    && $message->getType() === 'order.status_changed'
                    && $message->getData()['old_status'] === 'pending'
                    && $message->getData()['new_status'] === 'processing';
            });

        $processingData = OrderProcessingData::forStatusChange(
            orderId: $order->id,
            oldStatus: 'pending',
            newStatus: 'processing'
        );

        $processedOrder = $this->processingService->processOrderStatusChanged($processingData);

        $this->assertEquals('processing', $processedOrder->status);
        $this->assertEquals($order->id, $processedOrder->id);
    }

    public function test_throws_exception_for_invalid_status_transition(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $order = Order::factory()->create(['status' => 'completed']);
        
        $this->messageProducer
            ->shouldNotReceive('publishMessage');

        $processingData = OrderProcessingData::forStatusChange(
            orderId: $order->id,
            oldStatus: 'completed',
            newStatus: 'processing'
        );

        $this->processingService->processOrderStatusChanged($processingData);
    }
} 