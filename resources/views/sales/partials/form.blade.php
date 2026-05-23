@php
    $saleRows = $sale
        ? $sale->items->map(fn ($item) => [
            'product_id' => $item->product_id,
            'quantity' => $item->quantity,
            'rate' => $item->rate,
            'gst_percentage' => $item->gst_percentage,
            'gst_calculation' => $item->gst_calculation ?? 'exclusive',
            'gst_type' => $item->gst_type ?? 'cgst_sgst',
        ])->values()->toArray()
        : [[
            'product_id' => '',
            'quantity' => 1,
            'rate' => 0,
            'gst_percentage' => 0,
            'gst_calculation' => 'exclusive',
            'gst_type' => 'cgst_sgst',
        ]];
    $rows = old('items', $saleRows);
    $saleProductRows = $products->map(fn ($product) => [
        'id' => $product->id,
        'name' => $product->name,
        'code' => $product->code,
        'rate' => (float) $product->selling_price,
        'gst_percentage' => (float) $product->gst_percentage,
        'purchase_price' => (float) $product->purchase_price,
        'current_stock' => (float) $product->current_stock,
    ])->values();
@endphp

@if ($errors->any())
    <div class="mb-5 rounded border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
        <ul class="list-disc pl-5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ $action }}" id="saleForm" data-ajax-form>
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <x-erp.ajax-errors class="mb-5" />

    <div class="grid grid-cols-1 gap-5 md:grid-cols-4">
        <div>
            <label class="erp-required mb-1 block text-sm font-medium text-gray-700">Customer</label>
            <select name="customer_id" data-searchable data-placeholder="Search customer" class="w-full rounded border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500" required>
                <option value="">Select customer</option>
                @foreach ($customers as $customer)
                    <option value="{{ $customer->id }}" @selected(old('customer_id', $sale->customer_id ?? '') == $customer->id)>{{ $customer->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="erp-required mb-1 block text-sm font-medium text-gray-700">Sale Date</label>
            <input type="date" name="sale_date" value="{{ old('sale_date', optional($sale?->sale_date)->toDateString() ?? now()->toDateString()) }}" class="w-full rounded border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500" required>
        </div>

        <div>
            <label class="erp-required mb-1 block text-sm font-medium text-gray-700">Bill Type</label>
            <select name="bill_type" id="billType" data-searchable class="w-full rounded border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500" required>
                <option value="gst" @selected(old('bill_type', $sale->bill_type ?? 'gst') === 'gst')>GST Invoice</option>
                <option value="non_gst" @selected(old('bill_type', $sale->bill_type ?? 'gst') === 'non_gst')>Normal / Non-GST</option>
            </select>
        </div>

        <div>
            <label class="erp-required mb-1 block text-sm font-medium text-gray-700">Payment Mode</label>
            <select name="payment_mode" id="paymentMode" data-searchable class="w-full rounded border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500" required>
                <option value="credit" @selected(old('payment_mode', $sale->payment_mode ?? 'credit') === 'credit')>Credit</option>
                <option value="cash" @selected(old('payment_mode', $sale->payment_mode ?? 'credit') === 'cash')>Cash</option>
                <option value="bank" @selected(old('payment_mode', $sale->payment_mode ?? 'credit') === 'bank')>Bank</option>
                <option value="upi" @selected(old('payment_mode', $sale->payment_mode ?? 'credit') === 'upi')>UPI</option>
            </select>
        </div>
    </div>

    <div class="mt-6 overflow-x-auto rounded border border-gray-200">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-100 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                <tr>
                    <th class="px-3 py-3">Product</th>
                    <th class="px-3 py-3 text-right">Stock (Kg)</th>
                    <th class="px-3 py-3 text-right">Qty (Kg)</th>
                    <th class="px-3 py-3 text-right">Rate</th>
                    <th class="px-3 py-3">GST Mode</th>
                    <th class="px-3 py-3">GST Type</th>
                    <th class="px-3 py-3 text-right">GST %</th>
                    <th class="px-3 py-3"></th>
                </tr>
            </thead>
            <tbody id="saleRows" class="divide-y divide-gray-100"></tbody>
        </table>
    </div>

    <button type="button" id="addRow" class="mt-3 rounded border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Add Product Row</button>

    <div class="mt-6 grid grid-cols-1 gap-5 lg:grid-cols-3">
        <div class="lg:col-span-2">
            <label class="mb-1 block text-sm font-medium text-gray-700">Notes</label>
            <textarea name="notes" rows="4" class="w-full rounded border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500">{{ old('notes', $sale->notes ?? '') }}</textarea>

            <div class="mt-5 rounded border border-gray-200 bg-white p-4">
                <h3 class="text-sm font-semibold text-gray-900">E-Way Details</h3>
                <div class="mt-3 grid grid-cols-1 gap-4 md:grid-cols-3">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">E-Way Bill No</label>
                        <input type="text" name="eway_bill_no" value="{{ old('eway_bill_no', $sale->eway_bill_no ?? '') }}" class="w-full rounded border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500">
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">E-Way Date</label>
                        <input type="date" name="eway_date" value="{{ old('eway_date', optional($sale?->eway_date)->toDateString()) }}" class="w-full rounded border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500">
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Valid Upto</label>
                        <input type="date" name="eway_valid_upto" value="{{ old('eway_valid_upto', optional($sale?->eway_valid_upto)->toDateString()) }}" class="w-full rounded border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500">
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Driver Name</label>
                        <input type="text" name="eway_driver_name" value="{{ old('eway_driver_name', $sale->eway_driver_name ?? '') }}" class="w-full rounded border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500">
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Mobile No</label>
                        <input type="text" name="eway_mobile_no" value="{{ old('eway_mobile_no', $sale->eway_mobile_no ?? '') }}" class="w-full rounded border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500">
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Vehicle No</label>
                        <input type="text" name="eway_vehicle_no" value="{{ old('eway_vehicle_no', $sale->eway_vehicle_no ?? '') }}" class="w-full rounded border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500">
                    </div>
                </div>
            </div>
        </div>

        <div class="rounded bg-slate-50 p-5">
            <div class="space-y-3 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-600">Subtotal</span>
                    <span class="font-semibold text-gray-900" id="subtotalText">₹ 0.00</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">CGST</span>
                    <span class="font-semibold text-gray-900" id="cgstText">&#8377; 0.00</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">SGST</span>
                    <span class="font-semibold text-gray-900" id="sgstText">&#8377; 0.00</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">IGST</span>
                    <span class="font-semibold text-gray-900" id="igstText">&#8377; 0.00</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Total GST</span>
                    <span class="font-semibold text-gray-900" id="gstText">&#8377; 0.00</span>
                </div>
                <div class="flex justify-between border-t border-slate-200 pt-3">
                    <span class="text-gray-800">Grand Total</span>
                    <span class="font-bold text-gray-900" id="totalText">₹ 0.00</span>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Paid Amount</label>
                    <input type="number" name="paid_amount" id="paidAmount" value="{{ old('paid_amount', $sale->paid_amount ?? '0') }}" step="0.01" min="0" class="w-full rounded border-gray-300 text-right shadow-sm focus:border-slate-500 focus:ring-slate-500" required>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Balance</span>
                    <span class="font-bold text-red-700" id="balanceText">₹ 0.00</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Payment Status</span>
                    <span class="font-semibold text-gray-900" id="statusText">Pending</span>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-6 flex justify-end gap-3">
        <a href="{{ route('sales.index') }}" class="rounded border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Cancel</a>
        <button class="rounded bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">Save Sale</button>
    </div>
</form>

<script>
    const products = @json($saleProductRows);
    const initialRows = @json($rows);
    const productMeta = @json($productData);
    let rowIndex = 0;

    const rowsContainer = document.getElementById('saleRows');
    const billType = document.getElementById('billType');
    const paidAmount = document.getElementById('paidAmount');

    function money(value) {
        return '\u20B9 ' + Number(value || 0).toFixed(2);
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

    function addSaleRow(data = {}) {
        const index = rowIndex++;
        const tr = document.createElement('tr');
        tr.className = 'sale-row';
        tr.innerHTML = `
            <td class="px-3 py-3">
                <select name="items[${index}][product_id]" data-searchable data-placeholder="Search product" class="product-select w-64 rounded border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500" required>
                    ${productOptions(data.product_id)}
                </select>
            </td>
            <td class="row-stock px-3 py-3 text-right text-gray-600">0</td>
            <td class="px-3 py-3">
                <input type="number" name="items[${index}][quantity]" value="${data.quantity ?? 1}" step="0.001" min="0.001" class="quantity-input w-24 rounded border-gray-300 text-right shadow-sm focus:border-slate-500 focus:ring-slate-500" required>
            </td>
            <td class="px-3 py-3">
                <input type="number" name="items[${index}][rate]" value="${data.rate ?? 0}" step="0.01" min="0" class="rate-input w-28 rounded border-gray-300 text-right shadow-sm focus:border-slate-500 focus:ring-slate-500" required>
            </td>
            <td class="px-3 py-3">
                <select name="items[${index}][gst_calculation]" class="gst-calculation-select w-32 rounded border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500">
                    <option value="exclusive" ${(data.gst_calculation ?? 'exclusive') === 'exclusive' ? 'selected' : ''}>Exclusive</option>
                    <option value="inclusive" ${(data.gst_calculation ?? 'exclusive') === 'inclusive' ? 'selected' : ''}>Inclusive</option>
                </select>
            </td>
            <td class="px-3 py-3">
                <select name="items[${index}][gst_type]" class="gst-type-select w-36 rounded border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500">
                    <option value="cgst_sgst" ${(data.gst_type ?? 'cgst_sgst') === 'cgst_sgst' ? 'selected' : ''}>CGST + SGST</option>
                    <option value="igst" ${(data.gst_type ?? 'cgst_sgst') === 'igst' ? 'selected' : ''}>IGST</option>
                </select>
            </td>
            <td class="px-3 py-3">
                <input type="number" name="items[${index}][gst_percentage]" value="${data.gst_percentage ?? 0}" step="0.01" min="0" max="100" class="gst-input w-24 rounded border-gray-300 text-right shadow-sm focus:border-slate-500 focus:ring-slate-500" required>
            </td>
            <td class="px-3 py-3 text-right">
                <button type="button" class="remove-row font-semibold text-red-600 hover:text-red-800">Remove</button>
            </td>
        `;

        rowsContainer.appendChild(tr);
        bindRow(tr);
        updateProductMeta(tr);
        calculateTotals();
        document.dispatchEvent(new Event('erp:refresh-selects'));
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
            if (document.querySelectorAll('.sale-row').length > 1) {
                row.remove();
                calculateTotals();
            }
        });
    }

    function updateProductMeta(row, overwrite = false) {
        const productId = row.querySelector('.product-select').value;
        const meta = productMeta[productId] || {};
        row.querySelector('.row-stock').dataset.stock = meta.current_stock || 0;
        row.querySelector('.row-stock').textContent = Number(meta.current_stock || 0).toFixed(3);

        if (overwrite) {
            row.querySelector('.rate-input').value = meta.rate ?? 0;
            row.querySelector('.gst-input').value = meta.gst_percentage ?? 0;
        }
    }

    function calculateTotals() {
        let subtotal = 0;
        let cgst = 0;
        let sgst = 0;
        let igst = 0;
        const isGstBill = billType.value === 'gst';

        document.querySelectorAll('.sale-row').forEach(function (row) {
            const quantity = parseFloat(row.querySelector('.quantity-input').value) || 0;
            const rate = parseFloat(row.querySelector('.rate-input').value) || 0;
            const gstPercentage = isGstBill ? (parseFloat(row.querySelector('.gst-input').value) || 0) : 0;
            const gstCalculation = row.querySelector('.gst-calculation-select').value;
            const gstType = row.querySelector('.gst-type-select').value;
            const lineAmount = quantity * rate;
            let rowSubtotal = lineAmount;
            let rowGst = 0;

            if (isGstBill && gstCalculation === 'inclusive') {
                const rowTotal = lineAmount;
                rowSubtotal = gstPercentage > 0 ? rowTotal / (1 + (gstPercentage / 100)) : rowTotal;
                rowGst = rowTotal - rowSubtotal;
            } else if (isGstBill) {
                rowGst = rowSubtotal * gstPercentage / 100;
            }

            const availableStock = parseFloat(row.querySelector('.row-stock').dataset.stock) || 0;

            subtotal += rowSubtotal;
            if (isGstBill && rowGst > 0) {
                if (gstType === 'igst') {
                    igst += rowGst;
                } else {
                    const rowCgst = rowGst / 2;
                    cgst += rowCgst;
                    sgst += rowGst - rowCgst;
                }
            }

            row.querySelector('.row-stock').classList.toggle('text-red-700', quantity > availableStock);
            row.querySelector('.row-stock').classList.toggle('font-semibold', quantity > availableStock);
        });

        const gst = cgst + sgst + igst;
        const total = subtotal + gst;
        const paid = parseFloat(paidAmount.value) || 0;
        const balance = Math.max(total - paid, 0);

        document.getElementById('subtotalText').textContent = money(subtotal);
        document.getElementById('cgstText').textContent = money(cgst);
        document.getElementById('sgstText').textContent = money(sgst);
        document.getElementById('igstText').textContent = money(igst);
        document.getElementById('gstText').textContent = money(gst);
        document.getElementById('totalText').textContent = money(total);
        document.getElementById('balanceText').textContent = money(balance);
        document.getElementById('statusText').textContent = paid <= 0 ? 'Pending' : (paid >= total ? 'Paid' : 'Partial');
    }

    document.getElementById('addRow').addEventListener('click', function () {
        addSaleRow();
    });
    billType.addEventListener('change', calculateTotals);
    paidAmount.addEventListener('input', calculateTotals);

    initialRows.forEach(function (row) {
        addSaleRow(row);
    });
</script>
