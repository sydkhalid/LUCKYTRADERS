@php
    $quotationRows = $quotation
        ? $quotation->items->map(fn ($item) => [
            'product_id' => $item->product_id,
            'quantity' => $item->quantity,
            'unit' => $item->unit,
            'rate' => $item->rate,
            'gst_percentage' => $item->gst_percentage,
        ])->values()->toArray()
        : [[
            'product_id' => '',
            'quantity' => 1,
            'unit' => '',
            'rate' => 0,
            'gst_percentage' => 0,
        ]];
    $rows = old('items', $quotationRows);
    $productsForJs = $products->map(fn ($product) => [
        'id' => $product->id,
        'name' => $product->name,
        'code' => $product->code,
        'unit' => $product->unit,
        'rate' => (float) $product->selling_price,
        'gst_percentage' => (float) $product->gst_percentage,
        'current_stock' => (float) $product->current_stock,
    ])->values();
@endphp

<div class="mb-5 flex items-center justify-between">
    <div>
        <h2 class="text-lg font-semibold text-gray-900">{{ $quotation ? 'Edit Quotation '.$quotation->quotation_no : 'Create Quotation' }}</h2>
        <p class="text-sm text-gray-500">Quotation entries do not reduce stock and do not post ledger or cashbook entries.</p>
    </div>
    <a href="{{ $quotation ? route('quotations.show', $quotation) : route('quotations.index') }}" class="rounded border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Back</a>
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

