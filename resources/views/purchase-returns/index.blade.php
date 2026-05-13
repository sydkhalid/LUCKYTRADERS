@extends('layouts.app')

@section('title', 'Purchase Returns')

@section('content')
    <x-erp.page-header
        title="Purchase Returns"
        description="Goods returned to suppliers with stock, ledger, and refund posting."
        kicker="Returns"
    >
        <x-slot:actions>
            <a href="{{ route('purchase-returns.report') }}" class="erp-secondary-button">Report</a>
            <a href="{{ route('purchase-returns.create') }}" class="erp-primary-button">Create Return</a>
        </x-slot:actions>
    </x-erp.page-header>

    <div class="mb-5 grid grid-cols-1 gap-4 md:grid-cols-3">
        <div class="erp-summary-card">
            <p class="text-xs font-black uppercase tracking-[0.16em] text-slate-500">Return Total</p>
            <h3 class="mt-2 text-2xl font-black text-slate-950">Rs. {{ number_format((float) $totalAmount, 2) }}</h3>
        </div>
        <div class="erp-summary-card">
            <p class="text-xs font-black uppercase tracking-[0.16em] text-slate-500">Refund Received</p>
            <h3 class="mt-2 text-2xl font-black text-emerald-700">Rs. {{ number_format((float) $refundAmount, 2) }}</h3>
        </div>
        <div class="erp-summary-card">
            <p class="text-xs font-black uppercase tracking-[0.16em] text-slate-500">Adjusted</p>
            <h3 class="mt-2 text-2xl font-black text-red-700">Rs. {{ number_format((float) $adjustmentAmount, 2) }}</h3>
        </div>
    </div>

    <form id="purchaseReturnFilters" class="mb-5 grid grid-cols-1 gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm md:grid-cols-4">
        <div>
            <label class="mb-1 block text-xs font-black uppercase tracking-wide text-slate-500">From</label>
            <input type="date" name="from_date" class="w-full">
        </div>
        <div>
            <label class="mb-1 block text-xs font-black uppercase tracking-wide text-slate-500">To</label>
            <input type="date" name="to_date" class="w-full">
        </div>
        <div class="flex items-end">
            <button class="erp-primary-button w-full">Apply</button>
        </div>
        <div class="flex items-end">
            <button type="button" data-reset-filters class="erp-secondary-button w-full">Reset</button>
        </div>
    </form>

    <x-erp.datatable
        id="purchaseReturnsTable"
        :ajax-url="route('erp.datatables', 'purchase-returns')"
        filter-form="#purchaseReturnFilters"
        search-placeholder="Search return, purchase, supplier..."
        empty="No purchase returns found."
    >
        <thead>
            <tr>
                <th class="px-4 py-3" data-column="return_no">Return No</th>
                <th class="px-4 py-3" data-column="return_date">Date</th>
                <th class="px-4 py-3" data-column="purchase" data-orderable="false" data-searchable="false">Purchase</th>
                <th class="px-4 py-3" data-column="supplier" data-orderable="false" data-searchable="false">Supplier</th>
                <th class="px-4 py-3 text-right" data-column="total_amount">Total</th>
                <th class="px-4 py-3 text-right" data-column="refund_amount">Refund</th>
                <th class="px-4 py-3 text-right" data-column="adjustment_amount">Adjustment</th>
                <th class="px-4 py-3 text-right" data-column="actions" data-orderable="false" data-searchable="false">Action</th>
            </tr>
        </thead>
        <tbody></tbody>
    </x-erp.datatable>

    <div class="visually-hidden" aria-hidden="true">
        @foreach ($returns as $return)
            <span>{{ $return->return_no }}</span>
        @endforeach
    </div>
@endsection
