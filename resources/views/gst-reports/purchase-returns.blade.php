@extends('layouts.erp')

@section('title', 'GST Purchase Return Report')

@section('content')
    <x-erp.page-header title="GST Purchase Return / Debit Note Report" description="Only debit notes linked to GST purchases are included." kicker="GST Returns">
        <x-slot:actions>
            <a href="{{ route('gst-reports.index', $filters) }}" class="erp-secondary-button">Summary</a>
            <a href="{{ route('gst-reports.purchases', $filters) }}" class="erp-secondary-button">GST Purchases</a>
            <a href="{{ route('gst-reports.export', array_merge($filters, ['type' => 'purchase_returns'])) }}" class="erp-primary-button">Export CSV</a>
        </x-slot:actions>
    </x-erp.page-header>

    @include('gst-reports.partials.filters', [
        'action' => route('gst-reports.purchase-returns'),
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
        <div class="erp-summary-card"><p class="text-xs font-black uppercase tracking-[0.16em] text-slate-500">Input GST Adjustment</p><h3 class="mt-2 text-xl font-black text-emerald-700">Rs. {{ number_format((float) $totals['gst'], 2) }}</h3></div>
        <div class="erp-summary-card"><p class="text-xs font-black uppercase tracking-[0.16em] text-slate-500">Total Debit Notes</p><h3 class="mt-2 text-xl font-black text-slate-950">Rs. {{ number_format((float) $totals['total'], 2) }}</h3></div>
        <div class="erp-summary-card"><p class="text-xs font-black uppercase tracking-[0.16em] text-slate-500">Refund Received</p><h3 class="mt-2 text-xl font-black text-emerald-700">Rs. {{ number_format((float) $totals['refund'], 2) }}</h3></div>
        <div class="erp-summary-card"><p class="text-xs font-black uppercase tracking-[0.16em] text-slate-500">Payable Adjusted</p><h3 class="mt-2 text-xl font-black text-red-700">Rs. {{ number_format((float) $totals['adjustment'], 2) }}</h3></div>
    </div>

    <x-erp.datatable
        id="gstPurchaseReturnsTable"
        :ajax-url="route('erp.datatables', 'gst-purchase-returns')"
        search-placeholder="Search debit note, purchase, supplier..."
        empty="No GST purchase returns found."
    >
        <thead>
            <tr>
                <th class="px-4 py-3" data-column="return_no">Debit Note No</th>
                <th class="px-4 py-3" data-column="return_date">Return Date</th>
                <th class="px-4 py-3" data-column="purchase" data-orderable="false" data-searchable="false">Purchase</th>
                <th class="px-4 py-3" data-column="supplier" data-orderable="false" data-searchable="false">Supplier</th>
                <th class="px-4 py-3 text-right" data-column="subtotal">Taxable</th>
                <th class="px-4 py-3 text-right" data-column="gst_amount">Input GST</th>
                <th class="px-4 py-3 text-right" data-column="total_amount">Total</th>
                <th class="px-4 py-3 text-right" data-column="refund_amount">Refund</th>
                <th class="px-4 py-3 text-right" data-column="adjustment_amount">Adjustment</th>
                <th class="px-4 py-3 text-right" data-column="actions" data-orderable="false" data-searchable="false">Action</th>
            </tr>
        </thead>
        <tbody></tbody>
    </x-erp.datatable>
@endsection
