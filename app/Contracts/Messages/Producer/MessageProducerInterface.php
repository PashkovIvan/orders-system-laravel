<?php

namespace App\Contracts\Messages\Producer;

use App\Contracts\Messages\MessageInterface;

interface MessageProducerInterface
{
    public function publish(array $data, string $routingKey = null): void;
    public function publishMessage(MessageInterface $message): void;
} 