<?php

namespace App\Messages\Base;

use App\Contracts\Messages\MessageInterface;

abstract class AbstractMessage implements MessageInterface
{
    public function __construct(
        protected readonly string $type,
        protected readonly array $data
    ) {
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function toArray(): array
    {
        return array_merge(
            ['type' => $this->type],
            $this->data
        );
    }
} 