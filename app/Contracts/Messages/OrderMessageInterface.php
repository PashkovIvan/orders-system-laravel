<?php

namespace App\Contracts\Messages;

interface OrderMessageInterface extends MessageInterface
{
    public function getOrderId(): int;
} 