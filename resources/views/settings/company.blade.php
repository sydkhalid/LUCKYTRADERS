@extends('layouts.erp')

@section('title', 'Company Settings')

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-lg font-semibold text-gray-900">Company Profile</h2>
            <p class="text-sm text-gray-500">Used on invoices, quotations, receipts, vouchers, and reports.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('settings.invoice') }}" class="rounded border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Invoice Settings</a>
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

    <form method="POST" action="{{ route('settings.company.update') }}" enctype="multipart/form-data" class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm" data-ajax-form>
        @csrf
        @method('PATCH')

        <div class="grid gap-5 md:grid-cols-2">
            <div>
                <label class="block text-sm font-semibold text-gray-700">Company Name</label>
                <input name="company_name" value="{{ old('company_name', $settings->company_name) }}" class="mt-1 w-full rounded border-gray-300 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500" required>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700">GST Number</label>
                <input name="gst_number" value="{{ old('gst_number', $settings->gst_number) }}" class="mt-1 w-full rounded border-gray-300 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700">Phone</label>
                <input name="phone" value="{{ old('phone', $settings->phone) }}" class="mt-1 w-full rounded border-gray-300 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700">Email</label>
                <input type="email" name="email" value="{{ old('email', $settings->email) }}" class="mt-1 w-full rounded border-gray-300 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500">
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-semibold text-gray-700">Address</label>
                <textarea name="address" rows="3" class="mt-1 w-full rounded border-gray-300 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500">{{ old('address', $settings->address) }}</textarea>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700">State</label>
                <input name="state" value="{{ old('state', $settings->state) }}" class="mt-1 w-full rounded border-gray-300 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700">City</label>
                <input name="city" value="{{ old('city', $settings->city) }}" class="mt-1 w-full rounded border-gray-300 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700">Pincode</label>
                <input name="pincode" value="{{ old('pincode', $settings->pincode) }}" class="mt-1 w-full rounded border-gray-300 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700">Low Stock Threshold</label>
                <input type="number" min="0" step="0.001" name="low_stock_threshold" value="{{ old('low_stock_threshold', $systemSettings->low_stock_threshold ?? 10) }}" class="mt-1 w-full rounded border-gray-300 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500">
            </div>
        </div>

        <div class="mt-8 border-t border-gray-200 pt-6">
            <label class="block text-sm font-semibold text-gray-700">Company Logo</label>
            <div class="mt-2 flex flex-wrap items-center gap-4">
                @if ($settings->logo)
                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($settings->logo) }}" alt="Company logo" class="h-16 max-w-48 rounded border border-gray-200 bg-white object-contain p-2">
                @endif
                <input type="file" name="logo" accept="image/*" class="block text-sm text-gray-700 file:mr-4 file:rounded file:border-0 file:bg-slate-900 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white">
            </div>
        </div>

        <div class="mt-8 flex justify-end">
            <button class="rounded bg-slate-900 px-5 py-2 text-sm font-semibold text-white hover:bg-slate-800">Save Company Settings</button>
        </div>
    </form>
@endsection
