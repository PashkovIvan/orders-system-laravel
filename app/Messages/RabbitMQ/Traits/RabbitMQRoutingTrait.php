<?php

namespace App\Messages\RabbitMQ\Traits;

trait RabbitMQRoutingTrait
{
    abstract protected function getExchange(): string;
    abstract protected function getRoutingKey(): string;

    protected function getQueue(): string
    {
        return $this->getRoutingKey();
    }
} 