<form method="POST" action="{{ $action }}" id="quotationForm" data-ajax-form>
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="grid grid-cols-1 gap-5 md:grid-cols-5">
        <div class="md:col-span-2">
            <label class="mb-1 block text-sm font-medium text-gray-700">Customer</label>
            <select name="customer_id" class="w-full rounded border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500" required>
                <option value="">Select customer</option>
                @foreach ($customers as $customer)
                    <option value="{{ $customer->id }}" @selected(old('customer_id', $quotation->customer_id ?? '') == $customer->id)>{{ $customer->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">Quotation Date</label>
            <input type="date" name="quotation_date" value="{{ old('quotation_date', optional($quotation?->quotation_date)->toDateString() ?? now()->toDateString()) }}" class="w-full rounded border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500" required>
        </div>

        <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">Valid Until</label>
            <input type="date" name="valid_until" value="{{ old('valid_until', optional($quotation?->valid_until)->toDateString()) }}" class="w-full rounded border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500">
        </div>

        <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">Status</label>
            <select name="status" class="w-full rounded border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500" required>
                @foreach ($statuses as $value => $label)
                    <option value="{{ $value }}" @selected(old('status', $quotation->status ?? 'draft') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="mt-6 overflow-x-auto rounded border border-gray-200">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-100 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                <tr>
                    <th class="px-3 py-3">Product</th>
                    <th class="px-3 py-3 text-right">Stock</th>
                    <th class="px-3 py-3 text-right">Qty</th>
                    <th class="px-3 py-3">Unit</th>
                    <th class="px-3 py-3 text-right">Rate</th>
                    <th class="px-3 py-3 text-right">GST %</th>
                    <th class="px-3 py-3 text-right">Subtotal</th>
                    <th class="px-3 py-3 text-right">GST</th>
                    <th class="px-3 py-3 text-right">Total</th>
                    <th class="px-3 py-3"></th>
                </tr>
            </thead>
            <tbody id="quotationRows" class="divide-y divide-gray-100"></tbody>
        </table>
    </div>

    <button type="button" id="addRow" class="mt-3 rounded border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Add Product Row</button>

    <div class="mt-6 grid grid-cols-1 gap-5 lg:grid-cols-3">
        <div class="lg:col-span-2">
            <label class="mb-1 block text-sm font-medium text-gray-700">Notes</label>
            <textarea name="notes" rows="4" class="w-full rounded border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500">{{ old('notes', $quotation->notes ?? '') }}</textarea>
        </div>

        <div class="rounded bg-slate-50 p-5">
            <div class="space-y-3 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-600">Subtotal</span>
                    <span class="font-semibold text-gray-900" id="subtotalText">Rs. 0.00</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">GST</span>
                    <span class="font-semibold text-gray-900" id="gstText">Rs. 0.00</span>
                </div>
                <div class="flex justify-between border-t border-slate-200 pt-3">
                    <span class="text-gray-800">Grand Total</span>
                    <span class="font-bold text-gray-900" id="totalText">Rs. 0.00</span>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-6 flex justify-end gap-3">
        <a href="{{ route('quotations.index') }}" class="rounded border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Cancel</a>
        <button class="rounded bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">Save Quotation</button>
    </div>
</form>

<script>
    const products = @json($productsForJs);
    const initialRows = @json($rows);
    const productMeta = @json($productData);
    let rowIndex = 0;

    const rowsContainer = document.getElementById('quotationRows');

    function money(value) {
        return 'Rs. ' + Number(value || 0).toFixed(2);
    }

    function escapeHtml(value) {
        return String(value ?? '').replace(/[&<>"']/g, function (character) {
            return {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;',
            }[character];
        });
    }

    function productOptions(selectedId) {
        return '<option value="">Select product</option>' + products.map(function (product) {
            const selected = Number(selectedId) === Number(product.id) ? 'selected' : '';
            return `<option value="${product.id}" ${selected}>${escapeHtml(product.name)} (${escapeHtml(product.code)})</option>`;
        }).join('');
    }

    function addQuotationRow(data = {}) {
        const index = rowIndex++;
        const tr = document.createElement('tr');
        tr.className = 'quotation-row';
        tr.innerHTML = `
            <td class="px-3 py-3">
                <select name="items[${index}][product_id]" class="product-select w-64 rounded border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500" required>
                    ${productOptions(data.product_id)}
                </select>
            </td>
            <td class="row-stock px-3 py-3 text-right text-gray-600">0</td>
            <td class="px-3 py-3">
                <input type="number" name="items[${index}][quantity]" value="${data.quantity ?? 1}" step="0.001" min="0.001" class="quantity-input w-24 rounded border-gray-300 text-right shadow-sm focus:border-slate-500 focus:ring-slate-500" required>
            </td>
            <td class="px-3 py-3">
                <input type="text" name="items[${index}][unit]" value="${escapeHtml(data.unit ?? '')}" class="unit-input w-20 rounded border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500" required>
            </td>
            <td class="px-3 py-3">
                <input type="number" name="items[${index}][rate]" value="${data.rate ?? 0}" step="0.01" min="0" class="rate-input w-28 rounded border-gray-300 text-right shadow-sm focus:border-slate-500 focus:ring-slate-500" required>
            </td>
            <td class="px-3 py-3">
                <input type="number" name="items[${index}][gst_percentage]" value="${data.gst_percentage ?? 0}" step="0.01" min="0" max="100" class="gst-input w-24 rounded border-gray-300 text-right shadow-sm focus:border-slate-500 focus:ring-slate-500" required>
            </td>
            <td class="row-subtotal px-3 py-3 text-right text-gray-700">Rs. 0.00</td>
            <td class="row-gst px-3 py-3 text-right text-gray-700">Rs. 0.00</td>
            <td class="row-total px-3 py-3 text-right font-semibold text-gray-900">Rs. 0.00</td>
            <td class="px-3 py-3 text-right">
                <button type="button" class="remove-row font-semibold text-red-600 hover:text-red-800">Remove</button>
            </td>
        `;

        rowsContainer.appendChild(tr);
        bindRow(tr);
        updateProductMeta(tr);
        calculateTotals();
    }

    function bindRow(row) {
        row.querySelectorAll('input, select').forEach(function (input) {
            input.addEventListener('input', calculateTotals);
            input.addEventListener('change', calculateTotals);
        });

        row.querySelector('.product-select').addEventListener('change', function () {
            updateProductMeta(row, true);
            calculateTotals();
        });

        row.querySelector('.remove-row').addEventListener('click', function () {
            if (document.querySelectorAll('.quotation-row').length > 1) {
                row.remove();
                calculateTotals();
            }
        });
    }

    function updateProductMeta(row, overwrite = false) {
        const productId = row.querySelector('.product-select').value;
        const meta = productMeta[productId] || {};
        row.querySelector('.row-stock').textContent = `${Number(meta.current_stock || 0).toFixed(3)} ${meta.unit || ''}`;

        if (overwrite) {
            row.querySelector('.unit-input').value = meta.unit || '';
            row.querySelector('.rate-input').value = meta.rate ?? 0;
            row.querySelector('.gst-input').value = meta.gst_percentage ?? 0;
        }
    }

    function calculateTotals() {
        let subtotal = 0;
        let gst = 0;

        document.querySelectorAll('.quotation-row').forEach(function (row) {
            const quantity = parseFloat(row.querySelector('.quantity-input').value) || 0;
            const rate = parseFloat(row.querySelector('.rate-input').value) || 0;
            const gstPercentage = parseFloat(row.querySelector('.gst-input').value) || 0;
            const rowSubtotal = quantity * rate;
            const rowGst = rowSubtotal * gstPercentage / 100;
            const rowTotal = rowSubtotal + rowGst;

            subtotal += rowSubtotal;
            gst += rowGst;

            row.querySelector('.row-subtotal').textContent = money(rowSubtotal);
            row.querySelector('.row-gst').textContent = money(rowGst);
            row.querySelector('.row-total').textContent = money(rowTotal);
        });

        document.getElementById('subtotalText').textContent = money(subtotal);
        document.getElementById('gstText').textContent = money(gst);
        document.getElementById('totalText').textContent = money(subtotal + gst);
    }

    document.getElementById('addRow').addEventListener('click', function () {
        addQuotationRow();
    });

    initialRows.forEach(function (row) {
        addQuotationRow(row);
    });
</script>
