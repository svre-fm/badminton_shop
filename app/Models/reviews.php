<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class reviews extends Model
{
    protected $table = 'reviews';

    protected $primaryKey = 'no';

    public $timestamps = false;

    protected $fillable = ['customer_id', 'product_id', 'order_no', 'star', 'date', 'comment'];

    protected function casts(): array
    {
        return [
            'customer_id' => 'integer',
            'product_id' => 'integer',
            'star' => 'integer',
            'date' => 'date',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(products::class, 'product_id', 'product_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(orders::class, 'order_no', 'order_no');
    }
}
