<?php

namespace App\Models;

use App\Models\Concerns\HasCompositePrimaryKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class favorite extends Model
{
    use HasCompositePrimaryKey;

    protected $table = 'favorites';

    protected $primaryKey = 'customer_id';

    protected array $primaryKeyColumns = ['customer_id', 'product_id'];

    public $incrementing = false;

    public $timestamps = false;

    protected $keyType = 'int';

    protected $fillable = ['customer_id', 'product_id'];

    protected function casts(): array
    {
        return [
            'customer_id' => 'integer',
            'product_id' => 'integer',
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
}
