<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LckProduct extends Model
{
    protected $table = 'lck_products';

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'description',
        'origin',
        'vintage',
        'price',
        'image',
        'whatsapp_label',
        'stock',
        'stock_alert_threshold',
        'is_available',
        'is_active',
        'sort_order',
        'wordpress_product_id',
    ];

    protected $casts = [
        'price'        => 'decimal:2',
        'stock'        => 'integer',
        'is_available' => 'boolean',
        'is_active'    => 'boolean',
        'sort_order'   => 'integer',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(LckCategory::class, 'category_id');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(LckOrderItem::class, 'product_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    public function scopeAvailable($query)
    {
        return $query->where('is_available', true)->where('is_active', true);
    }

    public function getFormattedPriceAttribute(): string
    {
        return number_format($this->price, 2) . ' $';
    }

    public function getBotLabelAttribute(): string
    {
        $label = $this->whatsapp_label;
        if ($this->vintage) {
            $label .= " ({$this->vintage})";
        }
        return $label . ' — ' . $this->formatted_price;
    }
}
