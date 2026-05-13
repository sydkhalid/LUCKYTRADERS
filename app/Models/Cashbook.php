<?php

namespace App\Models;

use App\Models\Concerns\LogsErpActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cashbook extends Model
{
    use HasFactory, LogsErpActivity;

    protected $fillable = [
        'entry_date',
        'transaction_type',
        'reference_type',
        'reference_id',
        'amount',
        'payment_mode',
        'remarks',
    ];

    protected $casts = [
        'entry_date' => 'date',
        'amount' => 'decimal:2',
    ];
}
