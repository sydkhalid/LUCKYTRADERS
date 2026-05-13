@extends('layouts.erp')

@section('title', 'Loans')

@section('content')
    <x-erp.page-header
        title="Loan Management"
        description="Track borrowed money, money given, partner withdrawals, and partner deposits."
        kicker="Finance"
    >
        <x-slot:actions>
            <a href="{{ route('loans.reports.active') }}" class="erp-secondary-button">Active Report</a>
            <a href="{{ route('loans.create') }}" class="erp-primary-button">Create Loan</a>
        </x-slot:actions>
    </x-erp.page-header>

    <div class="mb-5 grid grid-cols-1 gap-4 md:grid-cols-3">
        <div class="erp-summary-card">
            <p class="text-xs font-black uppercase tracking-[0.16em] text-slate-500">Active Loans</p>
            <h3 class="mt-2 text-2xl font-black text-slate-950">{{ $activeCount }}</h3>
        </div>
        <div class="erp-summary-card">
            <p class="text-xs font-black uppercase tracking-[0.16em] text-slate-500">Closed Loans</p>
            <h3 class="mt-2 text-2xl font-black text-slate-950">{{ $closedCount }}</h3>
        </div>
        <div class="erp-summary-card">
            <p class="text-xs font-black uppercase tracking-[0.16em] text-slate-500">Active Balance</p>
            <h3 class="mt-2 text-2xl font-black text-red-700">Rs. {{ number_format((float) $activeBalance, 2) }}</h3>
        </div>
    </div>

    <form id="loanFilters" class="mb-5 grid grid-cols-1 gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm md:grid-cols-5">
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
                <option value="active">Active</option>
                <option value="closed">Closed</option>
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
        id="loansTable"
        :ajax-url="route('erp.datatables', 'loans')"
        filter-form="#loanFilters"
        search-placeholder="Search loan, party, type..."
        empty="No loans recorded yet."
    >
        <thead>
            <tr>
                <th class="px-4 py-3" data-column="loan_no">Loan No</th>
                <th class="px-4 py-3" data-column="loan_date">Date</th>
                <th class="px-4 py-3" data-column="type">Type</th>
                <th class="px-4 py-3" data-column="party_name">Party</th>
                <th class="px-4 py-3 text-right" data-column="amount">Amount</th>
                <th class="px-4 py-3 text-right" data-column="balance_amount">Balance</th>
                <th class="px-4 py-3" data-column="status">Status</th>
                <th class="px-4 py-3 text-right" data-column="actions" data-orderable="false" data-searchable="false">Action</th>
            </tr>
        </thead>
        <tbody></tbody>
    </x-erp.datatable>
@endsection
