<?php

namespace Database\Factories;

use App\Models\OrderItem;
use App\DataTransferObjects\OrderItemData;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderItemFactory extends Factory
{
    protected $model = OrderItem::class;

    public function definition(): array
    {
        $quantity = $this->faker->numberBetween(1, 10);
        $price = $this->faker->numberBetween(10, 1000);

        return [
            'order_id' => null, // Будет установлено при создании
            'product_name' => $this->faker->words(3, true),
            'quantity' => $quantity,
            'price' => $price,
            'total' => $quantity * $price,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    public function toOrderItemData(): OrderItemData
    {
        $attributes = $this->definition();
        
        return new OrderItemData(
            productName: $attributes['product_name'],
            quantity: $attributes['quantity'],
            price: $attributes['price'],
            total: $attributes['total']
        );
    }
} 