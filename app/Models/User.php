<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $password
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        $initials = Str::initials($this->name, true);

        return Str::length($initials) > 1
            ? Str::substr($initials, 0, 1).Str::substr($initials, -1)
            : $initials;
    }

    public function Delivery_address(): HasMany
    {
        return $this->hasMany(delivery_address::class, 'user_id');
    }

    public function cards(): HasMany
    {
        return $this->hasMany(credit_debit_card::class, 'user_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(orders::class, 'customer_id');
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(cart_item::class, 'customer_id');
    }

    public function favorites(): BelongsToMany
    {
        return $this->belongsToMany(products::class, 'favorites', 'customer_id', 'product_id', 'id', 'product_id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(reviews::class, 'customer_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(payment::class, 'user_id');
    }
}
