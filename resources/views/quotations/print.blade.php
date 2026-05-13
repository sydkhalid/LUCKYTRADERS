<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $quotation->quotation_no }} - Quotation</title>
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
        <a href="{{ route('quotations.show', $quotation) }}" class="rounded border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700">Back</a>
    </div>

    <main class="mx-auto max-w-5xl bg-white p-8 shadow print:shadow-none">
        <header class="border-b-2 border-slate-900 pb-5">
            <div class="flex items-start justify-between gap-6">
                <div>
                    <h1 class="text-3xl font-bold tracking-wide text-slate-900">LUCKY TRADERS</h1>
                    <p class="mt-2 text-sm text-gray-700">2/164/14 Line Kollai, Venkatapuram, Krishnagiri, Tamil Nadu, India</p>
                </div>
                <div class="text-right">
                    <h2 class="text-xl font-bold text-slate-900">QUOTATION</h2>
                    <p class="mt-2 text-sm text-gray-700">Quotation No: <span class="font-semibold">{{ $quotation->quotation_no }}</span></p>
                    <p class="text-sm text-gray-700">Date: <span class="font-semibold">{{ $quotation->quotation_date?->format('d M Y') }}</span></p>
                    <p class="text-sm text-gray-700">Valid Until: <span class="font-semibold">{{ $quotation->valid_until?->format('d M Y') ?: '-' }}</span></p>
                </div>
            </div>
        </header>

        <section class="mt-6 grid grid-cols-1 gap-5 md:grid-cols-2">
            <div class="rounded border border-gray-200 p-4">
                <p class="text-xs font-semibold uppercase text-gray-500">Quote To</p>
                <h3 class="mt-2 text-lg font-semibold text-gray-900">{{ $quotation->customer?->name }}</h3>
                <p class="mt-1 text-sm text-gray-700">{{ $quotation->customer?->address ?: '-' }}</p>
                <p class="text-sm text-gray-700">Phone: {{ $quotation->customer?->phone ?: '-' }}</p>
                <p class="text-sm text-gray-700">GST: {{ $quotation->customer?->gst_number ?: '-' }}</p>
            </div>
            <div class="rounded border border-gray-200 p-4">
                <p class="text-xs font-semibold uppercase text-gray-500">Status</p>
                <p class="mt-2 text-sm font-semibold text-gray-900">{{ $quotation->statusLabel() }}</p>
                <p class="mt-3 whitespace-pre-line text-sm text-gray-700">{{ $quotation->notes ?: '-' }}</p>
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
                        <th class="border border-gray-300 px-3 py-2 text-right">GST %</th>
                        <th class="border border-gray-300 px-3 py-2 text-right">GST</th>
                        <th class="border border-gray-300 px-3 py-2 text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($quotation->items as $item)
                        <tr>
                            <td class="border border-gray-300 px-3 py-2">{{ $item->product?->name }}</td>
                            <td class="border border-gray-300 px-3 py-2 text-right">{{ number_format((float) $item->quantity, 3) }}</td>
                            <td class="border border-gray-300 px-3 py-2">{{ $item->unit }}</td>
                            <td class="border border-gray-300 px-3 py-2 text-right">Rs. {{ number_format((float) $item->rate, 2) }}</td>
                            <td class="border border-gray-300 px-3 py-2 text-right">Rs. {{ number_format((float) $item->subtotal, 2) }}</td>
                            <td class="border border-gray-300 px-3 py-2 text-right">{{ number_format((float) $item->gst_percentage, 2) }}%</td>
                            <td class="border border-gray-300 px-3 py-2 text-right">Rs. {{ number_format((float) $item->gst_amount, 2) }}</td>
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
                    <span>Rs. {{ number_format((float) $quotation->subtotal, 2) }}</span>
                </div>
                <div class="flex justify-between">
                    <span>GST</span>
                    <span>Rs. {{ number_format((float) $quotation->gst_amount, 2) }}</span>
                </div>
                <div class="flex justify-between border-t border-gray-300 pt-2 text-lg font-bold">
                    <span>Grand Total</span>
                    <span>Rs. {{ number_format((float) $quotation->total_amount, 2) }}</span>
                </div>
            </div>
        </section>

        <footer class="mt-12 flex justify-between pt-8 text-sm text-gray-700">
            <span>Customer Signature</span>
            <span>Authorized Signature</span>
        </footer>
    </main>
</body>
</html>
