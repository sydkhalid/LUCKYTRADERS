@extends('layouts.app')

@section('title', 'Customers')

@section('content')
    <x-erp.page-header
        title="Customers"
        description="GST, contact details, and opening balances for billing."
        kicker="Receivables"
    >
        <x-slot:actions>
            <a href="{{ route('customers.create') }}" class="erp-primary-button">Add Customer</a>
        </x-slot:actions>
    </x-erp.page-header>

    <form id="customerFilters" class="mb-5 grid grid-cols-1 gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:grid-cols-3">
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
        id="customersTable"
        :ajax-url="route('erp.datatables', 'customers')"
        filter-form="#customerFilters"
        search-placeholder="Search customer, phone, GST..."
        empty="No customers found."
    >
        <thead>
            <tr>
                <th class="px-4 py-3" data-column="name">Name</th>
                <th class="px-4 py-3" data-column="phone">Phone</th>
                <th class="px-4 py-3" data-column="gst_number">GST Number</th>
                <th class="px-4 py-3 text-right" data-column="opening_balance">Opening Balance</th>
                <th class="px-4 py-3" data-column="status">Status</th>
                <th class="px-4 py-3 text-right" data-column="actions" data-orderable="false" data-searchable="false">Action</th>
            </tr>
        </thead>
        <tbody></tbody>
    </x-erp.datatable>
@endsection
