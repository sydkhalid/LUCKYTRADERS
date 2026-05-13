@extends('layouts.erp')

@section('title', 'Expenses')

@section('content')
    <x-erp.page-header
        title="Expense Management"
        description="Record every business expense with cashbook, bankbook, and ledger posting."
        kicker="Operating Cost"
    >
        <x-slot:actions>
            <a href="{{ route('expense-categories.index') }}" class="erp-secondary-button">Categories</a>
            <a href="{{ route('expenses.report') }}" class="erp-secondary-button">Report</a>
            <a href="{{ route('expenses.profit-loss') }}" class="erp-secondary-button">Profit & Loss</a>
            <a href="{{ route('expenses.create') }}" class="erp-primary-button">Create Expense</a>
        </x-slot:actions>
    </x-erp.page-header>

    <div class="mb-5 grid grid-cols-1 gap-4 md:grid-cols-3">
        <div class="erp-summary-card">
            <p class="text-xs font-black uppercase tracking-[0.16em] text-slate-500">Today Expenses</p>
            <h3 class="mt-2 text-2xl font-black text-red-700">Rs. {{ number_format((float) $todayTotal, 2) }}</h3>
        </div>
        <div class="erp-summary-card">
            <p class="text-xs font-black uppercase tracking-[0.16em] text-slate-500">This Month</p>
            <h3 class="mt-2 text-2xl font-black text-red-700">Rs. {{ number_format((float) $monthTotal, 2) }}</h3>
        </div>
        <div class="erp-summary-card">
            <p class="text-xs font-black uppercase tracking-[0.16em] text-slate-500">Total Expenses</p>
            <h3 class="mt-2 text-2xl font-black text-slate-950">Rs. {{ number_format((float) $overallTotal, 2) }}</h3>
        </div>
    </div>

    <form id="expenseFilters" class="mb-5 grid grid-cols-1 gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm md:grid-cols-5">
        <div>
            <label class="mb-1 block text-xs font-black uppercase tracking-wide text-slate-500">From</label>
            <input type="date" name="from_date" class="w-full">
        </div>
        <div>
            <label class="mb-1 block text-xs font-black uppercase tracking-wide text-slate-500">To</label>
            <input type="date" name="to_date" class="w-full">
        </div>
        <div>
            <label class="mb-1 block text-xs font-black uppercase tracking-wide text-slate-500">Mode</label>
            <select name="payment_mode" data-searchable class="w-full">
                <option value="">All Modes</option>
                <option value="cash">Cash</option>
                <option value="bank">Bank</option>
                <option value="upi">UPI</option>
                <option value="cheque">Cheque</option>
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
        id="expensesTable"
        :ajax-url="route('erp.datatables', 'expenses')"
        filter-form="#expenseFilters"
        search-placeholder="Search expense, category, paid to..."
        empty="No expenses recorded yet."
    >
        <thead>
            <tr>
                <th class="px-4 py-3" data-column="expense_no">Expense No</th>
                <th class="px-4 py-3" data-column="expense_date">Date</th>
                <th class="px-4 py-3" data-column="category" data-orderable="false" data-searchable="false">Category</th>
                <th class="px-4 py-3" data-column="paid_to">Paid To</th>
                <th class="px-4 py-3" data-column="payment_mode">Mode</th>
                <th class="px-4 py-3 text-right" data-column="amount">Amount</th>
                <th class="px-4 py-3" data-column="notes">Notes</th>
                <th class="px-4 py-3 text-right" data-column="actions" data-orderable="false" data-searchable="false">Action</th>
            </tr>
        </thead>
        <tbody></tbody>
    </x-erp.datatable>
@endsection
