@php
    $purchaseRows = $purchase
        ? $purchase->items->map(fn ($item) => [
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
    $rows = old('items', $purchaseRows);
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

<form method="POST" action="{{ $action }}" id="purchaseForm">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="grid grid-cols-1 gap-5 md:grid-cols-5">
        <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">Supplier</label>
            <select name="supplier_id" class="w-full rounded border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500" required>
                <option value="">Select supplier</option>
                @foreach ($suppliers as $supplier)
                    <option value="{{ $supplier->id }}" @selected(old('supplier_id', $purchase->supplier_id ?? '') == $supplier->id)>{{ $supplier->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">Purchase Date</label>
            <input type="date" name="purchase_date" value="{{ old('purchase_date', optional($purchase?->purchase_date)->toDateString() ?? now()->toDateString()) }}" class="w-full rounded border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500" required>
        </div>

        <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">Bill Type</label>
            <select name="bill_type" id="billType" class="w-full rounded border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500" required>
                <option value="gst" @selected(old('bill_type', $purchase->bill_type ?? 'gst') === 'gst')>GST</option>
                <option value="non_gst" @selected(old('bill_type', $purchase->bill_type ?? 'gst') === 'non_gst')>Non GST</option>
            </select>
        </div>

        <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">Supplier Invoice No</label>
            <input type="text" name="supplier_invoice_no" value="{{ old('supplier_invoice_no', $purchase->supplier_invoice_no ?? '') }}" class="w-full rounded border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500">
        </div>

        <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">Payment Mode</label>
            <select name="payment_mode" id="paymentMode" class="w-full rounded border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500" required>
                <option value="credit" @selected(old('payment_mode', $purchase->payment_mode ?? 'credit') === 'credit')>Credit</option>
                <option value="cash" @selected(old('payment_mode', $purchase->payment_mode ?? 'credit') === 'cash')>Cash</option>
                <option value="bank" @selected(old('payment_mode', $purchase->payment_mode ?? 'credit') === 'bank')>Bank</option>
                <option value="upi" @selected(old('payment_mode', $purchase->payment_mode ?? 'credit') === 'upi')>UPI</option>
                <option value="cheque" @selected(old('payment_mode', $purchase->payment_mode ?? 'credit') === 'cheque')>Cheque</option>
            </select>
        </div>
    </div>

    <div class="mt-6 overflow-x-auto rounded border border-gray-200">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-100 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                <tr>
                    <th class="px-3 py-3">Product</th>
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
            <tbody id="purchaseRows" class="divide-y divide-gray-100"></tbody>
        </table>
    </div>

    <button type="button" id="addRow" class="mt-3 rounded border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Add Product Row</button>

    <div class="mt-6 grid grid-cols-1 gap-5 lg:grid-cols-3">
        <div class="lg:col-span-2">
            <label class="mb-1 block text-sm font-medium text-gray-700">Notes</label>
            <textarea name="notes" rows="4" class="w-full rounded border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500">{{ old('notes', $purchase->notes ?? '') }}</textarea>
        </div>

        <div class="rounded bg-slate-50 p-5">
            <div class="space-y-3 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-600">Subtotal</span>
                    <span class="font-semibold text-gray-900" id="subtotalText">Rs. 0.00</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">GST Input</span>
                    <span class="font-semibold text-gray-900" id="gstText">Rs. 0.00</span>
                </div>
                <div class="flex justify-between border-t border-slate-200 pt-3">
                    <span class="text-gray-800">Total</span>
                    <span class="font-bold text-gray-900" id="totalText">Rs. 0.00</span>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Paid Amount</label>
                    <input type="number" name="paid_amount" id="paidAmount" value="{{ old('paid_amount', $purchase->paid_amount ?? '0') }}" step="0.01" min="0" class="w-full rounded border-gray-300 text-right shadow-sm focus:border-slate-500 focus:ring-slate-500" required>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Balance</span>
                    <span class="font-bold text-red-700" id="balanceText">Rs. 0.00</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Payment Status</span>
                    <span class="font-semibold text-gray-900" id="statusText">Pending</span>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-6 flex justify-end gap-3">
        <a href="{{ route('purchases.index') }}" class="rounded border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Cancel</a>
        <button class="rounded bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">Save Purchase</button>
    </div>
</form>

<script>
    const products = @json($products->map(fn ($product) => [
        'id' => $product->id,
        'name' => $product->name,
        'code' => $product->code,
        'unit' => $product->unit,
        'rate' => (float) $product->purchase_price,
        'gst_percentage' => (float) $product->gst_percentage,
    ])->values());
    const initialRows = @json($rows);
    const productMeta = @json($productData);
    let rowIndex = 0;

    const rowsContainer = document.getElementById('purchaseRows');
    const billType = document.getElementById('billType');
    const paidAmount = document.getElementById('paidAmount');

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

    function addPurchaseRow(data = {}) {
        const index = rowIndex++;
        const tr = document.createElement('tr');
        tr.className = 'purchase-row';
        tr.innerHTML = `
            <td class="px-3 py-3">
                <select name="items[${index}][product_id]" class="product-select w-64 rounded border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500" required>
                    ${productOptions(data.product_id)}
                </select>
            </td>
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
        calculateTotals();
    }

    function bindRow(row) {
        row.querySelectorAll('input, select').forEach(function (input) {
            input.addEventListener('input', calculateTotals);
            input.addEventListener('change', calculateTotals);
        });

        row.querySelector('.product-select').addEventListener('change', function (event) {
            const meta = productMeta[event.target.value] || {};
            row.querySelector('.unit-input').value = meta.unit || '';
            row.querySelector('.rate-input').value = meta.rate ?? 0;
            row.querySelector('.gst-input').value = meta.gst_percentage ?? 0;
            calculateTotals();
        });

        row.querySelector('.remove-row').addEventListener('click', function () {
            if (document.querySelectorAll('.purchase-row').length > 1) {
                row.remove();
                calculateTotals();
            }
        });
    }

    function calculateTotals() {
        let subtotal = 0;
        let gst = 0;
        const isGstBill = billType.value === 'gst';

        document.querySelectorAll('.purchase-row').forEach(function (row) {
            const quantity = parseFloat(row.querySelector('.quantity-input').value) || 0;
            const rate = parseFloat(row.querySelector('.rate-input').value) || 0;
            const gstPercentage = isGstBill ? (parseFloat(row.querySelector('.gst-input').value) || 0) : 0;
            const rowSubtotal = quantity * rate;
            const rowGst = rowSubtotal * gstPercentage / 100;
            const rowTotal = rowSubtotal + rowGst;

            subtotal += rowSubtotal;
            gst += rowGst;

            row.querySelector('.row-subtotal').textContent = money(rowSubtotal);
            row.querySelector('.row-gst').textContent = money(rowGst);
            row.querySelector('.row-total').textContent = money(rowTotal);
        });

        const total = subtotal + gst;
        const paid = parseFloat(paidAmount.value) || 0;
        const balance = Math.max(total - paid, 0);

        document.getElementById('subtotalText').textContent = money(subtotal);
        document.getElementById('gstText').textContent = money(gst);
        document.getElementById('totalText').textContent = money(total);
        document.getElementById('balanceText').textContent = money(balance);
        document.getElementById('statusText').textContent = paid <= 0 ? 'Pending' : (paid >= total ? 'Paid' : 'Partial');
    }

    document.getElementById('addRow').addEventListener('click', function () {
        addPurchaseRow();
    });
    billType.addEventListener('change', calculateTotals);
    paidAmount.addEventListener('input', calculateTotals);

    initialRows.forEach(function (row) {
        addPurchaseRow(row);
    });
</script>
