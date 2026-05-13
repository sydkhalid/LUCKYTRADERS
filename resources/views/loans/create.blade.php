@extends('layouts.erp')

@section('title', 'Create Loan')

@section('content')
    <div class="max-w-5xl">
        <div class="mb-5 flex items-center justify-between">
            <div>
                <h2 class="text-lg font-semibold text-gray-900">Create Loan Entry</h2>
                <p class="text-sm text-gray-500">Opening cash or bank movement will be posted automatically.</p>
            </div>
            <a href="{{ route('loans.index') }}" class="rounded border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Back</a>
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

        <form method="POST" action="{{ route('loans.store') }}" class="rounded bg-white p-6 shadow" id="loanForm">
            @csrf

            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Loan Type</label>
                    <select name="loan_type" class="w-full rounded border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500" required>
                        @foreach ($loanTypes as $value => $label)
                            <option value="{{ $value }}" @selected(old('loan_type') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Loan Date</label>
                    <input type="date" name="loan_date" value="{{ old('loan_date', now()->toDateString()) }}" class="w-full rounded border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500" required>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Party / Partner Name</label>
                    <input type="text" name="party_name" value="{{ old('party_name') }}" class="w-full rounded border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500" required>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Phone</label>
                    <input type="text" name="party_phone" value="{{ old('party_phone') }}" class="w-full rounded border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500">
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Partner ID</label>
                    <input type="number" name="partner_id" value="{{ old('partner_id') }}" min="1" class="w-full rounded border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500">
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

                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Principal Amount</label>
                    <input type="number" name="principal_amount" id="principalAmount" value="{{ old('principal_amount') }}" min="0.01" step="0.01" class="w-full rounded border-gray-300 text-right shadow-sm focus:border-slate-500 focus:ring-slate-500" required>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Interest Type</label>
                    <select name="interest_type" id="interestType" class="w-full rounded border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500" required>
                        <option value="none" @selected(old('interest_type', 'none') === 'none')>None</option>
                        <option value="monthly" @selected(old('interest_type') === 'monthly')>Monthly</option>
                        <option value="yearly" @selected(old('interest_type') === 'yearly')>Yearly</option>
                        <option value="fixed" @selected(old('interest_type') === 'fixed')>Fixed</option>
                    </select>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Interest %</label>
                    <input type="number" name="interest_percentage" id="interestPercentage" value="{{ old('interest_percentage', '0') }}" min="0" max="100" step="0.01" class="w-full rounded border-gray-300 text-right shadow-sm focus:border-slate-500 focus:ring-slate-500">
                </div>

                <div class="rounded bg-slate-50 p-4">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Interest</span>
                        <span class="font-semibold text-gray-900" id="interestText">Rs. 0.00</span>
                    </div>
                    <div class="mt-3 flex justify-between border-t border-slate-200 pt-3 text-sm">
                        <span class="text-gray-800">Total Amount</span>
                        <span class="font-bold text-gray-900" id="totalText">Rs. 0.00</span>
                    </div>
                </div>
            </div>

            <div class="mt-5">
                <label class="mb-1 block text-sm font-medium text-gray-700">Notes</label>
                <textarea name="notes" rows="3" class="w-full rounded border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500">{{ old('notes') }}</textarea>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <a href="{{ route('loans.index') }}" class="rounded border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Cancel</a>
                <button class="rounded bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">Save Loan</button>
            </div>
        </form>
    </div>

    <script>
        const principalAmount = document.getElementById('principalAmount');
        const interestType = document.getElementById('interestType');
        const interestPercentage = document.getElementById('interestPercentage');

        function money(value) {
            return 'Rs. ' + Number(value || 0).toFixed(2);
        }

        function refreshLoanTotal() {
            const principal = parseFloat(principalAmount.value) || 0;
            const percentage = parseFloat(interestPercentage.value) || 0;
            const interest = interestType.value === 'none' ? 0 : principal * percentage / 100;
            document.getElementById('interestText').textContent = money(interest);
            document.getElementById('totalText').textContent = money(principal + interest);
        }

        [principalAmount, interestType, interestPercentage].forEach(function (input) {
            input.addEventListener('input', refreshLoanTotal);
            input.addEventListener('change', refreshLoanTotal);
        });

        refreshLoanTotal();
    </script>
@endsection
