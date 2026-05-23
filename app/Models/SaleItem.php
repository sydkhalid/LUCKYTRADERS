<?php

namespace App\Models;

use App\Models\Concerns\LogsErpActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleItem extends Model
{
    use HasFactory, LogsErpActivity;

    protected $fillable = [
        'sale_id',
        'product_id',
        'quantity',
        'unit',
        'rate',
        'subtotal',
        'gst_percentage',
        'gst_amount',
        'gst_calculation',
        'gst_type',
        'total',
        'purchase_cost',
        'profit_amount',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'rate' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'gst_percentage' => 'decimal:2',
        'gst_amount' => 'decimal:2',
        'gst_calculation' => 'string',
        'gst_type' => 'string',
        'total' => 'decimal:2',
        'purchase_cost' => 'decimal:2',
        'profit_amount' => 'decimal:2',
    ];

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
