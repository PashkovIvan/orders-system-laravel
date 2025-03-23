<?php

namespace App\Messages\RabbitMQ;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Channel\AMQPChannel;
use Illuminate\Support\Facades\Log;

class RabbitMQConnection
{
    protected AMQPStreamConnection $connection;
    protected AMQPChannel $channel;
    protected string $queue;
    protected string $exchange;

    public function __construct()
    {
        $this->queue = config('queue.connections.rabbitmq.queue');
        $this->exchange = config('queue.connections.rabbitmq.exchange');
        
        $this->connect();
    }

    protected function connect(): void
    {
        try {
            $this->connection = new AMQPStreamConnection(
                config('queue.connections.rabbitmq.host'),
                config('queue.connections.rabbitmq.port'),
                config('queue.connections.rabbitmq.user'),
                config('queue.connections.rabbitmq.password'),
                config('queue.connections.rabbitmq.vhost')
            );

            $this->channel = $this->connection->channel();
            
            $this->channel->queue_declare($this->queue, false, true, false, false);
            $this->channel->exchange_declare($this->exchange, 'direct', false, true, false);
            $this->channel->queue_bind($this->queue, $this->exchange, $this->queue);
        } catch (\Exception $e) {
            Log::error('RabbitMQ connection error: ' . $e->getMessage());
            throw $e;
        }
    }

    public function __destruct()
    {
        if (isset($this->channel)) {
            $this->channel->close();
        }
        if (isset($this->connection)) {
            $this->connection->close();
        }
    }
} 