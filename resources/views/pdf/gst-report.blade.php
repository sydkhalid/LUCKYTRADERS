@extends('pdf.layout')

@section('content')
    @include('pdf.partials.header', [
        'documentNo' => 'GST-REPORT',
        'documentDate' => $generatedAt->format('d M Y h:i A'),
    ])

    <div class="section">
        <div class="section-title">Report Filters</div>
        <div class="box">
            From: <span class="bold">{{ $filters['from_date'] ?: 'All' }}</span>
            &nbsp;&nbsp; To: <span class="bold">{{ $filters['to_date'] ?: 'All' }}</span>
        </div>
    </div>

    <table class="items section">
        <thead>
            <tr><th>Summary</th><th class="right">Amount</th></tr>
        </thead>
        <tbody>
            <tr><td>Total Taxable Sales</td><td class="right">Rs. {{ number_format((float) $summary['taxable_sales'], 2) }}</td></tr>
            <tr><td>Output GST</td><td class="right">Rs. {{ number_format((float) $summary['output_gst'], 2) }}</td></tr>
            <tr><td>GST Sales Total</td><td class="right">Rs. {{ number_format((float) $summary['total_sales'], 2) }}</td></tr>
            <tr><td>Total Taxable Purchases</td><td class="right">Rs. {{ number_format((float) $summary['taxable_purchases'], 2) }}</td></tr>
            <tr><td>Input GST</td><td class="right">Rs. {{ number_format((float) $summary['input_gst'], 2) }}</td></tr>
            <tr><td>GST Purchase Total</td><td class="right">Rs. {{ number_format((float) $summary['total_purchases'], 2) }}</td></tr>
            <tr><td>GST Sales Returns</td><td class="right">Rs. {{ number_format((float) $summary['sales_returns'], 2) }}</td></tr>
            <tr><td>GST Purchase Returns</td><td class="right">Rs. {{ number_format((float) $summary['purchase_returns'], 2) }}</td></tr>
        </tbody>
        <tfoot>
            <tr class="total-row"><td class="right">Net GST Payable</td><td class="right">Rs. {{ number_format((float) $summary['net_gst_payable'], 2) }}</td></tr>
        </tfoot>
    </table>

    <div class="words"><span class="label">Net GST payable in words:</span> {{ $amountWords }}</div>

    <div class="section-title section">GST Sales</div>
    <table class="items">
        <thead>
            <tr><th>Invoice No</th><th>Date</th><th>Customer</th><th>GSTIN</th><th class="right">Taxable</th><th class="right">GST</th><th class="right">Total</th><th class="right">Paid</th><th class="right">Balance</th></tr>
        </thead>
        <tbody>
            @forelse ($sales as $sale)
                <tr>
                    <td>{{ $sale->sale_no }}</td>
                    <td>{{ $sale->sale_date?->format('d M Y') }}</td>
                    <td>{{ $sale->customer?->name ?: '-' }}</td>
                    <td>{{ $sale->customer?->gst_number ?: '-' }}</td>
                    <td class="right">{{ number_format((float) $sale->subtotal, 2) }}</td>
                    <td class="right">{{ number_format((float) $sale->gst_amount, 2) }}</td>
                    <td class="right">{{ number_format((float) $sale->total_amount, 2) }}</td>
                    <td class="right">{{ number_format((float) $sale->paid_amount, 2) }}</td>
                    <td class="right">{{ number_format((float) $sale->balance_amount, 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="9" class="center">No GST sales found.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="section-title section">GST Purchases</div>
    <table class="items">
        <thead>
            <tr><th>Purchase No</th><th>Supplier Inv</th><th>Date</th><th>Supplier</th><th>GSTIN</th><th class="right">Taxable</th><th class="right">Input GST</th><th class="right">Total</th><th class="right">Balance</th></tr>
        </thead>
        <tbody>
            @forelse ($purchases as $purchase)
                <tr>
                    <td>{{ $purchase->purchase_no }}</td>
                    <td>{{ $purchase->supplier_invoice_no ?: '-' }}</td>
                    <td>{{ $purchase->purchase_date?->format('d M Y') }}</td>
                    <td>{{ $purchase->supplier?->name ?: '-' }}</td>
                    <td>{{ $purchase->supplier?->gst_number ?: '-' }}</td>
                    <td class="right">{{ number_format((float) $purchase->subtotal, 2) }}</td>
                    <td class="right">{{ number_format((float) $purchase->gst_amount, 2) }}</td>
                    <td class="right">{{ number_format((float) $purchase->total_amount, 2) }}</td>
                    <td class="right">{{ number_format((float) $purchase->balance_amount, 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="9" class="center">No GST purchases found.</td></tr>
            @endforelse
        </tbody>
    </table>

    @if ($salesReturns->isNotEmpty() || $purchaseReturns->isNotEmpty())
        <div class="section-title section">GST Returns Adjustment</div>
        <table class="items">
            <thead>
                <tr><th>Type</th><th>Return No</th><th>Date</th><th>Party</th><th class="right">Taxable</th><th class="right">GST</th><th class="right">Total</th></tr>
            </thead>
            <tbody>
                @foreach ($salesReturns as $return)
                    <tr>
                        <td>Sales Return</td>
                        <td>{{ $return->return_no }}</td>
                        <td>{{ $return->return_date?->format('d M Y') }}</td>
                        <td>{{ $return->customer?->name ?: '-' }}</td>
                        <td class="right">-{{ number_format((float) $return->subtotal, 2) }}</td>
                        <td class="right">-{{ number_format((float) $return->gst_amount, 2) }}</td>
                        <td class="right">-{{ number_format((float) $return->total_amount, 2) }}</td>
                    </tr>
                @endforeach
                @foreach ($purchaseReturns as $return)
                    <tr>
                        <td>Purchase Return</td>
                        <td>{{ $return->return_no }}</td>
                        <td>{{ $return->return_date?->format('d M Y') }}</td>
                        <td>{{ $return->supplier?->name ?: '-' }}</td>
                        <td class="right">-{{ number_format((float) $return->subtotal, 2) }}</td>
                        <td class="right">-{{ number_format((float) $return->gst_amount, 2) }}</td>
                        <td class="right">-{{ number_format((float) $return->total_amount, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <table class="footer-grid">
        <tr>
            <td></td>
            <td>@include('pdf.partials.signature')</td>
        </tr>
    </table>
@endsection
