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

    <form method="GET" action="{{ route('products.index') }}" class="mb-5 flex flex-wrap gap-3 rounded bg-white p-4 shadow">
        <input type="search" name="search" value="{{ $search }}" placeholder="Search product, code, category, HSN, size, or thickness" class="min-w-0 flex-1 rounded border-gray-300 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500">
        <button class="rounded bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">Search</button>
        @if ($search !== '')
            <a href="{{ route('products.index') }}" class="rounded border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Clear</a>
        @endif
    </form>

    <div class="overflow-x-auto rounded bg-white shadow">
        <table
            class="min-w-full divide-y divide-gray-200 text-sm"
            data-erp-datatable
            data-ajax-url="{{ route('erp.datatables', 'products') }}"
            data-search-placeholder="Search product, code, category, HSN..."
            data-empty="No products found."
        >
            <thead class="bg-slate-100 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
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
            <tbody class="divide-y divide-gray-100">
                @forelse ($products as $product)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-900">{{ $product->code }}</td>
                        <td class="px-4 py-3 text-gray-900">{{ $product->name }}</td>
                        <td class="px-4 py-3 text-gray-700">{{ $product->category?->name ?: '-' }}</td>
                        <td class="px-4 py-3 text-gray-700">{{ $product->size ?: '-' }}</td>
                        <td class="px-4 py-3 text-right text-gray-900">{{ number_format((float) $product->current_stock, 3) }} {{ $product->unit }}</td>
                        <td class="px-4 py-3 text-right text-gray-900">Rs. {{ number_format((float) $product->selling_price, 2) }}</td>
                        <td class="px-4 py-3">
                            <x-erp.status-badge :value="ucfirst($product->status)" />
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex justify-end gap-3">
                                <a href="{{ route('products.show', $product) }}" class="font-semibold text-slate-700 hover:text-slate-900">View</a>
                                <a href="{{ route('products.edit', $product) }}" class="font-semibold text-slate-700 hover:text-slate-900">Edit</a>
                                <a href="{{ route('stock-adjustments.products.history', $product) }}" class="font-semibold text-slate-700 hover:text-slate-900">History</a>
                                @can('delete_records')
                                    <form method="POST" action="{{ route('products.destroy', $product) }}" data-confirm-delete data-confirm-title="Delete this product?">
                                        @csrf
                                        @method('DELETE')
                                        <button class="font-semibold text-red-600 hover:text-red-800">Delete</button>
                                    </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-8 text-center text-gray-500">No products found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-5">
        {{ $products->withQueryString()->links() }}
    </div>
@endsection
