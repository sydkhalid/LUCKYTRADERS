@extends('layouts.erp')

@section('title', 'Stock Adjustments')

@section('content')
    <x-erp.page-header
        title="Stock Adjustments"
        description="Record damage, shortage, excess, return, wastage, and manual correction entries."
        kicker="Inventory Control"
    >
        <x-slot:actions>
            <a href="{{ route('stock-adjustments.product-report') }}" class="erp-secondary-button">Product Report</a>
            <a href="{{ route('stock-adjustments.movements') }}" class="erp-secondary-button">Movement Report</a>
            <a href="{{ route('stock-adjustments.create') }}" class="erp-primary-button">Create Adjustment</a>
        </x-slot:actions>
    </x-erp.page-header>

    <div class="mb-5 grid grid-cols-1 gap-4 md:grid-cols-3">
        <div class="erp-summary-card">
            <p class="text-xs font-black uppercase tracking-[0.16em] text-slate-500">Total Increase Qty</p>
            <h3 class="mt-2 text-2xl font-black text-emerald-700">{{ number_format((float) $increaseTotal, 3) }}</h3>
        </div>
        <div class="erp-summary-card">
            <p class="text-xs font-black uppercase tracking-[0.16em] text-slate-500">Total Decrease Qty</p>
            <h3 class="mt-2 text-2xl font-black text-red-700">{{ number_format((float) $decreaseTotal, 3) }}</h3>
        </div>
        <div class="erp-summary-card">
            <p class="text-xs font-black uppercase tracking-[0.16em] text-slate-500">Net Adjustment Qty</p>
            <h3 class="mt-2 text-2xl font-black text-slate-950">{{ number_format((float) $increaseTotal - (float) $decreaseTotal, 3) }}</h3>
        </div>
    </div>

    <form id="stockAdjustmentFilters" class="mb-5 grid grid-cols-1 gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm md:grid-cols-5">
        <div>
            <label class="mb-1 block text-xs font-black uppercase tracking-wide text-slate-500">From</label>
            <input type="date" name="from_date" class="w-full">
        </div>
        <div>
            <label class="mb-1 block text-xs font-black uppercase tracking-wide text-slate-500">To</label>
            <input type="date" name="to_date" class="w-full">
        </div>
        <div>
            <label class="mb-1 block text-xs font-black uppercase tracking-wide text-slate-500">Type</label>
            <select name="status" data-searchable class="w-full">
                <option value="">All Types</option>
                <option value="increase">Increase</option>
                <option value="decrease">Decrease</option>
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
        id="stockAdjustmentsTable"
        :ajax-url="route('erp.datatables', 'stock-adjustments')"
        filter-form="#stockAdjustmentFilters"
        search-placeholder="Search adjustment, product, reason..."
        empty="No stock adjustments found."
    >
        <thead>
            <tr>
                <th class="px-4 py-3" data-column="adjustment_no">Adjustment No</th>
                <th class="px-4 py-3" data-column="adjustment_date">Date</th>
                <th class="px-4 py-3" data-column="product" data-orderable="false" data-searchable="false">Product</th>
                <th class="px-4 py-3" data-column="adjustment_type">Type</th>
                <th class="px-4 py-3" data-column="reason">Reason</th>
                <th class="px-4 py-3 text-right" data-column="quantity">Qty</th>
                <th class="px-4 py-3 text-right" data-column="old_stock">Old Stock</th>
                <th class="px-4 py-3 text-right" data-column="new_stock">New Stock</th>
                <th class="px-4 py-3 text-right" data-column="actions" data-orderable="false" data-searchable="false">Action</th>
            </tr>
        </thead>
        <tbody></tbody>
    </x-erp.datatable>
@endsection
