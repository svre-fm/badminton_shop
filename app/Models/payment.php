<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class payment extends Model
{
    protected $table = 'payments';

    protected $primaryKey = 'payment_id';

    protected $fillable = ['user_id', 'order_no', 'card_no', 'amount', 'method'];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'amount' => 'decimal:2',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(orders::class, 'order_no', 'order_no');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function cardRecord(): ?credit_debit_card
    {
        if ($this->user_id === null || $this->card_no === null) {
            return null;
        }

        return credit_debit_card::query()
            ->where('user_id', $this->user_id)
            ->where('card_no', $this->card_no)
            ->first();
    }
}
