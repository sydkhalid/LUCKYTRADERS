@php
    $isGst = $sale->bill_type === 'gst';
    $documentTitle = $title ?? ($isGst ? 'GST Invoice' : 'Normal Bill');
    $documentNoLabel = $isGst ? 'GST Invoice No' : 'Normal Bill No';
    $companyName = $company['name'] ?? 'LUCKY TRADERS';
    $companyAddress = $company['address'] ?? '2/164/14 Line Kollai, Venkatapuram, Krishnagiri, Tamil Nadu, India - 635002';
    $companyPhone = $company['phone'] ?? '+91 7418287561';
    $companyGst = $company['gst_number'] ?? null;
    $taxableValue = (float) $sale->subtotal;
    $gstAmount = $isGst ? (float) $sale->gst_amount : 0;
    $cgstAmount = round($gstAmount / 2, 2);
    $sgstAmount = round($gstAmount - $cgstAmount, 2);
    $roundOff = round((float) $sale->total_amount - ($taxableValue + $gstAmount), 2);
    $money = static fn ($value) => 'Rs. '.number_format((float) $value, 2);
    $qty = static fn ($value) => number_format((float) $value, 3);
    $gstRate = $sale->items->max(fn ($item) => (float) $item->gst_percentage);
    $bankText = trim((string) ($bankDetails ?? '')) ?: implode("\n", [
        'Bank Name: UNION BANK',
        'Account No: 558701010230709',
        'Branch: Krishnagiri',
        'IFSC Code: UBIN0555878',
    ]);
    $logoSrc = null;

    if (($isPdf ?? false) && ! empty($company['logo_path'])) {
        $logoSrc = $company['logo_path'];
    } elseif (! empty($company['logo'])) {
        $logoSrc = \Illuminate\Support\Facades\Storage::disk('public')->url($company['logo']);
    }

    $signatureSrc = $signatureImagePath ?? null;
    if (! ($isPdf ?? false) && $signatureSrc) {
        $publicRoot = storage_path('app/public').DIRECTORY_SEPARATOR;
        if (str_starts_with($signatureSrc, $publicRoot)) {
            $relativePath = str_replace('\\', '/', substr($signatureSrc, strlen($publicRoot)));
            $signatureSrc = asset('storage/'.$relativePath);
        }
    }
@endphp

<div class="invoice-page">
    <table class="invoice-header">
        <tr>
            <td class="invoice-brand">
                @if ($logoSrc)
                    <img src="{{ $logoSrc }}" class="invoice-logo" alt="{{ $companyName }} Logo">
                @else
                    <span class="invoice-logo-mark">LT</span>
                @endif
                <div class="invoice-company-name">{{ $companyName }}</div>
            </td>
            <td class="invoice-meta">
                <h2>{{ $documentTitle }}</h2>
                <p><strong>{{ $documentNoLabel }}:</strong> {{ $sale->sale_no }}</p>
                <p><strong>Invoice Date:</strong> {{ $sale->sale_date?->format('d-m-Y') }}</p>
                <p><strong>Payment:</strong> {{ strtoupper($sale->payment_mode) }} / {{ ucfirst($sale->payment_status) }}</p>
            </td>
        </tr>
    </table>

    <table class="invoice-info">
        <tr>
            <td class="left">
                <h3 class="invoice-section-title">From:</h3>
                <div class="invoice-party">
                    <strong>{{ $companyName }}</strong><br>
                    {{ $companyAddress }}<br>
                    @if ($isGst && $companyGst)
                        <strong>GSTIN:</strong> {{ $companyGst }}<br>
                    @endif
                    @if ($companyPhone)
                        <strong>Phone:</strong> {{ $companyPhone }}
                    @endif
                </div>
            </td>
            <td class="right">
                <h3 class="invoice-section-title">To:</h3>
                <div class="invoice-party">
                    <strong>{{ $sale->customer?->name ?: '-' }}</strong><br>
                    {{ $sale->customer?->address ?: '-' }}<br>
                    @if ($isGst)
                        <strong>Customer GSTIN:</strong> {{ $sale->customer?->gst_number ?: '-' }}<br>
                    @endif
                    <strong>Phone:</strong> {{ $sale->customer?->phone ?: '-' }}
                </div>
            </td>
        </tr>
    </table>

    <table class="invoice-items">
        <thead>
            <tr>
                <th class="text-center">#</th>
                <th>Product</th>
                @if ($isGst)
                    <th>HSN / Code</th>
                @endif
                <th class="text-right">Qty (Kg)</th>
                <th class="text-right">Rate</th>
                @if ($isGst)
                    <th class="text-right">GST %</th>
                @endif
                <th class="text-right">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($sale->items as $item)
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td>{{ $item->product?->name ?: '-' }}</td>
                    @if ($isGst)
                        <td>{{ $item->product?->hsn_code ?: $item->product?->code ?: '-' }}</td>
                    @endif
                    <td class="text-right">{{ $qty($item->quantity) }} {{ $item->unit }}</td>
                    <td class="text-right">{{ $money($item->rate) }}</td>
                    @if ($isGst)
                        <td class="text-right">{{ number_format((float) $item->gst_percentage, 2) }}%</td>
                    @endif
                    <td class="amount-cell">{{ $money($isGst ? $item->subtotal : $item->total) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="invoice-total-summary">
        <tr>
            <td>{{ $isGst ? 'Taxable Value:' : 'Subtotal:' }}</td>
            <td>{{ $money($taxableValue) }}</td>
        </tr>
        @if ($isGst)
            <tr>
                <td>CGST ({{ number_format($gstRate / 2, 2) }}%):</td>
                <td>{{ $money($cgstAmount) }}</td>
            </tr>
            <tr>
                <td>SGST ({{ number_format($gstRate / 2, 2) }}%):</td>
                <td>{{ $money($sgstAmount) }}</td>
            </tr>
            <tr>
                <td>GST Amount:</td>
                <td>{{ $money($gstAmount) }}</td>
            </tr>
        @endif
        <tr>
            <td>Round Off:</td>
            <td>{{ $money($roundOff === -0.0 ? 0 : $roundOff) }}</td>
        </tr>
        <tr class="invoice-total-highlight">
            <td>Grand Total:</td>
            <td>{{ $money($sale->total_amount) }}</td>
        </tr>
        <tr>
            <td>Paid Amount:</td>
            <td>{{ $money($sale->paid_amount) }}</td>
        </tr>
        <tr>
            <td>Balance Amount:</td>
            <td>{{ $money($sale->balance_amount) }}</td>
        </tr>
    </table>

    <div class="amount-words">Amount in Words: {{ $amountWords }}</div>

    @if ($sale->notes)
        <div class="invoice-note"><strong>Notes:</strong> {{ $sale->notes }}</div>
    @endif

    <table class="invoice-summary-section">
        <tr>
            <td class="invoice-bank">
                <strong>BANK DETAILS</strong><br><br>
                {!! nl2br(e($bankText)) !!}
                @if (! empty($termsAndConditions))
                    <div class="invoice-footer-note">
                        <strong>Terms:</strong><br>
                        {!! nl2br(e($termsAndConditions)) !!}
                    </div>
                @endif
            </td>
            <td class="invoice-signature">
                <p><strong>For {{ $companyName }}</strong></p>
                @if ($signatureSrc)
                    <img src="{{ $signatureSrc }}" alt="{{ $companyName }} Signature">
                @else
                    <div class="invoice-signature-space"></div>
                @endif
                <p class="invoice-signature-line">Authorized Signatory</p>
            </td>
        </tr>
    </table>
</div>
