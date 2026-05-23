@php
    $isGst = $sale->bill_type === 'gst';
    $companyName = $company['name'] ?? 'LUCKY TRADERS';
    $companyAddress = $company['address'] ?? '2/164/14 Line Kollai, Venkatapuram, Krishnagiri, Tamil Nadu, India - 635002';
    $companyPhone = $company['phone'] ?? '+91 7418287561';
    $companyGst = $company['gst_number'] ?? null;
    $taxableValue = (float) $sale->subtotal;
    $gstAmount = $isGst ? (float) $sale->gst_amount : 0;
    $roundOff = round((float) $sale->total_amount - ($taxableValue + $gstAmount), 2);
    $currency = "\u{20B9}";
    $money = static fn ($value) => $currency.' '.number_format((float) $value, 2);
    $qty = static fn ($value) => number_format((float) $value, 2);
    $rate = static fn ($value) => rtrim(rtrim(number_format((float) $value, 2), '0'), '.');
    $taxSummary = [];
    $addTax = static function (string $label, float $amount) use (&$taxSummary): void {
        $taxSummary[$label] = round(($taxSummary[$label] ?? 0) + $amount, 2);
    };

    if ($isGst) {
        foreach ($sale->items as $item) {
            $itemGstAmount = round((float) $item->gst_amount, 2);
            $itemGstRate = (float) $item->gst_percentage;

            if ($itemGstAmount <= 0 || $itemGstRate <= 0) {
                continue;
            }

            if (($item->gst_type ?? 'cgst_sgst') === 'igst') {
                $addTax('IGST ('.$rate($itemGstRate).'%)', $itemGstAmount);
                continue;
            }

            $cgstAmount = round($itemGstAmount / 2, 2);
            $sgstAmount = round($itemGstAmount - $cgstAmount, 2);
            $halfRate = $rate($itemGstRate / 2);
            $addTax('CGST ('.$halfRate.'%)', $cgstAmount);
            $addTax('SGST ('.$halfRate.'%)', $sgstAmount);
        }
    }

    $hasEway = filled($sale->eway_bill_no)
        || filled($sale->eway_date)
        || filled($sale->eway_driver_name)
        || filled($sale->eway_mobile_no)
        || filled($sale->eway_vehicle_no)
        || filled($sale->eway_valid_upto);

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
                <p><strong>Invoice No:</strong> #{{ $sale->sale_no }}</p>
                <p><strong>Invoice Date:</strong> {{ $sale->sale_date?->format('d-m-Y') }}</p>
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
                        <strong>GSTIN:</strong> {{ $sale->customer?->gst_number ?: '-' }}<br>
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
                    <th>Tax %</th>
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
                    <td class="text-right">{{ $qty($item->quantity) }}</td>
                    <td class="text-right">{{ $rate($item->rate) }}</td>
                    @if ($isGst)
                        <td>{{ $rate($item->gst_percentage) }}%{{ ($item->gst_calculation ?? 'exclusive') === 'inclusive' ? ' (Included)' : '' }}</td>
                    @endif
                    <td class="amount-cell">{{ $money($item->total) }}</td>
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
            @foreach ($taxSummary as $label => $amount)
                <tr>
                    <td>{{ $label }}:</td>
                    <td>{{ $money($amount) }}</td>
                </tr>
            @endforeach
            <tr>
                <td>Total GST:</td>
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
    </table>

    <div class="amount-words">Amount in Words: {{ $amountWords }}</div>

    @if ($hasEway)
        <table class="invoice-eway">
            <tr>
                <td><strong>E-Way Bill No:</strong></td>
                <td>{{ $sale->eway_bill_no ?: '-' }}</td>
                <td><strong>Date:</strong></td>
                <td>{{ $sale->eway_date?->format('d-m-Y') ?: '-' }}</td>
            </tr>
            <tr>
                <td><strong>Driver Name:</strong></td>
                <td>{{ $sale->eway_driver_name ?: '-' }}</td>
                <td><strong>Vehicle No:</strong></td>
                <td>{{ $sale->eway_vehicle_no ?: '-' }}</td>
            </tr>
            <tr>
                <td><strong>Mobile No:</strong></td>
                <td>{{ $sale->eway_mobile_no ?: '-' }}</td>
                <td><strong>Valid Upto:</strong></td>
                <td>{{ $sale->eway_valid_upto?->format('d-m-Y') ?: '-' }}</td>
            </tr>
        </table>
    @endif

    @if ($sale->notes)
        <div class="invoice-note"><strong>Notes:</strong> {{ $sale->notes }}</div>
    @endif

    <table class="invoice-summary-section">
        <tr>
            <td class="invoice-bank">
                <span class="invoice-bank-title">BANK DETAILS</span>
                {!! nl2br(e($bankText)) !!}
            </td>
            <td class="invoice-signature">
                <span class="invoice-signature-title">For {{ $companyName }}</span>
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
