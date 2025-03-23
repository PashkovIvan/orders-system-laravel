<?php

namespace App\Messages\Base;

use App\Contracts\Messages\OrderMessageInterface;

abstract class AbstractOrderMessage extends AbstractMessage implements OrderMessageInterface
{
    public function __construct(
        protected readonly int $orderId,
        string $type,
        array $data
    ) {
        parent::__construct($type, $data);
    }

    public function getOrderId(): int
    {
        return $this->orderId;
    }

    public function toArray(): array
    {
        return array_merge(
            parent::toArray(),
            ['order_id' => $this->orderId]
        );
    }
} 