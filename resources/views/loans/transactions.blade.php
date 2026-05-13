@extends('layouts.erp')

@section('title', 'Loan History')

@section('content')
    <div class="mb-5 flex items-center justify-between">
        <div>
            <h2 class="text-lg font-semibold text-gray-900">{{ $loan->loan_no }} Repayment History</h2>
            <p class="text-sm text-gray-500">{{ $loan->party_name }} - Balance Rs. {{ number_format((float) $loan->balance_amount, 2) }}</p>
        </div>
        <a href="{{ route('loans.show', $loan) }}" class="rounded border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Back</a>
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
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($transactions as $transaction)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-gray-700">{{ $transaction->transaction_date?->format('d M Y') }}</td>
                        <td class="px-4 py-3 font-medium text-gray-900">{{ $transaction->typeLabel() }}</td>
                        <td class="px-4 py-3 text-gray-700">{{ strtoupper($transaction->payment_mode) }}</td>
                        <td class="px-4 py-3 text-right font-semibold text-gray-900">Rs. {{ number_format((float) $transaction->amount, 2) }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $transaction->notes ?: '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-gray-500">No transactions found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-5">
        {{ $transactions->links() }}
    </div>
@endsection
