@extends('layouts.erp')

@section('title', 'Product Details')

@section('content')
    <div class="mb-5 flex items-center justify-between">
        <div>
            <h2 class="text-lg font-semibold text-gray-900">{{ $product->name }}</h2>
            <p class="text-sm text-gray-500">Code {{ $product->code }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('products.edit', $product) }}" class="rounded bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">Edit</a>
            <a href="{{ route('products.index') }}" class="rounded border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Back</a>
        </div>
    </div>

    <div class="rounded bg-white p-6 shadow">
        <div class="grid gap-5 md:grid-cols-3">
            <div>
                <p class="text-xs font-semibold uppercase text-gray-500">Category</p>
                <p class="mt-1 text-gray-900">{{ $product->category?->name ?: '-' }}</p>
            </div>
            <div>
                <p class="text-xs font-semibold uppercase text-gray-500">Status</p>
                <p class="mt-1">
                    <span class="rounded px-2 py-1 text-xs font-semibold {{ $product->status === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-600' }}">
                        {{ ucfirst($product->status) }}
                    </span>
                </p>
            </div>
            <div>
                <p class="text-xs font-semibold uppercase text-gray-500">HSN Code</p>
                <p class="mt-1 text-gray-900">{{ $product->hsn_code ?: '-' }}</p>
            </div>
            <div>
                <p class="text-xs font-semibold uppercase text-gray-500">Size</p>
                <p class="mt-1 text-gray-900">{{ $product->size ?: '-' }}</p>
            </div>
            <div>
                <p class="text-xs font-semibold uppercase text-gray-500">Thickness</p>
                <p class="mt-1 text-gray-900">{{ $product->thickness ?: '-' }}</p>
            </div>
            <div>
                <p class="text-xs font-semibold uppercase text-gray-500">Unit</p>
                <p class="mt-1 text-gray-900">{{ $product->unit }}</p>
            </div>
            <div>
                <p class="text-xs font-semibold uppercase text-gray-500">Opening Stock</p>
                <p class="mt-1 text-gray-900">{{ number_format((float) $product->opening_stock, 3) }} {{ $product->unit }}</p>
            </div>
            <div>
                <p class="text-xs font-semibold uppercase text-gray-500">Current Stock</p>
                <p class="mt-1 text-gray-900">{{ number_format((float) $product->current_stock, 3) }} {{ $product->unit }}</p>
            </div>
            <div>
                <p class="text-xs font-semibold uppercase text-gray-500">Weight Per Unit</p>
                <p class="mt-1 text-gray-900">{{ number_format((float) $product->weight_per_unit, 3) }}</p>
            </div>
            <div>
                <p class="text-xs font-semibold uppercase text-gray-500">Purchase Price</p>
                <p class="mt-1 text-gray-900">Rs. {{ number_format((float) $product->purchase_price, 2) }}</p>
            </div>
            <div>
                <p class="text-xs font-semibold uppercase text-gray-500">Selling Price</p>
                <p class="mt-1 text-gray-900">Rs. {{ number_format((float) $product->selling_price, 2) }}</p>
            </div>
            <div>
                <p class="text-xs font-semibold uppercase text-gray-500">GST</p>
                <p class="mt-1 text-gray-900">{{ number_format((float) $product->gst_percentage, 2) }}%</p>
            </div>
        </div>
    </div>
@endsection
