@extends('layouts.app')

@section('title', 'Expense Details')

@section('content')
    <div class="mb-5 flex items-center justify-between">
        <div>
            <h2 class="text-lg font-semibold text-gray-900">{{ $expense->expense_no }}</h2>
            <p class="text-sm text-gray-500">{{ $expense->category?->name ?? 'Expense' }} paid on {{ $expense->expense_date?->format('d M Y') }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('expenses.pdf', $expense) }}" target="_blank" class="rounded border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Voucher</a>
            <a href="{{ route('expenses.pdf', ['expense' => $expense, 'download' => 1]) }}" class="rounded border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Download</a>
            <a href="{{ route('expenses.index') }}" class="rounded border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Back</a>
        </div>
    </div>

    <div class="mb-5 grid grid-cols-1 gap-4 md:grid-cols-3">
        <div class="rounded bg-white p-5 shadow">
            <p class="text-sm text-gray-500">Amount</p>
            <h3 class="mt-1 text-2xl font-bold text-red-700">₹ {{ number_format((float) $expense->amount, 2) }}</h3>
        </div>
        <div class="rounded bg-white p-5 shadow">
            <p class="text-sm text-gray-500">Payment Mode</p>
            <h3 class="mt-1 text-xl font-bold text-gray-900">{{ strtoupper($expense->payment_mode) }}</h3>
        </div>
        <div class="rounded bg-white p-5 shadow">
            <p class="text-sm text-gray-500">Book Entry</p>
            <h3 class="mt-1 text-xl font-bold text-gray-900">{{ $expense->payment_mode === 'cash' ? 'Cashbook Out' : 'Bankbook Out' }}</h3>
        </div>
    </div>

    <div class="rounded bg-white p-6 shadow">
        <h3 class="mb-4 text-base font-semibold text-gray-900">Expense Information</h3>
        <dl class="grid grid-cols-1 gap-5 md:grid-cols-2">
            <div>
                <dt class="text-sm font-medium text-gray-500">Category</dt>
                <dd class="mt-1 text-sm font-semibold text-gray-900">{{ $expense->category?->name ?? '-' }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Paid To</dt>
                <dd class="mt-1 text-sm font-semibold text-gray-900">{{ $expense->paid_to ?: '-' }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Cashbook / Bankbook Ref</dt>
                <dd class="mt-1 text-sm font-semibold text-gray-900">{{ $cashbook?->transaction_type ? strtoupper(str_replace('_', ' ', $cashbook->transaction_type)) : '-' }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Ledger Balance</dt>
                <dd class="mt-1 text-sm font-semibold text-gray-900">₹ {{ number_format((float) ($ledger->balance ?? 0), 2) }}</dd>
            </div>
            <div class="md:col-span-2">
                <dt class="text-sm font-medium text-gray-500">Notes</dt>
                <dd class="mt-1 text-sm text-gray-700">{{ $expense->notes ?: '-' }}</dd>
            </div>
        </dl>
    </div>
@endsection
