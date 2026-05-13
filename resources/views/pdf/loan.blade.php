@extends('pdf.layout')

@section('content')
    @include('pdf.partials.header', [
        'documentNo' => $loan->loan_no,
        'documentDate' => $loan->loan_date?->format('d M Y'),
    ])

    <table class="grid-2 section">
        <tr>
            <td style="padding-right: 8px;">
                <div class="section-title">Loan Party</div>
                <div class="box">
                    <p class="bold">{{ $loan->party_name }}</p>
                    <p>Phone: {{ $loan->party_phone ?: '-' }}</p>
                    <p>Type: {{ $loan->typeLabel() }}</p>
                    <p>Status: {{ ucfirst($loan->status) }}</p>
                </div>
            </td>
            <td style="padding-left: 8px;">
                <div class="section-title">Loan Amounts</div>
                <div class="box">
                    <table class="meta">
                        <tr><td class="label">Principal</td><td class="right">Rs. {{ number_format((float) $loan->principal_amount, 2) }}</td></tr>
                        <tr><td class="label">Interest</td><td class="right">Rs. {{ number_format((float) $loan->total_interest, 2) }}</td></tr>
                        <tr><td class="label">Total</td><td class="right">Rs. {{ number_format((float) $loan->total_amount, 2) }}</td></tr>
                        <tr><td class="label">Paid / Returned</td><td class="right">Rs. {{ number_format((float) $loan->paid_amount, 2) }}</td></tr>
                        <tr><td class="label">Balance</td><td class="right bold">Rs. {{ number_format((float) $loan->balance_amount, 2) }}</td></tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>

    <table class="items section">
        <thead>
            <tr><th>Date</th><th>Type</th><th>Mode</th><th class="right">Amount</th><th>Notes</th></tr>
        </thead>
        <tbody>
            @foreach ($loan->transactions as $transaction)
                <tr>
                    <td>{{ $transaction->transaction_date?->format('d M Y') }}</td>
                    <td>{{ $transaction->typeLabel() }}</td>
                    <td>{{ strtoupper($transaction->payment_mode) }}</td>
                    <td class="right bold">Rs. {{ number_format((float) $transaction->amount, 2) }}</td>
                    <td>{{ $transaction->notes ?: '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="words"><span class="label">Total amount in words:</span> {{ $amountWords }}</div>
    @if ($loan->notes)
        <div class="words"><span class="label">Notes:</span> {{ $loan->notes }}</div>
    @endif

    <table class="footer-grid">
        <tr>
            <td>@include('pdf.partials.terms')</td>
            <td>@include('pdf.partials.signature')</td>
        </tr>
    </table>
@endsection
