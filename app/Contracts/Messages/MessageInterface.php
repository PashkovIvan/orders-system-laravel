<?php

namespace App\Contracts\Messages;

interface MessageInterface
{
    public function getType(): string;
    public function getData(): array;
    public function toArray(): array;
} 