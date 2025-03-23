<?php

namespace App\Messages\Orders;

use App\Messages\Base\AbstractOrderMessage;

class OrderStatusChangedMessage extends AbstractOrderMessage
{
    public function __construct(
        int $orderId,
        string $oldStatus,
        string $newStatus
    ) {
        parent::__construct(
            $orderId,
            'order.status_changed',
            [
                'old_status' => $oldStatus,
                'new_status' => $newStatus
            ]
        );
    }
} 