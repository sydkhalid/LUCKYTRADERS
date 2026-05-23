@extends('layouts.app')

@section('title', 'Sales Return Report')

@section('content')
    <div class="mb-5 flex items-center justify-between">
        <div>
            <h2 class="text-lg font-semibold text-gray-900">Sales Return Report</h2>
            <p class="text-sm text-gray-500">Date filtered customer return report.</p>
        </div>
        <a href="{{ route('sales-returns.index') }}" class="rounded border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Sales Returns</a>
    </div>

    <form method="GET" action="{{ route('sales-returns.report') }}" class="mb-5 rounded bg-white p-5 shadow">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
            <div><label class="mb-1 block text-sm font-medium text-gray-700">From Date</label><input type="date" name="from_date" value="{{ $filters['from_date'] ?? '' }}" class="w-full rounded border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500"></div>
            <div><label class="mb-1 block text-sm font-medium text-gray-700">To Date</label><input type="date" name="to_date" value="{{ $filters['to_date'] ?? '' }}" class="w-full rounded border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500"></div>
            <div class="flex items-end"><button class="w-full rounded bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">Filter</button></div>
            <div class="flex items-end"><a href="{{ route('sales-returns.report') }}" class="w-full rounded border border-gray-300 px-4 py-2 text-center text-sm font-semibold text-gray-700 hover:bg-gray-50">Reset</a></div>
        </div>
    </form>

    <div class="mb-5 grid grid-cols-1 gap-4 md:grid-cols-5">
        <div class="rounded bg-white p-4 shadow"><p class="text-sm text-gray-500">Taxable</p><h3 class="mt-1 text-xl font-bold text-gray-900">₹ {{ number_format((float) $totals['subtotal'], 2) }}</h3></div>
        <div class="rounded bg-white p-4 shadow"><p class="text-sm text-gray-500">GST</p><h3 class="mt-1 text-xl font-bold text-gray-900">₹ {{ number_format((float) $totals['gst'], 2) }}</h3></div>
        <div class="rounded bg-white p-4 shadow"><p class="text-sm text-gray-500">Total</p><h3 class="mt-1 text-xl font-bold text-gray-900">₹ {{ number_format((float) $totals['total'], 2) }}</h3></div>
        <div class="rounded bg-white p-4 shadow"><p class="text-sm text-gray-500">Refund</p><h3 class="mt-1 text-xl font-bold text-red-700">₹ {{ number_format((float) $totals['refund'], 2) }}</h3></div>
        <div class="rounded bg-white p-4 shadow"><p class="text-sm text-gray-500">Adjustment</p><h3 class="mt-1 text-xl font-bold text-emerald-700">₹ {{ number_format((float) $totals['adjustment'], 2) }}</h3></div>
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
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($returns as $return)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-900">{{ $return->return_no }}</td>
                        <td class="px-4 py-3 text-gray-700">{{ $return->return_date?->format('d M Y') }}</td>
                        <td class="px-4 py-3 text-gray-700">{{ $return->sale?->sale_no ?: '-' }}</td>
                        <td class="px-4 py-3 text-gray-700">{{ $return->customer?->name ?: '-' }}</td>
                        <td class="px-4 py-3 text-right font-semibold text-gray-900">₹ {{ number_format((float) $return->total_amount, 2) }}</td>
                        <td class="px-4 py-3 text-right text-red-700">₹ {{ number_format((float) $return->refund_amount, 2) }}</td>
                        <td class="px-4 py-3 text-right text-emerald-700">₹ {{ number_format((float) $return->adjustment_amount, 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-8 text-center text-gray-500">No sales returns found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-5">{{ $returns->links() }}</div>
@endsection
