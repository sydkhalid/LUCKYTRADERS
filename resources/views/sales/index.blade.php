@extends('layouts.app')

@section('title', 'Sales / Billing')

@section('content')
    <x-erp.page-header
        title="Sales / Billing"
        description="GST invoices and normal bills with stock, payment, and profit posting."
        kicker="Billing"
    >
        <x-slot:actions>
            <a href="{{ route('sales.create') }}" class="erp-primary-button">Create Sale</a>
        </x-slot:actions>
    </x-erp.page-header>

    <form id="saleFilters" class="mb-5 grid grid-cols-1 gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm md:grid-cols-6">
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
        id="salesTable"
        :ajax-url="route('erp.datatables', 'sales')"
        filter-form="#saleFilters"
        search-placeholder="Search invoice, customer, bill..."
        empty="No sales found."
    >
        <thead>
            <tr>
                <th class="px-4 py-3" data-column="sale_no">Sale No</th>
                <th class="px-4 py-3" data-column="customer" data-orderable="false" data-searchable="false">Customer</th>
                <th class="px-4 py-3" data-column="sale_date">Date</th>
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
