<?php

namespace App\Models;

use App\Models\Concerns\LogsErpActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use HasFactory, LogsErpActivity, SoftDeletes;

    protected $fillable = [
        'payment_no',
        'payment_date',
        'party_type',
        'party_id',
        'transaction_type',
        'reference_type',
        'reference_id',
        'amount',
        'payment_mode',
        'notes',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function scopeReceipts($query)
    {
        return $query->where('transaction_type', 'receipt');
    }

    public function scopePayments($query)
    {
        return $query->where('transaction_type', 'payment');
    }
}
