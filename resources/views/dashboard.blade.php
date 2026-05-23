@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    @php
        $money = fn ($value) => ($erpCurrency['symbol'] ?? '₹').' '.number_format((float) $value, 2);
        $chartValues = function ($value) use (&$chartValues): array {
            if (is_array($value)) {
                return collect($value)
                    ->flatMap(fn ($item, $key) => $key === 'labels' ? [] : $chartValues($item))
                    ->all();
            }

            return is_numeric($value) ? [(float) $value] : [];
        };
        $chartEmpty = fn (array $dataset) => array_sum(array_map('abs', $chartValues($dataset))) <= 0;
        $periodOptions = [
            'today' => 'Today',
            'this_month' => 'This Month',
            'custom' => 'Custom Range',
        ];
        $chartCards = [
            ['key' => 'stacked_business_flow', 'id' => 'stackedBusinessFlowChart', 'engine' => 'apex', 'type' => 'Stacked Area', 'title' => 'Last Updates', 'subtitle' => 'Sales, collection, purchase and expenses', 'empty' => 'No business movement for selected period.', 'column' => 'col-12', 'size' => 'showcase'],
            ['key' => 'sales_vs_purchases', 'id' => 'purchaseVsSalesChart', 'engine' => 'apex', 'type' => 'Column', 'title' => 'Data Science', 'subtitle' => 'Sales and purchase comparison', 'empty' => 'No purchase or sales comparison for selected period.', 'column' => 'col-xl-8', 'size' => 'wide'],
            ['key' => 'monthly_sales', 'id' => 'monthlySalesChart', 'engine' => 'apex', 'type' => 'Column', 'title' => 'Latest Statistics', 'subtitle' => 'Monthly sales momentum', 'empty' => 'No sales data for selected period.', 'column' => 'col-md-6 col-xl-4', 'size' => 'compact'],
            ['key' => 'monthly_purchases', 'id' => 'monthlyPurchaseChart', 'engine' => 'apex', 'type' => 'Line', 'title' => 'Balance', 'subtitle' => 'Supplier buying movement', 'empty' => 'No purchase data for selected period.', 'column' => 'col-xl-8', 'size' => 'wide'],
            ['key' => 'cash_flow', 'id' => 'cashFlowChart', 'engine' => 'chartjs', 'type' => 'Doughnut', 'title' => 'Cash Flow Split', 'subtitle' => 'Cash and bank movement', 'empty' => 'No cash flow data for selected period.', 'column' => 'col-md-6 col-xl-4', 'size' => 'compact'],
            ['key' => 'top_products', 'id' => 'topProductsChart', 'engine' => 'apex', 'type' => 'Horizontal Bar', 'title' => 'Product Movement', 'subtitle' => 'Top selling products', 'empty' => 'No product sales for selected period.', 'column' => 'col-xl-6', 'size' => 'wide'],
            ['key' => 'stock_value', 'id' => 'stockValueChart', 'engine' => 'apex', 'type' => 'Horizontal Bar', 'title' => 'Stock Prices', 'subtitle' => 'Inventory value by category', 'empty' => 'No stock value data available.', 'column' => 'col-xl-6', 'size' => 'wide'],
            ['key' => 'gst_split', 'id' => 'gstSplitChart', 'engine' => 'apex', 'type' => 'Donut', 'title' => 'GST Split', 'subtitle' => 'GST and non-GST invoices', 'empty' => 'No sales split data for selected period.', 'column' => 'col-md-6 col-xl-4', 'size' => 'compact'],
            ['key' => 'pending_payments', 'id' => 'pendingPaymentsChart', 'engine' => 'chartjs', 'type' => 'Polar', 'title' => 'Statistics', 'subtitle' => 'Receivable, payable and loans', 'empty' => 'No pending payment data available.', 'column' => 'col-md-6 col-xl-4', 'size' => 'compact'],
            ['key' => 'period_business_mix', 'id' => 'periodBusinessMixChart', 'engine' => 'chartjs', 'type' => 'Pie', 'title' => 'Expense Ratio', 'subtitle' => 'Current period composition', 'empty' => 'No business movement for selected period.', 'column' => 'col-md-6 col-xl-4', 'size' => 'compact'],
            ['key' => 'profit_vs_expense', 'id' => 'profitExpenseChart', 'engine' => 'chartjs', 'type' => 'Radar', 'title' => 'Mobile Comparison', 'subtitle' => 'Gross, expense and net position', 'empty' => 'No profit or expense data for selected period.', 'column' => 'col-md-6 col-xl-4', 'size' => 'compact'],
            ['key' => 'expense_categories', 'id' => 'expenseCategoriesChart', 'engine' => 'apex', 'type' => 'Bar', 'title' => 'Expense Categories', 'subtitle' => 'Spending by category', 'empty' => 'No expense category data for selected period.', 'column' => 'col-md-6 col-xl-4', 'size' => 'compact'],
        ];
    @endphp

    <div
        class="lt-dashboard sneat-dashboard"
        data-dashboard
        data-dashboard-data-url="{{ route('dashboard.data') }}"
        data-dashboard-page-url="{{ route('dashboard') }}"
    >
        <div class="row g-4">
            <div class="col-xl-8">
                <section class="card lt-dashboard-hero sneat-welcome-card h-100">
                    <div class="card-body p-4 p-lg-5">
                        <div class="row align-items-center g-4">
                            <div class="col-lg-8">
                                <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                                    <span class="badge bg-label-primary">Steel Trading ERP</span>
                                    <span class="sneat-live-dot"><span></span> Live dashboard</span>
                                </div>
                                <h2 class="mb-2">Lucky Traders command center</h2>
                                <p class="mb-0 text-muted">Live sales, purchase, inventory, finance and collection signals for <span class="fw-semibold text-primary" data-dashboard-range-label>{{ $filters['label'] }}</span>.</p>

                                <div class="sneat-hero-actions">
                                    @can('manage_sales')
                                        <a href="{{ route('sales.create') }}" class="btn btn-primary">
                                            <span>New Invoice</span>
                                        </a>
                                    @endcan
                                    @can('manage_purchases')
                                        <a href="{{ route('purchases.create') }}" class="btn btn-outline-primary">
                                            <span>Add Purchase</span>
                                        </a>
                                    @endcan
                                    @can('manage_receipts')
                                        <a href="{{ route('receipts.create') }}" class="btn btn-outline-secondary">
                                            <span>Receipt</span>
                                        </a>
                                    @endcan
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="sneat-hero-figure ms-lg-auto" aria-hidden="true">
                                    <div class="sneat-hero-panel">
                                        <span class="sneat-hero-figure-mark">LT</span>
                                        <div class="sneat-hero-pulse-row">
                                            <span>Sales</span>
                                            <strong>{{ $money($cards['period_sales']) }}</strong>
                                        </div>
                                        <div class="sneat-hero-pulse-row">
                                            <span>Stock</span>
                                            <strong>{{ $money($cards['stock_value']) }}</strong>
                                        </div>
                                        <div class="sneat-hero-pulse-row">
                                            <span>Profit</span>
                                            <strong>{{ $money($cards['net_profit']) }}</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3 mt-3 lt-dashboard-hero-stats">
                            <div class="col-sm-4">
                                <div class="sneat-mini-stat">
                                    <span class="sneat-mini-label">Total Receivable</span>
                                    <strong>{{ $money($cards['pending_customer_collection']) }}</strong>
                                    <small>Customer collection pending</small>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="sneat-mini-stat">
                                    <span class="sneat-mini-label">Payable</span>
                                    <strong>{{ $money($cards['supplier_payable']) }}</strong>
                                    <small>Supplier balance due</small>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="sneat-mini-stat">
                                    <span class="sneat-mini-label">Low Stock</span>
                                    <strong>{{ $cards['low_stock_count'] }}</strong>
                                    <small>Products need review</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>

            <div class="col-xl-4">
                <form method="GET" action="{{ route('dashboard') }}" class="card lt-dashboard-filter sneat-filter-card h-100" data-dashboard-filter>
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-3">
                            <span class="avatar rounded bg-label-primary sneat-filter-icon">
                                <svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M3 5h18"></path>
                                    <path d="M6 12h12"></path>
                                    <path d="M10 19h4"></path>
                                </svg>
                            </span>
                            <div>
                            <h5 class="card-title mb-0">Dashboard Filter</h5>
                            <small class="text-muted">AJAX updates without reload</small>
                            </div>
                        </div>
                        <span class="badge bg-label-info" data-dashboard-status>Ready</span>
                    </div>
                    <div class="card-body">
                        <div class="row g-3 lt-filter-grid">
                            <div class="col-12">
                                <label class="form-label">Period</label>
                                <select name="period" class="form-select" data-dashboard-period>
                                    @foreach ($periodOptions as $value => $label)
                                        <option value="{{ $value }}" @selected($filters['period'] === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label">From</label>
                                <input type="date" name="from_date" value="{{ $filters['from_date'] }}" class="form-control" data-dashboard-date>
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label">To</label>
                                <input type="date" name="to_date" value="{{ $filters['to_date'] }}" class="form-control" data-dashboard-date>
                            </div>
                        </div>

                        <div class="d-flex flex-wrap gap-2 mt-4 lt-filter-actions">
                            <button type="submit" class="btn btn-primary flex-fill" data-dashboard-submit>
                                <span class="lt-filter-spinner" aria-hidden="true"></span>
                                <span>Apply Filter</span>
                            </button>
                            <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary flex-fill" data-dashboard-reset>Reset</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <section class="lt-dashboard-section mt-4" data-dashboard-cards>
            @include('dashboard.partials.cards')
        </section>

        <section class="lt-dashboard-section mt-4" data-dashboard-widgets>
            @include('dashboard.partials.widgets')
        </section>

        <section class="row g-4 mt-0 lt-chart-grid" data-dashboard-chart-grid>
            @foreach ($chartCards as $chartCard)
                @php $isEmpty = $chartEmpty($charts[$chartCard['key']] ?? []); @endphp
                <div class="{{ $chartCard['column'] }}">
                    <article class="card lt-chart-card sneat-chart-card h-100" data-chart-card="{{ $chartCard['key'] }}" data-chart-size="{{ $chartCard['size'] }}">
                        <div class="card-header lt-chart-card-header">
                            <div>
                                <h5 class="card-title mb-1">{{ $chartCard['title'] }}</h5>
                                <small class="text-muted">{{ $chartCard['subtitle'] }} | <span data-dashboard-range-label>{{ $filters['label'] }}</span></small>
                            </div>
                            <div class="lt-chart-actions" title="{{ $chartCard['type'] }}" aria-label="{{ $chartCard['type'] }} chart">
                                <span class="lt-chart-action-icon" aria-hidden="true">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="3" y="4" width="18" height="18" rx="2"></rect>
                                        <path d="M16 2v4"></path>
                                        <path d="M8 2v4"></path>
                                        <path d="M3 10h18"></path>
                                    </svg>
                                </span>
                                <span class="lt-chart-action-icon" aria-hidden="true">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="m6 9 6 6 6-6"></path>
                                    </svg>
                                </span>
                            </div>
                        </div>
                        <div class="card-body lt-chart-shell" data-chart-shell="{{ $chartCard['key'] }}">
                            @if ($chartCard['engine'] === 'chartjs')
                                <canvas
                                    id="{{ $chartCard['id'] }}"
                                    data-dashboard-chart="{{ $chartCard['key'] }}"
                                    data-dashboard-chart-engine="chartjs"
                                    class="{{ $isEmpty ? 'hidden' : '' }}"
                                ></canvas>
                            @else
                                <div
                                    id="{{ $chartCard['id'] }}"
                                    data-dashboard-chart="{{ $chartCard['key'] }}"
                                    data-dashboard-chart-engine="apex"
                                    class="lt-apex-chart {{ $isEmpty ? 'hidden' : '' }}"
                                ></div>
                            @endif
                            <div class="erp-empty-state {{ $isEmpty ? '' : 'hidden' }}" data-chart-empty="{{ $chartCard['key'] }}">{{ $chartCard['empty'] }}</div>
                        </div>
                    </article>
                </div>
            @endforeach
        </section>

        <section class="lt-dashboard-section mt-4" data-dashboard-tables>
            @include('dashboard.partials.tables')
        </section>

        <script type="application/json" data-dashboard-initial-charts>@json($charts)</script>
    </div>
@endsection
