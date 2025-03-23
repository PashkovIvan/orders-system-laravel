<?php

namespace Database\Factories;

use App\Models\Order;
use App\DataTransferObjects\OrderData;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'customer_name' => $this->faker->name(),
            'customer_email' => $this->faker->email(),
            'status' => $this->faker->randomElement(['pending', 'processing', 'completed', 'cancelled']),
            'total_amount' => $this->faker->numberBetween(100, 10000),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    public function toOrderData(): OrderData
    {
        $attributes = $this->definition();
        
        return new OrderData(
            customerName: $attributes['customer_name'],
            customerEmail: $attributes['customer_email'],
            status: $attributes['status'],
            totalAmount: $attributes['total_amount']
        );
    }
} 