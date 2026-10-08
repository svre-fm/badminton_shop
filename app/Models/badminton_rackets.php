<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class badminton_rackets extends Model
{
    protected $table = 'badminton_rackets';

    protected $primaryKey = 'product_id';

    public $incrementing = false;

    public $timestamps = false;

    protected $keyType = 'int';

    protected $fillable = ['product_id', 'balance_point', 'shaft', 'flexibility', 'weight', 'grip_size'];

    protected function casts(): array
    {
        return [
            'product_id' => 'integer',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(products::class, 'product_id', 'product_id');
    }

    public function tensions(): HasMany
    {
        return $this->hasMany(racket_tensions::class, 'product_id', 'product_id');
    }
}
