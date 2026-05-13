<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ledger extends Model
{
    use HasFactory;

    protected $fillable = [
        'ledger_date',
        'party_type',
        'party_id',
        'reference_type',
        'reference_id',
        'debit',
        'credit',
        'balance',
        'remarks',
    ];

    protected $casts = [
        'ledger_date' => 'date',
        'debit' => 'decimal:2',
        'credit' => 'decimal:2',
        'balance' => 'decimal:2',
    ];
}
