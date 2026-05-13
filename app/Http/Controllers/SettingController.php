<?php

namespace App\Http\Controllers;

use App\Models\CompanySetting;
use App\Models\InvoiceSetting;
use App\Services\SystemSettingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function company(): View
    {
        return view('settings.company', [
            'settings' => CompanySetting::current(),
            'invoiceSettings' => InvoiceSetting::current(),
        ]);
    }

    public function updateCompany(Request $request): RedirectResponse
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
        ]);

        unset($validated['logo']);

        if ($request->hasFile('logo')) {
            $this->deletePublicFile($settings->logo);
            $validated['logo'] = $request->file('logo')->store('settings', 'public');
        }

        $settings->update($validated);
        app(SystemSettingService::class)->syncLegacySettings();

        return back()->with('success', 'Company settings updated successfully.');
    }

    public function invoice(): View
    {
        return view('settings.invoice', [
            'settings' => InvoiceSetting::current(),
        ]);
    }

    public function updateInvoice(Request $request): RedirectResponse
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
            $validated['signature_image'] = $request->file('signature_image')->store('settings', 'public');
        }

        $settings->update($validated);
        app(SystemSettingService::class)->syncLegacySettings();

        return back()->with('success', 'Invoice settings updated successfully.');
    }

    public function bank(): View
    {
        return view('settings.bank', [
            'settings' => InvoiceSetting::current(),
        ]);
    }

    public function updateBank(Request $request): RedirectResponse
    {
        InvoiceSetting::current()->update($request->validate([
            'bank_details' => ['nullable', 'string'],
        ]));

        app(SystemSettingService::class)->syncLegacySettings();

        return back()->with('success', 'Bank details updated successfully.');
    }

    public function terms(): View
    {
        return view('settings.terms', [
            'settings' => InvoiceSetting::current(),
        ]);
    }

    public function updateTerms(Request $request): RedirectResponse
    {
        InvoiceSetting::current()->update($request->validate([
            'terms_and_conditions' => ['nullable', 'string'],
        ]));

        app(SystemSettingService::class)->syncLegacySettings();

        return back()->with('success', 'Terms and conditions updated successfully.');
    }

    public function media(): View
    {
        return view('settings.media', [
            'companySettings' => CompanySetting::current(),
            'invoiceSettings' => InvoiceSetting::current(),
        ]);
    }

    public function updateMedia(Request $request): RedirectResponse
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
                'logo' => $request->file('logo')->store('settings', 'public'),
            ]);
        }

        if ($request->hasFile('signature_image')) {
            $this->deletePublicFile($invoice->signature_image);
            $invoice->update([
                'signature_image' => $request->file('signature_image')->store('settings', 'public'),
            ]);
        }

        if (! $request->hasFile('logo') && ! $request->hasFile('signature_image')) {
            validator($validated, [
                'logo' => ['required_without:signature_image'],
                'signature_image' => ['required_without:logo'],
            ])->validate();
        }

        app(SystemSettingService::class)->syncLegacySettings();

        return back()->with('success', 'Logo and signature updated successfully.');
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
}
