@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    @php
        $money = fn ($value) => ($erpCurrency['symbol'] ?? 'Rs.').' '.number_format((float) $value, 2);
        $chartEmpty = fn (array $dataset) => array_sum(array_map('floatval', $dataset['data'] ?? [])) <= 0;
        $periodOptions = [
            'today' => 'Today',
            'this_month' => 'This Month',
            'custom' => 'Custom Range',
        ];
        $chartCards = [
            ['key' => 'sales_vs_purchases', 'id' => 'purchaseVsSalesChart', 'title' => 'Sales vs Purchase', 'subtitle' => 'Filtered month comparison', 'empty' => 'No purchase or sales comparison for selected period.', 'column' => 'col-xl-8'],
            ['key' => 'cash_flow', 'id' => 'cashFlowChart', 'title' => 'Cash Flow', 'subtitle' => 'Cash and bank movement', 'empty' => 'No cash or bank movement for selected period.', 'column' => 'col-xl-4'],
            ['key' => 'gst_split', 'id' => 'gstSplitChart', 'title' => 'GST vs Non-GST Sales', 'subtitle' => 'Invoice type split', 'empty' => 'No sales split data for selected period.', 'column' => 'col-md-6 col-xl-4'],
            ['key' => 'monthly_sales', 'id' => 'monthlySalesChart', 'title' => 'Monthly Sales', 'subtitle' => 'Sales trend', 'empty' => 'No sales data for selected period.', 'column' => 'col-md-6 col-xl-4'],
            ['key' => 'monthly_purchases', 'id' => 'monthlyPurchaseChart', 'title' => 'Monthly Purchases', 'subtitle' => 'Purchase trend', 'empty' => 'No purchase data for selected period.', 'column' => 'col-md-6 col-xl-4'],
            ['key' => 'top_products', 'id' => 'topProductsChart', 'title' => 'Top Selling Products', 'subtitle' => 'Quantity sold', 'empty' => 'No product sales for selected period.', 'column' => 'col-xl-8'],
            ['key' => 'expense_categories', 'id' => 'expenseCategoryChart', 'title' => 'Expense Category', 'subtitle' => 'Spend concentration', 'empty' => 'No expense category data for selected period.', 'column' => 'col-md-6 col-xl-4'],
            ['key' => 'stock_value', 'id' => 'stockValueChart', 'title' => 'Stock Report', 'subtitle' => 'Stock value by category', 'empty' => 'No stock value data available.', 'column' => 'col-md-6 col-xl-4'],
            ['key' => 'pending_payments', 'id' => 'pendingPaymentsChart', 'title' => 'Pending Payments', 'subtitle' => 'Receivable, payable and loans', 'empty' => 'No pending payment data available.', 'column' => 'col-md-6 col-xl-4'],
            ['key' => 'period_business_mix', 'id' => 'periodBusinessMixChart', 'title' => 'Business Mix', 'subtitle' => 'Sales, purchase, collection and expenses', 'empty' => 'No business movement for selected period.', 'column' => 'col-xl-8'],
            ['key' => 'profit_vs_expense', 'id' => 'profitExpenseChart', 'title' => 'Profit vs Expense', 'subtitle' => 'Gross profit, expenses and net result', 'empty' => 'No profit or expense data for selected period.', 'column' => 'col-md-6 col-xl-4'],
            ['key' => 'stock_units_by_category', 'id' => 'stockUnitsByCategoryChart', 'title' => 'Stock Units', 'subtitle' => 'Inventory quantity by category', 'empty' => 'No stock unit data available.', 'column' => 'col-md-6 col-xl-4'],
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
                    <article class="card lt-chart-card sneat-chart-card h-100">
                        <div class="card-header lt-chart-card-header">
                            <div>
                                <h5 class="card-title mb-1">{{ $chartCard['title'] }}</h5>
                                <small class="text-muted">{{ $chartCard['subtitle'] }} | <span data-dashboard-range-label>{{ $filters['label'] }}</span></small>
                            </div>
                        </div>
                        <div class="card-body lt-chart-shell" data-chart-shell="{{ $chartCard['key'] }}">
                            <canvas
                                id="{{ $chartCard['id'] }}"
                                data-dashboard-chart="{{ $chartCard['key'] }}"
                                class="{{ $isEmpty ? 'hidden' : '' }}"
                            ></canvas>
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
