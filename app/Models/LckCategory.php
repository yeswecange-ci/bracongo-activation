<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LckCategory extends Model
{
    protected $table = 'lck_categories';

    protected $fillable = [
        'name',
        'slug',
        'emoji',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(LckProduct::class, 'category_id');
    }

    public function availableProducts(): HasMany
    {
        return $this->hasMany(LckProduct::class, 'category_id')
            ->where('is_available', true)
            ->where('is_active', true);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->emoji ? "{$this->emoji} {$this->name}" : $this->name;
    }
}
