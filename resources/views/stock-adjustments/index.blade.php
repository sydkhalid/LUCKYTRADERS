@extends('layouts.erp')

@section('title', 'Stock Adjustments')

@section('content')
    <div class="mb-5 flex items-center justify-between">
        <div>
            <h2 class="text-lg font-semibold text-gray-900">Stock Adjustments</h2>
            <p class="text-sm text-gray-500">Record damage, shortage, excess, return, wastage, and manual correction entries.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('stock-adjustments.movements') }}" class="rounded border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Movement Report</a>
            <a href="{{ route('stock-adjustments.create') }}" class="rounded bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">Create Adjustment</a>
        </div>
    </div>

    <div class="mb-5 grid grid-cols-1 gap-4 md:grid-cols-3">
        <div class="rounded bg-white p-5 shadow">
            <p class="text-sm text-gray-500">Total Increase Qty</p>
            <h3 class="mt-1 text-2xl font-bold text-emerald-700">{{ number_format((float) $increaseTotal, 3) }}</h3>
        </div>
        <div class="rounded bg-white p-5 shadow">
            <p class="text-sm text-gray-500">Total Decrease Qty</p>
            <h3 class="mt-1 text-2xl font-bold text-red-700">{{ number_format((float) $decreaseTotal, 3) }}</h3>
        </div>
        <div class="rounded bg-white p-5 shadow">
            <p class="text-sm text-gray-500">Net Adjustment Qty</p>
            <h3 class="mt-1 text-2xl font-bold text-gray-900">{{ number_format((float) $increaseTotal - (float) $decreaseTotal, 3) }}</h3>
        </div>
    </div>

    <div class="overflow-hidden rounded bg-white shadow">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-slate-100 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                <tr>
                    <th class="px-4 py-3">Adjustment No</th>
                    <th class="px-4 py-3">Date</th>
                    <th class="px-4 py-3">Product</th>
                    <th class="px-4 py-3">Type</th>
                    <th class="px-4 py-3">Reason</th>
                    <th class="px-4 py-3 text-right">Qty</th>
                    <th class="px-4 py-3 text-right">Old Stock</th>
                    <th class="px-4 py-3 text-right">New Stock</th>
                    <th class="px-4 py-3 text-right">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($adjustments as $adjustment)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-900">{{ $adjustment->adjustment_no }}</td>
                        <td class="px-4 py-3 text-gray-700">{{ $adjustment->adjustment_date?->format('d M Y') }}</td>
                        <td class="px-4 py-3 text-gray-700">{{ $adjustment->product?->name ?: '-' }}</td>
                        <td class="px-4 py-3">
                            <span class="rounded px-2 py-1 text-xs font-semibold {{ $adjustment->adjustment_type === 'increase' ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">
                                {{ $adjustment->typeLabel() }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-gray-700">{{ $adjustment->reasonLabel() }}</td>
                        <td class="px-4 py-3 text-right font-semibold text-gray-900">{{ number_format((float) $adjustment->quantity, 3) }}</td>
                        <td class="px-4 py-3 text-right text-gray-700">{{ number_format((float) $adjustment->old_stock, 3) }}</td>
                        <td class="px-4 py-3 text-right text-gray-700">{{ number_format((float) $adjustment->new_stock, 3) }}</td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('stock-adjustments.show', $adjustment) }}" class="font-semibold text-slate-700 hover:text-slate-900">View</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-4 py-8 text-center text-gray-500">No stock adjustments found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-5">
        {{ $adjustments->links() }}
    </div>
@endsection
