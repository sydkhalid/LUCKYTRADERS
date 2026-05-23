<?php

namespace App\Models;

use App\Models\Concerns\LogsErpActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sale extends Model
{
    use HasFactory, LogsErpActivity, SoftDeletes;

    protected $fillable = [
        'sale_no',
        'customer_id',
        'sale_date',
        'bill_type',
        'subtotal',
        'gst_amount',
        'total_amount',
        'paid_amount',
        'balance_amount',
        'payment_status',
        'payment_mode',
        'eway_bill_no',
        'eway_date',
        'eway_driver_name',
        'eway_mobile_no',
        'eway_vehicle_no',
        'eway_valid_upto',
        'notes',
    ];

    protected $casts = [
        'sale_date' => 'date',
        'eway_date' => 'date',
        'eway_valid_upto' => 'date',
        'subtotal' => 'decimal:2',
        'gst_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'balance_amount' => 'decimal:2',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function returns(): HasMany
    {
        return $this->hasMany(SalesReturn::class);
    }
}
