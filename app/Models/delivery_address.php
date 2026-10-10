<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class delivery_address extends Model
{
    protected $table = 'delivery_addresses';

    protected $primaryKey = 'delivery_id';

    public $timestamps = false;

    protected $fillable = ['user_id', 'name', 'phone', 'address'];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(orders::class, 'delivery_id', 'delivery_id');
    }
}
