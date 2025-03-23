<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('ru_RU');

        // Создаем 10,000 заказов
        for ($i = 0; $i < 10000; $i++) {
            $order = Order::create([
                'customer_name' => $faker->name,
                'customer_email' => $faker->email,
                'status' => $faker->randomElement(['pending', 'processing', 'completed', 'cancelled']),
                'total_amount' => 0,
                'created_at' => $faker->dateTimeBetween('-1 year', 'now'),
                'updated_at' => $faker->dateTimeBetween('-1 year', 'now'),
            ]);

            // Создаем 1-5 товаров для каждого заказа
            $itemsCount = $faker->numberBetween(1, 5);
            $orderTotal = 0;

            for ($j = 0; $j < $itemsCount; $j++) {
                $quantity = $faker->numberBetween(1, 10);
                $price = $faker->randomFloat(2, 10, 1000);
                $total = $quantity * $price;
                $orderTotal += $total;

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_name' => $faker->productName,
                    'quantity' => $quantity,
                    'price' => $price,
                    'total' => $total,
                ]);
            }

            // Обновляем общую сумму заказа
            $order->update(['total_amount' => $orderTotal]);
        }
    }
} 