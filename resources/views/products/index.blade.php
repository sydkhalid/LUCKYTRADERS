@extends('layouts.erp')

@section('title', 'Products')

@section('content')
    <div class="mb-5 flex items-center justify-between">
        <div>
            <h2 class="text-lg font-semibold text-gray-900">Products</h2>
            <p class="text-sm text-gray-500">Steel items, rates, GST, HSN, and opening stock.</p>
        </div>
        <a href="{{ route('products.create') }}" class="rounded bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">Add Product</a>
    </div>

    <div class="overflow-x-auto rounded bg-white shadow">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-slate-100 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                <tr>
                    <th class="px-4 py-3">Code</th>
                    <th class="px-4 py-3">Product</th>
                    <th class="px-4 py-3">Category</th>
                    <th class="px-4 py-3">Size</th>
                    <th class="px-4 py-3 text-right">Stock</th>
                    <th class="px-4 py-3 text-right">Sell Rate</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3 text-right">Action</th>
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
                            <span class="rounded px-2 py-1 text-xs font-semibold {{ $product->status === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-600' }}">
                                {{ ucfirst($product->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex justify-end gap-3">
                                <a href="{{ route('products.edit', $product) }}" class="font-semibold text-slate-700 hover:text-slate-900">Edit</a>
                                <a href="{{ route('stock-adjustments.products.history', $product) }}" class="font-semibold text-slate-700 hover:text-slate-900">History</a>
                                @can('delete_records')
                                    <form method="POST" action="{{ route('products.destroy', $product) }}" onsubmit="return confirm('Delete this product?')">
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
        {{ $products->links() }}
    </div>
@endsection
