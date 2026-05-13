@extends('layouts.erp')

@section('title', 'Product Category Details')

@section('content')
    <div class="mb-5 flex items-center justify-between">
        <div>
            <h2 class="text-lg font-semibold text-gray-900">{{ $productCategory->name }}</h2>
            <p class="text-sm text-gray-500">Product category details and linked products.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('product-categories.edit', $productCategory) }}" class="rounded bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">Edit</a>
            <a href="{{ route('product-categories.index') }}" class="rounded border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Back</a>
        </div>
    </div>

    <div class="grid gap-5 lg:grid-cols-3">
        <div class="rounded bg-white p-6 shadow lg:col-span-1">
            <dl class="space-y-4 text-sm">
                <div>
                    <dt class="font-semibold text-gray-500">Status</dt>
                    <dd class="mt-1">
                        <span class="rounded px-2 py-1 text-xs font-semibold {{ $productCategory->status === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-600' }}">
                            {{ ucfirst($productCategory->status) }}
                        </span>
                    </dd>
                </div>
                <div>
                    <dt class="font-semibold text-gray-500">Products</dt>
                    <dd class="mt-1 text-gray-900">{{ $productCategory->products_count }}</dd>
                </div>
                <div>
                    <dt class="font-semibold text-gray-500">Description</dt>
                    <dd class="mt-1 whitespace-pre-line text-gray-900">{{ $productCategory->description ?: '-' }}</dd>
                </div>
            </dl>
        </div>

        <div class="overflow-hidden rounded bg-white shadow lg:col-span-2">
            <div class="border-b border-gray-200 px-4 py-3">
                <h3 class="font-semibold text-gray-900">Linked Products</h3>
            </div>
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-slate-100 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                    <tr>
                        <th class="px-4 py-3">Code</th>
                        <th class="px-4 py-3">Product</th>
                        <th class="px-4 py-3 text-right">Stock</th>
                        <th class="px-4 py-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($products as $product)
                        <tr>
                            <td class="px-4 py-3 font-medium text-gray-900">{{ $product->code }}</td>
                            <td class="px-4 py-3 text-gray-700">{{ $product->name }}</td>
                            <td class="px-4 py-3 text-right text-gray-700">{{ number_format((float) $product->current_stock, 3) }} {{ $product->unit }}</td>
                            <td class="px-4 py-3 text-gray-700">{{ ucfirst($product->status) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center text-gray-500">No products linked.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="p-4">
                {{ $products->links() }}
            </div>
        </div>
    </div>
@endsection
