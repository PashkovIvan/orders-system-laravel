<?php

namespace App\Messages\RabbitMQ;

use App\Contracts\Messages\Producer\MessageProducerInterface;
use App\Contracts\Messages\MessageInterface;
use App\Messages\RabbitMQ\Traits\RabbitMQRoutingTrait;
use PhpAmqpLib\Message\AMQPMessage;
use Illuminate\Support\Facades\Log;

abstract class AbstractMessageProducer extends RabbitMQConnection implements MessageProducerInterface
{
    use RabbitMQRoutingTrait;

    public function publish(array $data, string $routingKey = null): void
    {
        try {
            $message = new AMQPMessage(
                json_encode($data),
                [
                    'content_type' => 'application/json',
                    'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT
                ]
            );

            $this->channel->basic_publish(
                $message,
                $this->getExchange(),
                $routingKey ?? $this->getRoutingKey()
            );
        } catch (\Exception $e) {
            Log::error('Message publish error: ' . $e->getMessage());
            throw $e;
        }
    }

    public function publishMessage(MessageInterface $message): void
    {
        $this->publish($message->toArray());
    }
} 