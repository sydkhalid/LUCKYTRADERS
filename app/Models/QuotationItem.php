<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuotationItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'quotation_id',
        'product_id',
        'quantity',
        'unit',
        'rate',
        'subtotal',
        'gst_percentage',
        'gst_amount',
        'total',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'rate' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'gst_percentage' => 'decimal:2',
        'gst_amount' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
