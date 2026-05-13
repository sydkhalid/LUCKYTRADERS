@extends('layouts.app')

@section('title', 'Stock Adjustment Details')

@section('content')
    <div class="mb-5 flex items-center justify-between">
        <div>
            <h2 class="text-lg font-semibold text-gray-900">{{ $adjustment->adjustment_no }}</h2>
            <p class="text-sm text-gray-500">{{ $adjustment->product?->name }} - {{ $adjustment->adjustment_date?->format('d M Y') }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('stock-adjustments.products.history', $adjustment->product) }}" class="rounded border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Product History</a>
            <a href="{{ route('stock-adjustments.index') }}" class="rounded border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Back</a>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
        <div class="rounded bg-white p-5 shadow">
            <p class="text-sm text-gray-500">Type</p>
            <h3 class="mt-1 text-xl font-bold {{ $adjustment->adjustment_type === 'increase' ? 'text-emerald-700' : 'text-red-700' }}">{{ $adjustment->typeLabel() }}</h3>
        </div>
        <div class="rounded bg-white p-5 shadow">
            <p class="text-sm text-gray-500">Reason</p>
            <h3 class="mt-1 text-xl font-bold text-gray-900">{{ $adjustment->reasonLabel() }}</h3>
        </div>
        <div class="rounded bg-white p-5 shadow">
            <p class="text-sm text-gray-500">Quantity</p>
            <h3 class="mt-1 text-xl font-bold text-gray-900">{{ number_format((float) $adjustment->quantity, 3) }} {{ $adjustment->product?->unit }}</h3>
        </div>
        <div class="rounded bg-white p-5 shadow">
            <p class="text-sm text-gray-500">Current Product Stock</p>
            <h3 class="mt-1 text-xl font-bold text-gray-900">{{ number_format((float) $adjustment->product?->current_stock, 3) }} {{ $adjustment->product?->unit }}</h3>
        </div>
    </div>

    <div class="mt-5 grid grid-cols-1 gap-4 md:grid-cols-3">
        <div class="rounded bg-white p-5 shadow">
            <p class="text-sm text-gray-500">Old Stock</p>
            <h3 class="mt-1 text-2xl font-bold text-gray-900">{{ number_format((float) $adjustment->old_stock, 3) }}</h3>
        </div>
        <div class="rounded bg-white p-5 shadow">
            <p class="text-sm text-gray-500">New Stock</p>
            <h3 class="mt-1 text-2xl font-bold text-gray-900">{{ number_format((float) $adjustment->new_stock, 3) }}</h3>
        </div>
        <div class="rounded bg-white p-5 shadow">
            <p class="text-sm text-gray-500">Product</p>
            <h3 class="mt-1 text-lg font-bold text-gray-900">{{ $adjustment->product?->code }} - {{ $adjustment->product?->name }}</h3>
            <p class="mt-1 text-sm text-gray-600">{{ $adjustment->product?->category?->name ?: '-' }}</p>
        </div>
    </div>

    <div class="mt-5 rounded bg-white p-5 shadow">
        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Remarks</p>
        <p class="mt-2 whitespace-pre-line text-sm text-gray-700">{{ $adjustment->remarks ?: '-' }}</p>
    </div>
@endsection
