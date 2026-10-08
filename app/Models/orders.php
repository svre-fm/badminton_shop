<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class orders extends Model
{
    protected $table = 'orders';

    protected $primaryKey = 'order_no';

    public $incrementing = false;

    public $timestamps = false;

    protected $keyType = 'string';

    protected $fillable = [
        'order_no',
        'customer_id',
        'status',
        'order_date',
        'delivery_id',
        'delivery_name',
        'delivery_phone',
        'delivery_address',
        'total_price',
    ];

    protected function casts(): array
    {
        return [
            'customer_id' => 'integer',
            'delivery_id' => 'integer',
            'order_date' => 'date',
            'total_price' => 'decimal:2',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function deliveryAddress(): BelongsTo
    {
        return $this->belongsTo(delivery_address::class, 'delivery_id', 'delivery_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(order_items::class, 'order_no', 'order_no');
    }

    public function payment(): HasOne
    {
        return $this->hasOne(payment::class, 'order_no', 'order_no');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(reviews::class, 'order_no', 'order_no');
    }
}
