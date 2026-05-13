<?php

namespace App\Models;

use App\Models\Concerns\LogsErpActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    use HasFactory, LogsErpActivity;

    protected $fillable = [
        'product_id',
        'movement_type',
        'reference_type',
        'reference_id',
        'quantity',
        'rate',
        'total_value',
        'movement_date',
        'remarks',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'rate' => 'decimal:2',
        'total_value' => 'decimal:2',
        'movement_date' => 'date',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
