<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoanTransaction extends Model
{
    use HasFactory;

    public const TYPES = [
        'given' => 'Given',
        'received' => 'Received',
        'repayment' => 'Repayment',
        'return' => 'Return',
    ];

    protected $fillable = [
        'loan_id',
        'transaction_date',
        'transaction_type',
        'amount',
        'payment_mode',
        'notes',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->transaction_type] ?? ucfirst($this->transaction_type);
    }
}
