<?php

namespace App\Models;

use App\Models\Concerns\LogsErpActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory, LogsErpActivity, SoftDeletes;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'gst_number',
        'address',
        'opening_balance',
        'balance_type',
        'current_balance',
        'status',
    ];

    protected $casts = [
        'opening_balance' => 'decimal:2',
        'current_balance' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::creating(function (Customer $customer) {
            if (! $customer->isDirty('current_balance')) {
                $openingBalance = (float) $customer->opening_balance;
                $customer->current_balance = $customer->balance_type === 'credit'
                    ? -$openingBalance
                    : $openingBalance;
            }
        });
    }

    public function ledgers(): HasMany
    {
        return $this->hasMany(Ledger::class, 'party_id')->where('party_type', 'customer');
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }
}
