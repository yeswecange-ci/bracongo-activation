<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prize extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'partner_id',
        'quantity',
        'distributed_count',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Relations
    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }

    public function winners()
    {
        return $this->belongsToMany(User::class, 'prize_winners')
            ->withPivot('match_id', 'collected_at')
            ->withTimestamps();
    }

    // Accessor pour les lots restants
    public function getRemainingAttribute()
    {
        return $this->quantity - $this->distributed_count;
    }
}
