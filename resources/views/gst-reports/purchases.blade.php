@extends('layouts.erp')

@section('title', 'GST Purchase Report')

@section('content')
    <x-erp.page-header title="GST Purchase Report" description="Only GST purchases are included for input GST." kicker="GST Input">
        <x-slot:actions>
            <a href="{{ route('gst-reports.index', $filters) }}" class="erp-secondary-button">Summary</a>
            <a href="{{ route('gst-reports.purchase-returns', $filters) }}" class="erp-secondary-button">Debit Notes</a>
            <a href="{{ route('gst-reports.pdf', $filters) }}" target="_blank" class="erp-secondary-button">PDF</a>
            <a href="{{ route('gst-reports.export', array_merge($filters, ['type' => 'purchases'])) }}" class="erp-primary-button">Export CSV</a>
        </x-slot:actions>
    </x-erp.page-header>

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
        <div class="erp-summary-card"><p class="text-xs font-black uppercase tracking-[0.16em] text-slate-500">Taxable</p><h3 class="mt-2 text-xl font-black text-slate-950">Rs. {{ number_format((float) $totals['taxable'], 2) }}</h3></div>
        <div class="erp-summary-card"><p class="text-xs font-black uppercase tracking-[0.16em] text-slate-500">Input GST</p><h3 class="mt-2 text-xl font-black text-red-700">Rs. {{ number_format((float) $totals['gst'], 2) }}</h3></div>
        <div class="erp-summary-card"><p class="text-xs font-black uppercase tracking-[0.16em] text-slate-500">Total</p><h3 class="mt-2 text-xl font-black text-slate-950">Rs. {{ number_format((float) $totals['total'], 2) }}</h3></div>
        <div class="erp-summary-card"><p class="text-xs font-black uppercase tracking-[0.16em] text-slate-500">Paid</p><h3 class="mt-2 text-xl font-black text-slate-950">Rs. {{ number_format((float) $totals['paid'], 2) }}</h3></div>
        <div class="erp-summary-card"><p class="text-xs font-black uppercase tracking-[0.16em] text-slate-500">Balance</p><h3 class="mt-2 text-xl font-black text-red-700">Rs. {{ number_format((float) $totals['balance'], 2) }}</h3></div>
        <div class="erp-summary-card"><p class="text-xs font-black uppercase tracking-[0.16em] text-slate-500">GST Returns</p><h3 class="mt-2 text-xl font-black text-emerald-700">Rs. {{ number_format((float) $totals['returns'], 2) }}</h3></div>
    </div>

    <x-erp.datatable
        id="gstPurchasesTable"
        :ajax-url="route('erp.datatables', 'gst-purchases')"
        search-placeholder="Search GST purchase, supplier..."
        empty="No GST purchases found."
    >
        <thead>
            <tr>
                <th class="px-4 py-3" data-column="purchase_no">Purchase No</th>
                <th class="px-4 py-3" data-column="supplier_invoice_no">Supplier Invoice</th>
                <th class="px-4 py-3" data-column="purchase_date">Purchase Date</th>
                <th class="px-4 py-3" data-column="supplier" data-orderable="false" data-searchable="false">Supplier</th>
                <th class="px-4 py-3 text-right" data-column="subtotal">Taxable</th>
                <th class="px-4 py-3 text-right" data-column="gst_amount">Input GST</th>
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
