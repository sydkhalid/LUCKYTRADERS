@extends('layouts.app')

@section('title', 'Quotations')

@section('content')
    <x-erp.page-header
        title="Quotation Management"
        description="Create customer quotations before converting accepted offers into GST invoices or normal bills."
        kicker="Sales Pipeline"
    >
        <x-slot:actions>
            <a href="{{ route('quotations.create') }}" class="erp-primary-button">Create Quotation</a>
        </x-slot:actions>
    </x-erp.page-header>

    <div class="mb-5 grid grid-cols-1 gap-4 md:grid-cols-3">
        <div class="erp-summary-card">
            <p class="text-xs font-black uppercase tracking-[0.16em] text-slate-500">Draft</p>
            <h3 class="mt-2 text-2xl font-black text-slate-950">{{ $draftCount }}</h3>
        </div>
        <div class="erp-summary-card">
            <p class="text-xs font-black uppercase tracking-[0.16em] text-slate-500">Accepted</p>
            <h3 class="mt-2 text-2xl font-black text-emerald-700">{{ $acceptedCount }}</h3>
        </div>
        <div class="erp-summary-card">
            <p class="text-xs font-black uppercase tracking-[0.16em] text-slate-500">Converted</p>
            <h3 class="mt-2 text-2xl font-black text-slate-950">{{ $convertedCount }}</h3>
        </div>
    </div>

    <form id="quotationFilters" class="mb-5 grid grid-cols-1 gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm md:grid-cols-5">
        <div>
            <label class="mb-1 block text-xs font-black uppercase tracking-wide text-slate-500">From</label>
            <input type="date" name="from_date" class="w-full">
        </div>
        <div>
            <label class="mb-1 block text-xs font-black uppercase tracking-wide text-slate-500">To</label>
            <input type="date" name="to_date" class="w-full">
        </div>
        <div>
            <label class="mb-1 block text-xs font-black uppercase tracking-wide text-slate-500">Status</label>
            <select name="status" data-searchable class="w-full">
                <option value="">All Status</option>
                <option value="draft">Draft</option>
                <option value="sent">Sent</option>
                <option value="accepted">Accepted</option>
                <option value="rejected">Rejected</option>
                <option value="converted">Converted</option>
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
        id="quotationsTable"
        :ajax-url="route('erp.datatables', 'quotations')"
        filter-form="#quotationFilters"
        search-placeholder="Search quotation, customer, status..."
        empty="No quotations found."
    >
        <thead>
            <tr>
                <th class="px-4 py-3" data-column="quotation_no">Quotation No</th>
                <th class="px-4 py-3" data-column="customer" data-orderable="false" data-searchable="false">Customer</th>
                <th class="px-4 py-3" data-column="quotation_date">Date</th>
                <th class="px-4 py-3" data-column="valid_until">Valid Until</th>
                <th class="px-4 py-3 text-right" data-column="total_amount">Total</th>
                <th class="px-4 py-3" data-column="status">Status</th>
                <th class="px-4 py-3 text-right" data-column="actions" data-orderable="false" data-searchable="false">Action</th>
            </tr>
        </thead>
        <tbody></tbody>
    </x-erp.datatable>
@endsection
