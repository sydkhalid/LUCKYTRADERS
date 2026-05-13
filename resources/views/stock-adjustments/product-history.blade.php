@extends('layouts.erp')

@section('title', 'Product Stock History')

@section('content')
    <div class="mb-5 flex items-center justify-between">
        <div>
            <h2 class="text-lg font-semibold text-gray-900">{{ $product->name }} Stock History</h2>
            <p class="text-sm text-gray-500">{{ $product->code }} - {{ $product->category?->name ?: '-' }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('stock-adjustments.create', ['product_id' => $product->id]) }}" class="rounded bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">Create Adjustment</a>
            <a href="{{ route('products.index') }}" class="rounded border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Products</a>
        </div>
    </div>

    <div class="mb-5 grid grid-cols-1 gap-4 md:grid-cols-3">
        <div class="rounded bg-white p-5 shadow">
            <p class="text-sm text-gray-500">Current Stock</p>
            <h3 class="mt-1 text-2xl font-bold text-gray-900">{{ number_format((float) $product->current_stock, 3) }} {{ $product->unit }}</h3>
        </div>
        <div class="rounded bg-white p-5 shadow">
            <p class="text-sm text-gray-500">Purchase Rate</p>
            <h3 class="mt-1 text-2xl font-bold text-gray-900">Rs. {{ number_format((float) $product->purchase_price, 2) }}</h3>
        </div>
        <div class="rounded bg-white p-5 shadow">
            <p class="text-sm text-gray-500">Stock Value</p>
            <h3 class="mt-1 text-2xl font-bold text-gray-900">Rs. {{ number_format((float) $product->current_stock * (float) $product->purchase_price, 2) }}</h3>
        </div>
    </div>

    @include('stock-adjustments.partials.movement-table', ['movements' => $movements, 'adjustmentsById' => $adjustmentsById])

    <div class="mt-5">
        {{ $movements->links() }}
    </div>
@endsection
