<?php

namespace App\Messages\Consumers;

use App\Messages\RabbitMQ\AbstractMessageConsumer;
use App\Contracts\Messages\Consumer\MessageConsumerInterface;

class OrderMessageConsumer extends AbstractMessageConsumer
{
    protected function getExchange(): string
    {
        return 'orders';
    }

    protected function getRoutingKey(): string
    {
        return 'orders';
    }
} 