@extends('layouts.app')

@section('title', 'Purchase Return Details')

@section('content')
    <div class="mb-5 flex items-center justify-between">
        <div>
            <h2 class="text-lg font-semibold text-gray-900">{{ $return->return_no }}</h2>
            <p class="text-sm text-gray-500">{{ $return->supplier?->name }} - {{ $return->return_date?->format('d M Y') }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('purchase-returns.print', $return) }}" class="rounded bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">Print Debit Note</a>
            <a href="{{ route('purchases.show', $return->purchase) }}" class="rounded border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Purchase</a>
            <a href="{{ route('purchase-returns.index') }}" class="rounded border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Back</a>
        </div>
    </div>

    <div class="mb-5 grid grid-cols-1 gap-4 md:grid-cols-5">
        <div class="rounded bg-white p-5 shadow"><p class="text-sm text-gray-500">Subtotal</p><h3 class="mt-1 text-xl font-bold text-gray-900">Rs. {{ number_format((float) $return->subtotal, 2) }}</h3></div>
        <div class="rounded bg-white p-5 shadow"><p class="text-sm text-gray-500">GST</p><h3 class="mt-1 text-xl font-bold text-gray-900">Rs. {{ number_format((float) $return->gst_amount, 2) }}</h3></div>
        <div class="rounded bg-white p-5 shadow"><p class="text-sm text-gray-500">Total</p><h3 class="mt-1 text-xl font-bold text-gray-900">Rs. {{ number_format((float) $return->total_amount, 2) }}</h3></div>
        <div class="rounded bg-white p-5 shadow"><p class="text-sm text-gray-500">Refund</p><h3 class="mt-1 text-xl font-bold text-emerald-700">Rs. {{ number_format((float) $return->refund_amount, 2) }}</h3></div>
        <div class="rounded bg-white p-5 shadow"><p class="text-sm text-gray-500">Adjustment</p><h3 class="mt-1 text-xl font-bold text-red-700">Rs. {{ number_format((float) $return->adjustment_amount, 2) }}</h3></div>
    </div>

    <div class="mb-5 rounded bg-white p-5 shadow">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <div><p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Purchase</p><p class="mt-1 font-semibold text-gray-900">{{ $return->purchase?->purchase_no }}</p></div>
            <div><p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Supplier</p><p class="mt-1 font-semibold text-gray-900">{{ $return->supplier?->name }}</p></div>
            <div><p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Refund Mode</p><p class="mt-1 font-semibold text-gray-900">{{ $return->payment_mode ? strtoupper($return->payment_mode) : '-' }}</p></div>
        </div>
        <p class="mt-4 whitespace-pre-line text-sm text-gray-700">{{ $return->notes ?: '-' }}</p>
    </div>

    <div class="overflow-hidden rounded bg-white shadow">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-slate-100 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                <tr>
                    <th class="px-4 py-3">Product</th>
                    <th class="px-4 py-3 text-right">Qty</th>
                    <th class="px-4 py-3 text-right">Rate</th>
                    <th class="px-4 py-3 text-right">Subtotal</th>
                    <th class="px-4 py-3 text-right">GST %</th>
                    <th class="px-4 py-3 text-right">GST</th>
                    <th class="px-4 py-3 text-right">Total</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach ($return->items as $item)
                    <tr>
                        <td class="px-4 py-3 font-medium text-gray-900">{{ $item->product?->name }}</td>
                        <td class="px-4 py-3 text-right text-gray-700">{{ number_format((float) $item->quantity, 3) }} {{ $item->product?->unit }}</td>
                        <td class="px-4 py-3 text-right text-gray-700">Rs. {{ number_format((float) $item->rate, 2) }}</td>
                        <td class="px-4 py-3 text-right text-gray-700">Rs. {{ number_format((float) $item->subtotal, 2) }}</td>
                        <td class="px-4 py-3 text-right text-gray-700">{{ number_format((float) $item->gst_percentage, 2) }}%</td>
                        <td class="px-4 py-3 text-right text-gray-700">Rs. {{ number_format((float) $item->gst_amount, 2) }}</td>
                        <td class="px-4 py-3 text-right font-semibold text-gray-900">Rs. {{ number_format((float) $item->total, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
