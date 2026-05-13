@extends('layouts.erp')

@section('title', 'Quotations')

@section('content')
    <div class="mb-5 flex items-center justify-between">
        <div>
            <h2 class="text-lg font-semibold text-gray-900">Quotation Management</h2>
            <p class="text-sm text-gray-500">Create customer quotations before converting accepted offers into GST invoices or normal bills.</p>
        </div>
        <a href="{{ route('quotations.create') }}" class="rounded bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">Create Quotation</a>
    </div>

    <div class="mb-5 grid grid-cols-1 gap-4 md:grid-cols-3">
        <div class="rounded bg-white p-5 shadow">
            <p class="text-sm text-gray-500">Draft</p>
            <h3 class="mt-1 text-2xl font-bold text-gray-900">{{ $draftCount }}</h3>
        </div>
        <div class="rounded bg-white p-5 shadow">
            <p class="text-sm text-gray-500">Accepted</p>
            <h3 class="mt-1 text-2xl font-bold text-emerald-700">{{ $acceptedCount }}</h3>
        </div>
        <div class="rounded bg-white p-5 shadow">
            <p class="text-sm text-gray-500">Converted</p>
            <h3 class="mt-1 text-2xl font-bold text-slate-900">{{ $convertedCount }}</h3>
        </div>
    </div>

    <div class="overflow-hidden rounded bg-white shadow">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-slate-100 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                <tr>
                    <th class="px-4 py-3">Quotation No</th>
                    <th class="px-4 py-3">Customer</th>
                    <th class="px-4 py-3">Date</th>
                    <th class="px-4 py-3">Valid Until</th>
                    <th class="px-4 py-3 text-right">Total</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3 text-right">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($quotations as $quotation)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-900">{{ $quotation->quotation_no }}</td>
                        <td class="px-4 py-3 text-gray-700">{{ $quotation->customer?->name ?: '-' }}</td>
                        <td class="px-4 py-3 text-gray-700">{{ $quotation->quotation_date?->format('d M Y') }}</td>
                        <td class="px-4 py-3 text-gray-700">{{ $quotation->valid_until?->format('d M Y') ?: '-' }}</td>
                        <td class="px-4 py-3 text-right font-semibold text-gray-900">Rs. {{ number_format((float) $quotation->total_amount, 2) }}</td>
                        <td class="px-4 py-3">
                            @include('quotations.partials.status-badge', ['status' => $quotation->status, 'label' => $quotation->statusLabel()])
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex justify-end gap-3">
                                <a href="{{ route('quotations.show', $quotation) }}" class="font-semibold text-slate-700 hover:text-slate-900">View</a>
                                <a href="{{ route('quotations.print', $quotation) }}" class="font-semibold text-slate-700 hover:text-slate-900">Print</a>
                                @if ($quotation->status !== 'converted')
                                    <a href="{{ route('quotations.edit', $quotation) }}" class="font-semibold text-slate-700 hover:text-slate-900">Edit</a>
                                @endif
                                @if ($quotation->status === 'accepted')
                                    <a href="{{ route('quotations.convert', $quotation) }}" class="font-semibold text-emerald-700 hover:text-emerald-900">Convert</a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-gray-500">No quotations found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-5">
        {{ $quotations->links() }}
    </div>
@endsection
