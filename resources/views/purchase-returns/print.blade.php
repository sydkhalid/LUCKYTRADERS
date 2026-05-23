<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $return->return_no }} - Debit Note</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @media print {
            .no-print {
                display: none !important;
            }

            body {
                background: white !important;
            }
        }
    </style>
</head>
<body class="bg-gray-100 text-gray-900">
    @php($isGst = $return->purchase?->bill_type === 'gst')

    <div class="no-print mx-auto my-6 flex max-w-5xl justify-end gap-2">
        <button onclick="window.print()" class="rounded bg-slate-900 px-4 py-2 text-sm font-semibold text-white">Print</button>
        <a href="{{ route('purchase-returns.show', $return) }}" class="rounded border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700">Back</a>
    </div>

    <main class="mx-auto max-w-5xl bg-white p-8 shadow print:shadow-none">
        <header class="border-b-2 border-slate-900 pb-5">
            <div class="flex items-start justify-between gap-6">
                <div>
                    @if (! empty($company['logo']))
                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($company['logo']) }}" alt="Company logo" class="mb-3 h-14 max-w-40 object-contain">
                    @endif
                    <h1 class="text-3xl font-bold tracking-wide text-slate-900">{{ $company['name'] }}</h1>
                    <p class="mt-2 text-sm text-gray-700">{{ $company['address'] }}</p>
                    @if (! empty($company['phone']) || ! empty($company['email']))
                        <p class="text-sm text-gray-700">
                            @if (! empty($company['phone']))
                                Phone: {{ $company['phone'] }}
                            @endif
                            @if (! empty($company['phone']) && ! empty($company['email']))
                                |
                            @endif
                            @if (! empty($company['email']))
                                Email: {{ $company['email'] }}
                            @endif
                        </p>
                    @endif
                    @if ($isGst && ! empty($company['gst_number']))
                        <p class="text-sm text-gray-700">GSTIN: {{ $company['gst_number'] }}</p>
                    @endif
                </div>
                <div class="text-right">
                    <h2 class="text-xl font-bold text-slate-900">DEBIT NOTE</h2>
                    <p class="mt-2 text-sm text-gray-700">Debit Note No: <span class="font-semibold">{{ $return->return_no }}</span></p>
                    <p class="text-sm text-gray-700">Date: <span class="font-semibold">{{ $return->return_date?->format('d M Y') }}</span></p>
                    <p class="text-sm text-gray-700">Purchase No: <span class="font-semibold">{{ $return->purchase?->purchase_no }}</span></p>
                    <p class="text-sm text-gray-700">Supplier Invoice: <span class="font-semibold">{{ $return->purchase?->supplier_invoice_no ?: '-' }}</span></p>
                </div>
            </div>
        </header>

        <section class="mt-6 grid grid-cols-1 gap-5 md:grid-cols-2">
            <div class="rounded border border-gray-200 p-4">
                <p class="text-xs font-semibold uppercase text-gray-500">Supplier</p>
                <h3 class="mt-2 text-lg font-semibold text-gray-900">{{ $return->supplier?->name }}</h3>
                <p class="mt-1 text-sm text-gray-700">{{ $return->supplier?->address ?: '-' }}</p>
                <p class="text-sm text-gray-700">Phone: {{ $return->supplier?->phone ?: '-' }}</p>
                @if ($isGst)
                    <p class="text-sm text-gray-700">GST: {{ $return->supplier?->gst_number ?: '-' }}</p>
                @endif
            </div>
            <div class="rounded border border-gray-200 p-4">
                <p class="text-xs font-semibold uppercase text-gray-500">Return Details</p>
                <p class="mt-2 text-sm text-gray-700">Bill Type: <span class="font-semibold">{{ $isGst ? 'GST Purchase' : 'Non-GST Purchase' }}</span></p>
                <p class="text-sm text-gray-700">Refund Mode: <span class="font-semibold">{{ $return->payment_mode ? strtoupper($return->payment_mode) : '-' }}</span></p>
                <p class="text-sm text-gray-700">Refund Received: <span class="font-semibold">₹ {{ number_format((float) $return->refund_amount, 2) }}</span></p>
                <p class="text-sm text-gray-700">Payable Adjusted: <span class="font-semibold">₹ {{ number_format((float) $return->adjustment_amount, 2) }}</span></p>
            </div>
        </section>

        <section class="mt-6">
            <table class="w-full border-collapse text-sm">
                <thead>
                    <tr class="bg-slate-100">
                        <th class="border border-gray-300 px-3 py-2 text-left">Product</th>
                        @if ($isGst)
                            <th class="border border-gray-300 px-3 py-2 text-left">HSN</th>
                        @endif
                        <th class="border border-gray-300 px-3 py-2 text-right">Qty</th>
                        <th class="border border-gray-300 px-3 py-2 text-left">Unit</th>
                        <th class="border border-gray-300 px-3 py-2 text-right">Rate</th>
                        <th class="border border-gray-300 px-3 py-2 text-right">Subtotal</th>
                        @if ($isGst)
                            <th class="border border-gray-300 px-3 py-2 text-right">GST %</th>
                            <th class="border border-gray-300 px-3 py-2 text-right">GST</th>
                        @endif
                        <th class="border border-gray-300 px-3 py-2 text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($return->items as $item)
                        <tr>
                            <td class="border border-gray-300 px-3 py-2">{{ $item->product?->name }}</td>
                            @if ($isGst)
                                <td class="border border-gray-300 px-3 py-2">{{ $item->product?->hsn_code ?: '-' }}</td>
                            @endif
                            <td class="border border-gray-300 px-3 py-2 text-right">{{ number_format((float) $item->quantity, 3) }}</td>
                            <td class="border border-gray-300 px-3 py-2">{{ $item->product?->unit }}</td>
                            <td class="border border-gray-300 px-3 py-2 text-right">₹ {{ number_format((float) $item->rate, 2) }}</td>
                            <td class="border border-gray-300 px-3 py-2 text-right">₹ {{ number_format((float) $item->subtotal, 2) }}</td>
                            @if ($isGst)
                                <td class="border border-gray-300 px-3 py-2 text-right">{{ number_format((float) $item->gst_percentage, 2) }}%</td>
                                <td class="border border-gray-300 px-3 py-2 text-right">₹ {{ number_format((float) $item->gst_amount, 2) }}</td>
                            @endif
                            <td class="border border-gray-300 px-3 py-2 text-right font-semibold">₹ {{ number_format((float) $item->total, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </section>

        <section class="mt-6 flex justify-end">
            <div class="w-full max-w-sm space-y-2 text-sm">
                <div class="flex justify-between">
                    <span>Subtotal</span>
                    <span>₹ {{ number_format((float) $return->subtotal, 2) }}</span>
                </div>
                @if ($isGst)
                    <div class="flex justify-between">
                        <span>GST</span>
                        <span>₹ {{ number_format((float) $return->gst_amount, 2) }}</span>
                    </div>
                @endif
                <div class="flex justify-between border-t border-gray-300 pt-2 text-lg font-bold">
                    <span>Debit Note Total</span>
                    <span>₹ {{ number_format((float) $return->total_amount, 2) }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Payable Adjusted</span>
                    <span>₹ {{ number_format((float) $return->adjustment_amount, 2) }}</span>
                </div>
                <div class="flex justify-between font-semibold">
                    <span>Refund Received</span>
                    <span>₹ {{ number_format((float) $return->refund_amount, 2) }}</span>
                </div>
            </div>
        </section>

        <section class="mt-6 rounded border border-gray-200 p-4 text-sm">
            <span class="font-semibold text-gray-900">Amount in words:</span> {{ $amountWords }}
            @if ($return->notes)
                <div class="mt-2"><span class="font-semibold text-gray-900">Notes:</span> {{ $return->notes }}</div>
            @endif
        </section>

        <footer class="mt-12 grid grid-cols-1 gap-6 border-t border-gray-200 pt-6 text-sm text-gray-700 md:grid-cols-2">
            <div>
                <p class="font-semibold text-gray-900">Terms and Conditions</p>
                <div class="mt-2 whitespace-pre-line text-xs">{{ $termsAndConditions }}</div>
            </div>
            <div class="flex flex-col items-end justify-end">
                @if (! empty($signatureImagePath))
                    <img src="{{ $signatureImagePath }}" alt="Signature" class="mb-2 h-14 max-w-48 object-contain">
                @endif
                <span class="border-t border-gray-900 px-10 pt-2">Authorized Signature</span>
            </div>
        </footer>
    </main>
</body>
</html>
