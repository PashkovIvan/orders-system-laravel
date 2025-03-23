<?php

namespace App\Messages\Orders;

use App\Contracts\Messages\OrderMessageInterface;
use App\Messages\AbstractMessage;

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