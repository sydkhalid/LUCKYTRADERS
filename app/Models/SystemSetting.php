<?php

namespace App\Models;

use App\Models\Concerns\LogsErpActivity;
use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    use LogsErpActivity;

    protected $fillable = [
        'company_name',
        'address',
        'phone',
        'email',
        'gst_number',
        'logo',
        'invoice_prefix',
        'quotation_prefix',
        'purchase_prefix',
        'receipt_prefix',
        'gst_invoice_prefix',
        'normal_bill_prefix',
        'next_gst_invoice_no',
        'next_normal_bill_no',
        'terms_and_conditions',
        'bank_details',
        'signature_image',
        'low_stock_threshold',
        'default_tax',
        'currency',
        'date_format',
        'theme_mode',
        'theme_color',
        'sidebar_style',
        'header_style',
    ];

    protected $casts = [
        'next_gst_invoice_no' => 'integer',
        'next_normal_bill_no' => 'integer',
        'low_stock_threshold' => 'decimal:3',
        'default_tax' => 'decimal:2',
    ];

    public static function current(): self
    {
        $settings = self::query()->whereKey(1)->first();

        if ($settings) {
            return $settings;
        }

        $settings = new self(self::defaultsForCurrentSchema());
        $settings->forceFill(['id' => 1])->save();

        return $settings;
    }

    public static function defaults(): array
    {
        return [
            'company_name' => 'LUCKY TRADERS',
            'address' => '2/164/14 Line Kollai, Venkatapuram, Krishnagiri, Tamil Nadu, India',
            'invoice_prefix' => 'INV',
            'quotation_prefix' => 'QTN',
            'purchase_prefix' => 'PUR',
            'receipt_prefix' => 'RCPT',
            'gst_invoice_prefix' => 'GST',
            'normal_bill_prefix' => 'BILL',
            'next_gst_invoice_no' => 1,
            'next_normal_bill_no' => 1,
            'low_stock_threshold' => 10,
            'default_tax' => 18,
            'currency' => 'INR',
            'date_format' => 'd M Y',
            'theme_mode' => 'light',
            'theme_color' => '#2563eb',
            'sidebar_style' => 'dark',
            'header_style' => 'light',
            'terms_and_conditions' => implode("\n", [
                '1. Goods once sold are subject to company return policy and stock verification.',
                '2. Payment must be made as per agreed credit terms.',
                '3. Disputes, if any, are subject to Krishnagiri jurisdiction.',
            ]),
        ];
    }

    private static function defaultsForCurrentSchema(): array
    {
        if (! \Illuminate\Support\Facades\Schema::hasTable('system_settings')) {
            return self::defaults();
        }

        $columns = array_flip(\Illuminate\Support\Facades\Schema::getColumnListing('system_settings'));

        return array_intersect_key(self::defaults(), $columns);
    }
}
