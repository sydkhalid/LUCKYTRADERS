@extends('pdf.layout')

@section('content')
    @include('pdf.partials.header', [
        'documentNo' => 'PTR-'.$transaction->id,
        'documentDate' => $transaction->transaction_date?->format('d M Y'),
    ])

    <table class="grid-2 section">
        <tr>
            <td style="padding-right: 8px;">
                <div class="section-title">Partner</div>
                <div class="box">
                    <p class="bold">{{ $partner->name }}</p>
                    <p>Phone: {{ $partner->phone ?: '-' }}</p>
                    <p>Email: {{ $partner->email ?: '-' }}</p>
                    <p>Share: {{ number_format((float) $partner->share_percentage, 2) }}%</p>
                </div>
            </td>
            <td style="padding-left: 8px;">
                <div class="section-title">Transaction Details</div>
                <div class="box">
                    <table class="meta">
                        <tr><td class="label">Type</td><td class="right">{{ $transaction->typeLabel() }}</td></tr>
                        <tr><td class="label">Payment Mode</td><td class="right">{{ strtoupper($transaction->payment_mode) }}</td></tr>
                        <tr><td class="label">Amount</td><td class="right bold">Rs. {{ number_format((float) $transaction->amount, 2) }}</td></tr>
                        <tr><td class="label">Current Capital</td><td class="right">Rs. {{ number_format((float) $partner->current_investment, 2) }}</td></tr>
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
