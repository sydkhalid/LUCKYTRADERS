@php
    $pendingSaleRows = $pendingSales->map(fn ($sale) => [
        'id' => $sale->id,
        'customer_id' => $sale->customer_id,
        'sale_no' => $sale->sale_no,
        'bill_type' => $sale->bill_type,
        'reference_type' => $sale->bill_type === 'gst' ? 'gst_invoice' : 'normal_bill',
        'sale_date' => $sale->sale_date?->format('d M Y'),
        'balance_amount' => (float) $sale->balance_amount,
        'label' => $sale->sale_no.' - '.($sale->bill_type === 'gst' ? 'GST Invoice' : 'Normal Bill').' - Balance Rs. '.number_format((float) $sale->balance_amount, 2),
    ])->values();
@endphp

@extends('layouts.erp')

@section('title', 'Customer Receipt')

@section('content')
    <div class="max-w-5xl">
        <div class="mb-5 flex items-center justify-between">
            <div>
                <h2 class="text-lg font-semibold text-gray-900">Receive Customer Payment</h2>
                <p class="text-sm text-gray-500">Record money received against pending GST invoices or normal bills.</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('receipts.index') }}" class="rounded border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Receipt List</a>
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

        <form method="POST" action="{{ route('receipts.store') }}" class="rounded bg-white p-6 shadow">
            @csrf
            <input type="hidden" name="reference_type" id="referenceType" value="{{ old('reference_type', 'sale') }}">

            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Customer</label>
                    <select name="customer_id" id="customerId" class="w-full rounded border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500" required>
                        <option value="">Select customer</option>
                        @foreach ($customers as $customer)
                            <option value="{{ $customer->id }}" @selected(old('customer_id') == $customer->id)>
                                {{ $customer->name }} - Balance Rs. {{ number_format((float) $customer->current_balance, 2) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Receipt Date</label>
                    <input type="date" name="payment_date" value="{{ old('payment_date', now()->toDateString()) }}" class="w-full rounded border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500" required>
                </div>

                <div class="md:col-span-2">
                    <label class="mb-1 block text-sm font-medium text-gray-700">Pending Sale Invoice</label>
                    <select name="reference_id" id="referenceId" class="w-full rounded border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500" required>
                        <option value="">Select customer first</option>
                    </select>
                    <p id="invoiceHelp" class="mt-1 text-xs text-gray-500">Only invoices with balance amount are shown.</p>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Received Amount</label>
                    <input type="number" name="amount" id="amount" value="{{ old('amount') }}" step="0.01" min="0.01" class="w-full rounded border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500" required>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Payment Mode</label>
                    <select name="payment_mode" class="w-full rounded border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500" required>
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
                <a href="{{ route('receipts.index') }}" class="rounded border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Cancel</a>
                <button class="rounded bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">Save Receipt</button>
            </div>
        </form>
    </div>

    <script>
        const pendingSales = @json($pendingSaleRows);
        const oldReferenceId = String(@json(old('reference_id', '')));
        const customerSelect = document.getElementById('customerId');
        const referenceSelect = document.getElementById('referenceId');
        const referenceType = document.getElementById('referenceType');
        const amount = document.getElementById('amount');
        const invoiceHelp = document.getElementById('invoiceHelp');

        function renderPendingSales() {
            const customerId = Number(customerSelect.value || 0);
            const sales = pendingSales.filter((sale) => Number(sale.customer_id) === customerId);

            referenceSelect.innerHTML = '<option value="">Select pending invoice</option>';

            sales.forEach((sale) => {
                const option = document.createElement('option');
                option.value = sale.id;
                option.dataset.referenceType = sale.reference_type;
                option.dataset.balance = sale.balance_amount;
                option.textContent = sale.label;
                option.selected = oldReferenceId && oldReferenceId === String(sale.id);
                referenceSelect.appendChild(option);
            });

            referenceSelect.disabled = sales.length === 0;
            invoiceHelp.textContent = sales.length === 0
                ? 'No pending invoices found for the selected customer.'
                : 'Only invoices with balance amount are shown.';
            syncInvoiceMeta();
        }

        function syncInvoiceMeta() {
            const selected = referenceSelect.selectedOptions[0];
            referenceType.value = selected?.dataset.referenceType || 'sale';
            amount.max = selected?.dataset.balance || '';
        }

        customerSelect.addEventListener('change', renderPendingSales);
        referenceSelect.addEventListener('change', syncInvoiceMeta);
        renderPendingSales();
    </script>
@endsection
