@extends('layouts.erp')

@section('title', $title ?? 'Payments')

@section('content')
    <div class="mb-5 flex items-center justify-between">
        <div>
            <h2 class="text-lg font-semibold text-gray-900">{{ $title ?? 'Receipt and Payment History' }}</h2>
            <p class="text-sm text-gray-500">{{ $description ?? 'All customer receipts and supplier payments posted to ledger and cashbook.' }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('receipts.create') }}" class="rounded bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">New Receipt</a>
            <a href="{{ route('supplier-payments.create') }}" class="rounded bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700">Supplier Payment</a>
        </div>
    </div>

    @if ($showFilters ?? true)
        <form method="GET" action="{{ route('payments.index') }}" class="mb-5 rounded bg-white p-5 shadow">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Transaction Type</label>
                    <select name="transaction_type" class="w-full rounded border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500">
                        <option value="">All</option>
                        <option value="receipt" @selected(($filters['transaction_type'] ?? '') === 'receipt')>Receipts</option>
                        <option value="payment" @selected(($filters['transaction_type'] ?? '') === 'payment')>Payments</option>
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Payment Mode</label>
                    <select name="payment_mode" class="w-full rounded border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500">
                        <option value="">All</option>
                        <option value="cash" @selected(($filters['payment_mode'] ?? '') === 'cash')>Cash</option>
                        <option value="bank" @selected(($filters['payment_mode'] ?? '') === 'bank')>Bank</option>
                        <option value="upi" @selected(($filters['payment_mode'] ?? '') === 'upi')>UPI</option>
                        <option value="cheque" @selected(($filters['payment_mode'] ?? '') === 'cheque')>Cheque</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button class="w-full rounded bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">Filter</button>
                </div>
                <div class="flex items-end">
                    <a href="{{ route('payments.index') }}" class="w-full rounded border border-gray-300 px-4 py-2 text-center text-sm font-semibold text-gray-700 hover:bg-gray-50">Reset</a>
                </div>
            </div>
        </form>
    @endif

    <div class="overflow-hidden rounded bg-white shadow">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-slate-100 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                <tr>
                    <th class="px-4 py-3">No</th>
                    <th class="px-4 py-3">Date</th>
                    <th class="px-4 py-3">Type</th>
                    <th class="px-4 py-3">Party</th>
                    <th class="px-4 py-3">Reference</th>
                    <th class="px-4 py-3 text-right">Amount</th>
                    <th class="px-4 py-3">Mode</th>
                    <th class="px-4 py-3">Notes</th>
                    <th class="px-4 py-3 text-right">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($payments as $payment)
                    @php
                        $partyName = match ($payment->party_type) {
                            'customer' => ($customerNames[$payment->party_id] ?? 'Customer #'.$payment->party_id),
                            'supplier' => ($supplierNames[$payment->party_id] ?? 'Supplier #'.$payment->party_id),
                            default => ucfirst($payment->party_type).' #'.$payment->party_id,
                        };
                        $showRoute = $payment->transaction_type === 'receipt' && $payment->party_type === 'customer'
                            ? route('receipts.show', $payment)
                            : ($payment->transaction_type === 'payment' && $payment->party_type === 'supplier'
                                ? route('supplier-payments.show', $payment)
                                : route('payments.show', $payment));
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-900">{{ $payment->payment_no }}</td>
                        <td class="px-4 py-3 text-gray-700">{{ $payment->payment_date?->format('d M Y') }}</td>
                        <td class="px-4 py-3">
                            <span class="rounded px-2 py-1 text-xs font-semibold {{ $payment->transaction_type === 'receipt' ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">
                                {{ ucfirst($payment->transaction_type) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-gray-700">{{ $partyName }}</td>
                        <td class="px-4 py-3 text-gray-700">
                            {{ $payment->reference_type ? ucfirst(str_replace('_', ' ', $payment->reference_type)) : '-' }}
                            @if ($payment->reference_id)
                                #{{ $payment->reference_id }}
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right font-semibold text-gray-900">Rs. {{ number_format((float) $payment->amount, 2) }}</td>
                        <td class="px-4 py-3 text-gray-700">{{ strtoupper($payment->payment_mode) }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $payment->notes ?: '-' }}</td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex justify-end gap-3">
                                <a href="{{ $showRoute }}" class="font-semibold text-slate-700 hover:text-slate-900">View</a>
                                <a href="{{ route('payments.pdf', $payment) }}" target="_blank" class="font-semibold text-slate-700 hover:text-slate-900">PDF</a>
                                <a href="{{ route('payments.pdf', ['payment' => $payment, 'download' => 1]) }}" class="font-semibold text-slate-700 hover:text-slate-900">Download</a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-4 py-8 text-center text-gray-500">No receipts or payments recorded yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-5">
        {{ $payments->withQueryString()->links() }}
    </div>
@endsection
