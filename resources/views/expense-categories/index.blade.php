@extends('layouts.app')

@section('title', 'Expense Categories')

@section('content')
    <x-erp.page-header
        title="Expense Categories"
        description="Maintain reusable heads for rent, salary, transport, fuel, and other business expenses."
        kicker="Expense Master"
    >
        <x-slot:actions>
            <a href="{{ route('expenses.index') }}" class="erp-secondary-button">Expenses</a>
            <a href="{{ route('expense-categories.create') }}" class="erp-primary-button">Create Category</a>
        </x-slot:actions>
    </x-erp.page-header>

    <form id="expenseCategoryFilters" class="mb-5 grid grid-cols-1 gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:grid-cols-3">
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
        id="expenseCategoriesTable"
        :ajax-url="route('erp.datatables', 'expense-categories')"
        filter-form="#expenseCategoryFilters"
        search-placeholder="Search expense category..."
        empty="No expense categories found."
    >
        <thead>
            <tr>
                <th class="px-4 py-3" data-column="name">Name</th>
                <th class="px-4 py-3" data-column="description">Description</th>
                <th class="px-4 py-3 text-right" data-column="expenses_count" data-searchable="false">Expenses</th>
                <th class="px-4 py-3" data-column="status">Status</th>
                <th class="px-4 py-3 text-right" data-column="actions" data-orderable="false" data-searchable="false">Action</th>
            </tr>
        </thead>
        <tbody></tbody>
    </x-erp.datatable>
@endsection
