<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class products extends Model
{
    protected $table = 'products';

    protected $primaryKey = 'product_id';

    protected $fillable = ['name', 'brand', 'detail', 'price', 'image', 'product_type'];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
        ];
    }

    public function variants(): HasMany
    {
        return $this->hasMany(productvariant::class, 'product_id', 'product_id');
    }

    public function badmintonRacket(): HasOne
    {
        return $this->hasOne(badminton_rackets::class, 'product_id', 'product_id');
    }

    public function badmintonString(): HasOne
    {
        return $this->hasOne(badminton_strings::class, 'product_id', 'product_id');
    }

    public function grip(): HasOne
    {
        return $this->hasOne(grips::class, 'product_id', 'product_id');
    }

    public function shuttlecock(): HasOne
    {
        return $this->hasOne(shuttlecocks::class, 'product_id', 'product_id');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(order_items::class, 'product_id', 'product_id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(reviews::class, 'product_id', 'product_id');
    }

    public function favoritedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'favorites', 'product_id', 'customer_id', 'product_id', 'id');
    }
}
