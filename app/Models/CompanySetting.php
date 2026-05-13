<?php

namespace App\Models;

use App\Models\Concerns\LogsErpActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class CompanySetting extends Model
{
    use LogsErpActivity;

    protected $fillable = [
        'company_name',
        'address',
        'phone',
        'email',
        'gst_number',
        'logo',
        'state',
        'city',
        'pincode',
    ];

    public static function current(): self
    {
        if (! Schema::hasTable('company_settings')) {
            return new self(self::defaults());
        }

        $settings = self::query()->whereKey(1)->first();

        if ($settings) {
            return $settings;
        }

        $settings = new self(self::defaultsFromLegacy());
        $settings->forceFill(['id' => 1])->save();

        return $settings;
    }

    public static function defaults(): array
    {
        return [
            'company_name' => 'LUCKY TRADERS',
            'address' => '2/164/14 Line Kollai, Venkatapuram, Krishnagiri, Tamil Nadu, India',
        ];
    }

    private static function defaultsFromLegacy(): array
    {
        $defaults = self::defaults();

        if (! Schema::hasTable('system_settings')) {
            return $defaults;
        }

        $legacy = SystemSetting::query()->whereKey(1)->first();

        if (! $legacy) {
            return $defaults;
        }

        return array_merge($defaults, [
            'company_name' => $legacy->company_name ?: $defaults['company_name'],
            'address' => $legacy->address ?: $defaults['address'],
            'phone' => $legacy->phone,
            'email' => $legacy->email,
            'gst_number' => $legacy->gst_number,
            'logo' => $legacy->logo,
        ]);
    }
}
