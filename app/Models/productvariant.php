<?php

namespace App\Models;

use App\Models\Concerns\HasCompositePrimaryKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class productvariant extends Model
{
    use HasCompositePrimaryKey;

    protected $table = 'productvariants';

    protected $primaryKey = 'product_id';

    protected array $primaryKeyColumns = ['product_id', 'color'];

    public $incrementing = false;

    public $timestamps = false;

    protected $keyType = 'int';

    protected $fillable = ['product_id', 'color', 'stock'];

    protected function casts(): array
    {
        return [
            'product_id' => 'integer',
            'stock' => 'integer',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(products::class, 'product_id', 'product_id');
    }
}
