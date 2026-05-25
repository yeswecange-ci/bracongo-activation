<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LckOrderItem extends Model
{
    protected $table = 'lck_order_items';

    protected $fillable = [
        'order_id',
        'product_id',
        'product_name',
        'product_category',
        'unit_price',
        'quantity',
        'subtotal',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'subtotal'   => 'decimal:2',
        'quantity'   => 'integer',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(LckOrder::class, 'order_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(LckProduct::class, 'product_id');
    }
}
