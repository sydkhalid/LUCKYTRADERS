@extends('layouts.erp')

@section('title', 'Partner Ledger')

@section('content')
    <div class="mb-5 flex items-center justify-between">
        <div>
            <h2 class="text-lg font-semibold text-gray-900">{{ $partner->name }}</h2>
            <p class="text-sm text-gray-500">Current returnable capital: Rs. {{ number_format((float) $partner->current_investment, 2) }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('partners.investments.create', $partner) }}" class="rounded bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">Add Investment</a>
            <a href="{{ route('partners.withdrawals.create', $partner) }}" class="rounded bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700">Add Withdrawal</a>
            <a href="{{ route('partners.transactions.create', ['partner' => $partner, 'transaction_type' => 'return']) }}" class="rounded border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Return</a>
            <a href="{{ route('partners.transactions.index', $partner) }}" class="rounded border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Transactions</a>
            <a href="{{ route('partners.edit', $partner) }}" class="rounded border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Edit</a>
            <a href="{{ route('partners.index') }}" class="rounded border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Back</a>
        </div>
    </div>

    <div class="mb-5 grid grid-cols-1 gap-4 md:grid-cols-4">
        <div class="rounded bg-white p-5 shadow">
            <p class="text-sm text-gray-500">Share</p>
            <h3 class="mt-1 text-xl font-bold text-gray-900">{{ number_format((float) $partner->share_percentage, 2) }}%</h3>
        </div>
        <div class="rounded bg-white p-5 shadow">
            <p class="text-sm text-gray-500">Opening</p>
            <h3 class="mt-1 text-xl font-bold text-gray-900">Rs. {{ number_format((float) $partner->opening_investment, 2) }}</h3>
        </div>
        <div class="rounded bg-white p-5 shadow">
            <p class="text-sm text-gray-500">Current Capital</p>
            <h3 class="mt-1 text-xl font-bold text-red-700">Rs. {{ number_format((float) $partner->current_investment, 2) }}</h3>
        </div>
        <div class="rounded bg-white p-5 shadow">
            <p class="text-sm text-gray-500">Status</p>
            <h3 class="mt-1 text-xl font-bold text-gray-900">{{ ucfirst($partner->status) }}</h3>
        </div>
    </div>

    <div class="mb-5 rounded bg-white p-5 shadow">
        <div class="grid grid-cols-1 gap-4 text-sm md:grid-cols-3">
            <div>
                <p class="text-gray-500">Phone</p>
                <p class="mt-1 font-semibold text-gray-900">{{ $partner->phone ?: '-' }}</p>
            </div>
            <div>
                <p class="text-gray-500">Email</p>
                <p class="mt-1 font-semibold text-gray-900">{{ $partner->email ?: '-' }}</p>
            </div>
            <div>
                <p class="text-gray-500">Address</p>
                <p class="mt-1 font-semibold text-gray-900">{{ $partner->address ?: '-' }}</p>
            </div>
        </div>
    </div>

    <div class="mb-5 overflow-hidden rounded bg-white shadow">
        <div class="border-b border-gray-100 px-4 py-3">
            <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-600">Recent Transactions</h3>
        </div>
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
                        <td class="px-4 py-3 text-right font-semibold text-gray-900">Rs. {{ number_format((float) $transaction->amount, 2) }}</td>
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

    <div class="overflow-hidden rounded bg-white shadow">
        <div class="border-b border-gray-100 px-4 py-3">
            <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-600">Partner Ledger</h3>
        </div>
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-slate-100 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                <tr>
                    <th class="px-4 py-3">Date</th>
                    <th class="px-4 py-3">Reference</th>
                    <th class="px-4 py-3 text-right">Debit</th>
                    <th class="px-4 py-3 text-right">Credit</th>
                    <th class="px-4 py-3 text-right">Balance</th>
                    <th class="px-4 py-3">Remarks</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($ledgers as $ledger)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-gray-700">{{ $ledger->ledger_date?->format('d M Y') }}</td>
                        <td class="px-4 py-3 text-gray-700">{{ ucfirst(str_replace('_', ' ', $ledger->reference_type ?? '-')) }} @if ($ledger->reference_id)#{{ $ledger->reference_id }}@endif</td>
                        <td class="px-4 py-3 text-right text-gray-900">Rs. {{ number_format((float) $ledger->debit, 2) }}</td>
                        <td class="px-4 py-3 text-right text-gray-900">Rs. {{ number_format((float) $ledger->credit, 2) }}</td>
                        <td class="px-4 py-3 text-right font-semibold text-gray-900">Rs. {{ number_format((float) $ledger->balance, 2) }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $ledger->remarks ?: '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-gray-500">No ledger entries found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-5">
        {{ $ledgers->links() }}
    </div>
@endsection
