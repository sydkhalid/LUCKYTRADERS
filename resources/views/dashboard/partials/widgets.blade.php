@php
    $money = fn ($value) => ($erpCurrency['symbol'] ?? '₹').' '.number_format((float) $value, 2);
    $collectionRate = $cards['period_sales'] > 0 ? min(100, round(((float) $cards['period_collection'] / (float) $cards['period_sales']) * 100, 1)) : 0;
    $expenseRate = $cards['period_sales'] > 0 ? min(100, round(((float) $cards['total_expense'] / (float) $cards['period_sales']) * 100, 1)) : 0;
    $stockRisk = $cards['product_count'] > 0 ? min(100, round(((int) $cards['low_stock_count'] / (int) $cards['product_count']) * 100, 1)) : 0;
    $netWorth = (float) $cards['cash_balance'] + (float) $cards['bank_balance'] + (float) $cards['stock_value'] + (float) $cards['pending_customer_collection'] - (float) $cards['supplier_payable'];
@endphp

<div class="row g-4 lt-widget-grid">
    <div class="col-md-6 col-xl-4">
        <section class="card h-100 lt-dashboard-widget sneat-widget-card">
            <div class="card-body">
                <div class="d-flex gap-3">
                    <span class="avatar rounded bg-label-success lt-widget-icon lt-widget-icon-profit">
                        <svg width="22" height="22" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M4 18 10 9l4 4 6-8M16 5h4v4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </span>
                    <div class="min-w-0">
                        <p class="lt-widget-label mb-1">Profit Summary</p>
                        <h4 class="lt-widget-value mb-1">{{ $money($cards['net_profit']) }}</h4>
                        <span class="badge bg-label-success mb-2">{{ number_format((float) $cards['profit_margin'], 2) }}% margin</span>
                        <p class="lt-widget-copy mb-0">{{ number_format((float) $cards['profit_margin'], 2) }}% margin after expenses</p>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <div class="col-md-6 col-xl-4">
        <section class="card h-100 lt-dashboard-widget sneat-widget-card">
            <div class="card-body">
                <div class="d-flex gap-3">
                    <span class="avatar rounded bg-label-primary lt-widget-icon lt-widget-icon-cash">
                        <svg width="22" height="22" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M4 7h16v10H4V7Zm8 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4ZM8 7a4 4 0 0 1-4 4m16 0a4 4 0 0 1-4-4M8 17a4 4 0 0 0-4-4m16 0a4 4 0 0 0-4 4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                        </svg>
                    </span>
                    <div class="min-w-0">
                        <p class="lt-widget-label mb-1">Cash Position</p>
                        <h4 class="lt-widget-value mb-1">{{ $money((float) $cards['cash_balance'] + (float) $cards['bank_balance']) }}</h4>
                        <span class="badge bg-label-primary mb-2">Cash + bank</span>
                        <p class="lt-widget-copy mb-0">Cash {{ $money($cards['cash_balance']) }} | Bank {{ $money($cards['bank_balance']) }}</p>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <div class="col-md-6 col-xl-4">
        <section class="card h-100 lt-dashboard-widget sneat-widget-card">
            <div class="card-body">
                <div class="d-flex gap-3">
                    <span class="avatar rounded bg-label-success lt-widget-icon lt-widget-icon-stock">
                        <svg width="22" height="22" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="m12 3 8 4.5v9L12 21l-8-4.5v-9L12 3Zm0 9 8-4.5M12 12 4 7.5M12 12v9" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round" />
                        </svg>
                    </span>
                    <div class="min-w-0 flex-grow-1">
                        <div class="d-flex align-items-center justify-content-between gap-3">
                            <p class="lt-widget-label mb-0">Stock Health</p>
                            <span class="badge bg-label-warning lt-widget-chip">{{ $cards['low_stock_count'] }} low</span>
                        </div>
                        <div class="progress mt-3 lt-progress"><div class="progress-bar bg-warning" role="progressbar" style="width: {{ $stockRisk }}%" aria-valuenow="{{ $stockRisk }}" aria-valuemin="0" aria-valuemax="100"></div></div>
                        <p class="lt-widget-copy mt-2 mb-0">{{ $cards['product_count'] }} active products, {{ number_format((float) $cards['stock_units'], 3) }} units</p>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <div class="col-md-6 col-xl-4">
        <section class="card h-100 lt-dashboard-widget sneat-widget-card">
            <div class="card-body">
                <div class="d-flex gap-3">
                    <span class="avatar rounded bg-label-info lt-widget-icon lt-widget-icon-collection">
                        <svg width="22" height="22" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M4 5h16v14H4V5Zm4 5h8M8 14h5M17 14h.01" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                        </svg>
                    </span>
                    <div class="min-w-0 flex-grow-1">
                        <div class="d-flex align-items-center justify-content-between gap-3">
                            <p class="lt-widget-label mb-0">Collection Rate</p>
                            <span class="badge bg-label-info lt-widget-chip">{{ $collectionRate }}%</span>
                        </div>
                        <div class="progress mt-3 lt-progress"><div class="progress-bar bg-info" role="progressbar" style="width: {{ $collectionRate }}%" aria-valuenow="{{ $collectionRate }}" aria-valuemin="0" aria-valuemax="100"></div></div>
                        <p class="lt-widget-copy mt-2 mb-0">Collected {{ $money($cards['period_collection']) }} in selected period</p>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <div class="col-md-6 col-xl-4">
        <section class="card h-100 lt-dashboard-widget sneat-widget-card">
            <div class="card-body">
                <div class="d-flex gap-3">
                    <span class="avatar rounded bg-label-danger lt-widget-icon lt-widget-icon-risk">
                        <svg width="22" height="22" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M12 9v4m0 4h.01M10.3 4.3 2.7 18a2 2 0 0 0 1.7 3h15.2a2 2 0 0 0 1.7-3L13.7 4.3a2 2 0 0 0-3.4 0Z" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                        </svg>
                    </span>
                    <div class="min-w-0">
                        <p class="lt-widget-label mb-1">Expense Pressure</p>
                        <h4 class="lt-widget-value mb-1">{{ $expenseRate }}%</h4>
                        <span class="badge bg-label-danger mb-2">Cost control</span>
                        <p class="lt-widget-copy mb-0">Expenses against selected period sales</p>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <div class="col-md-6 col-xl-4">
        <section class="card h-100 lt-dashboard-widget sneat-widget-card">
            <div class="card-body">
                <div class="d-flex gap-3">
                    <span class="avatar rounded bg-label-secondary lt-widget-icon lt-widget-icon-worth">
                        <svg width="22" height="22" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M12 3v18M5 7h14M6 11h12M8 15h8M10 19h4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                        </svg>
                    </span>
                    <div class="min-w-0">
                        <p class="lt-widget-label mb-1">Working Position</p>
                        <h4 class="lt-widget-value mb-1">{{ $money($netWorth) }}</h4>
                        <span class="badge bg-label-secondary mb-2">Net operating view</span>
                        <p class="lt-widget-copy mb-0">Cash, bank, stock and receivables less payable</p>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>
