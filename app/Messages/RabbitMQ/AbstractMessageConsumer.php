<?php

namespace App\Messages\RabbitMQ;

use App\Contracts\Messages\Consumer\MessageConsumerInterface;
use App\Messages\RabbitMQ\Traits\RabbitMQRoutingTrait;
use Illuminate\Support\Facades\Log;
use PhpAmqpLib\Message\AMQPMessage;

abstract class AbstractMessageConsumer extends RabbitMQConnection implements MessageConsumerInterface
{
    use RabbitMQRoutingTrait;

    abstract protected function getExchange(): string;
    abstract protected function getRoutingKey(): string;

    public function consume(callable $callback): void
    {
        try {
            $this->channel->basic_qos(null, 1, null);
            $this->channel->basic_consume(
                $this->getQueue(),
                '',
                false,
                false,
                false,
                false,
                $callback
            );

            while ($this->channel->is_consuming()) {
                $this->channel->wait();
            }
        } catch (\Exception $e) {
            Log::error('Message consume error: ' . $e->getMessage());
            throw $e;
        }
    }
} 