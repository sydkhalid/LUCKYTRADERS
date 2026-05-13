<?php

namespace App\Services;

use App\Models\Payment;
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

        return SystemSetting::current();
    }

    public function company(): array
    {
        $settings = $this->settings();
        $config = config('lucky.company', []);
        $logoPath = $this->publicPath($settings->logo);
        $signaturePath = $this->publicPath($settings->signature_image);

        return [
            'name' => $settings->company_name ?: ($config['name'] ?? 'LUCKY TRADERS'),
            'address' => $settings->address ?: ($config['address'] ?? ''),
            'phone' => $settings->phone,
            'email' => $settings->email,
            'gst_number' => $settings->gst_number ?: ($config['gst_number'] ?? null),
            'logo' => $settings->logo,
            'logo_path' => $logoPath,
            'signature_image' => $settings->signature_image,
            'signature_image_path' => $signaturePath,
        ];
    }

    public function termsAndConditions(): string
    {
        return $this->settings()->terms_and_conditions
            ?: (SystemSetting::defaults()['terms_and_conditions'] ?? '');
    }

    public function bankDetails(): ?string
    {
        return $this->settings()->bank_details;
    }

    public function signatureImagePath(): ?string
    {
        return $this->publicPath($this->settings()->signature_image);
    }

    public function nextSaleNumber(string $billType): string
    {
        if (! Schema::hasTable('system_settings')) {
            return $this->nextDatedNumber(Sale::class, 'sale_no', 'SAL');
        }

        return DB::transaction(function () use ($billType): string {
            $settings = $this->settingsForUpdate();
            $isGst = $billType === 'gst';
            $prefix = $isGst
                ? ($settings->gst_invoice_prefix ?: $settings->invoice_prefix)
                : ($settings->normal_bill_prefix ?: $settings->invoice_prefix);
            $column = $isGst ? 'next_gst_invoice_no' : 'next_normal_bill_no';
            $next = max(1, (int) $settings->{$column});

            do {
                $saleNo = $this->formatSequence($prefix, $next++);
            } while (Sale::where('sale_no', $saleNo)->exists());

            $settings->forceFill([$column => $next])->save();

            return $saleNo;
        });
    }

    public function nextQuotationNumber(): string
    {
        return $this->nextDatedNumber(
            Quotation::class,
            'quotation_no',
            $this->settings()->quotation_prefix ?: 'QTN'
        );
    }

    public function nextPurchaseNumber(): string
    {
        return $this->nextDatedNumber(
            Purchase::class,
            'purchase_no',
            $this->settings()->purchase_prefix ?: 'PUR'
        );
    }

    public function nextPaymentNumber(string $prefix): string
    {
        $configuredPrefix = $prefix === 'RCPT'
            ? ($this->settings()->receipt_prefix ?: $prefix)
            : $prefix;

        return $this->nextDatedNumber(Payment::class, 'payment_no', $configuredPrefix);
    }

    private function settingsForUpdate(): SystemSetting
    {
        $settings = SystemSetting::query()->whereKey(1)->lockForUpdate()->first();

        return $settings ?: SystemSetting::current();
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
