<?php

namespace App\Models;

use App\Models\Concerns\HasCompositePrimaryKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class credit_debit_card extends Model
{
    use HasCompositePrimaryKey;

    protected $table = 'credit_debit_cards';

    protected $primaryKey = 'user_id';

    protected array $primaryKeyColumns = ['user_id', 'card_no'];

    public $incrementing = false;

    public $timestamps = false;

    protected $keyType = 'int';

    protected $fillable = ['user_id', 'card_no', 'expiry_date'];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'expiry_date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
