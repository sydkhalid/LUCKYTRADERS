@php
    $showCustomer = $showCustomer ?? false;
    $showSupplier = $showSupplier ?? false;
    $showBillType = $showBillType ?? false;
    $showPaymentStatus = $showPaymentStatus ?? false;
    $customers = $customers ?? collect();
    $suppliers = $suppliers ?? collect();
    $billTypes = $billTypes ?? [];
    $paymentStatuses = $paymentStatuses ?? [];
@endphp

<form method="GET" action="{{ $action }}" class="mb-5 rounded bg-white p-5 shadow">
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-6">
        <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">From Date</label>
            <input type="date" name="from_date" value="{{ $filters['from_date'] ?? '' }}" class="w-full rounded border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500">
        </div>

        <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">To Date</label>
            <input type="date" name="to_date" value="{{ $filters['to_date'] ?? '' }}" class="w-full rounded border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500">
        </div>

        @if ($showCustomer)
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Customer</label>
                <select name="customer_id" class="w-full rounded border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500">
                    <option value="">All Customers</option>
                    @foreach ($customers as $customer)
                        <option value="{{ $customer->id }}" @selected((string) ($filters['customer_id'] ?? '') === (string) $customer->id)>
                            {{ $customer->name }}{{ $customer->gst_number ? ' - '.$customer->gst_number : '' }}
                        </option>
                    @endforeach
                </select>
            </div>
        @endif

        @if ($showSupplier)
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Supplier</label>
                <select name="supplier_id" class="w-full rounded border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500">
                    <option value="">All Suppliers</option>
                    @foreach ($suppliers as $supplier)
                        <option value="{{ $supplier->id }}" @selected((string) ($filters['supplier_id'] ?? '') === (string) $supplier->id)>
                            {{ $supplier->name }}{{ $supplier->gst_number ? ' - '.$supplier->gst_number : '' }}
                        </option>
                    @endforeach
                </select>
            </div>
        @endif

        @if ($showBillType)
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Bill Type</label>
                <select name="bill_type" class="w-full rounded border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500">
                    @foreach ($billTypes as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['bill_type'] ?? '') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        @endif

        @if ($showPaymentStatus)
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Payment Status</label>
                <select name="payment_status" class="w-full rounded border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500">
                    <option value="">All Status</option>
                    @foreach ($paymentStatuses as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['payment_status'] ?? '') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        @endif

        <div class="flex items-end">
            <button class="w-full rounded bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">Filter</button>
        </div>

        <div class="flex items-end">
            <a href="{{ $action }}" class="w-full rounded border border-gray-300 px-4 py-2 text-center text-sm font-semibold text-gray-700 hover:bg-gray-50">Reset</a>
        </div>
    </div>
</form>
