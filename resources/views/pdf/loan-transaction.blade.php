@extends('pdf.layout')

@section('content')
    @include('pdf.partials.header', [
        'documentNo' => $loan->loan_no.' / TXN-'.$transaction->id,
        'documentNoLabel' => 'Voucher No',
        'documentDate' => $transaction->transaction_date?->format('d M Y'),
        'documentDateLabel' => 'Transaction Date',
    ])

    <table class="grid-2 section">
        <tr>
            <td style="padding-right: 8px;">
                <div class="section-title">Loan Party</div>
                <div class="box">
                    <p class="bold">{{ $loan->party_name }}</p>
                    <p>Phone: {{ $loan->party_phone ?: '-' }}</p>
                    <p>Loan Type: {{ $loan->typeLabel() }}</p>
                    <p>Status: {{ ucfirst($loan->status) }}</p>
                </div>
            </td>
            <td style="padding-left: 8px;">
                <div class="section-title">Transaction Details</div>
                <div class="box">
                    <table class="meta">
                        <tr><td class="label">Transaction Type</td><td class="right">{{ $transaction->typeLabel() }}</td></tr>
                        <tr><td class="label">Payment Mode</td><td class="right">{{ strtoupper($transaction->payment_mode) }}</td></tr>
                        <tr><td class="label">Amount</td><td class="right bold">₹ {{ number_format((float) $transaction->amount, 2) }}</td></tr>
                        <tr><td class="label">Loan Balance</td><td class="right">₹ {{ number_format((float) $loan->balance_amount, 2) }}</td></tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>

    <div class="words"><span class="label">Amount in words:</span> {{ $amountWords }}</div>
    @if ($transaction->notes)
        <div class="words"><span class="label">Notes:</span> {{ $transaction->notes }}</div>
    @endif

    <table class="footer-grid">
        <tr>
            <td>@include('pdf.partials.terms')</td>
            <td>@include('pdf.partials.signature')</td>
        </tr>
    </table>
@endsection
