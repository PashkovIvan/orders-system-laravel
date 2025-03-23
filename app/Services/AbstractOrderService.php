<?php

namespace App\Services;

abstract class AbstractOrderService
{
    protected const STATUS_TRANSITIONS = [
        'pending' => ['processing', 'cancelled'],
        'processing' => ['completed', 'failed'],
        'completed' => [],
        'failed' => ['processing'],
        'cancelled' => []
    ];

    public function isValidStatusTransition(?string $oldStatus, string $newStatus): bool
    {
        return isset(self::STATUS_TRANSITIONS[$oldStatus]) 
            && in_array($newStatus, self::STATUS_TRANSITIONS[$oldStatus]);
    }

    protected function validateStatusTransition(?string $oldStatus, string $newStatus): void
    {
        if (!$this->isValidStatusTransition($oldStatus, $newStatus)) {
            throw new \InvalidArgumentException(
                "Invalid status transition from {$oldStatus} to {$newStatus}"
            );
        }
    }
} 