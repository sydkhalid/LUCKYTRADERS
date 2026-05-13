@extends('layouts.app')

@section('title', 'Convert Quotation')

@section('content')
    <div class="mb-5 flex items-center justify-between">
        <div>
            <h2 class="text-lg font-semibold text-gray-900">Convert {{ $quotation->quotation_no }} to Sale</h2>
            <p class="text-sm text-gray-500">{{ $quotation->customer?->name }} - accepted quotation will become a credit sale.</p>
        </div>
        <a href="{{ route('quotations.show', $quotation) }}" class="rounded border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Back</a>
    </div>

    @if ($errors->any())
        <div class="mb-5 rounded border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('quotations.convert.store', $quotation) }}" class="rounded bg-white p-6 shadow" data-ajax-form>
        @csrf

        <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Sale Date</label>
                <input type="date" name="sale_date" value="{{ old('sale_date', now()->toDateString()) }}" class="w-full rounded border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500" required>
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Bill Type</label>
                <select name="bill_type" id="billType" class="w-full rounded border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500" required>
                    <option value="gst" @selected(old('bill_type', $selectedBillType) === 'gst')>GST Invoice</option>
                    <option value="non_gst" @selected(old('bill_type', $selectedBillType) === 'non_gst')>Normal / Non-GST</option>
                </select>
            </div>

            <div class="rounded bg-slate-50 p-4">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">Sale Total</span>
                    <span class="font-bold text-gray-900" id="saleTotal">Rs. 0.00</span>
                </div>
                <div class="mt-2 flex justify-between text-sm">
                    <span class="text-gray-600">GST</span>
                    <span class="font-semibold text-gray-900" id="saleGst">Rs. 0.00</span>
                </div>
            </div>
        </div>

        <div class="mt-6 overflow-hidden rounded border border-gray-200">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-slate-100 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                    <tr>
                        <th class="px-4 py-3">Product</th>
                        <th class="px-4 py-3 text-right">Qty</th>
                        <th class="px-4 py-3">Unit</th>
                        <th class="px-4 py-3 text-right">Rate</th>
                        <th class="px-4 py-3 text-right">Quotation Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($quotation->items as $item)
                        <tr>
                            <td class="px-4 py-3 font-medium text-gray-900">{{ $item->product?->name }}</td>
                            <td class="px-4 py-3 text-right text-gray-700">{{ number_format((float) $item->quantity, 3) }}</td>
                            <td class="px-4 py-3 text-gray-700">{{ $item->unit }}</td>
                            <td class="px-4 py-3 text-right text-gray-700">Rs. {{ number_format((float) $item->rate, 2) }}</td>
                            <td class="px-4 py-3 text-right font-semibold text-gray-900">Rs. {{ number_format((float) $item->total, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-6 flex justify-end gap-3">
            <a href="{{ route('quotations.show', $quotation) }}" class="rounded border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Cancel</a>
            <button class="rounded bg-emerald-700 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-800">Convert to Sale</button>
        </div>
    </form>

    <script>
        const previews = {
            gst: @json($gstPreview),
            non_gst: @json($nonGstPreview),
        };
        const billType = document.getElementById('billType');

        function money(value) {
            return 'Rs. ' + Number(value || 0).toFixed(2);
        }

        function refreshPreview() {
            const preview = previews[billType.value] || previews.gst;
            document.getElementById('saleTotal').textContent = money(preview.total_amount);
            document.getElementById('saleGst').textContent = money(preview.gst_amount);
        }

        billType.addEventListener('change', refreshPreview);
        refreshPreview();
    </script>
@endsection
