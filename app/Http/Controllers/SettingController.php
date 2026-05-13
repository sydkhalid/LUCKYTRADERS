<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\RespondsToAjax;
use App\Models\CompanySetting;
use App\Models\InvoiceSetting;
use App\Models\SystemSetting;
use App\Services\SystemSettingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SettingController extends Controller
{
    use RespondsToAjax;

    public function company(): View
    {
        return view('settings.company', [
            'settings' => CompanySetting::current(),
            'invoiceSettings' => InvoiceSetting::current(),
            'systemSettings' => SystemSetting::current(),
        ]);
    }

    public function updateCompany(Request $request): JsonResponse|RedirectResponse
    {
        $settings = CompanySetting::current();
        $validated = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'gst_number' => ['nullable', 'string', 'max:50'],
            'state' => ['nullable', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
            'pincode' => ['nullable', 'string', 'max:20'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'low_stock_threshold' => ['nullable', 'numeric', 'min:0'],
        ]);

        unset($validated['logo']);
        $lowStockThreshold = $validated['low_stock_threshold'] ?? null;
        unset($validated['low_stock_threshold']);

        if ($request->hasFile('logo')) {
            $this->deletePublicFile($settings->logo);
            $validated['logo'] = $this->storeOptimizedImage($request->file('logo'));
        }

        $settings->update($validated);
        app(SystemSettingService::class)->syncLegacySettings();

        if ($lowStockThreshold !== null) {
            SystemSetting::current()
                ->forceFill(['low_stock_threshold' => $lowStockThreshold])
                ->save();
        }

        return $this->backSuccessResponse($request, 'Company settings updated successfully.');
    }

    public function invoice(): View
    {
        return view('settings.invoice', [
            'settings' => InvoiceSetting::current(),
        ]);
    }

    public function updateInvoice(Request $request): JsonResponse|RedirectResponse
    {
        $settings = InvoiceSetting::current();
        $validated = $request->validate([
            'gst_invoice_prefix' => ['required', 'string', 'max:30', $this->prefixRule()],
            'normal_bill_prefix' => ['required', 'string', 'max:30', $this->prefixRule()],
            'quotation_prefix' => ['required', 'string', 'max:30', $this->prefixRule()],
            'purchase_prefix' => ['required', 'string', 'max:30', $this->prefixRule()],
            'receipt_prefix' => ['required', 'string', 'max:30', $this->prefixRule()],
            'next_gst_invoice_no' => ['required', 'integer', 'min:1'],
            'next_normal_bill_no' => ['required', 'integer', 'min:1'],
            'next_quotation_no' => ['required', 'integer', 'min:1'],
            'next_purchase_no' => ['required', 'integer', 'min:1'],
            'next_receipt_no' => ['required', 'integer', 'min:1'],
            'terms_and_conditions' => ['nullable', 'string'],
            'bank_details' => ['nullable', 'string'],
            'signature_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        unset($validated['signature_image']);

        if ($request->hasFile('signature_image')) {
            $this->deletePublicFile($settings->signature_image);
            $validated['signature_image'] = $this->storeOptimizedImage($request->file('signature_image'));
        }

        $settings->update($validated);
        app(SystemSettingService::class)->syncLegacySettings();

        return $this->backSuccessResponse($request, 'Invoice settings updated successfully.');
    }

    public function bank(): View
    {
        return view('settings.bank', [
            'settings' => InvoiceSetting::current(),
        ]);
    }

    public function updateBank(Request $request): JsonResponse|RedirectResponse
    {
        InvoiceSetting::current()->update($request->validate([
            'bank_details' => ['nullable', 'string'],
        ]));

        app(SystemSettingService::class)->syncLegacySettings();

        return $this->backSuccessResponse($request, 'Bank details updated successfully.');
    }

    public function terms(): View
    {
        return view('settings.terms', [
            'settings' => InvoiceSetting::current(),
        ]);
    }

    public function updateTerms(Request $request): JsonResponse|RedirectResponse
    {
        InvoiceSetting::current()->update($request->validate([
            'terms_and_conditions' => ['nullable', 'string'],
        ]));

        app(SystemSettingService::class)->syncLegacySettings();

        return $this->backSuccessResponse($request, 'Terms and conditions updated successfully.');
    }

    public function media(): View
    {
        return view('settings.media', [
            'companySettings' => CompanySetting::current(),
            'invoiceSettings' => InvoiceSetting::current(),
        ]);
    }

    public function updateMedia(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'signature_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $company = CompanySetting::current();
        $invoice = InvoiceSetting::current();

        if ($request->hasFile('logo')) {
            $this->deletePublicFile($company->logo);
            $company->update([
                'logo' => $this->storeOptimizedImage($request->file('logo')),
            ]);
        }

        if ($request->hasFile('signature_image')) {
            $this->deletePublicFile($invoice->signature_image);
            $invoice->update([
                'signature_image' => $this->storeOptimizedImage($request->file('signature_image')),
            ]);
        }

        if (! $request->hasFile('logo') && ! $request->hasFile('signature_image')) {
            validator($validated, [
                'logo' => ['required_without:signature_image'],
                'signature_image' => ['required_without:logo'],
            ])->validate();
        }

        app(SystemSettingService::class)->syncLegacySettings();

        return $this->backSuccessResponse($request, 'Logo and signature updated successfully.');
    }

    private function prefixRule(): string
    {
        return 'regex:/^[A-Za-z0-9\-\/]+$/';
    }

    private function deletePublicFile(?string $path): void
    {
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }

    private function storeOptimizedImage(UploadedFile $file): string
    {
        if (! extension_loaded('gd')) {
            return $file->store('settings', 'public');
        }

        $source = @imagecreatefromstring((string) file_get_contents($file->getRealPath()));

        if (! $source) {
            return $file->store('settings', 'public');
        }

        $width = imagesx($source);
        $height = imagesy($source);
        $maxDimension = 1200;
        $target = $source;
        $targetCreated = false;

        if (max($width, $height) > $maxDimension) {
            $ratio = $maxDimension / max($width, $height);
            $targetWidth = max(1, (int) round($width * $ratio));
            $targetHeight = max(1, (int) round($height * $ratio));
            $target = imagecreatetruecolor($targetWidth, $targetHeight);
            imagealphablending($target, false);
            imagesavealpha($target, true);
            imagecopyresampled($target, $source, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);
            $targetCreated = true;
        }

        $extension = strtolower($file->extension() ?: $file->guessExtension() ?: 'jpg');
        $extension = $extension === 'jpeg' ? 'jpg' : $extension;

        if ($extension === 'webp' && ! function_exists('imagewebp')) {
            $extension = 'jpg';
        }

        if (! in_array($extension, ['jpg', 'png', 'webp'], true)) {
            $extension = 'jpg';
        }

        $tempPath = tempnam(sys_get_temp_dir(), 'erp-img-');

        if (! $tempPath) {
            if ($targetCreated) {
                imagedestroy($target);
            }

            imagedestroy($source);

            return $file->store('settings', 'public');
        }

        $saved = match ($extension) {
            'png' => imagepng($target, $tempPath, 6),
            'webp' => imagewebp($target, $tempPath, 82),
            default => imagejpeg($target, $tempPath, 82),
        };

        if ($targetCreated) {
            imagedestroy($target);
        }

        imagedestroy($source);

        if (! $saved || ! $tempPath || ! is_file($tempPath)) {
            if ($tempPath && is_file($tempPath)) {
                @unlink($tempPath);
            }

            return $file->store('settings', 'public');
        }

        $path = 'settings/'.Str::uuid().'.'.$extension;
        Storage::disk('public')->put($path, (string) file_get_contents($tempPath));
        @unlink($tempPath);

        return $path;
    }
}
