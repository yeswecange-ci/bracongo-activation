<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LckOrder extends Model
{
    protected $table = 'lck_orders';

    protected $fillable = [
        'order_ref',
        'cart_session_id',
        'commercant_id',
        'customer_phone',
        'customer_name',
        'customer_location',
        'total',
        'status',
        'notes',
        'confirmed_at',
        'ready_at',
        'delivered_at',
    ];

    protected $casts = [
        'total'        => 'decimal:2',
        'confirmed_at' => 'datetime',
        'ready_at'     => 'datetime',
        'delivered_at' => 'datetime',
    ];

    const STATUS_RECEIVED  = 'received';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_PREPARING = 'preparing';
    const STATUS_READY     = 'ready';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_CANCELLED = 'cancelled';

    const STATUS_LABELS = [
        'received'  => 'Reçue',
        'confirmed' => 'Confirmée',
        'preparing' => 'En préparation',
        'ready'     => 'Prête',
        'delivered' => 'Livrée',
        'cancelled' => 'Annulée',
    ];

    const STATUS_COLORS = [
        'received'  => 'blue',
        'confirmed' => 'indigo',
        'preparing' => 'yellow',
        'ready'     => 'green',
        'delivered' => 'gray',
        'cancelled' => 'red',
    ];

    public function cartSession(): BelongsTo
    {
        return $this->belongsTo(LckCartSession::class, 'cart_session_id');
    }

    public function commercant(): BelongsTo
    {
        return $this->belongsTo(Commercant::class, 'commercant_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(LckOrderItem::class, 'order_id');
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    public function getStatusColorAttribute(): string
    {
        return self::STATUS_COLORS[$this->status] ?? 'gray';
    }

    public static function generateRef(): string
    {
        $year = date('Y');
        $last = static::whereYear('created_at', $year)->max('id') ?? 0;
        $seq  = str_pad($last + 1, 4, '0', STR_PAD_LEFT);
        return "LCK-{$year}-{$seq}";
    }

    public function scopeActive($query)
    {
        return $query->whereNotIn('status', ['delivered', 'cancelled']);
    }
}
