<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Services\OrderProcessingService;
use App\DataTransferObjects\OrderProcessingData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderControllerTest extends TestCase
{
    use RefreshDatabase;

    private Order $order;

    protected function setUp(): void
    {
        parent::setUp();
        $this->order = Order::factory()->create(['status' => 'pending']);
    }

    public function test_can_create_order(): void
    {
        $response = $this->postJson('/api/v1/orders', [
            'customer_name' => 'Test Customer',
            'customer_email' => 'test@example.com',
            'items' => [
                [
                    'product_name' => 'Test Product',
                    'quantity' => 2,
                    'price' => 100,
                ],
            ],
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'customer_name',
                    'customer_email',
                    'status',
                    'total_amount',
                    'items' => [
                        '*' => [
                            'id',
                            'product_name',
                            'quantity',
                            'price',
                            'total',
                        ],
                    ],
                    'created_at',
                    'updated_at',
                ],
            ]);

        $this->assertDatabaseHas('orders', [
            'customer_name' => 'Test Customer',
            'customer_email' => 'test@example.com',
            'status' => 'processing',
            'total_amount' => 200,
        ]);

        $this->assertDatabaseHas('order_items', [
            'product_name' => 'Test Product',
            'quantity' => 2,
            'price' => 100,
            'total' => 200,
        ]);
    }

    public function test_can_get_order(): void
    {
        $this->order->items()->createMany([
            [
                'product_name' => 'Test Product 1',
                'quantity' => 2,
                'price' => 100,
                'total' => 200,
            ],
            [
                'product_name' => 'Test Product 2',
                'quantity' => 1,
                'price' => 150,
                'total' => 150,
            ],
        ]);

        $response = $this->getJson("/api/v1/orders/{$this->order->id}");

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'customer_name',
                    'customer_email',
                    'status',
                    'total_amount',
                    'items' => [
                        '*' => [
                            'id',
                            'product_name',
                            'quantity',
                            'price',
                            'total',
                        ],
                    ],
                    'created_at',
                    'updated_at',
                ],
            ]);
    }

    public function test_can_list_orders(): void
    {
        Order::factory()->count(3)->create()->each(function ($order) {
            $order->items()->createMany([
                [
                    'product_name' => 'Test Product 1',
                    'quantity' => 2,
                    'price' => 100,
                    'total' => 200,
                ],
                [
                    'product_name' => 'Test Product 2',
                    'quantity' => 1,
                    'price' => 150,
                    'total' => 150,
                ],
            ]);
        });

        $response = $this->getJson('/api/v1/orders');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'customer_name',
                        'customer_email',
                        'status',
                        'total_amount',
                        'items',
                        'created_at',
                        'updated_at',
                    ],
                ],
            ]);
    }

    public function test_can_update_order_status(): void
    {
        $response = $this->patchJson("/api/v1/orders/{$this->order->id}/status", [
            'status' => 'processing',
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'status',
                    'message',
                ],
            ])
            ->assertJson([
                'data' => [
                    'id' => $this->order->id,
                    'status' => 'processing',
                    'message' => 'Order status updated successfully',
                ],
            ]);

        $this->assertDatabaseHas('orders', [
            'id' => $this->order->id,
            'status' => 'processing',
        ]);
    }

    public function test_validates_order_creation(): void
    {
        $response = $this->postJson('/api/v1/orders', [
            'customer_name' => '',
            'customer_email' => 'invalid-email',
            'items' => [],
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors([
                'customer_name',
                'customer_email',
                'items',
            ]);
    }

    public function test_validates_order_status_update(): void
    {
        $response = $this->patchJson("/api/v1/orders/{$this->order->id}/status", [
            'status' => 'invalid-status',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['status']);
    }

    public function test_cannot_make_invalid_status_transition(): void
    {
        $this->order->update(['status' => 'completed']);

        $response = $this->patchJson("/api/v1/orders/{$this->order->id}/status", [
            'status' => 'processing',
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'error' => [
                    'message' => 'Invalid status transition from completed to processing',
                ],
            ]);
    }
} 