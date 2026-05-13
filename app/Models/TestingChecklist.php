<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TestingChecklist extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_PASS = 'pass';
    public const STATUS_FAIL = 'fail';

    protected $fillable = [
        'key',
        'module',
        'scenario',
        'expected_result',
        'automated_test',
        'sort_order',
        'status',
        'notes',
        'tested_by',
        'tested_at',
    ];

    protected $casts = [
        'tested_at' => 'datetime',
    ];

    public function tester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tested_by');
    }

    public function bugs(): HasMany
    {
        return $this->hasMany(TestingBug::class);
    }

    public function isPassed(): bool
    {
        return $this->status === self::STATUS_PASS;
    }

    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAIL;
    }
}
