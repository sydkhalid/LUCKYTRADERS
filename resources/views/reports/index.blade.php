@extends('layouts.app')

@section('title', $reportData['title'] ?? 'Reports')

@section('content')
@php
    $formatValue = fn ($value, $type) => \App\Http\Controllers\AdvancedReportController::formatForView($value, $type);
@endphp

<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <p class="text-sm font-medium text-slate-500">Advanced Reporting</p>
            <h2 class="text-2xl font-bold text-slate-900">{{ $reportData['title'] ?? 'Business Reports' }}</h2>
        </div>
        @if ($activeReport)
            @can('export_reports')
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('reports.export', array_merge(['report' => $activeReport, 'format' => 'pdf'], request()->query())) }}" class="rounded bg-slate-900 px-4 py-2 text-sm font-semibold text-white">PDF</a>
                    <a href="{{ route('reports.export', array_merge(['report' => $activeReport, 'format' => 'excel'], request()->query())) }}" class="rounded bg-emerald-700 px-4 py-2 text-sm font-semibold text-white">Excel</a>
                    <a href="{{ route('reports.export', array_merge(['report' => $activeReport, 'format' => 'csv'], request()->query())) }}" class="rounded bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm">CSV</a>
                </div>
            @endcan
        @endif
    </div>

    <div class="grid gap-3 md:grid-cols-3 xl:grid-cols-4">
        @foreach ($reports as $slug => $report)
            <a href="{{ route($report['route'], request()->query()) }}"
               class="block bg-white p-4 shadow-sm hover:bg-slate-50 {{ $activeReport === $slug ? 'ring-2 ring-slate-900' : '' }}">
                <div class="text-sm font-semibold text-slate-900">{{ $report['title'] }}</div>
                <div class="mt-1 text-xs text-slate-500">Open report</div>
            </a>
        @endforeach
    </div>

    <div class="bg-white p-5 shadow-sm">
        <form method="GET" class="grid gap-4 md:grid-cols-4 xl:grid-cols-8">
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">From Date</label>
                <input type="date" name="from_date" value="{{ $filters['from_date'] }}" class="w-full rounded border-slate-300 text-sm">
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">To Date</label>
                <input type="date" name="to_date" value="{{ $filters['to_date'] }}" class="w-full rounded border-slate-300 text-sm">
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">Product</label>
                <select name="product_id" class="w-full rounded border-slate-300 text-sm">
                    <option value="">All Products</option>
                    @foreach ($filterOptions['products'] as $product)
                        <option value="{{ $product->id }}" @selected((string) $filters['product_id'] === (string) $product->id)>{{ $product->name }} ({{ $product->code }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">Product Category</label>
                <select name="product_category_id" class="w-full rounded border-slate-300 text-sm">
                    <option value="">All Product Categories</option>
                    @foreach ($filterOptions['productCategories'] as $category)
                        <option value="{{ $category->id }}" @selected((string) $filters['product_category_id'] === (string) $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">Customer</label>
                <select name="customer_id" class="w-full rounded border-slate-300 text-sm">
                    <option value="">All Customers</option>
                    @foreach ($filterOptions['customers'] as $customer)
                        <option value="{{ $customer->id }}" @selected((string) $filters['customer_id'] === (string) $customer->id)>{{ $customer->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">Supplier</label>
                <select name="supplier_id" class="w-full rounded border-slate-300 text-sm">
                    <option value="">All Suppliers</option>
                    @foreach ($filterOptions['suppliers'] as $supplier)
                        <option value="{{ $supplier->id }}" @selected((string) $filters['supplier_id'] === (string) $supplier->id)>{{ $supplier->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">Expense Category</label>
                <select name="category_id" class="w-full rounded border-slate-300 text-sm">
                    <option value="">All Expense Categories</option>
                    @foreach ($filterOptions['expenseCategories'] as $category)
                        <option value="{{ $category->id }}" @selected((string) $filters['category_id'] === (string) $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">Payment Status</label>
                <select name="payment_status" class="w-full rounded border-slate-300 text-sm">
                    <option value="">All Statuses</option>
                    @foreach ($filterOptions['paymentStatuses'] as $status => $label)
                        <option value="{{ $status }}" @selected($filters['payment_status'] === $status)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end gap-2 xl:col-span-8">
                <button class="rounded bg-slate-900 px-4 py-2 text-sm font-semibold text-white">Apply Filters</button>
                <a href="{{ url()->current() }}" class="rounded border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700">Reset</a>
            </div>
        </form>
    </div>

    @if (! $reportData)
        <div class="bg-white p-8 text-center text-slate-500 shadow-sm">
            Select a report above to view financial, inventory, GST, loan, partner, and cash-flow analysis.
        </div>
    @else
        <div class="grid gap-4 md:grid-cols-3 xl:grid-cols-5">
            @foreach ($reportData['summaryCards'] as $card)
                <div class="bg-white p-4 shadow-sm">
                    <p class="text-sm text-slate-500">{{ $card['label'] }}</p>
                    <p class="mt-2 text-xl font-bold text-slate-900">{{ $formatValue($card['value'], $card['type']) }}</p>
                </div>
            @endforeach
        </div>

        <div class="overflow-hidden bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                        <tr>
                            @foreach ($reportData['columns'] as $column)
                                <th class="px-4 py-3">{{ $column['label'] }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($reportData['rows'] as $row)
                            <tr>
                                @foreach ($reportData['columns'] as $column)
                                    <td class="whitespace-nowrap px-4 py-3 text-slate-700">
                                        {{ $formatValue(data_get($row, $column['key']), $column['type']) }}
                                    </td>
                                @endforeach
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ count($reportData['columns']) }}" class="px-4 py-10 text-center text-slate-500">No report data found for the selected filters.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if (method_exists($reportData['rows'], 'links'))
                <div class="border-t border-slate-200 px-4 py-3">
                    {{ $reportData['rows']->links() }}
                </div>
            @endif
        </div>
    @endif
</div>
@endsection
