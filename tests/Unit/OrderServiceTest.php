<?php

namespace Tests\Unit;

use App\DataTransferObjects\OrderData;
use App\DataTransferObjects\OrderItemData;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderServiceTest extends TestCase
{
    use RefreshDatabase;

    private OrderService $orderService;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Мокаем OrderMessageProducer только для теста создания заказа
        if ($this->getName() === 'test_can_create_order') {
            $this->mock(\App\Messages\Producers\OrderMessageProducer::class, function ($mock) {
                $mock->shouldReceive('publishMessage')
                    ->once()
                    ->withArgs(function ($message) {
                        return $message instanceof \App\Contracts\Messages\MessageInterface;
                    })
                    ->andReturn(true);
            });
        } else {
            $this->mock(\App\Messages\Producers\OrderMessageProducer::class, function ($mock) {
                $mock->shouldReceive('publishMessage')->never();
            });
        }
        
        $this->orderService = new OrderService();
    }

    public function test_can_create_order()
    {
        $orderData = new OrderData(
            customer_name: 'Test Customer',
            customer_email: 'test@example.com',
            items: [
                new OrderItemData(
                    product_name: 'Test Product',
                    quantity: 2,
                    price: 100,
                    total: 200
                )
            ]
        );

        $order = $this->orderService->createOrder($orderData);

        $this->assertInstanceOf(Order::class, $order);
        $this->assertEquals('Test Customer', $order->customer_name);
        $this->assertEquals('test@example.com', $order->customer_email);
        $this->assertEquals('pending', $order->status);
        $this->assertEquals(200, $order->total_amount);

        $this->assertCount(1, $order->items);
        $this->assertEquals('Test Product', $order->items->first()->product_name);
        $this->assertEquals(2, $order->items->first()->quantity);
        $this->assertEquals(100, $order->items->first()->price);
        $this->assertEquals(200, $order->items->first()->total);
    }

    public function test_can_get_order()
    {
        $order = Order::factory()->create();
        OrderItem::factory()->count(3)->create(['order_id' => $order->id]);

        $retrievedOrder = $this->orderService->getOrder($order->id);

        $this->assertInstanceOf(Order::class, $retrievedOrder);
        $this->assertEquals($order->id, $retrievedOrder->id);
        $this->assertCount(3, $retrievedOrder->items);
    }

    public function test_can_list_orders()
    {
        Order::factory()->count(3)->create()->each(function ($order) {
            OrderItem::factory()->count(2)->create(['order_id' => $order->id]);
        });

        $orders = $this->orderService->listOrders();

        $this->assertCount(3, $orders);
        $this->assertCount(2, $orders->first()->items);
    }
} 