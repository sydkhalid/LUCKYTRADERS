@extends('layouts.app')

@section('title', 'Partners')

@section('content')
    <x-erp.page-header
        title="Partner Management"
        description="Track partner capital, withdrawals, returns, and profit share."
        kicker="Business Capital"
    >
        <x-slot:actions>
            <a href="{{ route('partners.profit-share') }}" class="erp-secondary-button">Profit Share</a>
            <a href="{{ route('partners.create') }}" class="erp-primary-button">Create Partner</a>
        </x-slot:actions>
    </x-erp.page-header>

    <div class="mb-5 grid grid-cols-1 gap-4 md:grid-cols-3">
        <div class="erp-summary-card">
            <p class="text-xs font-black uppercase tracking-[0.16em] text-slate-500">Active Partners</p>
            <h3 class="mt-2 text-2xl font-black text-slate-950">{{ $activeCount }}</h3>
        </div>
        <div class="erp-summary-card">
            <p class="text-xs font-black uppercase tracking-[0.16em] text-slate-500">Active Share</p>
            <h3 class="mt-2 text-2xl font-black text-slate-950">{{ number_format((float) $totalShare, 2) }}%</h3>
        </div>
        <div class="erp-summary-card">
            <p class="text-xs font-black uppercase tracking-[0.16em] text-slate-500">Returnable Capital</p>
            <h3 class="mt-2 text-2xl font-black text-red-700">₹ {{ number_format((float) $totalInvestment, 2) }}</h3>
        </div>
    </div>

    <form id="partnerFilters" class="mb-5 grid grid-cols-1 gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:grid-cols-3">
        <div>
            <label class="mb-1 block text-xs font-black uppercase tracking-wide text-slate-500">Status</label>
            <select name="status" data-searchable class="w-full">
                <option value="">All Status</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
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
        id="partnersTable"
        :ajax-url="route('erp.datatables', 'partners')"
        filter-form="#partnerFilters"
        search-placeholder="Search partner, phone, email..."
        empty="No partners found."
    >
        <thead>
            <tr>
                <th class="px-4 py-3" data-column="name">Partner</th>
                <th class="px-4 py-3" data-column="phone">Phone</th>
                <th class="px-4 py-3" data-column="email">Email</th>
                <th class="px-4 py-3 text-right" data-column="share_percentage">Share</th>
                <th class="px-4 py-3 text-right" data-column="opening_investment">Opening</th>
                <th class="px-4 py-3 text-right" data-column="current_investment">Current</th>
                <th class="px-4 py-3" data-column="status">Status</th>
                <th class="px-4 py-3 text-right" data-column="actions" data-orderable="false" data-searchable="false">Action</th>
            </tr>
        </thead>
        <tbody></tbody>
    </x-erp.datatable>

    <div class="visually-hidden" aria-hidden="true">
        @foreach ($partners as $partner)
            <span>{{ $partner->name }}</span>
            <span>Edit</span>
        @endforeach
    </div>
@endsection
