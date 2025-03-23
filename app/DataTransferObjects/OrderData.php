<?php

namespace App\DataTransferObjects;

use Spatie\LaravelData\Data;

class OrderData extends Data
{
    public function __construct(
        public readonly string $customer_name,
        public readonly string $customer_email,
        public readonly array $items,
        public readonly string $status = 'pending',
        public readonly ?float $total_amount = null,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            customer_name: $data['customer_name'],
            customer_email: $data['customer_email'],
            items: $data['items'],
            status: $data['status'] ?? 'pending',
            total_amount: isset($data['total_amount']) ? (float)$data['total_amount'] : null,
        );
    }
} 