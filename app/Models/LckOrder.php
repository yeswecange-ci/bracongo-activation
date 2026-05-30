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
        'payment_method',
        'payment_status',
        'payment_reference',
        'amount_paid',
        'paid_at',
        'confirmed_at',
        'ready_at',
        'delivered_at',
    ];

    protected $casts = [
        'total'        => 'decimal:2',
        'amount_paid'  => 'decimal:2',
        'confirmed_at' => 'datetime',
        'ready_at'     => 'datetime',
        'delivered_at' => 'datetime',
        'paid_at'      => 'datetime',
    ];

    const PAYMENT_CASH   = 'cash_on_delivery';
    const PAYMENT_ONLINE = 'online';

    const PAYMENT_STATUS_PENDING  = 'pending';
    const PAYMENT_STATUS_PAID     = 'paid';
    const PAYMENT_STATUS_FAILED   = 'failed';
    const PAYMENT_STATUS_REFUNDED = 'refunded';

    public function getPaymentMethodLabelAttribute(): string
    {
        return match ($this->payment_method) {
            'cash_on_delivery' => '💵 À la livraison',
            'online'           => '📱 Mobile Money',
            default            => $this->payment_method,
        };
    }

    public function getPaymentStatusLabelAttribute(): string
    {
        return match ($this->payment_status) {
            'pending'  => 'En attente',
            'paid'     => 'Payé',
            'failed'   => 'Échoué',
            'refunded' => 'Remboursé',
            default    => $this->payment_status,
        };
    }

    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }

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
        return \Illuminate\Support\Facades\DB::transaction(function () {
            $year  = date('Y');
            $count = static::whereYear('created_at', $year)
                ->lockForUpdate()
                ->count();
            $seq   = str_pad($count + 1, 4, '0', STR_PAD_LEFT);
            $ref   = "LCK-{$year}-{$seq}";

            // Sécurité : si la référence existe déjà, incrémenter jusqu'à trouver un libre
            while (static::where('order_ref', $ref)->exists()) {
                $seq  = str_pad((int) $seq + 1, 4, '0', STR_PAD_LEFT);
                $ref  = "LCK-{$year}-{$seq}";
            }

            return $ref;
        });
    }

    public function scopeActive($query)
    {
        return $query->whereNotIn('status', ['delivered', 'cancelled']);
    }
}
