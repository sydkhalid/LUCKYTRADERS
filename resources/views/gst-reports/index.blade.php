@extends('layouts.erp')

@section('title', 'GST Summary')

@section('content')
    <div class="mb-5 flex items-center justify-between">
        <div>
            <h2 class="text-lg font-semibold text-gray-900">GST Summary</h2>
            <p class="text-sm text-gray-500">GST bills are separated from normal bills for auditor reporting.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('gst-reports.sales', $filters) }}" class="rounded border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">GST Sales</a>
            <a href="{{ route('gst-reports.purchases', $filters) }}" class="rounded border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">GST Purchases</a>
            <a href="{{ route('gst-reports.pdf', $filters) }}" target="_blank" class="rounded border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">PDF</a>
            <a href="{{ route('gst-reports.pdf', array_merge($filters, ['download' => 1])) }}" class="rounded border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Download PDF</a>
            <a href="{{ route('gst-reports.export', array_merge($filters, ['type' => 'all'])) }}" class="rounded bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">Auditor CSV</a>
        </div>
    </div>

    @include('gst-reports.partials.filters', ['action' => route('gst-reports.index'), 'filters' => $filters])

    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
        <div class="rounded bg-white p-5 shadow">
            <p class="text-sm text-gray-500">Total Taxable Sales</p>
            <h3 class="mt-1 text-2xl font-bold text-gray-900">Rs. {{ number_format((float) $summary['taxable_sales'], 2) }}</h3>
        </div>
        <div class="rounded bg-white p-5 shadow">
            <p class="text-sm text-gray-500">Output GST</p>
            <h3 class="mt-1 text-2xl font-bold text-emerald-700">Rs. {{ number_format((float) $summary['output_gst'], 2) }}</h3>
        </div>
        <div class="rounded bg-white p-5 shadow">
            <p class="text-sm text-gray-500">GST Sales Total</p>
            <h3 class="mt-1 text-2xl font-bold text-gray-900">Rs. {{ number_format((float) $summary['total_sales'], 2) }}</h3>
        </div>
        <div class="rounded bg-white p-5 shadow">
            <p class="text-sm text-gray-500">Total Taxable Purchases</p>
            <h3 class="mt-1 text-2xl font-bold text-gray-900">Rs. {{ number_format((float) $summary['taxable_purchases'], 2) }}</h3>
        </div>
        <div class="rounded bg-white p-5 shadow">
            <p class="text-sm text-gray-500">Input GST</p>
            <h3 class="mt-1 text-2xl font-bold text-red-700">Rs. {{ number_format((float) $summary['input_gst'], 2) }}</h3>
        </div>
        <div class="rounded bg-white p-5 shadow">
            <p class="text-sm text-gray-500">GST Purchase Total</p>
            <h3 class="mt-1 text-2xl font-bold text-gray-900">Rs. {{ number_format((float) $summary['total_purchases'], 2) }}</h3>
        </div>
        <div class="rounded bg-white p-5 shadow">
            <p class="text-sm text-gray-500">Net GST Payable</p>
            <h3 class="mt-1 text-2xl font-bold {{ $summary['net_gst_payable'] >= 0 ? 'text-red-700' : 'text-emerald-700' }}">Rs. {{ number_format((float) $summary['net_gst_payable'], 2) }}</h3>
        </div>
        <div class="rounded bg-white p-5 shadow">
            <p class="text-sm text-gray-500">Non-GST Sales</p>
            <h3 class="mt-1 text-2xl font-bold text-gray-900">Rs. {{ number_format((float) $summary['non_gst_sales'], 2) }}</h3>
        </div>
        <a href="{{ route('gst-reports.non-gst-sales', $filters) }}" class="rounded bg-white p-5 shadow hover:shadow-md">
            <p class="text-sm text-gray-500">Normal Bill Report</p>
            <h3 class="mt-1 text-lg font-bold text-slate-800">Open Non-GST Sales</h3>
        </a>
        <div class="rounded bg-white p-5 shadow">
            <p class="text-sm text-gray-500">GST Sales Returns</p>
            <h3 class="mt-1 text-2xl font-bold text-red-700">Rs. {{ number_format((float) $summary['sales_returns'], 2) }}</h3>
        </div>
        <div class="rounded bg-white p-5 shadow">
            <p class="text-sm text-gray-500">GST Purchase Returns</p>
            <h3 class="mt-1 text-2xl font-bold text-emerald-700">Rs. {{ number_format((float) $summary['purchase_returns'], 2) }}</h3>
        </div>
    </div>
@endsection
