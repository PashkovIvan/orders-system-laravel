<?php

namespace App\DataTransferObjects;

use Spatie\LaravelData\Data;

class OrderProcessingData extends Data
{
    public function __construct(
        public readonly int $orderId,
        public readonly ?string $oldStatus = null,
        public readonly ?string $newStatus = null
    ) {}

    public static function forStatusChange(
        int $orderId,
        string $oldStatus,
        string $newStatus
    ): self {
        return new self(
            orderId: $orderId,
            oldStatus: $oldStatus,
            newStatus: $newStatus
        );
    }
} 