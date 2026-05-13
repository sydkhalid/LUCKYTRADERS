@extends('layouts.erp')

@section('title', 'Dashboard')

@section('content')
    @php
        $money = fn ($value) => 'Rs. '.number_format((float) $value, 2);
        $number = fn ($value) => number_format((float) $value, 3);
        $chartEmpty = fn (array $dataset) => array_sum(array_map('floatval', $dataset['data'] ?? [])) <= 0;
        $cardItems = [
            ['label' => 'Today Sales', 'value' => $money($cards['today_sales']), 'tone' => 'text-emerald-700'],
            ['label' => 'This Month Sales', 'value' => $money($cards['month_sales']), 'tone' => 'text-emerald-700'],
            ['label' => 'Today Purchase', 'value' => $money($cards['today_purchase']), 'tone' => 'text-red-700'],
            ['label' => 'This Month Purchase', 'value' => $money($cards['month_purchase']), 'tone' => 'text-red-700'],
            ['label' => 'Cash Balance', 'value' => $money($cards['cash_balance']), 'tone' => 'text-gray-900'],
            ['label' => 'Bank Balance', 'value' => $money($cards['bank_balance']), 'tone' => 'text-gray-900'],
            ['label' => 'Pending Customer Collection', 'value' => $money($cards['pending_customer_collection']), 'tone' => 'text-amber-700'],
            ['label' => 'Supplier Payable', 'value' => $money($cards['supplier_payable']), 'tone' => 'text-red-700'],
            ['label' => 'Stock Value', 'value' => $money($cards['stock_value']), 'tone' => 'text-gray-900'],
            ['label' => 'Total Expense', 'value' => $money($cards['total_expense']), 'tone' => 'text-red-700'],
            ['label' => 'Active Loans', 'value' => $money($cards['active_loans']), 'tone' => 'text-red-700'],
            ['label' => 'Partner Investment', 'value' => $money($cards['partner_investment']), 'tone' => 'text-gray-900'],
            ['label' => 'Net Profit', 'value' => $money($cards['net_profit']), 'tone' => $cards['net_profit'] >= 0 ? 'text-emerald-700' : 'text-red-700'],
        ];
    @endphp

    <div class="mb-5 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h2 class="text-lg font-semibold text-gray-900">Business Summary</h2>
            <p class="text-sm text-gray-500">Selected period: {{ $filters['label'] }}</p>
        </div>

        <form method="GET" action="{{ route('dashboard') }}" class="rounded bg-white p-4 shadow">
            <div class="grid grid-cols-1 gap-3 md:grid-cols-5">
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Period</label>
                    <select name="period" class="w-full rounded border-gray-300 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500">
                        <option value="today" @selected($filters['period'] === 'today')>Today</option>
                        <option value="this_month" @selected($filters['period'] === 'this_month')>This Month</option>
                        <option value="custom" @selected($filters['period'] === 'custom')>Custom</option>
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">From</label>
                    <input type="date" name="from_date" value="{{ $filters['from_date'] }}" class="w-full rounded border-gray-300 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">To</label>
                    <input type="date" name="to_date" value="{{ $filters['to_date'] }}" class="w-full rounded border-gray-300 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500">
                </div>
                <div class="flex items-end">
                    <button class="w-full rounded bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">Apply</button>
                </div>
                <div class="flex items-end">
                    <a href="{{ route('dashboard') }}" class="w-full rounded border border-gray-300 px-4 py-2 text-center text-sm font-semibold text-gray-700 hover:bg-gray-50">Reset</a>
                </div>
            </div>
        </form>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($cardItems as $item)
            <div class="rounded bg-white p-5 shadow">
                <p class="text-sm font-medium text-gray-500">{{ $item['label'] }}</p>
                <h3 class="mt-2 text-2xl font-bold {{ $item['tone'] }}">{{ $item['value'] }}</h3>
            </div>
        @endforeach
    </div>

    <div class="mt-6 grid grid-cols-1 gap-5 xl:grid-cols-2">
        <div class="rounded bg-white p-5 shadow">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-600">Monthly Sales</h3>
                <span class="text-xs text-gray-500">{{ $filters['label'] }}</span>
            </div>
            <div class="h-72">
                @if ($chartEmpty($charts['monthly_sales']))
                    <div class="flex h-full items-center justify-center rounded border border-dashed border-gray-200 text-sm text-gray-500">No sales data for selected period.</div>
                @else
                    <canvas id="monthlySalesChart"></canvas>
                @endif
            </div>
        </div>

        <div class="rounded bg-white p-5 shadow">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-600">Monthly Purchases</h3>
                <span class="text-xs text-gray-500">{{ $filters['label'] }}</span>
            </div>
            <div class="h-72">
                @if ($chartEmpty($charts['monthly_purchases']))
                    <div class="flex h-full items-center justify-center rounded border border-dashed border-gray-200 text-sm text-gray-500">No purchase data for selected period.</div>
                @else
                    <canvas id="monthlyPurchaseChart"></canvas>
                @endif
            </div>
        </div>

        <div class="rounded bg-white p-5 shadow">
            <h3 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-600">GST vs Non-GST Sales</h3>
            <div class="h-72">
                @if ($chartEmpty($charts['gst_split']))
                    <div class="flex h-full items-center justify-center rounded border border-dashed border-gray-200 text-sm text-gray-500">No sales split data for selected period.</div>
                @else
                    <canvas id="gstSplitChart"></canvas>
                @endif
            </div>
        </div>

        <div class="rounded bg-white p-5 shadow">
            <h3 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-600">Cash In vs Cash Out</h3>
            <div class="h-72">
                @if ($chartEmpty($charts['cash_flow']))
                    <div class="flex h-full items-center justify-center rounded border border-dashed border-gray-200 text-sm text-gray-500">No cash or bank movement for selected period.</div>
                @else
                    <canvas id="cashFlowChart"></canvas>
                @endif
            </div>
        </div>

        <div class="rounded bg-white p-5 shadow xl:col-span-2">
            <h3 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-600">Top Selling Products</h3>
            <div class="h-80">
                @if ($chartEmpty($charts['top_products']))
                    <div class="flex h-full items-center justify-center rounded border border-dashed border-gray-200 text-sm text-gray-500">No product sales for selected period.</div>
                @else
                    <canvas id="topProductsChart"></canvas>
                @endif
            </div>
        </div>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-5 xl:grid-cols-2">
        <div class="overflow-hidden rounded bg-white shadow">
            <div class="border-b border-gray-100 px-4 py-3">
                <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-600">Recent Sales</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-slate-100 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                        <tr>
                            <th class="px-4 py-3">Invoice</th>
                            <th class="px-4 py-3">Customer</th>
                            <th class="px-4 py-3">Type</th>
                            <th class="px-4 py-3 text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($tables['recent_sales'] as $sale)
                            <tr>
                                <td class="px-4 py-3 font-medium text-gray-900">{{ $sale->sale_no }}</td>
                                <td class="px-4 py-3 text-gray-700">{{ $sale->customer?->name ?: '-' }}</td>
                                <td class="px-4 py-3 text-gray-700">{{ strtoupper(str_replace('_', '-', $sale->bill_type)) }}</td>
                                <td class="px-4 py-3 text-right font-semibold text-gray-900">{{ $money($sale->total_amount) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-4 py-8 text-center text-gray-500">No sales in selected period.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="overflow-hidden rounded bg-white shadow">
            <div class="border-b border-gray-100 px-4 py-3">
                <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-600">Recent Purchases</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-slate-100 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                        <tr>
                            <th class="px-4 py-3">Purchase</th>
                            <th class="px-4 py-3">Supplier</th>
                            <th class="px-4 py-3">Type</th>
                            <th class="px-4 py-3 text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($tables['recent_purchases'] as $purchase)
                            <tr>
                                <td class="px-4 py-3 font-medium text-gray-900">{{ $purchase->purchase_no }}</td>
                                <td class="px-4 py-3 text-gray-700">{{ $purchase->supplier?->name ?: '-' }}</td>
                                <td class="px-4 py-3 text-gray-700">{{ strtoupper(str_replace('_', '-', $purchase->bill_type)) }}</td>
                                <td class="px-4 py-3 text-right font-semibold text-gray-900">{{ $money($purchase->total_amount) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-4 py-8 text-center text-gray-500">No purchases in selected period.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="overflow-hidden rounded bg-white shadow">
            <div class="border-b border-gray-100 px-4 py-3">
                <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-600">Low Stock Products</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-slate-100 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                        <tr>
                            <th class="px-4 py-3">Product</th>
                            <th class="px-4 py-3">Category</th>
                            <th class="px-4 py-3 text-right">Stock</th>
                            <th class="px-4 py-3 text-right">Value</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($tables['low_stock_products'] as $product)
                            <tr>
                                <td class="px-4 py-3 font-medium text-gray-900">{{ $product->name }}</td>
                                <td class="px-4 py-3 text-gray-700">{{ $product->category?->name ?: '-' }}</td>
                                <td class="px-4 py-3 text-right text-red-700">{{ $number($product->current_stock) }} {{ $product->unit }}</td>
                                <td class="px-4 py-3 text-right font-semibold text-gray-900">{{ $money((float) $product->current_stock * (float) $product->purchase_price) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-4 py-8 text-center text-gray-500">No low stock products.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="overflow-hidden rounded bg-white shadow">
            <div class="border-b border-gray-100 px-4 py-3">
                <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-600">Active Loans</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-slate-100 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                        <tr>
                            <th class="px-4 py-3">Loan</th>
                            <th class="px-4 py-3">Party</th>
                            <th class="px-4 py-3">Type</th>
                            <th class="px-4 py-3 text-right">Balance</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($tables['active_loans'] as $loan)
                            <tr>
                                <td class="px-4 py-3 font-medium text-gray-900">{{ $loan->loan_no }}</td>
                                <td class="px-4 py-3 text-gray-700">{{ $loan->party_name }}</td>
                                <td class="px-4 py-3 text-gray-700">{{ $loan->typeLabel() }}</td>
                                <td class="px-4 py-3 text-right font-semibold text-red-700">{{ $money($loan->balance_amount) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-4 py-8 text-center text-gray-500">No active loans.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="overflow-hidden rounded bg-white shadow">
            <div class="border-b border-gray-100 px-4 py-3">
                <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-600">Pending Customer Payments</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-slate-100 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                        <tr>
                            <th class="px-4 py-3">Invoice</th>
                            <th class="px-4 py-3">Customer</th>
                            <th class="px-4 py-3 text-right">Balance</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($tables['pending_customer_payments'] as $sale)
                            <tr>
                                <td class="px-4 py-3 font-medium text-gray-900">{{ $sale->sale_no }}</td>
                                <td class="px-4 py-3 text-gray-700">{{ $sale->customer?->name ?: '-' }}</td>
                                <td class="px-4 py-3 text-right font-semibold text-red-700">{{ $money($sale->balance_amount) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="px-4 py-8 text-center text-gray-500">No pending customer payments.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="overflow-hidden rounded bg-white shadow">
            <div class="border-b border-gray-100 px-4 py-3">
                <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-600">Pending Supplier Payments</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-slate-100 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                        <tr>
                            <th class="px-4 py-3">Purchase</th>
                            <th class="px-4 py-3">Supplier</th>
                            <th class="px-4 py-3 text-right">Balance</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($tables['pending_supplier_payments'] as $purchase)
                            <tr>
                                <td class="px-4 py-3 font-medium text-gray-900">{{ $purchase->purchase_no }}</td>
                                <td class="px-4 py-3 text-gray-700">{{ $purchase->supplier?->name ?: '-' }}</td>
                                <td class="px-4 py-3 text-right font-semibold text-red-700">{{ $money($purchase->balance_amount) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="px-4 py-8 text-center text-gray-500">No pending supplier payments.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const charts = @json($charts);
            const commonOptions = {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { labels: { boxWidth: 12 } }
                },
                scales: {
                    y: { beginAtZero: true }
                }
            };

            function renderChart(id, config) {
                const element = document.getElementById(id);
                if (!element || !window.Chart) {
                    return;
                }

                new window.Chart(element, config);
            }

            renderChart('monthlySalesChart', {
                type: 'line',
                data: {
                    labels: charts.monthly_sales.labels,
                    datasets: [{
                        label: 'Sales',
                        data: charts.monthly_sales.data,
                        borderColor: '#047857',
                        backgroundColor: 'rgba(4, 120, 87, 0.12)',
                        tension: 0.3,
                        fill: true
                    }]
                },
                options: commonOptions
            });

            renderChart('monthlyPurchaseChart', {
                type: 'line',
                data: {
                    labels: charts.monthly_purchases.labels,
                    datasets: [{
                        label: 'Purchases',
                        data: charts.monthly_purchases.data,
                        borderColor: '#b91c1c',
                        backgroundColor: 'rgba(185, 28, 28, 0.12)',
                        tension: 0.3,
                        fill: true
                    }]
                },
                options: commonOptions
            });

            renderChart('gstSplitChart', {
                type: 'doughnut',
                data: {
                    labels: charts.gst_split.labels,
                    datasets: [{
                        data: charts.gst_split.data,
                        backgroundColor: ['#2563eb', '#64748b']
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false }
            });

            renderChart('cashFlowChart', {
                type: 'bar',
                data: {
                    labels: charts.cash_flow.labels,
                    datasets: [{
                        label: 'Amount',
                        data: charts.cash_flow.data,
                        backgroundColor: ['#059669', '#dc2626', '#0f766e', '#b91c1c']
                    }]
                },
                options: commonOptions
            });

            renderChart('topProductsChart', {
                type: 'bar',
                data: {
                    labels: charts.top_products.labels,
                    datasets: [{
                        label: 'Sold Quantity',
                        data: charts.top_products.data,
                        backgroundColor: '#334155'
                    }]
                },
                options: {
                    ...commonOptions,
                    indexAxis: 'y'
                }
            });
        });
    </script>
@endsection
