<script>
    const sourceData = @json($sourceData);
    const sourceId = document.getElementById('sourceId');
    const rowsContainer = document.getElementById('returnRows');
    let rowIndex = 0;

    function money(value) {
        return 'Rs. ' + Number(value || 0).toFixed(2);
    }

    function escapeHtml(value) {
        return String(value ?? '').replace(/[&<>"']/g, function (character) {
            return {'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'}[character];
        });
    }

    function currentItems() {
        return sourceData[sourceId.value]?.items || [];
    }

    function productOptions(selectedId) {
        return '<option value="">Select product</option>' + currentItems().map(function (item) {
            const selected = Number(selectedId) === Number(item.product_id) ? 'selected' : '';
            return `<option value="${item.product_id}" ${selected}>${escapeHtml(item.name)} (${escapeHtml(item.code)})</option>`;
        }).join('');
    }

    function addReturnRow(data = {}) {
        const index = rowIndex++;
        const tr = document.createElement('tr');
        tr.className = 'return-row';
        tr.innerHTML = `
            <td class="px-3 py-3"><select name="items[${index}][product_id]" class="product-select w-64 rounded border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500" required>${productOptions(data.product_id)}</select></td>
            <td class="row-remaining px-3 py-3 text-right text-gray-600">0</td>
            <td class="px-3 py-3"><input type="number" name="items[${index}][quantity]" value="${data.quantity ?? 1}" step="0.001" min="0.001" class="quantity-input w-24 rounded border-gray-300 text-right shadow-sm focus:border-slate-500 focus:ring-slate-500" required></td>
            <td class="px-3 py-3"><input type="number" name="items[${index}][rate]" value="${data.rate ?? 0}" step="0.01" min="0" class="rate-input w-28 rounded border-gray-300 text-right shadow-sm focus:border-slate-500 focus:ring-slate-500" required></td>
            <td class="px-3 py-3"><input type="number" name="items[${index}][gst_percentage]" value="${data.gst_percentage ?? 0}" step="0.01" min="0" max="100" class="gst-input w-24 rounded border-gray-300 text-right shadow-sm focus:border-slate-500 focus:ring-slate-500" required></td>
            <td class="row-subtotal px-3 py-3 text-right text-gray-700">Rs. 0.00</td>
            <td class="row-gst px-3 py-3 text-right text-gray-700">Rs. 0.00</td>
            <td class="row-total px-3 py-3 text-right font-semibold text-gray-900">Rs. 0.00</td>
            <td class="px-3 py-3 text-right"><button type="button" class="remove-row font-semibold text-red-600 hover:text-red-800">Remove</button></td>
        `;
        rowsContainer.appendChild(tr);
        bindRow(tr);
        updateProductMeta(tr, true);
        calculateTotals();
    }

    function itemFor(row) {
        const productId = Number(row.querySelector('.product-select').value);
        return currentItems().find((item) => Number(item.product_id) === productId);
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
            if (document.querySelectorAll('.return-row').length > 1) {
                row.remove();
                calculateTotals();
            }
        });
    }

    function updateProductMeta(row, overwrite = false) {
        const item = itemFor(row);
        row.querySelector('.row-remaining').dataset.remaining = item?.remaining_quantity || 0;
        row.querySelector('.row-remaining').textContent = `${Number(item?.remaining_quantity || 0).toFixed(3)} ${item?.unit || ''}`;
        if (overwrite && item) {
            row.querySelector('.rate-input').value = item.rate;
            row.querySelector('.gst-input').value = item.gst_percentage;
        }
    }

    function refreshProductOptions() {
        document.querySelectorAll('.return-row').forEach(function (row) {
            row.querySelector('.product-select').innerHTML = productOptions();
            updateProductMeta(row, true);
        });
        calculateTotals();
    }

    function calculateTotals() {
        let subtotal = 0;
        let gst = 0;
        document.querySelectorAll('.return-row').forEach(function (row) {
            const quantity = parseFloat(row.querySelector('.quantity-input').value) || 0;
            const rate = parseFloat(row.querySelector('.rate-input').value) || 0;
            const gstPercentage = parseFloat(row.querySelector('.gst-input').value) || 0;
            const rowSubtotal = quantity * rate;
            const rowGst = rowSubtotal * gstPercentage / 100;
            const remaining = parseFloat(row.querySelector('.row-remaining').dataset.remaining) || 0;
            subtotal += rowSubtotal;
            gst += rowGst;
            row.querySelector('.row-subtotal').textContent = money(rowSubtotal);
            row.querySelector('.row-gst').textContent = money(rowGst);
            row.querySelector('.row-total').textContent = money(rowSubtotal + rowGst);
            row.querySelector('.row-remaining').classList.toggle('text-red-700', quantity > remaining);
            row.querySelector('.row-remaining').classList.toggle('font-semibold', quantity > remaining);
        });
        document.getElementById('subtotalText').textContent = money(subtotal);
        document.getElementById('gstText').textContent = money(gst);
        document.getElementById('totalText').textContent = money(subtotal + gst);
    }

    document.getElementById('addRow').addEventListener('click', () => addReturnRow());
    sourceId.addEventListener('change', refreshProductOptions);
    addReturnRow();
</script>
