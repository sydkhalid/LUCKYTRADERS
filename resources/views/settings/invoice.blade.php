@extends('layouts.app')

@section('title', 'Invoice Settings')

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-lg font-semibold text-gray-900">Invoice Numbering</h2>
            <p class="text-sm text-gray-500">GST invoices and normal bills use separate configured number series.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('settings.company') }}" class="rounded border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Company Settings</a>
            <a href="{{ route('settings.bank') }}" class="rounded border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Bank Details</a>
            <a href="{{ route('settings.terms') }}" class="rounded border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Terms</a>
            <a href="{{ route('settings.media') }}" class="rounded border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Logo & Signature</a>
            <a href="{{ route('settings.testing-checklist') }}" class="rounded border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Testing Checklist</a>
            @role('Super Admin')
                <a href="{{ route('settings.backups.index') }}" class="rounded border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Backups</a>
            @endrole
        </div>
    </div>

    @if ($errors->any())
        <div class="mb-5 rounded border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('settings.invoice.update') }}" enctype="multipart/form-data" class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm" data-ajax-form>
        @csrf
        @method('PATCH')

        <div class="grid gap-5 md:grid-cols-2 lg:grid-cols-5">
            <div>
                <label class="block text-sm font-semibold text-gray-700">GST Invoice Prefix</label>
                <input name="gst_invoice_prefix" value="{{ old('gst_invoice_prefix', $settings->gst_invoice_prefix) }}" class="mt-1 w-full rounded border-gray-300 text-sm uppercase shadow-sm focus:border-slate-500 focus:ring-slate-500" required>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700">Next GST Invoice No</label>
                <input type="number" min="1" name="next_gst_invoice_no" value="{{ old('next_gst_invoice_no', $settings->next_gst_invoice_no) }}" class="mt-1 w-full rounded border-gray-300 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500" required>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700">Normal Bill Prefix</label>
                <input name="normal_bill_prefix" value="{{ old('normal_bill_prefix', $settings->normal_bill_prefix) }}" class="mt-1 w-full rounded border-gray-300 text-sm uppercase shadow-sm focus:border-slate-500 focus:ring-slate-500" required>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700">Next Normal Bill No</label>
                <input type="number" min="1" name="next_normal_bill_no" value="{{ old('next_normal_bill_no', $settings->next_normal_bill_no) }}" class="mt-1 w-full rounded border-gray-300 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500" required>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700">Quotation Prefix</label>
                <input name="quotation_prefix" value="{{ old('quotation_prefix', $settings->quotation_prefix) }}" class="mt-1 w-full rounded border-gray-300 text-sm uppercase shadow-sm focus:border-slate-500 focus:ring-slate-500" required>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700">Next Quotation No</label>
                <input type="number" min="1" name="next_quotation_no" value="{{ old('next_quotation_no', $settings->next_quotation_no) }}" class="mt-1 w-full rounded border-gray-300 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500" required>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700">Purchase Prefix</label>
                <input name="purchase_prefix" value="{{ old('purchase_prefix', $settings->purchase_prefix) }}" class="mt-1 w-full rounded border-gray-300 text-sm uppercase shadow-sm focus:border-slate-500 focus:ring-slate-500" required>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700">Next Purchase No</label>
                <input type="number" min="1" name="next_purchase_no" value="{{ old('next_purchase_no', $settings->next_purchase_no) }}" class="mt-1 w-full rounded border-gray-300 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500" required>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700">Receipt Prefix</label>
                <input name="receipt_prefix" value="{{ old('receipt_prefix', $settings->receipt_prefix) }}" class="mt-1 w-full rounded border-gray-300 text-sm uppercase shadow-sm focus:border-slate-500 focus:ring-slate-500" required>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700">Next Receipt No</label>
                <input type="number" min="1" name="next_receipt_no" value="{{ old('next_receipt_no', $settings->next_receipt_no) }}" class="mt-1 w-full rounded border-gray-300 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500" required>
            </div>
        </div>

        <div class="mt-6 grid gap-5 md:grid-cols-2">
            <div>
                <label class="block text-sm font-semibold text-gray-700">Terms and Conditions</label>
                <textarea name="terms_and_conditions" rows="8" class="mt-1 w-full rounded border-gray-300 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500">{{ old('terms_and_conditions', $settings->terms_and_conditions) }}</textarea>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700">Bank Details</label>
                <textarea name="bank_details" rows="8" class="mt-1 w-full rounded border-gray-300 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500">{{ old('bank_details', $settings->bank_details) }}</textarea>
            </div>
        </div>

        <div class="mt-8 border-t border-gray-200 pt-6">
            <label class="block text-sm font-semibold text-gray-700">Signature Image</label>
            <div class="mt-2 flex flex-wrap items-center gap-4">
                @if ($settings->signature_image)
                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($settings->signature_image) }}" alt="Signature" class="h-16 max-w-56 rounded border border-gray-200 bg-white object-contain p-2">
                @endif
                <input type="file" name="signature_image" accept="image/*" class="block text-sm text-gray-700 file:mr-4 file:rounded file:border-0 file:bg-slate-900 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white">
            </div>
        </div>

        <div class="mt-8 flex justify-end">
            <button class="rounded bg-slate-900 px-5 py-2 text-sm font-semibold text-white hover:bg-slate-800">Save Invoice Settings</button>
        </div>
    </form>
@endsection
