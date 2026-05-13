@extends('layouts.app')

@section('title', 'Purchases')

@section('content')
    <x-erp.page-header
        title="Purchases"
        description="Supplier invoices, stock inward, GST input, and payable balance."
        kicker="Procurement"
    >
        <x-slot:actions>
            <a href="{{ route('purchases.create') }}" class="erp-primary-button">New Purchase</a>
        </x-slot:actions>
    </x-erp.page-header>

    <form id="purchaseFilters" class="mb-5 grid grid-cols-1 gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm md:grid-cols-6">
        <div>
            <label class="mb-1 block text-xs font-black uppercase tracking-wide text-slate-500">From</label>
            <input type="date" name="from_date" class="w-full">
        </div>
        <div>
            <label class="mb-1 block text-xs font-black uppercase tracking-wide text-slate-500">To</label>
            <input type="date" name="to_date" class="w-full">
        </div>
        <div>
            <label class="mb-1 block text-xs font-black uppercase tracking-wide text-slate-500">Payment</label>
            <select name="payment_status" data-searchable class="w-full">
                <option value="">All Status</option>
                <option value="paid">Paid</option>
                <option value="partial">Partial</option>
                <option value="pending">Pending</option>
            </select>
        </div>
        <div>
            <label class="mb-1 block text-xs font-black uppercase tracking-wide text-slate-500">Bill Type</label>
            <select name="bill_type" data-searchable class="w-full">
                <option value="">All Bills</option>
                <option value="gst">GST</option>
                <option value="non_gst">Non-GST</option>
            </select>
        </div>
        <div class="flex items-end">
            <button class="erp-primary-button w-full">Apply</button>
        </div>
        <div class="flex items-end">
            <button type="button" data-reset-filters class="erp-secondary-button w-full">Reset</button>
        </div>
    </form>

    <x-erp.datatable
        id="purchasesTable"
        :ajax-url="route('erp.datatables', 'purchases')"
        filter-form="#purchaseFilters"
        search-placeholder="Search purchase, supplier, bill..."
        empty="No purchases found."
    >
        <thead>
            <tr>
                <th class="px-4 py-3" data-column="purchase_no">Purchase No</th>
                <th class="px-4 py-3" data-column="supplier" data-orderable="false" data-searchable="false">Supplier</th>
                <th class="px-4 py-3" data-column="purchase_date">Date</th>
                <th class="px-4 py-3" data-column="bill_type">Bill</th>
                <th class="px-4 py-3 text-right" data-column="total_amount">Total</th>
                <th class="px-4 py-3 text-right" data-column="paid_amount">Paid</th>
                <th class="px-4 py-3 text-right" data-column="balance_amount">Balance</th>
                <th class="px-4 py-3" data-column="payment_status">Status</th>
                <th class="px-4 py-3 text-right" data-column="actions" data-orderable="false" data-searchable="false">Action</th>
            </tr>
        </thead>
        <tbody></tbody>
    </x-erp.datatable>
@endsection
