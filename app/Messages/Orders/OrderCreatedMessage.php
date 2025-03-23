<?php

namespace App\Messages\Orders;

use App\Models\Order;
use App\Messages\Base\AbstractOrderMessage;

class OrderCreatedMessage extends AbstractOrderMessage
{
    public function __construct(
        int $orderId,
        string $customerName,
        string $customerEmail,
        float $totalAmount,
        string $status,
        array $items
    ) {
        parent::__construct(
            $orderId,
            'order.created',
            [
                'customer_name' => $customerName,
                'customer_email' => $customerEmail,
                'total_amount' => $totalAmount,
                'status' => $status,
                'items_count' => count($items),
                'items' => array_map(fn($item) => [
                    'product_name' => $item['product_name'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'total' => $item['total']
                ], $items)
            ]
        );
    }
} 