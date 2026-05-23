@extends('pdf.layout')

@section('content')
    @include('pdf.partials.header', [
        'documentNo' => $expense->expense_no,
        'documentNoLabel' => 'Voucher No',
        'documentDate' => $expense->expense_date?->format('d M Y'),
        'documentDateLabel' => 'Expense Date',
    ])

    <table class="grid-2 section">
        <tr>
            <td style="padding-right: 8px;">
                <div class="section-title">Expense Details</div>
                <div class="box">
                    <table class="meta">
                        <tr><td class="label">Category</td><td class="right">{{ $expense->category?->name ?: '-' }}</td></tr>
                        <tr><td class="label">Paid To</td><td class="right">{{ $expense->paid_to ?: '-' }}</td></tr>
                        <tr><td class="label">Payment Mode</td><td class="right">{{ strtoupper($expense->payment_mode) }}</td></tr>
                    </table>
                </div>
            </td>
            <td style="padding-left: 8px;">
                <div class="section-title">Amount</div>
                <div class="box">
                    <table class="meta">
                        <tr><td class="label">Expense Amount</td><td class="right bold">₹ {{ number_format((float) $expense->amount, 2) }}</td></tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>

    <table class="items section">
        <thead>
            <tr><th>Description</th><th class="right">Amount</th></tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $expense->notes ?: ($expense->category?->name ?: 'Business Expense') }}</td>
                <td class="right bold">₹ {{ number_format((float) $expense->amount, 2) }}</td>
            </tr>
        </tbody>
        <tfoot>
            <tr class="total-row"><td class="right">Total</td><td class="right">₹ {{ number_format((float) $expense->amount, 2) }}</td></tr>
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
