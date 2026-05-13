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

<form method="GET" action="{{ $action }}" class="mb-5 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-6">
        <div>
                <label class="mb-1 block text-xs font-black uppercase tracking-wide text-slate-500">From Date</label>
            <input type="date" name="from_date" value="{{ $filters['from_date'] ?? '' }}" class="w-full rounded border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500">
        </div>

        <div>
                <label class="mb-1 block text-xs font-black uppercase tracking-wide text-slate-500">To Date</label>
            <input type="date" name="to_date" value="{{ $filters['to_date'] ?? '' }}" class="w-full rounded border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500">
        </div>

        @if ($showCustomer)
            <div>
                <label class="mb-1 block text-xs font-black uppercase tracking-wide text-slate-500">Customer</label>
                <select name="customer_id" data-searchable class="w-full rounded border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500">
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
                <label class="mb-1 block text-xs font-black uppercase tracking-wide text-slate-500">Supplier</label>
                <select name="supplier_id" data-searchable class="w-full rounded border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500">
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
                <label class="mb-1 block text-xs font-black uppercase tracking-wide text-slate-500">Bill Type</label>
                <select name="bill_type" data-searchable class="w-full rounded border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500">
                    @foreach ($billTypes as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['bill_type'] ?? '') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        @endif

        @if ($showPaymentStatus)
            <div>
                <label class="mb-1 block text-xs font-black uppercase tracking-wide text-slate-500">Payment Status</label>
                <select name="payment_status" data-searchable class="w-full rounded border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500">
                    <option value="">All Status</option>
                    @foreach ($paymentStatuses as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['payment_status'] ?? '') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        @endif

        <div class="flex items-end">
            <button class="erp-primary-button w-full">Filter</button>
        </div>

        <div class="flex items-end">
            <a href="{{ $action }}" class="erp-secondary-button w-full">Reset</a>
        </div>
    </div>
</form>
