@extends('layouts.app')

@section('title', 'GST Sales Return Report')

@section('content')
    <x-erp.page-header title="GST Sales Return / Credit Note Report" description="Only credit notes linked to GST invoices are included." kicker="GST Returns">
        <x-slot:actions>
            <a href="{{ route('gst-reports.index', $filters) }}" class="erp-secondary-button">Summary</a>
            <a href="{{ route('gst-reports.sales', $filters) }}" class="erp-secondary-button">GST Sales</a>
            <a href="{{ route('gst-reports.export', array_merge($filters, ['type' => 'sales_returns'])) }}" class="erp-primary-button">Export CSV</a>
        </x-slot:actions>
    </x-erp.page-header>

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
        <div class="erp-summary-card"><p class="text-xs font-black uppercase tracking-[0.16em] text-slate-500">Taxable</p><h3 class="mt-2 text-xl font-black text-slate-950">₹ {{ number_format((float) $totals['taxable'], 2) }}</h3></div>
        <div class="erp-summary-card"><p class="text-xs font-black uppercase tracking-[0.16em] text-slate-500">GST Adjustment</p><h3 class="mt-2 text-xl font-black text-red-700">₹ {{ number_format((float) $totals['gst'], 2) }}</h3></div>
        <div class="erp-summary-card"><p class="text-xs font-black uppercase tracking-[0.16em] text-slate-500">Total Credit Notes</p><h3 class="mt-2 text-xl font-black text-slate-950">₹ {{ number_format((float) $totals['total'], 2) }}</h3></div>
        <div class="erp-summary-card"><p class="text-xs font-black uppercase tracking-[0.16em] text-slate-500">Refunded</p><h3 class="mt-2 text-xl font-black text-red-700">₹ {{ number_format((float) $totals['refund'], 2) }}</h3></div>
        <div class="erp-summary-card"><p class="text-xs font-black uppercase tracking-[0.16em] text-slate-500">Adjusted</p><h3 class="mt-2 text-xl font-black text-emerald-700">₹ {{ number_format((float) $totals['adjustment'], 2) }}</h3></div>
    </div>

    <x-erp.datatable
        id="gstSalesReturnsTable"
        :ajax-url="route('erp.datatables', 'gst-sales-returns')"
        search-placeholder="Search credit note, invoice, customer..."
        empty="No GST sales returns found."
    >
        <thead>
            <tr>
                <th class="px-4 py-3" data-column="return_no">Credit Note No</th>
                <th class="px-4 py-3" data-column="return_date">Return Date</th>
                <th class="px-4 py-3" data-column="sale" data-orderable="false" data-searchable="false">Invoice</th>
                <th class="px-4 py-3" data-column="customer" data-orderable="false" data-searchable="false">Customer</th>
                <th class="px-4 py-3 text-right" data-column="subtotal">Taxable</th>
                <th class="px-4 py-3 text-right" data-column="gst_amount">GST</th>
                <th class="px-4 py-3 text-right" data-column="total_amount">Total</th>
                <th class="px-4 py-3 text-right" data-column="refund_amount">Refund</th>
                <th class="px-4 py-3 text-right" data-column="adjustment_amount">Adjustment</th>
                <th class="px-4 py-3 text-right" data-column="actions" data-orderable="false" data-searchable="false">Action</th>
            </tr>
        </thead>
        <tbody></tbody>
    </x-erp.datatable>

    <div class="visually-hidden" aria-hidden="true">
        @foreach ($returns as $return)
            <span>{{ $return->return_no }}</span>
        @endforeach
    </div>
@endsection
