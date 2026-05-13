<?php

namespace App\Models;

use App\Models\Concerns\LogsErpActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Partner extends Model
{
    use HasFactory, LogsErpActivity, SoftDeletes;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'address',
        'share_percentage',
        'opening_investment',
        'current_investment',
        'status',
    ];

    protected $casts = [
        'share_percentage' => 'decimal:2',
        'opening_investment' => 'decimal:2',
        'current_investment' => 'decimal:2',
    ];

    public function transactions(): HasMany
    {
        return $this->hasMany(PartnerTransaction::class);
    }

    public function ledgers(): HasMany
    {
        return $this->hasMany(Ledger::class, 'party_id')->where('party_type', 'partner');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
