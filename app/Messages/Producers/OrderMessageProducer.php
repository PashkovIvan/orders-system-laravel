<?php

namespace App\Messages\Producers;

use App\Messages\RabbitMQ\AbstractMessageProducer;
use App\Contracts\Messages\Producer\MessageProducerInterface;

class OrderMessageProducer extends AbstractMessageProducer
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