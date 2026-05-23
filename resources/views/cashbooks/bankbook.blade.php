@extends('layouts.app')

@section('title', 'Bankbook')

@section('content')
    <div class="mb-5 flex items-center justify-between">
        <div>
            <h2 class="text-lg font-semibold text-gray-900">Bankbook Report</h2>
            <p class="text-sm text-gray-500">Bank, UPI, and cheque receipts and payments.</p>
        </div>
        <a href="{{ route('cashbook.index') }}" class="rounded bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">Cashbook</a>
    </div>

    <div class="mb-5 grid grid-cols-1 gap-4 md:grid-cols-3">
        <div class="rounded bg-white p-5 shadow">
            <p class="text-sm text-gray-500">Bank In</p>
            <h3 class="mt-1 text-2xl font-bold text-emerald-700">₹ {{ number_format((float) $totalIn, 2) }}</h3>
        </div>
        <div class="rounded bg-white p-5 shadow">
            <p class="text-sm text-gray-500">Bank Out</p>
            <h3 class="mt-1 text-2xl font-bold text-red-700">₹ {{ number_format((float) $totalOut, 2) }}</h3>
        </div>
        <div class="rounded bg-white p-5 shadow">
            <p class="text-sm text-gray-500">Bank Balance</p>
            <h3 class="mt-1 text-2xl font-bold text-gray-900">₹ {{ number_format((float) $balance, 2) }}</h3>
        </div>
    </div>

    <form method="GET" action="{{ route('bankbook.index') }}" class="mb-5 rounded bg-white p-5 shadow">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">From Date</label>
                <input type="date" name="from_date" value="{{ $filters['from_date'] ?? '' }}" class="w-full rounded border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500">
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">To Date</label>
                <input type="date" name="to_date" value="{{ $filters['to_date'] ?? '' }}" class="w-full rounded border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500">
            </div>
            <div class="flex items-end">
                <button class="w-full rounded bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">Filter</button>
            </div>
            <div class="flex items-end">
                <a href="{{ route('bankbook.index') }}" class="w-full rounded border border-gray-300 px-4 py-2 text-center text-sm font-semibold text-gray-700 hover:bg-gray-50">Reset</a>
            </div>
        </div>
    </form>

    <div class="overflow-hidden rounded bg-white shadow">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-slate-100 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                <tr>
                    <th class="px-4 py-3">Date</th>
                    <th class="px-4 py-3">Type</th>
                    <th class="px-4 py-3">Mode</th>
                    <th class="px-4 py-3">Reference</th>
                    <th class="px-4 py-3 text-right">Amount</th>
                    <th class="px-4 py-3">Remarks</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($entries as $entry)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-gray-700">{{ $entry->entry_date?->format('d M Y') }}</td>
                        <td class="px-4 py-3">
                            <span class="rounded px-2 py-1 text-xs font-semibold {{ $entry->transaction_type === 'bank_in' ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">
                                {{ str_replace('_', ' ', strtoupper($entry->transaction_type)) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-gray-700">{{ strtoupper($entry->payment_mode) }}</td>
                        <td class="px-4 py-3 text-gray-700">{{ ucfirst($entry->reference_type ?? '-') }} @if ($entry->reference_id)#{{ $entry->reference_id }}@endif</td>
                        <td class="px-4 py-3 text-right font-semibold text-gray-900">₹ {{ number_format((float) $entry->amount, 2) }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $entry->remarks ?: '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-gray-500">No bank entries found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-5">
        {{ $entries->withQueryString()->links() }}
    </div>
@endsection
