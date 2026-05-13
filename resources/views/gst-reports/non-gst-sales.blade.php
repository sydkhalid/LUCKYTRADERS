@extends('layouts.app')

@section('title', 'Non-GST Sales Report')

@section('content')
    <x-erp.page-header title="Non-GST Sales Report" description="Normal bills stay separate from GST reports and auditor export." kicker="Normal Bills">
        <x-slot:actions>
            <a href="{{ route('gst-reports.index', $filters) }}" class="erp-secondary-button">Summary</a>
        </x-slot:actions>
    </x-erp.page-header>

    @include('gst-reports.partials.filters', [
        'action' => route('gst-reports.non-gst-sales'),
        'filters' => $filters,
        'customers' => $customers,
        'billTypes' => $billTypes,
        'paymentStatuses' => $paymentStatuses,
        'showCustomer' => true,
        'showBillType' => true,
        'showPaymentStatus' => true,
    ])

    <div class="mb-5 grid grid-cols-1 gap-4 md:grid-cols-4">
        <div class="erp-summary-card"><p class="text-xs font-black uppercase tracking-[0.16em] text-slate-500">Taxable</p><h3 class="mt-2 text-xl font-black text-slate-950">Rs. {{ number_format((float) $totals['taxable'], 2) }}</h3></div>
        <div class="erp-summary-card"><p class="text-xs font-black uppercase tracking-[0.16em] text-slate-500">Total</p><h3 class="mt-2 text-xl font-black text-slate-950">Rs. {{ number_format((float) $totals['total'], 2) }}</h3></div>
        <div class="erp-summary-card"><p class="text-xs font-black uppercase tracking-[0.16em] text-slate-500">Paid</p><h3 class="mt-2 text-xl font-black text-slate-950">Rs. {{ number_format((float) $totals['paid'], 2) }}</h3></div>
        <div class="erp-summary-card"><p class="text-xs font-black uppercase tracking-[0.16em] text-slate-500">Balance</p><h3 class="mt-2 text-xl font-black text-red-700">Rs. {{ number_format((float) $totals['balance'], 2) }}</h3></div>
    </div>

    <x-erp.datatable
        id="nonGstSalesTable"
        :ajax-url="route('erp.datatables', 'non-gst-sales')"
        search-placeholder="Search normal bill, customer..."
        empty="No non-GST sales found."
    >
        <thead>
            <tr>
                <th class="px-4 py-3" data-column="sale_no">Bill No</th>
                <th class="px-4 py-3" data-column="sale_date">Bill Date</th>
                <th class="px-4 py-3" data-column="customer" data-orderable="false" data-searchable="false">Customer</th>
                <th class="px-4 py-3 text-right" data-column="subtotal">Taxable</th>
                <th class="px-4 py-3 text-right" data-column="total_amount">Total</th>
                <th class="px-4 py-3 text-right" data-column="paid_amount">Paid</th>
                <th class="px-4 py-3 text-right" data-column="balance_amount">Balance</th>
                <th class="px-4 py-3" data-column="payment_status">Status</th>
                <th class="px-4 py-3 text-right" data-column="actions" data-orderable="false" data-searchable="false">Action</th>
            </tr>
        </thead>
        <tbody></tbody>
    </x-erp.datatable>

    <div class="visually-hidden" aria-hidden="true">
        @foreach ($sales as $sale)
            <span>{{ $sale->sale_no }}</span>
        @endforeach
    </div>
@endsection
