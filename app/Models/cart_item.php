<?php

namespace App\Models;

use App\Models\Concerns\HasCompositePrimaryKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class cart_item extends Model
{
    use HasCompositePrimaryKey;

    protected $table = 'cart_items';

    protected $primaryKey = 'customer_id';

    protected array $primaryKeyColumns = ['customer_id', 'product_id', 'color'];

    public $incrementing = false;

    protected $keyType = 'int';

    protected $fillable = ['customer_id', 'product_id', 'color', 'quantity'];

    protected function casts(): array
    {
        return [
            'customer_id' => 'integer',
            'product_id' => 'integer',
            'quantity' => 'integer',
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

    public function variantRecord(): ?productvariant
    {
        return productvariant::query()
            ->where('product_id', $this->product_id)
            ->where('color', $this->color)
            ->first();
    }
}
