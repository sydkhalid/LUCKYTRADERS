<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    use HasFactory;

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
        static::creating(function (Supplier $supplier) {
            if (! $supplier->isDirty('current_balance')) {
                $openingBalance = (float) $supplier->opening_balance;
                $supplier->current_balance = $supplier->balance_type === 'debit'
                    ? -$openingBalance
                    : $openingBalance;
            }
        });
    }

    public function ledgers(): HasMany
    {
        return $this->hasMany(Ledger::class, 'party_id')->where('party_type', 'supplier');
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }
}
