@extends('layouts.app')

@section('title', 'Quotation Details')

@section('content')
    <div class="mb-5 flex items-center justify-between">
        <div>
            <h2 class="text-lg font-semibold text-gray-900">{{ $quotation->quotation_no }}</h2>
            <p class="text-sm text-gray-500">{{ $quotation->customer?->name }} - {{ $quotation->quotation_date?->format('d M Y') }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('quotations.print', $quotation) }}" class="rounded bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">Print</a>
            <a href="{{ route('quotations.pdf', $quotation) }}" target="_blank" class="rounded border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">PDF</a>
            <a href="{{ route('quotations.pdf', ['quotation' => $quotation, 'download' => 1]) }}" class="rounded border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Download</a>
            @if ($quotation->status === 'accepted')
                <a href="{{ route('quotations.convert', $quotation) }}" class="rounded bg-emerald-700 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-800">Convert to Sale</a>
            @endif
            @if ($quotation->status !== 'converted')
                <a href="{{ route('quotations.edit', $quotation) }}" class="rounded border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Edit</a>
            @endif
            <a href="{{ route('quotations.index') }}" class="rounded border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Back</a>
        </div>
    </div>

    <div class="mb-5 grid grid-cols-1 gap-4 md:grid-cols-5">
        <div class="rounded bg-white p-5 shadow">
            <p class="text-sm text-gray-500">Subtotal</p>
            <h3 class="mt-1 text-xl font-bold text-gray-900">Rs. {{ number_format((float) $quotation->subtotal, 2) }}</h3>
        </div>
        <div class="rounded bg-white p-5 shadow">
            <p class="text-sm text-gray-500">GST</p>
            <h3 class="mt-1 text-xl font-bold text-gray-900">Rs. {{ number_format((float) $quotation->gst_amount, 2) }}</h3>
        </div>
        <div class="rounded bg-white p-5 shadow">
            <p class="text-sm text-gray-500">Total</p>
            <h3 class="mt-1 text-xl font-bold text-gray-900">Rs. {{ number_format((float) $quotation->total_amount, 2) }}</h3>
        </div>
        <div class="rounded bg-white p-5 shadow">
            <p class="text-sm text-gray-500">Valid Until</p>
            <h3 class="mt-1 text-xl font-bold text-gray-900">{{ $quotation->valid_until?->format('d M Y') ?: '-' }}</h3>
        </div>
        <div class="rounded bg-white p-5 shadow">
            <p class="text-sm text-gray-500">Status</p>
            <div class="mt-2">
                @include('quotations.partials.status-badge', ['status' => $quotation->status, 'label' => $quotation->statusLabel()])
            </div>
        </div>
    </div>

    <div class="mb-5 grid grid-cols-1 gap-4 lg:grid-cols-3">
        <div class="rounded bg-white p-5 shadow lg:col-span-2">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Customer</p>
            <h3 class="mt-2 text-lg font-semibold text-gray-900">{{ $quotation->customer?->name }}</h3>
            <p class="mt-1 text-sm text-gray-700">{{ $quotation->customer?->address ?: '-' }}</p>
            <p class="text-sm text-gray-700">Phone: {{ $quotation->customer?->phone ?: '-' }}</p>
            <p class="text-sm text-gray-700">GST: {{ $quotation->customer?->gst_number ?: '-' }}</p>
        </div>
        <div class="rounded bg-white p-5 shadow">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Notes</p>
            <p class="mt-2 whitespace-pre-line text-sm text-gray-700">{{ $quotation->notes ?: '-' }}</p>
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
                    <th class="px-4 py-3 text-right">GST %</th>
                    <th class="px-4 py-3 text-right">GST</th>
                    <th class="px-4 py-3 text-right">Total</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach ($quotation->items as $item)
                    <tr>
                        <td class="px-4 py-3 font-medium text-gray-900">{{ $item->product?->name }}</td>
                        <td class="px-4 py-3 text-right text-gray-700">{{ number_format((float) $item->quantity, 3) }}</td>
                        <td class="px-4 py-3 text-gray-700">{{ $item->unit }}</td>
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
