<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_PROCESSING,
        self::STATUS_COMPLETED,
        self::STATUS_CANCELLED,
    ];

    protected $fillable = [
        'customer_name',
        'customer_email',
        'total_amount',
        'status',
        'processed_at'
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'processed_at' => 'datetime'
    ];

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
} 