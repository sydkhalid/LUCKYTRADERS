@extends('layouts.erp')

@section('title', 'Products')

@section('content')
    <x-erp.page-header
        title="Products"
        description="Steel items, rates, GST, HSN, and opening stock."
        kicker="Inventory Master"
    >
        <x-slot:actions>
            <a href="{{ route('products.create') }}" class="erp-primary-button">Add Product</a>
        </x-slot:actions>
    </x-erp.page-header>

    <form id="productFilters" class="mb-5 grid grid-cols-1 gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:grid-cols-3">
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
        id="productsTable"
        :ajax-url="route('erp.datatables', 'products')"
        filter-form="#productFilters"
        search-placeholder="Search product, code, category, HSN..."
        empty="No products found."
    >
        <thead>
            <tr>
                <th class="px-4 py-3" data-column="code">Code</th>
                <th class="px-4 py-3" data-column="name">Product</th>
                <th class="px-4 py-3" data-column="category" data-orderable="false" data-searchable="false">Category</th>
                <th class="px-4 py-3" data-column="size">Size</th>
                <th class="px-4 py-3 text-right" data-column="current_stock">Stock</th>
                <th class="px-4 py-3 text-right" data-column="selling_price">Sell Rate</th>
                <th class="px-4 py-3" data-column="status">Status</th>
                <th class="px-4 py-3 text-right" data-column="actions" data-orderable="false" data-searchable="false">Action</th>
            </tr>
        </thead>
        <tbody></tbody>
    </x-erp.datatable>
@endsection
