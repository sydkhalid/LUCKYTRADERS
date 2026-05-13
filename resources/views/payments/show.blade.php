@extends('layouts.app')

@section('title', $payment->transaction_type === 'receipt' ? 'Customer Receipt' : 'Supplier Payment')

@section('content')
    @php
        $isReceipt = $payment->transaction_type === 'receipt';
        $partyLabel = ucfirst($payment->party_type);
        $partyName = $party->name ?? $partyLabel.' #'.$payment->party_id;
        $referenceType = ucfirst(str_replace('_', ' ', $payment->reference_type ?? ''));
        $referenceNo = match (true) {
            $reference instanceof \App\Models\Sale => $reference->sale_no,
            $reference instanceof \App\Models\Purchase => $reference->purchase_no,
            default => $payment->reference_id ? '#'.$payment->reference_id : '-',
        };
        $ledgerRoute = $payment->party_type === 'customer' && $party
            ? route('ledgers.customers.show', $party)
            : ($payment->party_type === 'supplier' && $party ? route('ledgers.suppliers.show', $party) : null);
    @endphp

    <div class="mb-5 flex items-center justify-between">
        <div>
            <h2 class="text-lg font-semibold text-gray-900">{{ $payment->payment_no }}</h2>
            <p class="text-sm text-gray-500">{{ $isReceipt ? 'Customer receipt' : 'Supplier payment' }} posted to ledger and {{ $payment->payment_mode === 'cash' ? 'cashbook' : 'bankbook' }}.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ $backRoute ?? route('payments.index') }}" class="rounded border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Back</a>
            <a href="{{ route('payments.pdf', $payment) }}" target="_blank" class="rounded bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">Print PDF</a>
            <a href="{{ route('payments.pdf', ['payment' => $payment, 'download' => 1]) }}" class="rounded bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">Download</a>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-5 lg:grid-cols-3">
        <div class="rounded bg-white p-5 shadow">
            <p class="text-sm text-gray-500">Amount</p>
            <h3 class="mt-1 text-2xl font-bold {{ $isReceipt ? 'text-emerald-700' : 'text-red-700' }}">Rs. {{ number_format((float) $payment->amount, 2) }}</h3>
            <p class="mt-2 text-sm text-gray-500">{{ strtoupper($payment->payment_mode) }} on {{ $payment->payment_date?->format('d M Y') }}</p>
        </div>

        <div class="rounded bg-white p-5 shadow">
            <p class="text-sm text-gray-500">Party</p>
            <h3 class="mt-1 text-lg font-semibold text-gray-900">{{ $partyName }}</h3>
            <p class="mt-2 text-sm text-gray-500">{{ $partyLabel }}</p>
            @if ($ledgerRoute)
                <a href="{{ $ledgerRoute }}" class="mt-3 inline-flex text-sm font-semibold text-slate-700 hover:text-slate-900">View Ledger</a>
            @endif
        </div>

        <div class="rounded bg-white p-5 shadow">
            <p class="text-sm text-gray-500">Reference</p>
            <h3 class="mt-1 text-lg font-semibold text-gray-900">{{ $referenceNo }}</h3>
            <p class="mt-2 text-sm text-gray-500">{{ $referenceType ?: '-' }}</p>
        </div>
    </div>

    <div class="mt-5 rounded bg-white p-6 shadow">
        <h3 class="mb-4 text-base font-semibold text-gray-900">Transaction Details</h3>
        <dl class="grid grid-cols-1 gap-5 md:grid-cols-2">
            <div>
                <dt class="text-sm font-medium text-gray-500">Payment No</dt>
                <dd class="mt-1 text-sm font-semibold text-gray-900">{{ $payment->payment_no }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Transaction Type</dt>
                <dd class="mt-1">
                    <span class="rounded px-2 py-1 text-xs font-semibold {{ $isReceipt ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">
                        {{ ucfirst($payment->transaction_type) }}
                    </span>
                </dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Payment Mode</dt>
                <dd class="mt-1 text-sm font-semibold text-gray-900">{{ strtoupper($payment->payment_mode) }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Book Entry</dt>
                <dd class="mt-1 text-sm font-semibold text-gray-900">{{ $payment->payment_mode === 'cash' ? 'Cashbook' : 'Bankbook' }}</dd>
            </div>
            <div class="md:col-span-2">
                <dt class="text-sm font-medium text-gray-500">Notes</dt>
                <dd class="mt-1 text-sm text-gray-700">{{ $payment->notes ?: '-' }}</dd>
            </div>
        </dl>
    </div>
@endsection
