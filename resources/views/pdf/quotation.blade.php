@extends('pdf.layout')

@section('content')
    @php($hasGst = (float) $quotation->gst_amount > 0)
    @include('pdf.partials.header', [
        'documentNo' => $quotation->quotation_no,
        'documentNoLabel' => 'Quotation No',
        'documentDate' => $quotation->quotation_date?->format('d M Y'),
        'documentDateLabel' => 'Quotation Date',
    ])

    <table class="grid-2 section">
        <tr>
            <td style="padding-right: 8px;">
                <div class="section-title">Customer</div>
                <div class="box">
                    <p class="bold">{{ $quotation->customer?->name ?: '-' }}</p>
                    <p>{{ $quotation->customer?->address ?: '-' }}</p>
                    <p>Phone: {{ $quotation->customer?->phone ?: '-' }}</p>
                    @if ($hasGst)
                        <p>Customer GSTIN: {{ $quotation->customer?->gst_number ?: '-' }}</p>
                    @endif
                </div>
            </td>
            <td style="padding-left: 8px;">
                <div class="section-title">Quotation Details</div>
                <div class="box">
                    <table class="meta">
                        <tr><td class="label">Status</td><td class="right">{{ $quotation->statusLabel() }}</td></tr>
                        <tr><td class="label">Valid Until</td><td class="right">{{ $quotation->valid_until?->format('d M Y') ?: '-' }}</td></tr>
                        <tr><td class="label">GST Calculation</td><td class="right">{{ $hasGst ? 'Included' : 'Not Applied' }}</td></tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>

    <table class="items section">
        <thead>
            @if ($hasGst)
                <tr>
                    <th>#</th><th>Product</th><th>HSN</th><th class="right">Qty</th><th>Unit</th><th class="right">Rate</th><th class="right">Taxable</th><th class="right">GST %</th><th class="right">GST</th><th class="right">Total</th>
                </tr>
            @else
                <tr>
                    <th>#</th><th>Product</th><th class="right">Qty</th><th>Unit</th><th class="right">Rate</th><th class="right">Total</th>
                </tr>
            @endif
        </thead>
        <tbody>
            @foreach ($quotation->items as $item)
                @if ($hasGst)
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
            <tr><td colspan="{{ $hasGst ? 9 : 5 }}" class="right">Subtotal</td><td class="right">₹ {{ number_format((float) $quotation->subtotal, 2) }}</td></tr>
            @if ($hasGst)
                <tr><td colspan="9" class="right">GST Amount</td><td class="right">₹ {{ number_format((float) $quotation->gst_amount, 2) }}</td></tr>
            @endif
            <tr class="total-row"><td colspan="{{ $hasGst ? 9 : 5 }}" class="right">Quotation Total</td><td class="right">₹ {{ number_format((float) $quotation->total_amount, 2) }}</td></tr>
        </tfoot>
    </table>

    <div class="words"><span class="label">Amount in words:</span> {{ $amountWords }}</div>
    @if ($quotation->notes)
        <div class="words"><span class="label">Notes:</span> {{ $quotation->notes }}</div>
    @endif

    <table class="footer-grid">
        <tr>
            <td>@include('pdf.partials.terms')</td>
            <td>@include('pdf.partials.signature')</td>
        </tr>
    </table>
@endsection
