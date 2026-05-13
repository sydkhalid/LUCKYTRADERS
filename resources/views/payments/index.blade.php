@extends('layouts.erp')

@section('title', $title ?? 'Payments')

@section('content')
    @php
        $paymentsTableModule = request()->routeIs('receipts.index')
            ? 'receipts'
            : (request()->routeIs('supplier-payments.index') ? 'supplier-payments' : 'payments');
    @endphp

    <x-erp.page-header
        :title="$title ?? 'Receipt and Payment History'"
        :description="$description ?? 'All customer receipts and supplier payments posted to ledger and cashbook.'"
        kicker="Accounts"
    >
        <x-slot:actions>
            <a href="{{ route('receipts.create') }}" class="erp-secondary-button">New Receipt</a>
            <a href="{{ route('supplier-payments.create') }}" class="erp-primary-button">Supplier Payment</a>
        </x-slot:actions>
    </x-erp.page-header>

    @if ($showFilters ?? true)
        <form id="paymentFilters" class="mb-5 grid grid-cols-1 gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm md:grid-cols-6">
            <div>
                <label class="mb-1 block text-xs font-black uppercase tracking-wide text-slate-500">From</label>
                <input type="date" name="from_date" value="{{ $filters['from_date'] ?? '' }}" class="w-full">
            </div>
            <div>
                <label class="mb-1 block text-xs font-black uppercase tracking-wide text-slate-500">To</label>
                <input type="date" name="to_date" value="{{ $filters['to_date'] ?? '' }}" class="w-full">
            </div>
            <div>
                <label class="mb-1 block text-xs font-black uppercase tracking-wide text-slate-500">Transaction</label>
                <select name="transaction_type" data-searchable class="w-full">
                    <option value="">All</option>
                    <option value="receipt" @selected(($filters['transaction_type'] ?? '') === 'receipt')>Receipts</option>
                    <option value="payment" @selected(($filters['transaction_type'] ?? '') === 'payment')>Payments</option>
                </select>
            </div>
            <div>
                <label class="mb-1 block text-xs font-black uppercase tracking-wide text-slate-500">Mode</label>
                <select name="payment_mode" data-searchable class="w-full">
                    <option value="">All Modes</option>
                    <option value="cash" @selected(($filters['payment_mode'] ?? '') === 'cash')>Cash</option>
                    <option value="bank" @selected(($filters['payment_mode'] ?? '') === 'bank')>Bank</option>
                    <option value="upi" @selected(($filters['payment_mode'] ?? '') === 'upi')>UPI</option>
                    <option value="cheque" @selected(($filters['payment_mode'] ?? '') === 'cheque')>Cheque</option>
                </select>
            </div>
            <div class="flex items-end">
                <button class="erp-primary-button w-full">Apply</button>
            </div>
            <div class="flex items-end">
                <button type="button" data-reset-filters class="erp-secondary-button w-full">Reset</button>
            </div>
        </form>
    @endif

    <x-erp.datatable
        id="paymentsTable"
        :ajax-url="route('erp.datatables', $paymentsTableModule)"
        :filter-form="($showFilters ?? true) ? '#paymentFilters' : null"
        search-placeholder="Search payment, party, reference..."
        empty="No receipts or payments recorded yet."
    >
        <thead>
            <tr>
                <th class="px-4 py-3" data-column="payment_no">No</th>
                <th class="px-4 py-3" data-column="payment_date">Date</th>
                <th class="px-4 py-3" data-column="transaction_type">Type</th>
                <th class="px-4 py-3" data-column="party" data-orderable="false" data-searchable="false">Party</th>
                <th class="px-4 py-3" data-column="reference" data-orderable="false" data-searchable="false">Reference</th>
                <th class="px-4 py-3 text-right" data-column="amount">Amount</th>
                <th class="px-4 py-3" data-column="payment_mode">Mode</th>
                <th class="px-4 py-3 text-right" data-column="actions" data-orderable="false" data-searchable="false">Action</th>
            </tr>
        </thead>
        <tbody></tbody>
    </x-erp.datatable>
@endsection
