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
        $this->orderService = new OrderService();
    }

    public function test_can_create_order()
    {
        $orderData = new OrderData(
            customer_name: 'Test Customer',
            customer_email: 'test@example.com',
            customer_phone: '+1234567890',
            items: [
                new OrderItemData(
                    product_name: 'Test Product',
                    quantity: 2,
                    price: 100,
                    total: 200
                )
            ],
            notes: 'Test notes'
        );

        $order = $this->orderService->createOrder($orderData);

        $this->assertInstanceOf(Order::class, $order);
        $this->assertEquals('Test Customer', $order->customer_name);
        $this->assertEquals('test@example.com', $order->customer_email);
        $this->assertEquals('+1234567890', $order->customer_phone);
        $this->assertEquals('pending', $order->status);
        $this->assertEquals('Test notes', $order->notes);
        $this->assertEquals(200, $order->total_amount);

        $this->assertCount(1, $order->items);
        $this->assertEquals('Test Product', $order->items->first()->product_name);
        $this->assertEquals(2, $order->items->first()->quantity);
        $this->assertEquals(100, $order->items->first()->price);
        $this->assertEquals(200, $order->items->first()->total);
    }

    public function test_can_update_order_status()
    {
        $order = Order::factory()->create(['status' => 'pending']);
        
        $updatedOrder = $this->orderService->updateOrderStatus($order, 'processing');

        $this->assertEquals('processing', $updatedOrder->status);
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
        $this->assertCount(2, $orders[0]['items']);
    }
} 