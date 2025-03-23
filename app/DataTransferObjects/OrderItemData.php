<?php

namespace App\DataTransferObjects;

use Spatie\LaravelData\Data;

class OrderItemData extends Data
{
    public function __construct(
        public readonly string $product_name,
        public readonly int $quantity,
        public readonly float $price,
        public readonly ?float $total = null,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            product_name: $data['product_name'],
            quantity: $data['quantity'],
            price: $data['price'],
            total: $data['quantity'] * $data['price'],
        );
    }
} 