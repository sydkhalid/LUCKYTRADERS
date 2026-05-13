<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $sale->sale_no }} - {{ $sale->bill_type === 'gst' ? 'GST Invoice' : 'Normal Bill' }}</title>
    <style>
        @include('sales.partials.invoice-style')

        body {
            padding: 0 12px 24px;
        }

        .invoice-page {
            max-width: 210mm;
        }
    </style>
</head>
<body>
    <div class="print-actions">
        <button type="button" onclick="window.print()">Print Invoice</button>
        <a href="{{ route('sales.pdf', $sale) }}" target="_blank" rel="noopener">PDF</a>
        <a href="{{ route('sales.show', $sale) }}">Back</a>
    </div>

    @include('sales.partials.invoice-document', [
        'title' => $sale->bill_type === 'gst' ? 'GST Invoice' : 'Normal Bill',
        'isPdf' => false,
    ])
</body>
</html>
