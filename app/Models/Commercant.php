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
        'name',
        'email',
        'password',
        'phone',
        'role',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'password'  => 'hashed',
        'is_active' => 'boolean',
    ];

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
