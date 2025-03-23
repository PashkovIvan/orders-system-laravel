<?php

namespace App\Contracts\Messages\Consumer;

interface MessageConsumerInterface
{
    public function consume(callable $callback): void;
} 