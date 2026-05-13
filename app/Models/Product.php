<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_category_id',
        'name',
        'code',
        'size',
        'thickness',
        'unit',
        'weight_per_unit',
        'hsn_code',
        'gst_percentage',
        'purchase_price',
        'selling_price',
        'opening_stock',
        'current_stock',
        'status',
    ];

    protected $casts = [
        'weight_per_unit' => 'decimal:3',
        'gst_percentage' => 'decimal:2',
        'purchase_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'opening_stock' => 'decimal:3',
        'current_stock' => 'decimal:3',
    ];

    protected static function booted(): void
    {
        static::creating(function (Product $product) {
            if (! $product->isDirty('current_stock')) {
                $product->current_stock = $product->opening_stock ?? 0;
            }
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'product_category_id');
    }

    public function purchaseItems(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }
}
