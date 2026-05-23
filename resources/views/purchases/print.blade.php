@php
    $company = app(\App\Services\SystemSettingService::class)->company();
    $isGst = $purchase->bill_type === 'gst';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Print - {{ $purchase->purchase_no }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @media print {
            .no-print {
                display: none !important;
            }

            body {
                background: #fff !important;
            }

            .print-sheet {
                box-shadow: none !important;
                margin: 0 !important;
                max-width: none !important;
            }
        }
    </style>
</head>
<body class="bg-gray-100 text-gray-900">
    <div class="no-print mx-auto flex max-w-5xl justify-end gap-3 px-4 py-4">
        <button type="button" onclick="window.print()" class="rounded bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">Print</button>
        <a href="{{ route('purchases.show', $purchase) }}" class="rounded border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Back</a>
    </div>

    <main class="print-sheet mx-auto mb-8 max-w-5xl bg-white p-8 shadow">
        <header class="flex items-start justify-between border-b border-gray-300 pb-5">
            <div>
                <h1 class="text-2xl font-bold tracking-wide text-gray-950">{{ $company['name'] ?? 'LUCKY TRADERS' }}</h1>
                <p class="mt-1 max-w-xl text-sm leading-6 text-gray-700">{{ $company['address'] ?? '2/164/14 Line Kollai, Venkatapuram, Krishnagiri, Tamil Nadu, India' }}</p>
                @if (! empty($company['phone']))
                    <p class="text-sm text-gray-700">Phone: {{ $company['phone'] }}</p>
                @endif
                @if ($isGst && ! empty($company['gst_number']))
                    <p class="text-sm font-semibold text-gray-800">GSTIN: {{ $company['gst_number'] }}</p>
                @endif
            </div>
            <div class="text-right">
                <p class="text-sm font-semibold uppercase text-gray-500">{{ $isGst ? 'GST Purchase' : 'Non-GST Purchase' }}</p>
                <h2 class="mt-1 text-xl font-bold text-gray-950">{{ $purchase->purchase_no }}</h2>
                <p class="mt-1 text-sm text-gray-700">{{ $purchase->purchase_date?->format('d M Y') }}</p>
            </div>
        </header>

        <section class="mt-6 grid gap-5 md:grid-cols-2">
            <div class="rounded border border-gray-200 p-4">
                <p class="text-xs font-semibold uppercase text-gray-500">Supplier</p>
                <h3 class="mt-2 font-semibold text-gray-950">{{ $purchase->supplier?->name ?: '-' }}</h3>
                <p class="mt-1 whitespace-pre-line text-sm text-gray-700">{{ $purchase->supplier?->address ?: '-' }}</p>
                <p class="mt-1 text-sm text-gray-700">Phone: {{ $purchase->supplier?->phone ?: '-' }}</p>
                @if ($isGst)
                    <p class="text-sm text-gray-700">GSTIN: {{ $purchase->supplier?->gst_number ?: '-' }}</p>
                @endif
            </div>
            <div class="rounded border border-gray-200 p-4">
                <p class="text-xs font-semibold uppercase text-gray-500">Invoice Details</p>
                <dl class="mt-2 space-y-2 text-sm">
                    <div class="flex justify-between gap-4">
                        <dt class="text-gray-600">Supplier Invoice</dt>
                        <dd class="font-medium text-gray-950">{{ $purchase->supplier_invoice_no ?: '-' }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-gray-600">Payment Mode</dt>
                        <dd class="font-medium text-gray-950">{{ strtoupper($purchase->payment_mode) }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-gray-600">Payment Status</dt>
                        <dd class="font-medium text-gray-950">{{ ucfirst($purchase->payment_status) }}</dd>
                    </div>
                </dl>
            </div>
        </section>

        <section class="mt-6 overflow-hidden rounded border border-gray-200">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-slate-100 text-left text-xs font-semibold uppercase text-gray-600">
                    <tr>
                        <th class="px-3 py-3">#</th>
                        <th class="px-3 py-3">Product</th>
                        @if ($isGst)
                            <th class="px-3 py-3">HSN</th>
                        @endif
                        <th class="px-3 py-3 text-right">Qty</th>
                        <th class="px-3 py-3">Unit</th>
                        <th class="px-3 py-3 text-right">Rate</th>
                        <th class="px-3 py-3 text-right">{{ $isGst ? 'Taxable' : 'Amount' }}</th>
                        @if ($isGst)
                            <th class="px-3 py-3 text-right">GST %</th>
                            <th class="px-3 py-3 text-right">GST</th>
                        @endif
                        <th class="px-3 py-3 text-right">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($purchase->items as $item)
                        <tr>
                            <td class="px-3 py-3">{{ $loop->iteration }}</td>
                            <td class="px-3 py-3 font-medium text-gray-950">{{ $item->product?->name ?: '-' }}</td>
                            @if ($isGst)
                                <td class="px-3 py-3 text-gray-700">{{ $item->product?->hsn_code ?: '-' }}</td>
                            @endif
                            <td class="px-3 py-3 text-right text-gray-700">{{ number_format((float) $item->quantity, 3) }}</td>
                            <td class="px-3 py-3 text-gray-700">{{ $item->unit }}</td>
                            <td class="px-3 py-3 text-right text-gray-700">₹ {{ number_format((float) $item->rate, 2) }}</td>
                            <td class="px-3 py-3 text-right text-gray-700">₹ {{ number_format((float) $item->subtotal, 2) }}</td>
                            @if ($isGst)
                                <td class="px-3 py-3 text-right text-gray-700">{{ number_format((float) $item->gst_percentage, 2) }}</td>
                                <td class="px-3 py-3 text-right text-gray-700">₹ {{ number_format((float) $item->gst_amount, 2) }}</td>
                            @endif
                            <td class="px-3 py-3 text-right font-semibold text-gray-950">₹ {{ number_format((float) $item->total, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </section>

        <section class="mt-6 flex justify-end">
            <dl class="w-full max-w-sm space-y-2 text-sm">
                <div class="flex justify-between border-b border-gray-100 pb-2">
                    <dt class="text-gray-600">Subtotal</dt>
                    <dd class="font-semibold text-gray-950">₹ {{ number_format((float) $purchase->subtotal, 2) }}</dd>
                </div>
                @if ($isGst)
                    <div class="flex justify-between border-b border-gray-100 pb-2">
                        <dt class="text-gray-600">Input GST</dt>
                        <dd class="font-semibold text-gray-950">₹ {{ number_format((float) $purchase->gst_amount, 2) }}</dd>
                    </div>
                @endif
                <div class="flex justify-between border-b border-gray-100 pb-2 text-base">
                    <dt class="font-semibold text-gray-800">Total</dt>
                    <dd class="font-bold text-gray-950">₹ {{ number_format((float) $purchase->total_amount, 2) }}</dd>
                </div>
                <div class="flex justify-between border-b border-gray-100 pb-2">
                    <dt class="text-gray-600">Paid</dt>
                    <dd class="font-semibold text-gray-950">₹ {{ number_format((float) $purchase->paid_amount, 2) }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-600">Balance</dt>
                    <dd class="font-bold text-gray-950">₹ {{ number_format((float) $purchase->balance_amount, 2) }}</dd>
                </div>
            </dl>
        </section>

        @if ($purchase->notes)
            <section class="mt-6 rounded border border-gray-200 p-4 text-sm">
                <p class="font-semibold text-gray-700">Notes</p>
                <p class="mt-1 whitespace-pre-line text-gray-800">{{ $purchase->notes }}</p>
            </section>
        @endif

        <footer class="mt-12 flex justify-end">
            <div class="w-56 border-t border-gray-400 pt-2 text-center text-sm font-semibold text-gray-700">
                Authorized Signature
            </div>
        </footer>
    </main>
</body>
</html>
