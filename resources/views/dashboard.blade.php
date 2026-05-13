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
            ['key' => 'sales_vs_purchases', 'id' => 'purchaseVsSalesChart', 'title' => 'Sales vs Purchase', 'subtitle' => 'Filtered month comparison', 'empty' => 'No purchase or sales comparison for selected period.', 'span' => 'xl:col-span-2'],
            ['key' => 'cash_flow', 'id' => 'cashFlowChart', 'title' => 'Cash Flow', 'subtitle' => 'Cash and bank movement', 'empty' => 'No cash or bank movement for selected period.', 'span' => ''],
            ['key' => 'gst_split', 'id' => 'gstSplitChart', 'title' => 'GST vs Non-GST Sales', 'subtitle' => 'Invoice type split', 'empty' => 'No sales split data for selected period.', 'span' => ''],
            ['key' => 'monthly_sales', 'id' => 'monthlySalesChart', 'title' => 'Monthly Sales', 'subtitle' => 'Sales trend', 'empty' => 'No sales data for selected period.', 'span' => ''],
            ['key' => 'monthly_purchases', 'id' => 'monthlyPurchaseChart', 'title' => 'Monthly Purchases', 'subtitle' => 'Purchase trend', 'empty' => 'No purchase data for selected period.', 'span' => ''],
            ['key' => 'top_products', 'id' => 'topProductsChart', 'title' => 'Top Selling Products', 'subtitle' => 'Quantity sold', 'empty' => 'No product sales for selected period.', 'span' => 'xl:col-span-2'],
            ['key' => 'expense_categories', 'id' => 'expenseCategoryChart', 'title' => 'Expense Category', 'subtitle' => 'Spend concentration', 'empty' => 'No expense category data for selected period.', 'span' => ''],
            ['key' => 'stock_value', 'id' => 'stockValueChart', 'title' => 'Stock Report', 'subtitle' => 'Stock value by category', 'empty' => 'No stock value data available.', 'span' => ''],
            ['key' => 'pending_payments', 'id' => 'pendingPaymentsChart', 'title' => 'Pending Payments', 'subtitle' => 'Receivable, payable and loans', 'empty' => 'No pending payment data available.', 'span' => ''],
        ];
    @endphp

    <div
        class="lt-dashboard"
        data-dashboard
        data-dashboard-data-url="{{ route('dashboard.data') }}"
        data-dashboard-page-url="{{ route('dashboard') }}"
    >
        <section class="lt-dashboard-hero">
            <div class="lt-dashboard-hero-main">
                <span class="lt-dashboard-eyebrow">Steel Trading ERP</span>
                <h2>Lucky Traders command dashboard</h2>
                <p>Live sales, purchase, inventory, finance and collection signals for <span data-dashboard-range-label>{{ $filters['label'] }}</span>.</p>

                <div class="lt-dashboard-hero-stats">
                    <div>
                        <span>Total Receivable</span>
                        <strong>{{ $money($cards['pending_customer_collection']) }}</strong>
                    </div>
                    <div>
                        <span>Payable</span>
                        <strong>{{ $money($cards['supplier_payable']) }}</strong>
                    </div>
                    <div>
                        <span>Low Stock</span>
                        <strong>{{ $cards['low_stock_count'] }}</strong>
                    </div>
                </div>
            </div>

            <form method="GET" action="{{ route('dashboard') }}" class="lt-dashboard-filter" data-dashboard-filter>
                <div class="lt-filter-grid">
                    <label>
                        <span>Period</span>
                        <select name="period" data-dashboard-period>
                            @foreach ($periodOptions as $value => $label)
                                <option value="{{ $value }}" @selected($filters['period'] === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label>
                        <span>From</span>
                        <input type="date" name="from_date" value="{{ $filters['from_date'] }}" data-dashboard-date>
                    </label>
                    <label>
                        <span>To</span>
                        <input type="date" name="to_date" value="{{ $filters['to_date'] }}" data-dashboard-date>
                    </label>
                </div>

                <div class="lt-filter-actions">
                    <button type="submit" data-dashboard-submit>
                        <span class="lt-filter-spinner" aria-hidden="true"></span>
                        <span>Apply Filter</span>
                    </button>
                    <a href="{{ route('dashboard') }}" data-dashboard-reset>Reset</a>
                </div>
                <p class="lt-filter-status" data-dashboard-status>AJAX ready</p>
            </form>
        </section>

        <section class="lt-dashboard-section" data-dashboard-cards>
            @include('dashboard.partials.cards')
        </section>

        <section class="lt-dashboard-section" data-dashboard-widgets>
            @include('dashboard.partials.widgets')
        </section>

        <section class="lt-chart-grid" data-dashboard-chart-grid>
            @foreach ($chartCards as $chartCard)
                @php $isEmpty = $chartEmpty($charts[$chartCard['key']] ?? []); @endphp
                <article class="lt-chart-card {{ $chartCard['span'] }}">
                    <div class="lt-chart-card-header">
                        <div>
                            <h3>{{ $chartCard['title'] }}</h3>
                            <p>{{ $chartCard['subtitle'] }} | <span data-dashboard-range-label>{{ $filters['label'] }}</span></p>
                        </div>
                    </div>
                    <div class="lt-chart-shell" data-chart-shell="{{ $chartCard['key'] }}">
                        <canvas
                            id="{{ $chartCard['id'] }}"
                            data-dashboard-chart="{{ $chartCard['key'] }}"
                            class="{{ $isEmpty ? 'hidden' : '' }}"
                        ></canvas>
                        <div class="erp-empty-state {{ $isEmpty ? '' : 'hidden' }}" data-chart-empty="{{ $chartCard['key'] }}">{{ $chartCard['empty'] }}</div>
                    </div>
                </article>
            @endforeach
        </section>

        <section class="lt-dashboard-section" data-dashboard-tables>
            @include('dashboard.partials.tables')
        </section>

        <script type="application/json" data-dashboard-initial-charts>@json($charts)</script>
    </div>
@endsection
