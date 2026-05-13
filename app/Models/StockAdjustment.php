<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockAdjustment extends Model
{
    use HasFactory;

    public const TYPES = [
        'increase' => 'Increase',
        'decrease' => 'Decrease',
    ];

    public const REASONS = [
        'damage' => 'Damage',
        'shortage' => 'Shortage',
        'excess' => 'Excess',
        'return' => 'Return',
        'wastage' => 'Wastage',
        'correction' => 'Correction',
        'other' => 'Other',
    ];

    protected $fillable = [
        'adjustment_no',
        'adjustment_date',
        'product_id',
        'adjustment_type',
        'reason',
        'quantity',
        'old_stock',
        'new_stock',
        'remarks',
    ];

    protected $casts = [
        'adjustment_date' => 'date',
        'quantity' => 'decimal:3',
        'old_stock' => 'decimal:3',
        'new_stock' => 'decimal:3',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->adjustment_type] ?? ucfirst($this->adjustment_type);
    }

    public function reasonLabel(): string
    {
        return self::REASONS[$this->reason] ?? ucfirst($this->reason);
    }
}
