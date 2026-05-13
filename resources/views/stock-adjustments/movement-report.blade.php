@extends('layouts.erp')

@section('title', 'Stock Movement Report')

@section('content')
    <div class="mb-5 flex items-center justify-between">
        <div>
            <h2 class="text-lg font-semibold text-gray-900">Stock Movement Report</h2>
            <p class="text-sm text-gray-500">Purchase, sale, and adjustment stock movements in one report.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('stock-adjustments.product-report') }}" class="rounded border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Product Report</a>
            <a href="{{ route('stock-adjustments.index') }}" class="rounded border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Adjustments</a>
        </div>
    </div>

    <form method="GET" action="{{ route('stock-adjustments.movements') }}" class="mb-5 rounded bg-white p-5 shadow">
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-5">
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">From Date</label>
                <input type="date" name="from_date" value="{{ $filters['from_date'] ?? '' }}" class="w-full rounded border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500">
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">To Date</label>
                <input type="date" name="to_date" value="{{ $filters['to_date'] ?? '' }}" class="w-full rounded border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500">
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Product</label>
                <select name="product_id" class="w-full rounded border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500">
                    <option value="">All Products</option>
                    @foreach ($products as $product)
                        <option value="{{ $product->id }}" @selected((string) ($filters['product_id'] ?? '') === (string) $product->id)>{{ $product->name }} ({{ $product->code }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Movement Type</label>
                <select name="movement_type" class="w-full rounded border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500">
                    <option value="">All</option>
                    <option value="purchase_in" @selected(($filters['movement_type'] ?? '') === 'purchase_in')>Purchase In</option>
                    <option value="sale_out" @selected(($filters['movement_type'] ?? '') === 'sale_out')>Sale Out</option>
                    <option value="sales_return_in" @selected(($filters['movement_type'] ?? '') === 'sales_return_in')>Sales Return In</option>
                    <option value="purchase_return_out" @selected(($filters['movement_type'] ?? '') === 'purchase_return_out')>Purchase Return Out</option>
                    <option value="adjustment" @selected(($filters['movement_type'] ?? '') === 'adjustment')>Adjustment</option>
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button class="flex-1 rounded bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">Filter</button>
                <a href="{{ route('stock-adjustments.movements') }}" class="rounded border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Reset</a>
            </div>
        </div>
    </form>

    <div class="mb-5 grid grid-cols-1 gap-4 md:grid-cols-2">
        <div class="rounded bg-white p-5 shadow">
            <p class="text-sm text-gray-500">Filtered Quantity</p>
            <h3 class="mt-1 text-2xl font-bold text-gray-900">{{ number_format((float) $totalQuantity, 3) }}</h3>
        </div>
        <div class="rounded bg-white p-5 shadow">
            <p class="text-sm text-gray-500">Filtered Value</p>
            <h3 class="mt-1 text-2xl font-bold text-gray-900">Rs. {{ number_format((float) $totalValue, 2) }}</h3>
        </div>
    </div>

    @include('stock-adjustments.partials.movement-table', ['movements' => $movements, 'adjustmentsById' => $adjustmentsById])

    <div class="mt-5">
        {{ $movements->links() }}
    </div>
@endsection
