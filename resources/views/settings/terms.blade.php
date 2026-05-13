@extends('layouts.app')

@section('title', 'Terms Settings')

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-lg font-semibold text-gray-900">Terms and Conditions Settings</h2>
            <p class="text-sm text-gray-500">Printed at the bottom of invoices, quotations, receipts, and vouchers.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('settings.company') }}" class="rounded border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Company Settings</a>
            <a href="{{ route('settings.invoice') }}" class="rounded border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Invoice Settings</a>
            <a href="{{ route('settings.bank') }}" class="rounded border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Bank Details</a>
            <a href="{{ route('settings.media') }}" class="rounded border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Logo & Signature</a>
        </div>
    </div>

    @if ($errors->any())
        <div class="mb-5 rounded border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('settings.terms.update') }}" class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm" data-ajax-form>
        @csrf
        @method('PATCH')

        <label class="block text-sm font-semibold text-gray-700">Terms and Conditions</label>
        <textarea name="terms_and_conditions" rows="10" class="mt-1 w-full rounded border-gray-300 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500">{{ old('terms_and_conditions', $settings->terms_and_conditions) }}</textarea>

        <div class="mt-8 flex justify-end">
            <button class="rounded bg-slate-900 px-5 py-2 text-sm font-semibold text-white hover:bg-slate-800">Save Terms</button>
        </div>
    </form>
@endsection
