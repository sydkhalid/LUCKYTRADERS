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
    ];

    protected $casts = [
        'next_gst_invoice_no' => 'integer',
        'next_normal_bill_no' => 'integer',
        'low_stock_threshold' => 'decimal:3',
    ];

    public static function current(): self
    {
        $settings = self::query()->whereKey(1)->first();

        if ($settings) {
            return $settings;
        }

        $settings = new self(self::defaults());
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
            'terms_and_conditions' => implode("\n", [
                '1. Goods once sold are subject to company return policy and stock verification.',
                '2. Payment must be made as per agreed credit terms.',
                '3. Disputes, if any, are subject to Krishnagiri jurisdiction.',
            ]),
        ];
    }
}
