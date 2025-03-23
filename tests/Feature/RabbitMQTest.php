<?php

namespace Tests\Feature;

use App\Messages\Producers\OrderMessageProducer;
use App\Messages\Consumers\OrderMessageConsumer;
use App\Messages\Orders\OrderCreatedMessage;
use App\Messages\Orders\OrderStatusChangedMessage;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RabbitMQTest extends TestCase
{
    use RefreshDatabase;

    private OrderMessageProducer $producer;
    private OrderMessageConsumer $consumer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->producer = new OrderMessageProducer();
        $this->consumer = new OrderMessageConsumer();
    }

    protected function tearDown(): void
    {
        $this->producer->close();
        $this->consumer->close();
        parent::tearDown();
    }

    public function test_order_created_message(): void
    {
        $order = Order::factory()->create();
        $message = new OrderCreatedMessage($order);

        $this->producer->publishMessage($message);

        $this->consumer->consume(function ($message) use ($order) {
            $data = json_decode($message->getBody(), true);
            
            $this->assertEquals('order.created', $data['type']);
            $this->assertEquals($order->id, $data['order_id']);
            
            $message->ack();
        });
    }

    public function test_order_status_changed_message(): void
    {
        $order = Order::factory()->create(['status' => 'pending']);
        $message = new OrderStatusChangedMessage($order, 'pending', 'processing');

        $this->producer->publishMessage($message);

        $this->consumer->consume(function ($message) use ($order) {
            $data = json_decode($message->getBody(), true);
            
            $this->assertEquals('order.status_changed', $data['type']);
            $this->assertEquals($order->id, $data['order_id']);
            $this->assertEquals('pending', $data['old_status']);
            $this->assertEquals('processing', $data['new_status']);
            
            $message->ack();
        });
    }

    public function test_message_rejection_on_error(): void
    {
        $this->producer->publish([
            'type' => 'invalid.type',
            'data' => []
        ]);

        $this->consumer->consume(function ($message) {
            $data = json_decode($message->getBody(), true);
            
            $this->assertEquals('invalid.type', $data['type']);
            
            $message->reject();
        });
    }

    public function test_publish_error_handling(): void
    {
        $this->producer->close();
        
        $this->expectException(\RuntimeException::class);
        
        $order = Order::factory()->create();
        $message = new OrderCreatedMessage($order);
        
        $this->producer->publishMessage($message);
    }

    public function test_consume_error_handling(): void
    {
        $this->consumer->close();
        
        $this->expectException(\RuntimeException::class);
        
        $this->consumer->consume(function ($message) {
            $message->ack();
        });
    }
} 