@extends('layouts.erp')

@section('title', 'Sales Returns')

@section('content')
    <div class="mb-5 flex items-center justify-between">
        <div>
            <h2 class="text-lg font-semibold text-gray-900">Sales Returns</h2>
            <p class="text-sm text-gray-500">Customer returned goods with stock, ledger, and refund posting.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('sales-returns.report') }}" class="rounded border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Report</a>
            <a href="{{ route('sales-returns.create') }}" class="rounded bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">Create Return</a>
        </div>
    </div>

    <div class="mb-5 grid grid-cols-1 gap-4 md:grid-cols-3">
        <div class="rounded bg-white p-5 shadow">
            <p class="text-sm text-gray-500">Return Total</p>
            <h3 class="mt-1 text-2xl font-bold text-gray-900">Rs. {{ number_format((float) $totalAmount, 2) }}</h3>
        </div>
        <div class="rounded bg-white p-5 shadow">
            <p class="text-sm text-gray-500">Refunded</p>
            <h3 class="mt-1 text-2xl font-bold text-red-700">Rs. {{ number_format((float) $refundAmount, 2) }}</h3>
        </div>
        <div class="rounded bg-white p-5 shadow">
            <p class="text-sm text-gray-500">Adjusted</p>
            <h3 class="mt-1 text-2xl font-bold text-emerald-700">Rs. {{ number_format((float) $adjustmentAmount, 2) }}</h3>
        </div>
    </div>

    <div class="overflow-hidden rounded bg-white shadow">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-slate-100 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                <tr>
                    <th class="px-4 py-3">Return No</th>
                    <th class="px-4 py-3">Date</th>
                    <th class="px-4 py-3">Sale</th>
                    <th class="px-4 py-3">Customer</th>
                    <th class="px-4 py-3 text-right">Total</th>
                    <th class="px-4 py-3 text-right">Refund</th>
                    <th class="px-4 py-3 text-right">Adjustment</th>
                    <th class="px-4 py-3 text-right">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($returns as $return)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-900">{{ $return->return_no }}</td>
                        <td class="px-4 py-3 text-gray-700">{{ $return->return_date?->format('d M Y') }}</td>
                        <td class="px-4 py-3 text-gray-700">{{ $return->sale?->sale_no ?: '-' }}</td>
                        <td class="px-4 py-3 text-gray-700">{{ $return->customer?->name ?: '-' }}</td>
                        <td class="px-4 py-3 text-right font-semibold text-gray-900">Rs. {{ number_format((float) $return->total_amount, 2) }}</td>
                        <td class="px-4 py-3 text-right text-red-700">Rs. {{ number_format((float) $return->refund_amount, 2) }}</td>
                        <td class="px-4 py-3 text-right text-emerald-700">Rs. {{ number_format((float) $return->adjustment_amount, 2) }}</td>
                        <td class="px-4 py-3 text-right space-x-3">
                            <a href="{{ route('sales-returns.print', $return) }}" class="font-semibold text-slate-700 hover:text-slate-900">Print</a>
                            <a href="{{ route('sales-returns.show', $return) }}" class="font-semibold text-slate-700 hover:text-slate-900">View</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-8 text-center text-gray-500">No sales returns found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-5">{{ $returns->links() }}</div>
@endsection
