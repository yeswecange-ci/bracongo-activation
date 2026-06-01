<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Commercant extends Authenticatable
{
    use Notifiable;

    protected $table = 'commercants';

    protected $fillable = [
        'name', 'email', 'password', 'phone',
        'zones', 'role', 'is_active', 'is_online', 'last_online_at',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'password'       => 'hashed',
        'is_active'      => 'boolean',
        'is_online'      => 'boolean',
        'zones'          => 'array',
        'last_online_at' => 'datetime',
    ];

    public function goOnline(): void
    {
        $this->update(['is_online' => true, 'last_online_at' => now()]);
    }

    public function goOffline(): void
    {
        $this->update(['is_online' => false]);
    }

    // Session WhatsApp active si le commercant est allé online dans les 23h
    public function hasActiveWhatsAppSession(): bool
    {
        return $this->is_online
            && $this->last_online_at
            && $this->last_online_at->isAfter(now()->subHours(23));
    }

    const ROLE_COMMERCIAL = 'commercial';
    const ROLE_CAVISTE    = 'caviste';

    public function orders(): HasMany
    {
        return $this->hasMany(LckOrder::class, 'commercant_id');
    }

    public function isCaviste(): bool
    {
        return $this->role === self::ROLE_CAVISTE;
    }

    public function isCommercial(): bool
    {
        return $this->role === self::ROLE_COMMERCIAL;
    }

    public function getRoleLabelAttribute(): string
    {
        return match ($this->role) {
            'commercial' => 'Commercial(e)',
            'caviste'    => 'Caviste',
            default      => $this->role,
        };
    }
}
