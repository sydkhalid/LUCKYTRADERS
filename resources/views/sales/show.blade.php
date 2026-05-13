@extends('layouts.erp')

@section('title', 'Sale Details')

@section('content')
    <div class="mb-5 flex items-center justify-between">
        <div>
            <h2 class="text-lg font-semibold text-gray-900">{{ $sale->sale_no }}</h2>
            <p class="text-sm text-gray-500">{{ $sale->customer?->name }} - {{ $sale->sale_date?->format('d M Y') }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('sales.print', $sale) }}" class="rounded bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">Print Invoice</a>
            <a href="{{ route('sales.pdf', $sale) }}" target="_blank" class="rounded border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">PDF</a>
            <a href="{{ route('sales.pdf', ['sale' => $sale, 'download' => 1]) }}" class="rounded border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Download</a>
            @can('edit_old_records')
                <a href="{{ route('sales.edit', $sale) }}" class="rounded border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Edit</a>
            @endcan
            @if (auth()->user()?->hasRole('Super Admin') || auth()->user()?->hasRole('Admin'))
                <form method="POST" action="{{ route('sales.destroy', $sale) }}" onsubmit="return confirm('Cancel this sale and reverse stock?')">
                    @csrf
                    @method('DELETE')
                    <button class="rounded border border-red-200 px-4 py-2 text-sm font-semibold text-red-700 hover:bg-red-50">Cancel Sale</button>
                </form>
            @endif
            <a href="{{ route('sales.index') }}" class="rounded border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Back</a>
        </div>
    </div>

    <div class="mb-5 grid grid-cols-1 gap-4 md:grid-cols-5">
        <div class="rounded bg-white p-5 shadow">
            <p class="text-sm text-gray-500">Subtotal</p>
            <h3 class="mt-1 text-xl font-bold text-gray-900">Rs. {{ number_format((float) $sale->subtotal, 2) }}</h3>
        </div>
        <div class="rounded bg-white p-5 shadow">
            <p class="text-sm text-gray-500">GST</p>
            <h3 class="mt-1 text-xl font-bold text-gray-900">Rs. {{ number_format((float) $sale->gst_amount, 2) }}</h3>
        </div>
        <div class="rounded bg-white p-5 shadow">
            <p class="text-sm text-gray-500">Paid</p>
            <h3 class="mt-1 text-xl font-bold text-emerald-700">Rs. {{ number_format((float) $sale->paid_amount, 2) }}</h3>
        </div>
        <div class="rounded bg-white p-5 shadow">
            <p class="text-sm text-gray-500">Balance</p>
            <h3 class="mt-1 text-xl font-bold text-red-700">Rs. {{ number_format((float) $sale->balance_amount, 2) }}</h3>
        </div>
        <div class="rounded bg-white p-5 shadow">
            <p class="text-sm text-gray-500">Profit</p>
            <h3 class="mt-1 text-xl font-bold text-gray-900">Rs. {{ number_format((float) $sale->items->sum('profit_amount'), 2) }}</h3>
        </div>
    </div>

    <div class="overflow-hidden rounded bg-white shadow">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-slate-100 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                <tr>
                    <th class="px-4 py-3">Product</th>
                    <th class="px-4 py-3 text-right">Qty</th>
                    <th class="px-4 py-3">Unit</th>
                    <th class="px-4 py-3 text-right">Rate</th>
                    <th class="px-4 py-3 text-right">Subtotal</th>
                    <th class="px-4 py-3 text-right">GST</th>
                    <th class="px-4 py-3 text-right">Total</th>
                    <th class="px-4 py-3 text-right">Profit</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach ($sale->items as $item)
                    <tr>
                        <td class="px-4 py-3 font-medium text-gray-900">{{ $item->product?->name }}</td>
                        <td class="px-4 py-3 text-right text-gray-700">{{ number_format((float) $item->quantity, 3) }}</td>
                        <td class="px-4 py-3 text-gray-700">{{ $item->unit }}</td>
                        <td class="px-4 py-3 text-right text-gray-700">Rs. {{ number_format((float) $item->rate, 2) }}</td>
                        <td class="px-4 py-3 text-right text-gray-700">Rs. {{ number_format((float) $item->subtotal, 2) }}</td>
                        <td class="px-4 py-3 text-right text-gray-700">Rs. {{ number_format((float) $item->gst_amount, 2) }}</td>
                        <td class="px-4 py-3 text-right font-semibold text-gray-900">Rs. {{ number_format((float) $item->total, 2) }}</td>
                        <td class="px-4 py-3 text-right text-gray-700">Rs. {{ number_format((float) $item->profit_amount, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
