@extends('pdf.layout')

@section('content')
    @php
        $partyName = $party?->name ?? $party?->party_name ?? $party?->expense_no ?? ucfirst($payment->party_type).' #'.$payment->party_id;
        $partyAddress = $party?->address ?? null;
        $partyPhone = $party?->phone ?? $party?->party_phone ?? null;
        $referenceNo = $reference?->sale_no ?? $reference?->purchase_no ?? $reference?->loan?->loan_no ?? $reference?->partner?->name ?? $reference?->expense_no ?? null;
    @endphp

    @include('pdf.partials.header', [
        'documentNo' => $payment->payment_no,
        'documentNoLabel' => $payment->transaction_type === 'receipt' ? 'Receipt No' : 'Voucher No',
        'documentDate' => $payment->payment_date?->format('d M Y'),
        'documentDateLabel' => 'Payment Date',
    ])

    <table class="grid-2 section">
        <tr>
            <td style="padding-right: 8px;">
                <div class="section-title">{{ $payment->transaction_type === 'receipt' ? 'Received From' : 'Paid To' }}</div>
                <div class="box">
                    <p class="bold">{{ $partyName }}</p>
                    <p>{{ $partyAddress ?: '-' }}</p>
                    <p>Phone: {{ $partyPhone ?: '-' }}</p>
                    @if (($party?->gst_number ?? null))
                        <p>GSTIN: {{ $party->gst_number }}</p>
                    @endif
                </div>
            </td>
            <td style="padding-left: 8px;">
                <div class="section-title">Payment Details</div>
                <div class="box">
                    <table class="meta">
                        <tr><td class="label">Type</td><td class="right">{{ ucfirst($payment->transaction_type) }}</td></tr>
                        <tr><td class="label">Mode</td><td class="right">{{ strtoupper($payment->payment_mode) }}</td></tr>
                        <tr><td class="label">Reference</td><td class="right">{{ $payment->reference_type ? ucfirst(str_replace('_', ' ', $payment->reference_type)) : '-' }}</td></tr>
                        <tr><td class="label">Reference No</td><td class="right">{{ $referenceNo ?: ($payment->reference_id ? '#'.$payment->reference_id : '-') }}</td></tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>

    <table class="items section">
        <thead>
            <tr>
                <th>Description</th>
                <th class="right">Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $payment->notes ?: $title.' '.$payment->payment_no }}</td>
                <td class="right bold">Rs. {{ number_format((float) $payment->amount, 2) }}</td>
            </tr>
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td class="right">Total</td>
                <td class="right">Rs. {{ number_format((float) $payment->amount, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="words"><span class="label">Amount in words:</span> {{ $amountWords }}</div>

    <table class="footer-grid">
        <tr>
            <td>@include('pdf.partials.terms')</td>
            <td>@include('pdf.partials.signature')</td>
        </tr>
    </table>
@endsection
