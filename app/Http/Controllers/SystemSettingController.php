<?php

namespace App\Http\Controllers;

use App\Models\SystemSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SystemSettingController extends Controller
{
    public function company(): View
    {
        return view('settings.company', [
            'settings' => SystemSetting::current(),
        ]);
    }

    public function updateCompany(Request $request): RedirectResponse
    {
        $settings = SystemSetting::current();
        $validated = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'gst_number' => ['nullable', 'string', 'max:50'],
            'invoice_prefix' => ['required', 'string', 'max:30', $this->prefixRule()],
            'quotation_prefix' => ['required', 'string', 'max:30', $this->prefixRule()],
            'purchase_prefix' => ['required', 'string', 'max:30', $this->prefixRule()],
            'receipt_prefix' => ['required', 'string', 'max:30', $this->prefixRule()],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        unset($validated['logo']);

        if ($request->hasFile('logo')) {
            $this->deletePublicFile($settings->logo);
            $validated['logo'] = $request->file('logo')->store('settings', 'public');
        }

        $settings->update($validated);

        return back()->with('success', 'Company settings updated successfully.');
    }

    public function invoice(): View
    {
        return view('settings.invoice', [
            'settings' => SystemSetting::current(),
        ]);
    }

    public function updateInvoice(Request $request): RedirectResponse
    {
        $settings = SystemSetting::current();
        $validated = $request->validate([
            'gst_invoice_prefix' => ['required', 'string', 'max:30', $this->prefixRule()],
            'normal_bill_prefix' => ['required', 'string', 'max:30', $this->prefixRule()],
            'next_gst_invoice_no' => ['required', 'integer', 'min:1'],
            'next_normal_bill_no' => ['required', 'integer', 'min:1'],
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

        return back()->with('success', 'Invoice settings updated successfully.');
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
