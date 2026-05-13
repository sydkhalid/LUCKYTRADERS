<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PartnerTransaction extends Model
{
    use HasFactory;

    public const TYPES = [
        'investment' => 'Investment',
        'withdrawal' => 'Withdrawal',
        'profit_share' => 'Profit Share',
        'return' => 'Return',
    ];

    protected $fillable = [
        'partner_id',
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

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->transaction_type] ?? ucfirst(str_replace('_', ' ', $this->transaction_type));
    }
}
