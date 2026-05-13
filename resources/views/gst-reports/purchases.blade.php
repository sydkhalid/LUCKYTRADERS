@extends('layouts.erp')

@section('title', 'GST Purchase Report')

@section('content')
    <div class="mb-5 flex items-center justify-between">
        <div>
            <h2 class="text-lg font-semibold text-gray-900">GST Purchase Report</h2>
            <p class="text-sm text-gray-500">Only GST purchases are included for input GST.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('gst-reports.index', $filters) }}" class="rounded border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Summary</a>
            <a href="{{ route('gst-reports.purchase-returns', $filters) }}" class="rounded border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Debit Notes</a>
            <a href="{{ route('gst-reports.pdf', $filters) }}" target="_blank" class="rounded border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">PDF</a>
            <a href="{{ route('gst-reports.export', array_merge($filters, ['type' => 'purchases'])) }}" class="rounded bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">Export CSV</a>
        </div>
    </div>

    @include('gst-reports.partials.filters', [
        'action' => route('gst-reports.purchases'),
        'filters' => $filters,
        'suppliers' => $suppliers,
        'billTypes' => $billTypes,
        'paymentStatuses' => $paymentStatuses,
        'showSupplier' => true,
        'showBillType' => true,
        'showPaymentStatus' => true,
    ])

    <div class="mb-5 grid grid-cols-1 gap-4 md:grid-cols-5">
        <div class="rounded bg-white p-4 shadow">
            <p class="text-sm text-gray-500">Taxable</p>
            <h3 class="mt-1 text-xl font-bold text-gray-900">Rs. {{ number_format((float) $totals['taxable'], 2) }}</h3>
        </div>
        <div class="rounded bg-white p-4 shadow">
            <p class="text-sm text-gray-500">Input GST</p>
            <h3 class="mt-1 text-xl font-bold text-red-700">Rs. {{ number_format((float) $totals['gst'], 2) }}</h3>
        </div>
        <div class="rounded bg-white p-4 shadow">
            <p class="text-sm text-gray-500">Total</p>
            <h3 class="mt-1 text-xl font-bold text-gray-900">Rs. {{ number_format((float) $totals['total'], 2) }}</h3>
        </div>
        <div class="rounded bg-white p-4 shadow">
            <p class="text-sm text-gray-500">Paid</p>
            <h3 class="mt-1 text-xl font-bold text-gray-900">Rs. {{ number_format((float) $totals['paid'], 2) }}</h3>
        </div>
        <div class="rounded bg-white p-4 shadow">
            <p class="text-sm text-gray-500">Balance</p>
            <h3 class="mt-1 text-xl font-bold text-red-700">Rs. {{ number_format((float) $totals['balance'], 2) }}</h3>
        </div>
        <div class="rounded bg-white p-4 shadow">
            <p class="text-sm text-gray-500">GST Returns</p>
            <h3 class="mt-1 text-xl font-bold text-emerald-700">Rs. {{ number_format((float) $totals['returns'], 2) }}</h3>
        </div>
    </div>

    <div class="overflow-hidden rounded bg-white shadow">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-slate-100 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                <tr>
                    <th class="px-4 py-3">Purchase No</th>
                    <th class="px-4 py-3">Supplier Invoice No</th>
                    <th class="px-4 py-3">Purchase Date</th>
                    <th class="px-4 py-3">Supplier Name</th>
                    <th class="px-4 py-3">Supplier GST Number</th>
                    <th class="px-4 py-3 text-right">Taxable Amount</th>
                    <th class="px-4 py-3 text-right">Input GST</th>
                    <th class="px-4 py-3 text-right">Total Amount</th>
                    <th class="px-4 py-3 text-right">Paid Amount</th>
                    <th class="px-4 py-3 text-right">Balance Amount</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($purchases as $purchase)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-900">{{ $purchase->purchase_no }}</td>
                        <td class="px-4 py-3 text-gray-700">{{ $purchase->supplier_invoice_no ?: '-' }}</td>
                        <td class="px-4 py-3 text-gray-700">{{ $purchase->purchase_date?->format('d M Y') }}</td>
                        <td class="px-4 py-3 text-gray-700">{{ $purchase->supplier?->name ?: '-' }}</td>
                        <td class="px-4 py-3 text-gray-700">{{ $purchase->supplier?->gst_number ?: '-' }}</td>
                        <td class="px-4 py-3 text-right text-gray-900">Rs. {{ number_format((float) $purchase->subtotal, 2) }}</td>
                        <td class="px-4 py-3 text-right text-gray-900">Rs. {{ number_format((float) $purchase->gst_amount, 2) }}</td>
                        <td class="px-4 py-3 text-right font-semibold text-gray-900">Rs. {{ number_format((float) $purchase->total_amount, 2) }}</td>
                        <td class="px-4 py-3 text-right text-gray-900">Rs. {{ number_format((float) $purchase->paid_amount, 2) }}</td>
                        <td class="px-4 py-3 text-right text-red-700">Rs. {{ number_format((float) $purchase->balance_amount, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="px-4 py-8 text-center text-gray-500">No GST purchases found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-5">
        {{ $purchases->links() }}
    </div>
@endsection
