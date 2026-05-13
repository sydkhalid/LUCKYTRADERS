@extends('pdf.layout')

@section('content')
    @php($isGst = $sale->bill_type === 'gst')
    @include('pdf.partials.header', [
        'documentNo' => $sale->sale_no,
        'documentDate' => $sale->sale_date?->format('d M Y'),
    ])

    <table class="grid-2 section">
        <tr>
            <td style="padding-right: 8px;">
                <div class="section-title">Bill To</div>
                <div class="box">
                    <p class="bold">{{ $sale->customer?->name ?: '-' }}</p>
                    <p>{{ $sale->customer?->address ?: '-' }}</p>
                    <p>Phone: {{ $sale->customer?->phone ?: '-' }}</p>
                    @if ($isGst)
                        <p>Customer GSTIN: {{ $sale->customer?->gst_number ?: '-' }}</p>
                    @endif
                </div>
            </td>
            <td style="padding-left: 8px;">
                <div class="section-title">Invoice Details</div>
                <div class="box">
                    <table class="meta">
                        <tr><td class="label">Invoice Type</td><td class="right">{{ $isGst ? 'GST Invoice' : 'Normal Bill' }}</td></tr>
                        <tr><td class="label">Payment Mode</td><td class="right">{{ strtoupper($sale->payment_mode) }}</td></tr>
                        <tr><td class="label">Payment Status</td><td class="right">{{ ucfirst($sale->payment_status) }}</td></tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>

    <table class="items section">
        <thead>
            @if ($isGst)
                <tr>
                    <th>#</th>
                    <th>Product</th>
                    <th>HSN</th>
                    <th class="right">Qty</th>
                    <th>Unit</th>
                    <th class="right">Rate</th>
                    <th class="right">Taxable</th>
                    <th class="right">GST %</th>
                    <th class="right">GST</th>
                    <th class="right">Total</th>
                </tr>
            @else
                <tr>
                    <th>#</th>
                    <th>Product</th>
                    <th class="right">Qty</th>
                    <th>Unit</th>
                    <th class="right">Rate</th>
                    <th class="right">Total</th>
                </tr>
            @endif
        </thead>
        <tbody>
            @foreach ($sale->items as $item)
                @if ($isGst)
                    <tr>
                        <td class="center">{{ $loop->iteration }}</td>
                        <td>{{ $item->product?->name ?: '-' }}</td>
                        <td>{{ $item->product?->hsn_code ?: '-' }}</td>
                        <td class="right">{{ number_format((float) $item->quantity, 3) }}</td>
                        <td>{{ $item->unit }}</td>
                        <td class="right">{{ number_format((float) $item->rate, 2) }}</td>
                        <td class="right">{{ number_format((float) $item->subtotal, 2) }}</td>
                        <td class="right">{{ number_format((float) $item->gst_percentage, 2) }}</td>
                        <td class="right">{{ number_format((float) $item->gst_amount, 2) }}</td>
                        <td class="right bold">{{ number_format((float) $item->total, 2) }}</td>
                    </tr>
                @else
                    <tr>
                        <td class="center">{{ $loop->iteration }}</td>
                        <td>{{ $item->product?->name ?: '-' }}</td>
                        <td class="right">{{ number_format((float) $item->quantity, 3) }}</td>
                        <td>{{ $item->unit }}</td>
                        <td class="right">{{ number_format((float) $item->rate, 2) }}</td>
                        <td class="right bold">{{ number_format((float) $item->total, 2) }}</td>
                    </tr>
                @endif
            @endforeach
        </tbody>
        <tfoot>
            <tr><td colspan="{{ $isGst ? 9 : 5 }}" class="right">Subtotal</td><td class="right">Rs. {{ number_format((float) $sale->subtotal, 2) }}</td></tr>
            @if ($isGst)
                <tr><td colspan="9" class="right">GST Amount</td><td class="right">Rs. {{ number_format((float) $sale->gst_amount, 2) }}</td></tr>
            @endif
            <tr class="total-row"><td colspan="{{ $isGst ? 9 : 5 }}" class="right">Total Amount</td><td class="right">Rs. {{ number_format((float) $sale->total_amount, 2) }}</td></tr>
            <tr><td colspan="{{ $isGst ? 9 : 5 }}" class="right">Paid Amount</td><td class="right">Rs. {{ number_format((float) $sale->paid_amount, 2) }}</td></tr>
            <tr><td colspan="{{ $isGst ? 9 : 5 }}" class="right">Balance Amount</td><td class="right">Rs. {{ number_format((float) $sale->balance_amount, 2) }}</td></tr>
        </tfoot>
    </table>

    <div class="words"><span class="label">Amount in words:</span> {{ $amountWords }}</div>
    @if ($sale->notes)
        <div class="words"><span class="label">Notes:</span> {{ $sale->notes }}</div>
    @endif

    <table class="footer-grid">
        <tr>
            <td>@include('pdf.partials.terms')</td>
            <td>@include('pdf.partials.signature')</td>
        </tr>
    </table>
@endsection
