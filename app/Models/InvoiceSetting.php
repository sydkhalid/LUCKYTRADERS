<?php

namespace App\Models;

use App\Models\Concerns\LogsErpActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class InvoiceSetting extends Model
{
    use LogsErpActivity;

    protected $fillable = [
        'gst_invoice_prefix',
        'normal_bill_prefix',
        'quotation_prefix',
        'purchase_prefix',
        'receipt_prefix',
        'next_gst_invoice_no',
        'next_normal_bill_no',
        'next_quotation_no',
        'next_purchase_no',
        'next_receipt_no',
        'terms_and_conditions',
        'bank_details',
        'signature_image',
    ];

    protected $casts = [
        'next_gst_invoice_no' => 'integer',
        'next_normal_bill_no' => 'integer',
        'next_quotation_no' => 'integer',
        'next_purchase_no' => 'integer',
        'next_receipt_no' => 'integer',
    ];

    public static function current(): self
    {
        if (! Schema::hasTable('invoice_settings')) {
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
            'gst_invoice_prefix' => 'GST',
            'normal_bill_prefix' => 'BILL',
            'quotation_prefix' => 'QTN',
            'purchase_prefix' => 'PUR',
            'receipt_prefix' => 'RCPT',
            'next_gst_invoice_no' => 1,
            'next_normal_bill_no' => 1,
            'next_quotation_no' => 1,
            'next_purchase_no' => 1,
            'next_receipt_no' => 1,
            'terms_and_conditions' => implode("\n", [
                '1. Goods once sold are subject to company return policy and stock verification.',
                '2. Payment must be made as per agreed credit terms.',
                '3. Disputes, if any, are subject to Krishnagiri jurisdiction.',
            ]),
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
            'gst_invoice_prefix' => $legacy->gst_invoice_prefix ?: $defaults['gst_invoice_prefix'],
            'normal_bill_prefix' => $legacy->normal_bill_prefix ?: $defaults['normal_bill_prefix'],
            'quotation_prefix' => $legacy->quotation_prefix ?: $defaults['quotation_prefix'],
            'purchase_prefix' => $legacy->purchase_prefix ?: $defaults['purchase_prefix'],
            'receipt_prefix' => $legacy->receipt_prefix ?: $defaults['receipt_prefix'],
            'next_gst_invoice_no' => max(1, (int) $legacy->next_gst_invoice_no),
            'next_normal_bill_no' => max(1, (int) $legacy->next_normal_bill_no),
            'terms_and_conditions' => $legacy->terms_and_conditions ?: $defaults['terms_and_conditions'],
            'bank_details' => $legacy->bank_details,
            'signature_image' => $legacy->signature_image,
        ]);
    }
}
