<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class LckCartSession extends Model
{
    protected $table = 'lck_cart_sessions';

    protected $fillable = [
        'token',
        'customer_phone',
        'customer_name',
        'items',
        'total',
        'status',
        'source',
        'expires_at',
    ];

    protected $casts = [
        'items'      => 'array',
        'total'      => 'decimal:2',
        'expires_at' => 'datetime',
    ];

    public function order(): HasOne
    {
        return $this->hasOne(LckOrder::class, 'cart_session_id');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending')->where('expires_at', '>', now());
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isUsable(): bool
    {
        return $this->status === 'pending' && !$this->isExpired();
    }

    public function getSummaryTextAttribute(): string
    {
        $lines = [];
        foreach ($this->items as $item) {
            $subtotal = number_format($item['unit_price'] * $item['quantity'], 2);
            $lines[] = "• {$item['name']} × {$item['quantity']} = {$subtotal} $";
        }
        $lines[] = '';
        $lines[] = '*Total: ' . number_format($this->total, 2) . ' $*';
        return implode("\n", $lines);
    }

    public static function generateToken(): string
    {
        do {
            $token = strtoupper(Str::random(8));
        } while (static::where('token', $token)->exists());

        return $token;
    }
}
