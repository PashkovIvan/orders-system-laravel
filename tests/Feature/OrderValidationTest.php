<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Services\OrderProcessingService;
use App\DataTransferObjects\OrderProcessingData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderValidationTest extends TestCase
{
    use RefreshDatabase;

    private Order $order;

    protected function setUp(): void
    {
        parent::setUp();
        $this->order = Order::factory()->create(['status' => 'pending']);
    }

    public function test_order_creation_validation(): void
    {
        $response = $this->postJson('/api/v1/orders', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors([
                'customer_name',
                'customer_email',
                'items'
            ]);
    }

    public function test_order_items_validation(): void
    {
        $response = $this->postJson('/api/v1/orders', [
            'customer_name' => 'John Doe',
            'customer_email' => 'john@example.com',
            'items' => []
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['items']);
    }

    public function test_order_item_validation(): void
    {
        $response = $this->postJson('/api/v1/orders', [
            'customer_name' => 'John Doe',
            'customer_email' => 'john@example.com',
            'items' => [
                [
                    'product_name' => '',
                    'quantity' => 0,
                    'price' => -1
                ]
            ]
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors([
                'items.0.product_name',
                'items.0.quantity',
                'items.0.price'
            ]);
    }

    public function test_order_status_validation(): void
    {
        $response = $this->patchJson("/api/v1/orders/{$this->order->id}/status", [
            'status' => 'invalid-status'
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['status']);
    }

    public function test_order_email_validation(): void
    {
        $response = $this->postJson('/api/v1/orders', [
            'customer_name' => 'John Doe',
            'customer_email' => 'invalid-email',
            'items' => [
                [
                    'product_name' => 'Product 1',
                    'quantity' => 1,
                    'price' => 10.00
                ]
            ]
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['customer_email']);
    }

    public function test_order_quantity_validation(): void
    {
        $response = $this->postJson('/api/v1/orders', [
            'customer_name' => 'John Doe',
            'customer_email' => 'john@example.com',
            'items' => [
                [
                    'product_name' => 'Product 1',
                    'quantity' => 0,
                    'price' => 10.00
                ]
            ]
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['items.0.quantity']);
    }

    public function test_order_price_validation(): void
    {
        $response = $this->postJson('/api/v1/orders', [
            'customer_name' => 'John Doe',
            'customer_email' => 'john@example.com',
            'items' => [
                [
                    'product_name' => 'Product 1',
                    'quantity' => 1,
                    'price' => -1
                ]
            ]
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['items.0.price']);
    }

    public function test_invalid_status_transition(): void
    {
        $this->order->update(['status' => 'completed']);

        $response = $this->patchJson("/api/v1/orders/{$this->order->id}/status", [
            'status' => 'processing'
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'error' => [
                    'message' => 'Invalid status transition from completed to processing'
                ]
            ]);
    }

    public function test_valid_status_transition(): void
    {
        $response = $this->patchJson("/api/v1/orders/{$this->order->id}/status", [
            'status' => 'processing'
        ]);

        $response->assertOk()
            ->assertJson([
                'data' => [
                    'id' => $this->order->id,
                    'status' => 'processing',
                    'message' => 'Order status updated successfully'
                ]
            ]);

        $this->assertDatabaseHas('orders', [
            'id' => $this->order->id,
            'status' => 'processing'
        ]);
    }
} 