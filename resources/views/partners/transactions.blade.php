@extends('layouts.app')

@section('title', 'Partner Transactions')

@section('content')
    <div class="mb-5 flex items-center justify-between">
        <div>
            <h2 class="text-lg font-semibold text-gray-900">{{ $partner->name }} Transactions</h2>
            <p class="text-sm text-gray-500">Current returnable capital: ₹ {{ number_format((float) $partner->current_investment, 2) }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('partners.transactions.create', ['partner' => $partner, 'transaction_type' => 'investment']) }}" class="rounded bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">Investment</a>
            <a href="{{ route('partners.transactions.create', ['partner' => $partner, 'transaction_type' => 'withdrawal']) }}" class="rounded bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700">Withdrawal</a>
            <a href="{{ route('partners.show', $partner) }}" class="rounded border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Back</a>
        </div>
    </div>

    <div class="overflow-hidden rounded bg-white shadow">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-slate-100 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                <tr>
                    <th class="px-4 py-3">Date</th>
                    <th class="px-4 py-3">Type</th>
                    <th class="px-4 py-3">Mode</th>
                    <th class="px-4 py-3 text-right">Amount</th>
                    <th class="px-4 py-3">Notes</th>
                    <th class="px-4 py-3 text-right">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($transactions as $transaction)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-gray-700">{{ $transaction->transaction_date?->format('d M Y') }}</td>
                        <td class="px-4 py-3 font-medium text-gray-900">{{ $transaction->typeLabel() }}</td>
                        <td class="px-4 py-3 text-gray-700">{{ $transaction->transaction_type === 'profit_share' ? '-' : strtoupper($transaction->payment_mode) }}</td>
                        <td class="px-4 py-3 text-right font-semibold text-gray-900">₹ {{ number_format((float) $transaction->amount, 2) }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $transaction->notes ?: '-' }}</td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex justify-end gap-3">
                                <a href="{{ route('partners.transactions.pdf', ['partner' => $partner, 'transaction' => $transaction]) }}" target="_blank" class="font-semibold text-slate-700 hover:text-slate-900">Voucher</a>
                                <a href="{{ route('partners.transactions.pdf', ['partner' => $partner, 'transaction' => $transaction, 'download' => 1]) }}" class="font-semibold text-slate-700 hover:text-slate-900">Download</a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-gray-500">No partner transactions found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-5">
        {{ $transactions->links() }}
    </div>
@endsection
