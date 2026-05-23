@extends('layouts.app')

@section('title', $title)

@section('content')
    <div class="max-w-4xl">
        <div class="mb-5 flex items-center justify-between">
            <div>
                <h2 class="text-lg font-semibold text-gray-900">{{ $title }} - {{ $partner->name }}</h2>
                <p class="text-sm text-gray-500">Current capital: ₹ {{ number_format((float) $partner->current_investment, 2) }}</p>
            </div>
            <a href="{{ route('partners.show', $partner) }}" class="rounded border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Back</a>
        </div>

        @if ($errors->any())
            <div class="mb-5 rounded border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                <ul class="list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('partners.transactions.store', $partner) }}" class="rounded bg-white p-6 shadow" data-ajax-form>
            @csrf
            <input type="hidden" name="transaction_type" value="{{ old('transaction_type', $transactionType) }}">
            @if ($transactionType === 'profit_share')
                <input type="hidden" name="payment_mode" value="{{ old('payment_mode', 'bank') }}">
            @endif

            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Transaction Date</label>
                    <input type="date" name="transaction_date" value="{{ old('transaction_date', now()->toDateString()) }}" class="w-full rounded border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500" required>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Amount</label>
                    <input type="number" name="amount" value="{{ old('amount', request('amount')) }}" min="0.01" step="0.01" class="w-full rounded border-gray-300 text-right shadow-sm focus:border-slate-500 focus:ring-slate-500" required>
                </div>

                @if ($transactionType !== 'profit_share')
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Payment Mode</label>
                        <select name="payment_mode" class="w-full rounded border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500" required>
                            <option value="cash" @selected(old('payment_mode') === 'cash')>Cash</option>
                            <option value="bank" @selected(old('payment_mode') === 'bank')>Bank</option>
                            <option value="upi" @selected(old('payment_mode') === 'upi')>UPI</option>
                            <option value="cheque" @selected(old('payment_mode') === 'cheque')>Cheque</option>
                        </select>
                    </div>
                @endif

                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Type</label>
                    <div class="rounded border border-gray-200 bg-slate-50 px-3 py-2 text-sm font-semibold text-gray-800">
                        {{ \App\Models\PartnerTransaction::TYPES[$transactionType] ?? ucfirst($transactionType) }}
                    </div>
                </div>
            </div>

            <div class="mt-5">
                <label class="mb-1 block text-sm font-medium text-gray-700">Notes</label>
                <textarea name="notes" rows="3" class="w-full rounded border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500">{{ old('notes', request('notes')) }}</textarea>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <a href="{{ route('partners.show', $partner) }}" class="rounded border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Cancel</a>
                <button class="rounded bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">Save Transaction</button>
            </div>
        </form>
    </div>
@endsection
