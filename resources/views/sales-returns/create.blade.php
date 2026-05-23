@extends('layouts.app')

@section('title', 'Create Sales Return')

@section('content')
    <div class="max-w-6xl">
        <div class="mb-5 flex items-center justify-between">
            <div>
                <h2 class="text-lg font-semibold text-gray-900">Create Sales Return</h2>
                <p class="text-sm text-gray-500">Returned sales goods increase stock and reduce receivable or create refund.</p>
            </div>
            <a href="{{ route('sales-returns.index') }}" class="rounded border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Back</a>
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

        <form method="POST" action="{{ route('sales-returns.store') }}" class="rounded bg-white p-6 shadow" data-ajax-form>
            @csrf

            <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
                <div class="md:col-span-2">
                    <label class="mb-1 block text-sm font-medium text-gray-700">Sale</label>
                    <select name="sale_id" id="sourceId" class="w-full rounded border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500" required>
                        <option value="">Select sale</option>
                        @foreach ($sales as $sale)
                            <option value="{{ $sale->id }}" @selected((int) old('sale_id', request('sale_id')) === $sale->id)>
                                {{ $sale->sale_no }} - {{ $sale->customer?->name }} - Balance ₹ {{ number_format((float) $sale->balance_amount, 2) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Return Date</label>
                    <input type="date" name="return_date" value="{{ old('return_date', now()->toDateString()) }}" class="w-full rounded border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500" required>
                </div>
            </div>

            <div class="mt-5 grid grid-cols-1 gap-4 rounded bg-slate-50 p-4 text-sm md:grid-cols-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Customer</p>
                    <p class="mt-1 font-semibold text-gray-900" id="selectedCustomerText">Select sale</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Bill Type</p>
                    <p class="mt-1 font-semibold text-gray-900" id="selectedBillTypeText">-</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Sale Balance</p>
                    <p class="mt-1 font-semibold text-gray-900" id="selectedBalanceText">₹ 0.00</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Returnable Products</p>
                    <p class="mt-1 font-semibold text-gray-900" id="selectedItemCountText">0</p>
                </div>
            </div>

            @include('sales-returns.partials.item-rows')

            <div class="mt-6 grid grid-cols-1 gap-5 lg:grid-cols-3">
                <div class="lg:col-span-2">
                    <label class="mb-1 block text-sm font-medium text-gray-700">Notes</label>
                    <textarea name="notes" rows="4" class="w-full rounded border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500">{{ old('notes') }}</textarea>
                </div>
                <div class="rounded bg-slate-50 p-5">
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between"><span class="text-gray-600">Subtotal</span><span class="font-semibold text-gray-900" id="subtotalText">₹ 0.00</span></div>
                        <div class="flex justify-between"><span class="text-gray-600">GST</span><span class="font-semibold text-gray-900" id="gstText">₹ 0.00</span></div>
                        <div class="flex justify-between border-t border-slate-200 pt-3"><span class="text-gray-800">Return Total</span><span class="font-bold text-gray-900" id="totalText">₹ 0.00</span></div>
                        <div class="text-xs text-gray-500">Allocate full return total between adjustment and refund.</div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">Adjustment Amount</label>
                            <input type="number" name="adjustment_amount" id="adjustmentAmount" value="{{ old('adjustment_amount', '0') }}" min="0" step="0.01" class="w-full rounded border-gray-300 text-right shadow-sm focus:border-slate-500 focus:ring-slate-500" required>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">Refund Amount</label>
                            <input type="number" name="refund_amount" id="refundAmount" value="{{ old('refund_amount', '0') }}" min="0" step="0.01" class="w-full rounded border-gray-300 text-right shadow-sm focus:border-slate-500 focus:ring-slate-500" required>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">Refund Mode</label>
                            <select name="payment_mode" class="w-full rounded border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500">
                                <option value="">No refund</option>
                                <option value="cash" @selected(old('payment_mode') === 'cash')>Cash</option>
                                <option value="bank" @selected(old('payment_mode') === 'bank')>Bank</option>
                                <option value="upi" @selected(old('payment_mode') === 'upi')>UPI</option>
                                <option value="cheque" @selected(old('payment_mode') === 'cheque')>Cheque</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <a href="{{ route('sales-returns.index') }}" class="rounded border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Cancel</a>
                <button class="rounded bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">Save Sales Return</button>
            </div>
        </form>
    </div>

    @include('sales-returns.partials.item-script', ['sourceData' => $sourceData])
@endsection
