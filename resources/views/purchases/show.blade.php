@extends('layouts.erp')

@section('title', 'Purchase Details')

@section('content')
    <div class="mb-5 flex items-center justify-between">
        <div>
            <h2 class="text-lg font-semibold text-gray-900">{{ $purchase->purchase_no }}</h2>
            <p class="text-sm text-gray-500">{{ $purchase->supplier?->name }} - {{ $purchase->purchase_date?->format('d M Y') }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('purchases.print', $purchase) }}" target="_blank" class="rounded bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">Print</a>
            <a href="{{ route('purchases.pdf', $purchase) }}" target="_blank" class="rounded border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">PDF</a>
            <a href="{{ route('purchases.pdf', ['purchase' => $purchase, 'download' => 1]) }}" class="rounded border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Download</a>
            @can('edit_old_records')
                <a href="{{ route('purchases.edit', $purchase) }}" class="rounded border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Edit</a>
            @endcan
            <a href="{{ route('purchases.index') }}" class="rounded border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Back</a>
        </div>
    </div>

    <div class="mb-5 grid grid-cols-1 gap-4 md:grid-cols-4">
        <div class="rounded bg-white p-5 shadow">
            <p class="text-sm text-gray-500">Subtotal</p>
            <h3 class="mt-1 text-xl font-bold text-gray-900">Rs. {{ number_format((float) $purchase->subtotal, 2) }}</h3>
        </div>
        <div class="rounded bg-white p-5 shadow">
            <p class="text-sm text-gray-500">GST Input</p>
            <h3 class="mt-1 text-xl font-bold text-gray-900">Rs. {{ number_format((float) $purchase->gst_amount, 2) }}</h3>
        </div>
        <div class="rounded bg-white p-5 shadow">
            <p class="text-sm text-gray-500">Paid</p>
            <h3 class="mt-1 text-xl font-bold text-emerald-700">Rs. {{ number_format((float) $purchase->paid_amount, 2) }}</h3>
        </div>
        <div class="rounded bg-white p-5 shadow">
            <p class="text-sm text-gray-500">Balance</p>
            <h3 class="mt-1 text-xl font-bold text-red-700">Rs. {{ number_format((float) $purchase->balance_amount, 2) }}</h3>
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
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach ($purchase->items as $item)
                    <tr>
                        <td class="px-4 py-3 font-medium text-gray-900">{{ $item->product?->name }}</td>
                        <td class="px-4 py-3 text-right text-gray-700">{{ number_format((float) $item->quantity, 3) }}</td>
                        <td class="px-4 py-3 text-gray-700">{{ $item->unit }}</td>
                        <td class="px-4 py-3 text-right text-gray-700">Rs. {{ number_format((float) $item->rate, 2) }}</td>
                        <td class="px-4 py-3 text-right text-gray-700">Rs. {{ number_format((float) $item->subtotal, 2) }}</td>
                        <td class="px-4 py-3 text-right text-gray-700">Rs. {{ number_format((float) $item->gst_amount, 2) }} ({{ number_format((float) $item->gst_percentage, 2) }}%)</td>
                        <td class="px-4 py-3 text-right font-semibold text-gray-900">Rs. {{ number_format((float) $item->total, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
