@php
    $pendingPurchaseRows = $pendingPurchases->map(fn ($purchase) => [
        'id' => $purchase->id,
        'supplier_id' => $purchase->supplier_id,
        'purchase_no' => $purchase->purchase_no,
        'purchase_date' => $purchase->purchase_date?->format('d M Y'),
        'supplier_invoice_no' => $purchase->supplier_invoice_no,
        'balance_amount' => (float) $purchase->balance_amount,
        'label' => $purchase->purchase_no.' - Supplier Inv '.($purchase->supplier_invoice_no ?: '-').' - Balance Rs. '.number_format((float) $purchase->balance_amount, 2),
    ])->values();
@endphp

@extends('layouts.app')

@section('title', 'Supplier Payment')

@section('content')
    <div class="max-w-5xl">
        <div class="mb-5 flex items-center justify-between">
            <div>
                <h2 class="text-lg font-semibold text-gray-900">Pay Supplier</h2>
                <p class="text-sm text-gray-500">Record supplier payment against pending purchase invoices.</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('supplier-payments.index') }}" class="rounded border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Payment List</a>
                <a href="{{ route('payments.index') }}" class="rounded bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">All Payments</a>
            </div>
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

        <form method="POST" action="{{ route('supplier-payments.store') }}" class="rounded bg-white p-6 shadow" data-ajax-form>
            @csrf
            <input type="hidden" name="reference_type" value="purchase">

            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Supplier</label>
                    <select name="supplier_id" id="supplierId" data-searchable data-placeholder="Search supplier" class="w-full rounded border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500" required>
                        <option value="">Select supplier</option>
                        @foreach ($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" @selected(old('supplier_id') == $supplier->id)>
                                {{ $supplier->name }} - Balance Rs. {{ number_format((float) $supplier->current_balance, 2) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Payment Date</label>
                    <input type="date" name="payment_date" value="{{ old('payment_date', now()->toDateString()) }}" class="w-full rounded border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500" required>
                </div>

                <div class="md:col-span-2">
                    <label class="mb-1 block text-sm font-medium text-gray-700">Pending Purchase Invoice</label>
                    <select name="reference_id" id="referenceId" data-searchable data-placeholder="Search pending purchase" class="w-full rounded border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500" required>
                        <option value="">Select supplier first</option>
                    </select>
                    <p id="invoiceHelp" class="mt-1 text-xs text-gray-500">Only purchases with balance amount are shown.</p>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Paid Amount</label>
                    <input type="number" name="amount" id="amount" value="{{ old('amount') }}" step="0.01" min="0.01" class="w-full rounded border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500" required>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Payment Mode</label>
                    <select name="payment_mode" data-searchable class="w-full rounded border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500" required>
                        <option value="cash" @selected(old('payment_mode') === 'cash')>Cash</option>
                        <option value="bank" @selected(old('payment_mode') === 'bank')>Bank</option>
                        <option value="upi" @selected(old('payment_mode') === 'upi')>UPI</option>
                        <option value="cheque" @selected(old('payment_mode') === 'cheque')>Cheque</option>
                    </select>
                </div>
            </div>

            <div class="mt-5">
                <label class="mb-1 block text-sm font-medium text-gray-700">Notes</label>
                <textarea name="notes" rows="3" class="w-full rounded border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500">{{ old('notes') }}</textarea>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <a href="{{ route('supplier-payments.index') }}" class="rounded border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Cancel</a>
                <button class="rounded bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700">Save Payment</button>
            </div>
        </form>
    </div>

    <script>
        const pendingPurchases = @json($pendingPurchaseRows);
        const oldReferenceId = String(@json(old('reference_id', '')));
        const supplierSelect = document.getElementById('supplierId');
        const referenceSelect = document.getElementById('referenceId');
        const amount = document.getElementById('amount');
        const invoiceHelp = document.getElementById('invoiceHelp');

        function renderPendingPurchases() {
            const supplierId = Number(supplierSelect.value || 0);
            const purchases = pendingPurchases.filter((purchase) => Number(purchase.supplier_id) === supplierId);

            referenceSelect.innerHTML = '<option value="">Select pending purchase</option>';

            purchases.forEach((purchase) => {
                const option = document.createElement('option');
                option.value = purchase.id;
                option.dataset.balance = purchase.balance_amount;
                option.textContent = purchase.label;
                option.selected = oldReferenceId && oldReferenceId === String(purchase.id);
                referenceSelect.appendChild(option);
            });

            referenceSelect.disabled = purchases.length === 0;
            window.$?.(referenceSelect).trigger('change.select2');
            invoiceHelp.textContent = purchases.length === 0
                ? 'No pending purchases found for the selected supplier.'
                : 'Only purchases with balance amount are shown.';
            syncInvoiceMeta();
        }

        function syncInvoiceMeta() {
            const selected = referenceSelect.selectedOptions[0];
            amount.max = selected?.dataset.balance || '';
        }

        supplierSelect.addEventListener('change', renderPendingPurchases);
        referenceSelect.addEventListener('change', syncInvoiceMeta);
        renderPendingPurchases();
    </script>
@endsection
