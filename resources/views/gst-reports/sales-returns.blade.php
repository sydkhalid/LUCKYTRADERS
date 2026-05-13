@extends('layouts.erp')

@section('title', 'GST Sales Return Report')

@section('content')
    <div class="mb-5 flex items-center justify-between">
        <div>
            <h2 class="text-lg font-semibold text-gray-900">GST Sales Return / Credit Note Report</h2>
            <p class="text-sm text-gray-500">Only credit notes linked to GST invoices are included.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('gst-reports.index', $filters) }}" class="rounded border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Summary</a>
            <a href="{{ route('gst-reports.sales', $filters) }}" class="rounded border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">GST Sales</a>
            <a href="{{ route('gst-reports.export', array_merge($filters, ['type' => 'sales_returns'])) }}" class="rounded bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">Export CSV</a>
        </div>
    </div>

    @include('gst-reports.partials.filters', [
        'action' => route('gst-reports.sales-returns'),
        'filters' => $filters,
        'customers' => $customers,
        'billTypes' => $billTypes,
        'paymentStatuses' => $paymentStatuses,
        'showCustomer' => true,
        'showBillType' => true,
        'showPaymentStatus' => true,
    ])

    <div class="mb-5 grid grid-cols-1 gap-4 md:grid-cols-5">
        <div class="rounded bg-white p-4 shadow"><p class="text-sm text-gray-500">Taxable</p><h3 class="mt-1 text-xl font-bold text-gray-900">Rs. {{ number_format((float) $totals['taxable'], 2) }}</h3></div>
        <div class="rounded bg-white p-4 shadow"><p class="text-sm text-gray-500">GST Adjustment</p><h3 class="mt-1 text-xl font-bold text-red-700">Rs. {{ number_format((float) $totals['gst'], 2) }}</h3></div>
        <div class="rounded bg-white p-4 shadow"><p class="text-sm text-gray-500">Total Credit Notes</p><h3 class="mt-1 text-xl font-bold text-gray-900">Rs. {{ number_format((float) $totals['total'], 2) }}</h3></div>
        <div class="rounded bg-white p-4 shadow"><p class="text-sm text-gray-500">Refunded</p><h3 class="mt-1 text-xl font-bold text-red-700">Rs. {{ number_format((float) $totals['refund'], 2) }}</h3></div>
        <div class="rounded bg-white p-4 shadow"><p class="text-sm text-gray-500">Adjusted</p><h3 class="mt-1 text-xl font-bold text-emerald-700">Rs. {{ number_format((float) $totals['adjustment'], 2) }}</h3></div>
    </div>

    <div class="overflow-hidden rounded bg-white shadow">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-slate-100 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                <tr>
                    <th class="px-4 py-3">Credit Note No</th>
                    <th class="px-4 py-3">Return Date</th>
                    <th class="px-4 py-3">Invoice No</th>
                    <th class="px-4 py-3">Customer</th>
                    <th class="px-4 py-3">GST Number</th>
                    <th class="px-4 py-3 text-right">Taxable</th>
                    <th class="px-4 py-3 text-right">GST</th>
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
                        <td class="px-4 py-3 text-gray-700">{{ $return->customer?->gst_number ?: '-' }}</td>
                        <td class="px-4 py-3 text-right text-gray-900">Rs. {{ number_format((float) $return->subtotal, 2) }}</td>
                        <td class="px-4 py-3 text-right text-gray-900">Rs. {{ number_format((float) $return->gst_amount, 2) }}</td>
                        <td class="px-4 py-3 text-right font-semibold text-gray-900">Rs. {{ number_format((float) $return->total_amount, 2) }}</td>
                        <td class="px-4 py-3 text-right text-red-700">Rs. {{ number_format((float) $return->refund_amount, 2) }}</td>
                        <td class="px-4 py-3 text-right text-emerald-700">Rs. {{ number_format((float) $return->adjustment_amount, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="px-4 py-8 text-center text-gray-500">No GST sales returns found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-5">
        {{ $returns->links() }}
    </div>
@endsection
