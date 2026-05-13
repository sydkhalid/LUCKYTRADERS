@extends('layouts.app')

@section('title', 'Product-wise Adjustment Report')

@section('content')
    <div class="mb-5 flex items-center justify-between">
        <div>
            <h2 class="text-lg font-semibold text-gray-900">Product-wise Adjustment Report</h2>
            <p class="text-sm text-gray-500">Summarized stock increase and decrease adjustments grouped by product.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('stock-adjustments.movements') }}" class="rounded border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Movement Report</a>
            <a href="{{ route('stock-adjustments.index') }}" class="rounded border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Adjustments</a>
        </div>
    </div>

    <form method="GET" action="{{ route('stock-adjustments.product-report') }}" class="mb-5 rounded bg-white p-5 shadow">
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-6">
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
                <label class="mb-1 block text-sm font-medium text-gray-700">Type</label>
                <select name="adjustment_type" class="w-full rounded border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500">
                    <option value="">All Types</option>
                    @foreach ($types as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['adjustment_type'] ?? '') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Reason</label>
                <select name="reason" class="w-full rounded border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500">
                    <option value="">All Reasons</option>
                    @foreach ($reasons as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['reason'] ?? '') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button class="flex-1 rounded bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">Filter</button>
                <a href="{{ route('stock-adjustments.product-report') }}" class="rounded border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Reset</a>
            </div>
        </div>
    </form>

    <div class="mb-5 grid grid-cols-1 gap-4 md:grid-cols-3">
        <div class="rounded bg-white p-5 shadow">
            <p class="text-sm text-gray-500">Filtered Increase Qty</p>
            <h3 class="mt-1 text-2xl font-bold text-emerald-700">{{ number_format((float) $increaseTotal, 3) }}</h3>
        </div>
        <div class="rounded bg-white p-5 shadow">
            <p class="text-sm text-gray-500">Filtered Decrease Qty</p>
            <h3 class="mt-1 text-2xl font-bold text-red-700">{{ number_format((float) $decreaseTotal, 3) }}</h3>
        </div>
        <div class="rounded bg-white p-5 shadow">
            <p class="text-sm text-gray-500">Filtered Net Qty</p>
            <h3 class="mt-1 text-2xl font-bold text-gray-900">{{ number_format((float) $increaseTotal - (float) $decreaseTotal, 3) }}</h3>
        </div>
    </div>

    <div class="overflow-hidden rounded bg-white shadow">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-slate-100 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                <tr>
                    <th class="px-4 py-3">Product</th>
                    <th class="px-4 py-3">Code</th>
                    <th class="px-4 py-3 text-right">Increase Qty</th>
                    <th class="px-4 py-3 text-right">Decrease Qty</th>
                    <th class="px-4 py-3 text-right">Net Qty</th>
                    <th class="px-4 py-3 text-right">Entries</th>
                    <th class="px-4 py-3">Last Adjustment</th>
                    <th class="px-4 py-3 text-right">Current Stock</th>
                    <th class="px-4 py-3 text-right">History</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($summaries as $summary)
                    @php
                        $increaseQty = (float) $summary->increase_quantity;
                        $decreaseQty = (float) $summary->decrease_quantity;
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-900">{{ $summary->product?->name ?: '-' }}</td>
                        <td class="px-4 py-3 text-gray-700">{{ $summary->product?->code ?: '-' }}</td>
                        <td class="px-4 py-3 text-right font-semibold text-emerald-700">{{ number_format($increaseQty, 3) }}</td>
                        <td class="px-4 py-3 text-right font-semibold text-red-700">{{ number_format($decreaseQty, 3) }}</td>
                        <td class="px-4 py-3 text-right font-semibold text-gray-900">{{ number_format($increaseQty - $decreaseQty, 3) }}</td>
                        <td class="px-4 py-3 text-right text-gray-700">{{ number_format((int) $summary->adjustments_count) }}</td>
                        <td class="px-4 py-3 text-gray-700">{{ $summary->last_adjustment_date ? \Illuminate\Support\Carbon::parse($summary->last_adjustment_date)->format('d M Y') : '-' }}</td>
                        <td class="px-4 py-3 text-right text-gray-700">{{ number_format((float) $summary->product?->current_stock, 3) }} {{ $summary->product?->unit }}</td>
                        <td class="px-4 py-3 text-right">
                            @if ($summary->product)
                                <a href="{{ route('stock-adjustments.products.history', $summary->product) }}" class="font-semibold text-slate-700 hover:text-slate-900">View</a>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-4 py-8 text-center text-gray-500">No product adjustments found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-5">
        {{ $summaries->links() }}
    </div>
@endsection
