<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class order_items extends Model
{
    protected $table = 'order_items';

    public $timestamps = false;

    protected $fillable = ['order_no', 'product_id', 'color', 'quantity', 'unit_price'];

    protected function casts(): array
    {
        return [
            'product_id' => 'integer',
            'quantity' => 'integer',
            'unit_price' => 'decimal:2',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(orders::class, 'order_no', 'order_no');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(products::class, 'product_id', 'product_id');
    }

    public function variantRecord(): ?productvariant
    {
        return productvariant::query()
            ->where('product_id', $this->product_id)
            ->where('color', $this->color)
            ->first();
    }
}
