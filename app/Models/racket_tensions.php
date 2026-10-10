<?php

namespace App\Models;

use App\Models\Concerns\HasCompositePrimaryKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class racket_tensions extends Model
{
    use HasCompositePrimaryKey;

    protected $table = 'racket_tensions';

    protected $primaryKey = 'product_id';

    protected array $primaryKeyColumns = ['product_id', 'tension'];

    public $incrementing = false;

    public $timestamps = false;

    protected $keyType = 'int';

    protected $fillable = ['product_id', 'tension'];

    protected function casts(): array
    {
        return [
            'product_id' => 'integer',
        ];
    }

    public function racket(): BelongsTo
    {
        return $this->belongsTo(badminton_rackets::class, 'product_id', 'product_id');
    }
}
