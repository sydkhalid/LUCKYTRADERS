<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $sale->sale_no }} - {{ $sale->bill_type === 'gst' ? 'GST Invoice' : 'Normal Bill' }}</title>
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
    <div class="no-print mx-auto my-6 flex max-w-5xl justify-end gap-2">
        <button onclick="window.print()" class="rounded bg-slate-900 px-4 py-2 text-sm font-semibold text-white">Print</button>
        <a href="{{ route('sales.show', $sale) }}" class="rounded border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700">Back</a>
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
                    @if (! empty($company['gst_number']))
                        <p class="text-sm text-gray-700">GSTIN: {{ $company['gst_number'] }}</p>
                    @endif
                </div>
                <div class="text-right">
                    <h2 class="text-xl font-bold text-slate-900">{{ $sale->bill_type === 'gst' ? 'GST INVOICE' : 'NORMAL BILL' }}</h2>
                    <p class="mt-2 text-sm text-gray-700">Invoice No: <span class="font-semibold">{{ $sale->sale_no }}</span></p>
                    <p class="text-sm text-gray-700">Date: <span class="font-semibold">{{ $sale->sale_date?->format('d M Y') }}</span></p>
                </div>
            </div>
        </header>

        <section class="mt-6 grid grid-cols-1 gap-5 md:grid-cols-2">
            <div class="rounded border border-gray-200 p-4">
                <p class="text-xs font-semibold uppercase text-gray-500">Bill To</p>
                <h3 class="mt-2 text-lg font-semibold text-gray-900">{{ $sale->customer?->name }}</h3>
                <p class="mt-1 text-sm text-gray-700">{{ $sale->customer?->address ?: '-' }}</p>
                <p class="text-sm text-gray-700">Phone: {{ $sale->customer?->phone ?: '-' }}</p>
                <p class="text-sm text-gray-700">GST: {{ $sale->customer?->gst_number ?: '-' }}</p>
            </div>
            <div class="rounded border border-gray-200 p-4">
                <p class="text-xs font-semibold uppercase text-gray-500">Payment</p>
                <p class="mt-2 text-sm text-gray-700">Mode: <span class="font-semibold">{{ strtoupper($sale->payment_mode) }}</span></p>
                <p class="text-sm text-gray-700">Status: <span class="font-semibold">{{ ucfirst($sale->payment_status) }}</span></p>
            </div>
        </section>

        <section class="mt-6">
            <table class="w-full border-collapse text-sm">
                <thead>
                    <tr class="bg-slate-100">
                        <th class="border border-gray-300 px-3 py-2 text-left">Product</th>
                        <th class="border border-gray-300 px-3 py-2 text-right">Qty</th>
                        <th class="border border-gray-300 px-3 py-2 text-left">Unit</th>
                        <th class="border border-gray-300 px-3 py-2 text-right">Rate</th>
                        <th class="border border-gray-300 px-3 py-2 text-right">Subtotal</th>
                        @if ($sale->bill_type === 'gst')
                            <th class="border border-gray-300 px-3 py-2 text-right">GST %</th>
                            <th class="border border-gray-300 px-3 py-2 text-right">GST</th>
                        @endif
                        <th class="border border-gray-300 px-3 py-2 text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($sale->items as $item)
                        <tr>
                            <td class="border border-gray-300 px-3 py-2">{{ $item->product?->name }}</td>
                            <td class="border border-gray-300 px-3 py-2 text-right">{{ number_format((float) $item->quantity, 3) }}</td>
                            <td class="border border-gray-300 px-3 py-2">{{ $item->unit }}</td>
                            <td class="border border-gray-300 px-3 py-2 text-right">Rs. {{ number_format((float) $item->rate, 2) }}</td>
                            <td class="border border-gray-300 px-3 py-2 text-right">Rs. {{ number_format((float) $item->subtotal, 2) }}</td>
                            @if ($sale->bill_type === 'gst')
                                <td class="border border-gray-300 px-3 py-2 text-right">{{ number_format((float) $item->gst_percentage, 2) }}%</td>
                                <td class="border border-gray-300 px-3 py-2 text-right">Rs. {{ number_format((float) $item->gst_amount, 2) }}</td>
                            @endif
                            <td class="border border-gray-300 px-3 py-2 text-right font-semibold">Rs. {{ number_format((float) $item->total, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </section>

        <section class="mt-6 flex justify-end">
            <div class="w-full max-w-sm space-y-2 text-sm">
                <div class="flex justify-between">
                    <span>Subtotal</span>
                    <span>Rs. {{ number_format((float) $sale->subtotal, 2) }}</span>
                </div>
                <div class="flex justify-between">
                    <span>GST</span>
                    <span>Rs. {{ number_format((float) $sale->gst_amount, 2) }}</span>
                </div>
                <div class="flex justify-between border-t border-gray-300 pt-2 text-lg font-bold">
                    <span>Grand Total</span>
                    <span>Rs. {{ number_format((float) $sale->total_amount, 2) }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Paid</span>
                    <span>Rs. {{ number_format((float) $sale->paid_amount, 2) }}</span>
                </div>
                <div class="flex justify-between font-semibold">
                    <span>Balance</span>
                    <span>Rs. {{ number_format((float) $sale->balance_amount, 2) }}</span>
                </div>
            </div>
        </section>

        <footer class="mt-12 grid grid-cols-1 gap-6 border-t border-gray-200 pt-6 text-sm text-gray-700 md:grid-cols-2">
            <div>
                <p class="font-semibold text-gray-900">Terms and Conditions</p>
                <div class="mt-2 whitespace-pre-line text-xs">{{ $termsAndConditions }}</div>
                @if (! empty($bankDetails))
                    <p class="mt-4 font-semibold text-gray-900">Bank Details</p>
                    <div class="mt-2 whitespace-pre-line text-xs">{{ $bankDetails }}</div>
                @endif
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
