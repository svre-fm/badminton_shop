<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class badminton_strings extends Model
{
    protected $table = 'badminton_strings';

    protected $primaryKey = 'product_id';

    public $incrementing = false;

    public $timestamps = false;

    protected $keyType = 'int';

    protected $fillable = ['product_id', 'thickness', 'string_characteristic'];

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
}
