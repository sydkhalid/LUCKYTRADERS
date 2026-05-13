@extends('layouts.app')

@section('title', 'Loan Details')

@section('content')
    <div class="mb-5 flex items-center justify-between">
        <div>
            <h2 class="text-lg font-semibold text-gray-900">{{ $loan->loan_no }} - {{ $loan->party_name }}</h2>
            <p class="text-sm text-gray-500">{{ $loan->typeLabel() }} recorded on {{ $loan->loan_date?->format('d M Y') }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('loans.pdf', $loan) }}" target="_blank" class="rounded border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Loan PDF</a>
            <a href="{{ route('loans.pdf', ['loan' => $loan, 'download' => 1]) }}" class="rounded border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Download</a>
            <a href="{{ route('loans.transactions.index', $loan) }}" class="rounded border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">History</a>
            @if ($loan->status === 'active' && $transactionTypes !== [])
                <a href="{{ route('loans.transactions.create', $loan) }}" class="rounded bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">Add Transaction</a>
            @endif
            <a href="{{ route('loans.index') }}" class="rounded border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Back</a>
        </div>
    </div>

    <div class="mb-5 grid grid-cols-1 gap-4 md:grid-cols-4">
        <div class="rounded bg-white p-5 shadow">
            <p class="text-sm text-gray-500">Principal</p>
            <h3 class="mt-1 text-xl font-bold text-gray-900">Rs. {{ number_format((float) $loan->principal_amount, 2) }}</h3>
        </div>
        <div class="rounded bg-white p-5 shadow">
            <p class="text-sm text-gray-500">Interest</p>
            <h3 class="mt-1 text-xl font-bold text-gray-900">Rs. {{ number_format((float) $loan->total_interest, 2) }}</h3>
        </div>
        <div class="rounded bg-white p-5 shadow">
            <p class="text-sm text-gray-500">Paid / Returned</p>
            <h3 class="mt-1 text-xl font-bold text-emerald-700">Rs. {{ number_format((float) $loan->paid_amount, 2) }}</h3>
        </div>
        <div class="rounded bg-white p-5 shadow">
            <p class="text-sm text-gray-500">Balance</p>
            <h3 class="mt-1 text-xl font-bold text-red-700">Rs. {{ number_format((float) $loan->balance_amount, 2) }}</h3>
        </div>
    </div>

    <div class="mb-5 rounded bg-white p-5 shadow">
        <div class="grid grid-cols-1 gap-4 text-sm md:grid-cols-4">
            <div>
                <p class="text-gray-500">Status</p>
                <p class="mt-1 font-semibold text-gray-900">{{ ucfirst($loan->status) }}</p>
            </div>
            <div>
                <p class="text-gray-500">Interest Type</p>
                <p class="mt-1 font-semibold text-gray-900">{{ ucfirst($loan->interest_type) }} @if ((float) $loan->interest_percentage > 0)({{ number_format((float) $loan->interest_percentage, 2) }}%)@endif</p>
            </div>
            <div>
                <p class="text-gray-500">Phone</p>
                <p class="mt-1 font-semibold text-gray-900">{{ $loan->party_phone ?: '-' }}</p>
            </div>
            <div>
                <p class="text-gray-500">Partner</p>
                <p class="mt-1 font-semibold text-gray-900">{{ $loan->partner?->name ?? ($loan->partner_id ? 'Partner #'.$loan->partner_id : '-') }}</p>
            </div>
        </div>
        @if ($loan->notes)
            <p class="mt-4 border-t border-slate-100 pt-4 text-sm text-gray-700">{{ $loan->notes }}</p>
        @endif
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
                        <td class="px-4 py-3 text-gray-700">{{ strtoupper($transaction->payment_mode) }}</td>
                        <td class="px-4 py-3 text-right font-semibold text-gray-900">Rs. {{ number_format((float) $transaction->amount, 2) }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $transaction->notes ?: '-' }}</td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex justify-end gap-3">
                                <a href="{{ route('loans.transactions.pdf', ['loan' => $loan, 'transaction' => $transaction]) }}" target="_blank" class="font-semibold text-slate-700 hover:text-slate-900">Voucher</a>
                                <a href="{{ route('loans.transactions.pdf', ['loan' => $loan, 'transaction' => $transaction, 'download' => 1]) }}" class="font-semibold text-slate-700 hover:text-slate-900">Download</a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-gray-500">No loan transactions found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-5">
        {{ $transactions->links() }}
    </div>
@endsection
