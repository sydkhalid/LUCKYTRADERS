@extends('layouts.erp')

@section('title', 'Dashboard')

@section('content')
    @php
        $money = fn ($value) => 'Rs. '.number_format((float) $value, 2);
        $number = fn ($value) => number_format((float) $value, 3);
        $chartEmpty = fn (array $dataset) => array_sum(array_map('floatval', $dataset['data'] ?? [])) <= 0;
        $cardItems = [
            ['label' => 'Today Sales', 'value' => $money($cards['today_sales']), 'tone' => 'text-emerald-700', 'accent' => 'from-emerald-500 to-teal-500', 'hint' => 'Current day billing'],
            ['label' => 'This Month Sales', 'value' => $money($cards['month_sales']), 'tone' => 'text-emerald-700', 'accent' => 'from-emerald-500 to-cyan-500', 'hint' => 'Month to date'],
            ['label' => 'Today Purchase', 'value' => $money($cards['today_purchase']), 'tone' => 'text-rose-700', 'accent' => 'from-rose-500 to-orange-500', 'hint' => 'Current day buying'],
            ['label' => 'This Month Purchase', 'value' => $money($cards['month_purchase']), 'tone' => 'text-rose-700', 'accent' => 'from-orange-500 to-amber-500', 'hint' => 'Month to date'],
            ['label' => 'Cash Balance', 'value' => $money($cards['cash_balance']), 'tone' => 'text-slate-950', 'accent' => 'from-slate-700 to-slate-500', 'hint' => 'Cashbook net'],
            ['label' => 'Bank Balance', 'value' => $money($cards['bank_balance']), 'tone' => 'text-slate-950', 'accent' => 'from-blue-600 to-cyan-500', 'hint' => 'Bankbook net'],
            ['label' => 'Pending Customer Collection', 'value' => $money($cards['pending_customer_collection']), 'tone' => 'text-amber-700', 'accent' => 'from-amber-500 to-yellow-500', 'hint' => 'Receivable balance'],
            ['label' => 'Supplier Payable', 'value' => $money($cards['supplier_payable']), 'tone' => 'text-rose-700', 'accent' => 'from-red-500 to-rose-500', 'hint' => 'Purchase payable'],
            ['label' => 'Stock Value', 'value' => $money($cards['stock_value']), 'tone' => 'text-slate-950', 'accent' => 'from-indigo-500 to-sky-500', 'hint' => 'Current stock value'],
            ['label' => 'Total Expense', 'value' => $money($cards['total_expense']), 'tone' => 'text-rose-700', 'accent' => 'from-fuchsia-500 to-rose-500', 'hint' => 'Filtered period'],
            ['label' => 'Active Loans', 'value' => $money($cards['active_loans']), 'tone' => 'text-rose-700', 'accent' => 'from-violet-500 to-indigo-500', 'hint' => 'Open balances'],
            ['label' => 'Partner Investment', 'value' => $money($cards['partner_investment']), 'tone' => 'text-slate-950', 'accent' => 'from-cyan-500 to-blue-500', 'hint' => 'Capital balance'],
            ['label' => 'Net Profit', 'value' => $money($cards['net_profit']), 'tone' => $cards['net_profit'] >= 0 ? 'text-emerald-700' : 'text-rose-700', 'accent' => $cards['net_profit'] >= 0 ? 'from-emerald-500 to-lime-500' : 'from-red-500 to-rose-500', 'hint' => 'Profit after expenses'],
        ];
    @endphp

    <div data-dashboard-charts="{{ route('dashboard.charts') }}" class="mb-6 rounded-3xl border border-slate-200 bg-white p-5 shadow-sm shadow-slate-200/70 lg:p-6">
        <div class="flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
            <div class="max-w-2xl">
                <p class="text-xs font-black uppercase tracking-[0.22em] text-cyan-700">Live Business Summary</p>
                <h2 class="mt-2 text-2xl font-black tracking-tight text-slate-950 sm:text-3xl">Lucky Traders command dashboard</h2>
                <p class="mt-2 text-sm font-medium text-slate-500">Selected period: {{ $filters['label'] }}</p>
            </div>

            <form method="GET" action="{{ route('dashboard') }}" class="w-full rounded-2xl border border-slate-200 bg-slate-50 p-4 xl:max-w-3xl">
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-5">
                    <div>
                        <label class="mb-1 block text-xs font-black uppercase tracking-wide text-slate-500">Period</label>
                        <select name="period" class="w-full">
                            <option value="today" @selected($filters['period'] === 'today')>Today</option>
                            <option value="this_month" @selected($filters['period'] === 'this_month')>This Month</option>
                            <option value="custom" @selected($filters['period'] === 'custom')>Custom</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-black uppercase tracking-wide text-slate-500">From</label>
                        <input type="date" name="from_date" value="{{ $filters['from_date'] }}" class="w-full">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-black uppercase tracking-wide text-slate-500">To</label>
                        <input type="date" name="to_date" value="{{ $filters['to_date'] }}" class="w-full">
                    </div>
                    <div class="flex items-end">
                        <button class="w-full rounded-xl bg-slate-950 px-4 py-2.5 text-sm font-black text-white shadow-sm hover:bg-slate-800">Apply</button>
                    </div>
                    <div class="flex items-end">
                        <a href="{{ route('dashboard') }}" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-center text-sm font-black text-slate-700 hover:bg-slate-50">Reset</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4 2xl:grid-cols-5">
        @foreach ($cardItems as $item)
            <div class="erp-summary-card overflow-hidden">
                <div class="flex items-start justify-between gap-4">
                    <div class="min-w-0">
                        <p class="truncate text-xs font-black uppercase tracking-[0.16em] text-slate-500">{{ $item['label'] }}</p>
                        <h3 class="mt-3 text-2xl font-black tracking-tight {{ $item['tone'] }}">{{ $item['value'] }}</h3>
                        <p class="mt-2 text-xs font-semibold text-slate-400">{{ $item['hint'] }}</p>
                    </div>
                    <div class="h-11 w-2 shrink-0 rounded-full bg-gradient-to-b {{ $item['accent'] }}"></div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-6 grid grid-cols-1 gap-5 xl:grid-cols-2">
        <div class="erp-panel">
            <div class="mb-4 flex items-center justify-between gap-3">
                <div>
                    <h3 class="text-sm font-black uppercase tracking-[0.16em] text-slate-600">Monthly Sales</h3>
                    <p class="mt-1 text-xs font-medium text-slate-400">{{ $filters['label'] }}</p>
                </div>
            </div>
            <div class="h-72">
                @if ($chartEmpty($charts['monthly_sales']))
                    <div class="erp-empty-state">No sales data for selected period.</div>
                @else
                    <canvas id="monthlySalesChart"></canvas>
                @endif
            </div>
        </div>

        <div class="erp-panel">
            <div class="mb-4 flex items-center justify-between gap-3">
                <div>
                    <h3 class="text-sm font-black uppercase tracking-[0.16em] text-slate-600">Monthly Purchases</h3>
                    <p class="mt-1 text-xs font-medium text-slate-400">{{ $filters['label'] }}</p>
                </div>
            </div>
            <div class="h-72">
                @if ($chartEmpty($charts['monthly_purchases']))
                    <div class="erp-empty-state">No purchase data for selected period.</div>
                @else
                    <canvas id="monthlyPurchaseChart"></canvas>
                @endif
            </div>
        </div>

        <div class="erp-panel">
            <h3 class="mb-4 text-sm font-black uppercase tracking-[0.16em] text-slate-600">GST vs Non-GST Sales</h3>
            <div class="h-72">
                @if ($chartEmpty($charts['gst_split']))
                    <div class="erp-empty-state">No sales split data for selected period.</div>
                @else
                    <canvas id="gstSplitChart"></canvas>
                @endif
            </div>
        </div>

        <div class="erp-panel">
            <h3 class="mb-4 text-sm font-black uppercase tracking-[0.16em] text-slate-600">Cash In vs Cash Out</h3>
            <div class="h-72">
                @if ($chartEmpty($charts['cash_flow']))
                    <div class="erp-empty-state">No cash or bank movement for selected period.</div>
                @else
                    <canvas id="cashFlowChart"></canvas>
                @endif
            </div>
        </div>

        <div class="erp-panel xl:col-span-2">
            <h3 class="mb-4 text-sm font-black uppercase tracking-[0.16em] text-slate-600">Top Selling Products</h3>
            <div class="h-80">
                @if ($chartEmpty($charts['top_products']))
                    <div class="erp-empty-state">No product sales for selected period.</div>
                @else
                    <canvas id="topProductsChart"></canvas>
                @endif
            </div>
        </div>

        <div class="erp-panel">
            <h3 class="mb-4 text-sm font-black uppercase tracking-[0.16em] text-slate-600">Purchase vs Sales</h3>
            <div class="h-72">
                @if ($chartEmpty($charts['sales_vs_purchases']))
                    <div class="erp-empty-state">No purchase or sales comparison for selected period.</div>
                @else
                    <canvas id="purchaseVsSalesChart"></canvas>
                @endif
            </div>
        </div>

        <div class="erp-panel">
            <h3 class="mb-4 text-sm font-black uppercase tracking-[0.16em] text-slate-600">Expense Category</h3>
            <div class="h-72">
                @if ($chartEmpty($charts['expense_categories']))
                    <div class="erp-empty-state">No expense category data for selected period.</div>
                @else
                    <canvas id="expenseCategoryChart"></canvas>
                @endif
            </div>
        </div>

        <div class="erp-panel">
            <h3 class="mb-4 text-sm font-black uppercase tracking-[0.16em] text-slate-600">Stock Value</h3>
            <div class="h-72">
                @if ($chartEmpty($charts['stock_value']))
                    <div class="erp-empty-state">No stock value data available.</div>
                @else
                    <canvas id="stockValueChart"></canvas>
                @endif
            </div>
        </div>

        <div class="erp-panel">
            <h3 class="mb-4 text-sm font-black uppercase tracking-[0.16em] text-slate-600">Pending Payments</h3>
            <div class="h-72">
                @if ($chartEmpty($charts['pending_payments']))
                    <div class="erp-empty-state">No pending payment data available.</div>
                @else
                    <canvas id="pendingPaymentsChart"></canvas>
                @endif
            </div>
        </div>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-5 xl:grid-cols-2">
        <div class="overflow-hidden rounded-2xl bg-white shadow">
            <div class="erp-panel-header">
                <h3 class="text-sm font-black uppercase tracking-[0.16em] text-slate-600">Recent Sales</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead>
                        <tr>
                            <th>Invoice</th>
                            <th>Customer</th>
                            <th>Type</th>
                            <th class="text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($tables['recent_sales'] as $sale)
                            <tr>
                                <td class="px-4 py-3 font-bold text-slate-950">{{ $sale->sale_no }}</td>
                                <td class="px-4 py-3 text-slate-700">{{ $sale->customer?->name ?: '-' }}</td>
                                <td class="px-4 py-3 text-slate-700">{{ strtoupper(str_replace('_', '-', $sale->bill_type)) }}</td>
                                <td class="px-4 py-3 text-right font-black text-slate-950">{{ $money($sale->total_amount) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4">No sales in selected period.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="overflow-hidden rounded-2xl bg-white shadow">
            <div class="erp-panel-header">
                <h3 class="text-sm font-black uppercase tracking-[0.16em] text-slate-600">Recent Purchases</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead>
                        <tr>
                            <th>Purchase</th>
                            <th>Supplier</th>
                            <th>Type</th>
                            <th class="text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($tables['recent_purchases'] as $purchase)
                            <tr>
                                <td class="px-4 py-3 font-bold text-slate-950">{{ $purchase->purchase_no }}</td>
                                <td class="px-4 py-3 text-slate-700">{{ $purchase->supplier?->name ?: '-' }}</td>
                                <td class="px-4 py-3 text-slate-700">{{ strtoupper(str_replace('_', '-', $purchase->bill_type)) }}</td>
                                <td class="px-4 py-3 text-right font-black text-slate-950">{{ $money($purchase->total_amount) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4">No purchases in selected period.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="overflow-hidden rounded-2xl bg-white shadow">
            <div class="erp-panel-header">
                <h3 class="text-sm font-black uppercase tracking-[0.16em] text-slate-600">Low Stock Products</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Category</th>
                            <th class="text-right">Stock</th>
                            <th class="text-right">Value</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($tables['low_stock_products'] as $product)
                            <tr>
                                <td class="px-4 py-3 font-bold text-slate-950">{{ $product->name }}</td>
                                <td class="px-4 py-3 text-slate-700">{{ $product->category?->name ?: '-' }}</td>
                                <td class="px-4 py-3 text-right font-semibold text-rose-700">{{ $number($product->current_stock) }} {{ $product->unit }}</td>
                                <td class="px-4 py-3 text-right font-black text-slate-950">{{ $money((float) $product->current_stock * (float) $product->purchase_price) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4">No low stock products.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="overflow-hidden rounded-2xl bg-white shadow">
            <div class="erp-panel-header">
                <h3 class="text-sm font-black uppercase tracking-[0.16em] text-slate-600">Active Loans</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead>
                        <tr>
                            <th>Loan</th>
                            <th>Party</th>
                            <th>Type</th>
                            <th class="text-right">Balance</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($tables['active_loans'] as $loan)
                            <tr>
                                <td class="px-4 py-3 font-bold text-slate-950">{{ $loan->loan_no }}</td>
                                <td class="px-4 py-3 text-slate-700">{{ $loan->party_name }}</td>
                                <td class="px-4 py-3 text-slate-700">{{ $loan->typeLabel() }}</td>
                                <td class="px-4 py-3 text-right font-black text-rose-700">{{ $money($loan->balance_amount) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4">No active loans.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="overflow-hidden rounded-2xl bg-white shadow">
            <div class="erp-panel-header">
                <h3 class="text-sm font-black uppercase tracking-[0.16em] text-slate-600">Pending Customer Payments</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead>
                        <tr>
                            <th>Invoice</th>
                            <th>Customer</th>
                            <th class="text-right">Balance</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($tables['pending_customer_payments'] as $sale)
                            <tr>
                                <td class="px-4 py-3 font-bold text-slate-950">{{ $sale->sale_no }}</td>
                                <td class="px-4 py-3 text-slate-700">{{ $sale->customer?->name ?: '-' }}</td>
                                <td class="px-4 py-3 text-right font-black text-rose-700">{{ $money($sale->balance_amount) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3">No pending customer payments.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="overflow-hidden rounded-2xl bg-white shadow">
            <div class="erp-panel-header">
                <h3 class="text-sm font-black uppercase tracking-[0.16em] text-slate-600">Pending Supplier Payments</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead>
                        <tr>
                            <th>Purchase</th>
                            <th>Supplier</th>
                            <th class="text-right">Balance</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($tables['pending_supplier_payments'] as $purchase)
                            <tr>
                                <td class="px-4 py-3 font-bold text-slate-950">{{ $purchase->purchase_no }}</td>
                                <td class="px-4 py-3 text-slate-700">{{ $purchase->supplier?->name ?: '-' }}</td>
                                <td class="px-4 py-3 text-right font-black text-rose-700">{{ $money($purchase->balance_amount) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3">No pending supplier payments.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            let charts = @json($charts);
            const renderedCharts = {};
            const commonOptions = {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { labels: { boxWidth: 12, color: '#475569', font: { weight: '600' } } }
                },
                scales: {
                    x: { grid: { color: 'rgba(148, 163, 184, 0.18)' }, ticks: { color: '#64748b' } },
                    y: { beginAtZero: true, grid: { color: 'rgba(148, 163, 184, 0.18)' }, ticks: { color: '#64748b' } }
                }
            };

            function renderChart(id, config) {
                const element = document.getElementById(id);
                if (!element || !window.Chart) {
                    return;
                }

                if (renderedCharts[id]) {
                    renderedCharts[id].destroy();
                }

                renderedCharts[id] = new window.Chart(element, config);
            }

            function renderDashboardCharts(nextCharts) {
                charts = nextCharts;

                renderChart('monthlySalesChart', {
                type: 'line',
                data: {
                    labels: charts.monthly_sales.labels,
                    datasets: [{
                        label: 'Sales',
                        data: charts.monthly_sales.data,
                        borderColor: '#059669',
                        backgroundColor: 'rgba(5, 150, 105, 0.12)',
                        tension: 0.35,
                        fill: true,
                        pointRadius: 3,
                        pointHoverRadius: 5
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
                        borderColor: '#e11d48',
                        backgroundColor: 'rgba(225, 29, 72, 0.12)',
                        tension: 0.35,
                        fill: true,
                        pointRadius: 3,
                        pointHoverRadius: 5
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
                        backgroundColor: ['#0891b2', '#64748b'],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '68%',
                    plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, color: '#475569', font: { weight: '600' } } } }
                }
                });

                renderChart('cashFlowChart', {
                type: 'bar',
                data: {
                    labels: charts.cash_flow.labels,
                    datasets: [{
                        label: 'Amount',
                        data: charts.cash_flow.data,
                        backgroundColor: ['#059669', '#e11d48', '#0891b2', '#f97316'],
                        borderRadius: 10
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
                        backgroundColor: '#0f766e',
                        borderRadius: 10
                    }]
                },
                options: {
                    ...commonOptions,
                    indexAxis: 'y'
                }
                });

                renderChart('purchaseVsSalesChart', {
                    type: 'bar',
                    data: {
                        labels: charts.sales_vs_purchases.labels,
                        datasets: [
                            {
                                label: 'Sales',
                                data: charts.sales_vs_purchases.sales,
                                backgroundColor: '#059669',
                                borderRadius: 10
                            },
                            {
                                label: 'Purchases',
                                data: charts.sales_vs_purchases.purchases,
                                backgroundColor: '#e11d48',
                                borderRadius: 10
                            }
                        ]
                    },
                    options: commonOptions
                });

                renderChart('expenseCategoryChart', {
                    type: 'doughnut',
                    data: {
                        labels: charts.expense_categories.labels,
                        datasets: [{
                            data: charts.expense_categories.data,
                            backgroundColor: ['#0f766e', '#0891b2', '#6366f1', '#f97316', '#e11d48', '#84cc16', '#a855f7', '#64748b'],
                            borderWidth: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '62%',
                        plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, color: '#475569', font: { weight: '600' } } } }
                    }
                });

                renderChart('stockValueChart', {
                    type: 'bar',
                    data: {
                        labels: charts.stock_value.labels,
                        datasets: [{
                            label: 'Stock Value',
                            data: charts.stock_value.data,
                            backgroundColor: '#0891b2',
                            borderRadius: 10
                        }]
                    },
                    options: { ...commonOptions, indexAxis: 'y' }
                });

                renderChart('pendingPaymentsChart', {
                    type: 'bar',
                    data: {
                        labels: charts.pending_payments.labels,
                        datasets: [{
                            label: 'Pending',
                            data: charts.pending_payments.data,
                            backgroundColor: ['#f59e0b', '#ef4444', '#6366f1'],
                            borderRadius: 10
                        }]
                    },
                    options: commonOptions
                });
            }

            renderDashboardCharts(charts);
            window.addEventListener('erp:dashboard-charts', function (event) {
                renderDashboardCharts(event.detail);
            });
        });
    </script>
@endsection
