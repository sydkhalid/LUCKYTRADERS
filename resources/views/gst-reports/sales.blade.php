@extends('layouts.erp')

@section('title', 'GST Sales Report')

@section('content')
    <x-erp.page-header title="GST Sales Report" description="Only GST invoices from sales are included." kicker="GST Output">
        <x-slot:actions>
            <a href="{{ route('gst-reports.index', $filters) }}" class="erp-secondary-button">Summary</a>
            <a href="{{ route('gst-reports.sales-returns', $filters) }}" class="erp-secondary-button">Credit Notes</a>
            <a href="{{ route('gst-reports.pdf', $filters) }}" target="_blank" class="erp-secondary-button">PDF</a>
            <a href="{{ route('gst-reports.export', array_merge($filters, ['type' => 'sales'])) }}" class="erp-primary-button">Export CSV</a>
        </x-slot:actions>
    </x-erp.page-header>

    @include('gst-reports.partials.filters', [
        'action' => route('gst-reports.sales'),
        'filters' => $filters,
        'customers' => $customers,
        'billTypes' => $billTypes,
        'paymentStatuses' => $paymentStatuses,
        'showCustomer' => true,
        'showBillType' => true,
        'showPaymentStatus' => true,
    ])

    <div class="mb-5 grid grid-cols-1 gap-4 md:grid-cols-5">
        <div class="erp-summary-card"><p class="text-xs font-black uppercase tracking-[0.16em] text-slate-500">Taxable</p><h3 class="mt-2 text-xl font-black text-slate-950">Rs. {{ number_format((float) $totals['taxable'], 2) }}</h3></div>
        <div class="erp-summary-card"><p class="text-xs font-black uppercase tracking-[0.16em] text-slate-500">GST</p><h3 class="mt-2 text-xl font-black text-emerald-700">Rs. {{ number_format((float) $totals['gst'], 2) }}</h3></div>
        <div class="erp-summary-card"><p class="text-xs font-black uppercase tracking-[0.16em] text-slate-500">Total</p><h3 class="mt-2 text-xl font-black text-slate-950">Rs. {{ number_format((float) $totals['total'], 2) }}</h3></div>
        <div class="erp-summary-card"><p class="text-xs font-black uppercase tracking-[0.16em] text-slate-500">Paid</p><h3 class="mt-2 text-xl font-black text-slate-950">Rs. {{ number_format((float) $totals['paid'], 2) }}</h3></div>
        <div class="erp-summary-card"><p class="text-xs font-black uppercase tracking-[0.16em] text-slate-500">Balance</p><h3 class="mt-2 text-xl font-black text-red-700">Rs. {{ number_format((float) $totals['balance'], 2) }}</h3></div>
        <div class="erp-summary-card"><p class="text-xs font-black uppercase tracking-[0.16em] text-slate-500">GST Returns</p><h3 class="mt-2 text-xl font-black text-red-700">Rs. {{ number_format((float) $totals['returns'], 2) }}</h3></div>
    </div>

    <x-erp.datatable
        id="gstSalesTable"
        :ajax-url="route('erp.datatables', 'gst-sales')"
        search-placeholder="Search GST invoice, customer..."
        empty="No GST sales found."
    >
        <thead>
            <tr>
                <th class="px-4 py-3" data-column="sale_no">Invoice No</th>
                <th class="px-4 py-3" data-column="sale_date">Invoice Date</th>
                <th class="px-4 py-3" data-column="customer" data-orderable="false" data-searchable="false">Customer</th>
                <th class="px-4 py-3 text-right" data-column="subtotal">Taxable</th>
                <th class="px-4 py-3 text-right" data-column="gst_amount">GST</th>
                <th class="px-4 py-3 text-right" data-column="total_amount">Total</th>
                <th class="px-4 py-3 text-right" data-column="paid_amount">Paid</th>
                <th class="px-4 py-3 text-right" data-column="balance_amount">Balance</th>
                <th class="px-4 py-3" data-column="payment_status">Status</th>
                <th class="px-4 py-3 text-right" data-column="actions" data-orderable="false" data-searchable="false">Action</th>
            </tr>
        </thead>
        <tbody></tbody>
    </x-erp.datatable>
@endsection
