<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Loan extends Model
{
    use HasFactory;

    public const TYPES = [
        'loan_taken' => 'Loan Taken',
        'loan_given' => 'Loan Given',
        'partner_withdrawal' => 'Partner Withdrawal',
        'partner_deposit' => 'Partner Deposit',
    ];

    protected $fillable = [
        'loan_no',
        'loan_type',
        'party_name',
        'party_phone',
        'partner_id',
        'loan_date',
        'principal_amount',
        'interest_percentage',
        'interest_type',
        'total_interest',
        'total_amount',
        'paid_amount',
        'balance_amount',
        'status',
        'notes',
    ];

    protected $casts = [
        'loan_date' => 'date',
        'principal_amount' => 'decimal:2',
        'interest_percentage' => 'decimal:2',
        'total_interest' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'balance_amount' => 'decimal:2',
    ];

    public function transactions(): HasMany
    {
        return $this->hasMany(LoanTransaction::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeClosed($query)
    {
        return $query->where('status', 'closed');
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->loan_type] ?? ucfirst(str_replace('_', ' ', $this->loan_type));
    }
}
