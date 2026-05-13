<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\CompanySetting;
use App\Models\InvoiceSetting;
use App\Models\Purchase;
use App\Models\Quotation;
use App\Models\Sale;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class SystemSettingService
{
    public function settings(): SystemSetting
    {
        if (! Schema::hasTable('system_settings')) {
            return new SystemSetting(SystemSetting::defaults());
        }

        return $this->syncLegacySettings();
    }

    public function companySettings(): CompanySetting
    {
        return CompanySetting::current();
    }

    public function invoiceSettings(): InvoiceSetting
    {
        return InvoiceSetting::current();
    }

    public function company(): array
    {
        $settings = $this->companySettings();
        $config = config('lucky.company', []);
        $logoPath = $this->publicPath($settings->logo);

        return [
            'name' => $settings->company_name ?: ($config['name'] ?? 'LUCKY TRADERS'),
            'address' => $settings->address ?: ($config['address'] ?? ''),
            'phone' => $settings->phone,
            'email' => $settings->email,
            'gst_number' => $settings->gst_number ?: ($config['gst_number'] ?? null),
            'state' => $settings->state,
            'city' => $settings->city,
            'pincode' => $settings->pincode,
            'logo' => $settings->logo,
            'logo_path' => $logoPath,
        ];
    }

    public function termsAndConditions(): string
    {
        return $this->invoiceSettings()->terms_and_conditions
            ?: (InvoiceSetting::defaults()['terms_and_conditions'] ?? '');
    }

    public function bankDetails(): ?string
    {
        return $this->invoiceSettings()->bank_details;
    }

    public function signatureImagePath(): ?string
    {
        return $this->publicPath($this->invoiceSettings()->signature_image);
    }

    public function nextSaleNumber(string $billType): string
    {
        if (! Schema::hasTable('invoice_settings')) {
            return $this->nextDatedNumber(Sale::class, 'sale_no', 'SAL');
        }

        return DB::transaction(function () use ($billType): string {
            InvoiceSetting::current();
            $settings = InvoiceSetting::query()->whereKey(1)->lockForUpdate()->firstOrFail();
            $isGst = $billType === 'gst';
            $prefix = $isGst
                ? $settings->gst_invoice_prefix
                : $settings->normal_bill_prefix;
            $column = $isGst ? 'next_gst_invoice_no' : 'next_normal_bill_no';
            $next = max(1, (int) $settings->{$column});

            do {
                $saleNo = $this->formatSequence($prefix, $next++);
            } while (Sale::where('sale_no', $saleNo)->exists());

            $settings->forceFill([$column => $next])->save();
            $this->syncLegacySettings();

            return $saleNo;
        });
    }

    public function nextQuotationNumber(): string
    {
        if (! Schema::hasTable('invoice_settings')) {
            return $this->nextDatedNumber(Quotation::class, 'quotation_no', 'QTN');
        }

        return $this->nextSequentialNumber(Quotation::class, 'quotation_no', 'quotation_prefix', 'next_quotation_no');
    }

    public function nextPurchaseNumber(): string
    {
        if (! Schema::hasTable('invoice_settings')) {
            return $this->nextDatedNumber(Purchase::class, 'purchase_no', 'PUR');
        }

        return $this->nextSequentialNumber(Purchase::class, 'purchase_no', 'purchase_prefix', 'next_purchase_no');
    }

    public function nextPaymentNumber(string $prefix): string
    {
        if ($prefix === 'RCPT' && Schema::hasTable('invoice_settings')) {
            return $this->nextSequentialNumber(Payment::class, 'payment_no', 'receipt_prefix', 'next_receipt_no');
        }

        return $this->nextDatedNumber(Payment::class, 'payment_no', $prefix);
    }

    public function syncLegacySettings(): SystemSetting
    {
        $settings = SystemSetting::current();

        if (! Schema::hasTable('company_settings') || ! Schema::hasTable('invoice_settings')) {
            return $settings;
        }

        $company = CompanySetting::current();
        $invoice = InvoiceSetting::current();

        $settings->forceFill([
            'company_name' => $company->company_name,
            'address' => $company->address,
            'phone' => $company->phone,
            'email' => $company->email,
            'gst_number' => $company->gst_number,
            'logo' => $company->logo,
            'invoice_prefix' => $invoice->gst_invoice_prefix,
            'quotation_prefix' => $invoice->quotation_prefix,
            'purchase_prefix' => $invoice->purchase_prefix,
            'receipt_prefix' => $invoice->receipt_prefix,
            'gst_invoice_prefix' => $invoice->gst_invoice_prefix,
            'normal_bill_prefix' => $invoice->normal_bill_prefix,
            'next_gst_invoice_no' => $invoice->next_gst_invoice_no,
            'next_normal_bill_no' => $invoice->next_normal_bill_no,
            'terms_and_conditions' => $invoice->terms_and_conditions,
            'bank_details' => $invoice->bank_details,
            'signature_image' => $invoice->signature_image,
        ]);

        if ($settings->isDirty()) {
            $settings->save();
        }

        return $settings;
    }

    /**
     * @param class-string<\Illuminate\Database\Eloquent\Model> $modelClass
     */
    private function nextDatedNumber(string $modelClass, string $column, string $prefix): string
    {
        $prefix = $this->cleanPrefix($prefix);
        $date = now()->format('Ymd');
        $sequence = $modelClass::where($column, 'like', $prefix.'-'.$date.'-%')->count() + 1;

        do {
            $number = sprintf('%s-%s-%05d', $prefix, $date, $sequence++);
        } while ($modelClass::where($column, $number)->exists());

        return $number;
    }

    private function formatSequence(string $prefix, int $sequence): string
    {
        return sprintf('%s-%05d', $this->cleanPrefix($prefix), $sequence);
    }

    /**
     * @param class-string<\Illuminate\Database\Eloquent\Model> $modelClass
     */
    private function nextSequentialNumber(string $modelClass, string $column, string $prefixColumn, string $nextColumn): string
    {
        return DB::transaction(function () use ($modelClass, $column, $prefixColumn, $nextColumn): string {
            InvoiceSetting::current();
            $settings = InvoiceSetting::query()->whereKey(1)->lockForUpdate()->firstOrFail();
            $next = max(1, (int) $settings->{$nextColumn});

            do {
                $number = $this->formatSequence((string) $settings->{$prefixColumn}, $next++);
            } while ($modelClass::where($column, $number)->exists());

            $settings->forceFill([$nextColumn => $next])->save();
            $this->syncLegacySettings();

            return $number;
        });
    }

    private function cleanPrefix(?string $prefix): string
    {
        $prefix = trim((string) $prefix);

        return $prefix !== '' ? strtoupper($prefix) : 'DOC';
    }

    private function publicPath(?string $path): ?string
    {
        if (! $path || ! Storage::disk('public')->exists($path)) {
            return null;
        }

        return Storage::disk('public')->path($path);
    }
}
