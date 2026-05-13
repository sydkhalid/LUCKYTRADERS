@extends('layouts.erp')

@section('title', 'Create Stock Adjustment')

@section('content')
    <div class="max-w-5xl">
        <div class="mb-5 flex items-center justify-between">
            <div>
                <h2 class="text-lg font-semibold text-gray-900">Create Stock Adjustment</h2>
                <p class="text-sm text-gray-500">Adjustments update product stock and create stock movement history only.</p>
            </div>
            <a href="{{ route('stock-adjustments.index') }}" class="rounded border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Back</a>
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

        <form method="POST" action="{{ route('stock-adjustments.store') }}" class="rounded bg-white p-6 shadow">
            @csrf

            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Adjustment Date</label>
                    <input type="date" name="adjustment_date" value="{{ old('adjustment_date', now()->toDateString()) }}" class="w-full rounded border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500" required>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Product</label>
                    <select name="product_id" id="productId" class="w-full rounded border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500" required>
                        <option value="">Select product</option>
                        @foreach ($products as $product)
                            <option value="{{ $product->id }}" data-stock="{{ (float) $product->current_stock }}" data-unit="{{ $product->unit }}" @selected((int) old('product_id', request('product_id')) === $product->id)>
                                {{ $product->name }} ({{ $product->code }}) - {{ number_format((float) $product->current_stock, 3) }} {{ $product->unit }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Adjustment Type</label>
                    <select name="adjustment_type" id="adjustmentType" class="w-full rounded border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500" required>
                        @foreach ($types as $value => $label)
                            <option value="{{ $value }}" @selected(old('adjustment_type') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Reason</label>
                    <select name="reason" class="w-full rounded border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500" required>
                        @foreach ($reasons as $value => $label)
                            <option value="{{ $value }}" @selected(old('reason') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Quantity</label>
                    <input type="number" name="quantity" id="quantity" value="{{ old('quantity') }}" step="0.001" min="0.001" class="w-full rounded border-gray-300 text-right shadow-sm focus:border-slate-500 focus:ring-slate-500" required>
                </div>

                <div class="rounded bg-slate-50 p-4">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Current Stock</span>
                        <span class="font-semibold text-gray-900" id="currentStockText">0.000</span>
                    </div>
                    <div class="mt-3 flex justify-between border-t border-slate-200 pt-3 text-sm">
                        <span class="text-gray-800">New Stock</span>
                        <span class="font-bold text-gray-900" id="newStockText">0.000</span>
                    </div>
                </div>
            </div>

            <div class="mt-5">
                <label class="mb-1 block text-sm font-medium text-gray-700">Remarks</label>
                <textarea name="remarks" rows="3" class="w-full rounded border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500">{{ old('remarks') }}</textarea>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <a href="{{ route('stock-adjustments.index') }}" class="rounded border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Cancel</a>
                <button class="rounded bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">Save Adjustment</button>
            </div>
        </form>
    </div>

    <script>
        const productId = document.getElementById('productId');
        const adjustmentType = document.getElementById('adjustmentType');
        const quantity = document.getElementById('quantity');

        function selectedStock() {
            const option = productId.options[productId.selectedIndex];
            return {
                stock: parseFloat(option?.dataset.stock || 0),
                unit: option?.dataset.unit || '',
            };
        }

        function refreshStockPreview() {
            const selected = selectedStock();
            const qty = parseFloat(quantity.value) || 0;
            const newStock = adjustmentType.value === 'increase'
                ? selected.stock + qty
                : Math.max(selected.stock - qty, 0);
            document.getElementById('currentStockText').textContent = selected.stock.toFixed(3) + ' ' + selected.unit;
            document.getElementById('newStockText').textContent = newStock.toFixed(3) + ' ' + selected.unit;
        }

        [productId, adjustmentType, quantity].forEach(function (input) {
            input.addEventListener('input', refreshStockPreview);
            input.addEventListener('change', refreshStockPreview);
        });

        refreshStockPreview();
    </script>
@endsection
