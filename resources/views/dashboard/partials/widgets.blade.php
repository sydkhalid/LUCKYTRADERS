@php
    $money = fn ($value) => ($erpCurrency['symbol'] ?? 'Rs.').' '.number_format((float) $value, 2);
    $collectionRate = $cards['period_sales'] > 0 ? min(100, round(((float) $cards['period_collection'] / (float) $cards['period_sales']) * 100, 1)) : 0;
    $expenseRate = $cards['period_sales'] > 0 ? min(100, round(((float) $cards['total_expense'] / (float) $cards['period_sales']) * 100, 1)) : 0;
    $stockRisk = $cards['product_count'] > 0 ? min(100, round(((int) $cards['low_stock_count'] / (int) $cards['product_count']) * 100, 1)) : 0;
    $netWorth = (float) $cards['cash_balance'] + (float) $cards['bank_balance'] + (float) $cards['stock_value'] + (float) $cards['pending_customer_collection'] - (float) $cards['supplier_payable'];
@endphp

<div class="lt-widget-grid">
    <section class="lt-dashboard-widget">
        <div class="lt-widget-icon lt-widget-icon-profit">
            <svg width="22" height="22" viewBox="0 0 24 24" aria-hidden="true">
                <path d="M4 18 10 9l4 4 6-8M16 5h4v4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
        </div>
        <div class="min-w-0">
            <p class="lt-widget-label">Profit Summary</p>
            <h3 class="lt-widget-value">{{ $money($cards['net_profit']) }}</h3>
            <p class="lt-widget-copy">{{ number_format((float) $cards['profit_margin'], 2) }}% margin after expenses</p>
        </div>
    </section>

    <section class="lt-dashboard-widget">
        <div class="lt-widget-icon lt-widget-icon-cash">
            <svg width="22" height="22" viewBox="0 0 24 24" aria-hidden="true">
                <path d="M4 7h16v10H4V7Zm8 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4ZM8 7a4 4 0 0 1-4 4m16 0a4 4 0 0 1-4-4M8 17a4 4 0 0 0-4-4m16 0a4 4 0 0 0-4 4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
            </svg>
        </div>
        <div class="min-w-0">
            <p class="lt-widget-label">Cash Position</p>
            <h3 class="lt-widget-value">{{ $money((float) $cards['cash_balance'] + (float) $cards['bank_balance']) }}</h3>
            <p class="lt-widget-copy">Cash {{ $money($cards['cash_balance']) }} | Bank {{ $money($cards['bank_balance']) }}</p>
        </div>
    </section>

    <section class="lt-dashboard-widget">
        <div class="lt-widget-icon lt-widget-icon-stock">
            <svg width="22" height="22" viewBox="0 0 24 24" aria-hidden="true">
                <path d="m12 3 8 4.5v9L12 21l-8-4.5v-9L12 3Zm0 9 8-4.5M12 12 4 7.5M12 12v9" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round" />
            </svg>
        </div>
        <div class="min-w-0">
            <div class="d-flex align-items-center justify-content-between gap-3">
                <p class="lt-widget-label mb-0">Stock Health</p>
                <span class="lt-widget-chip">{{ $cards['low_stock_count'] }} low</span>
            </div>
            <div class="lt-progress mt-3"><span style="width: {{ $stockRisk }}%"></span></div>
            <p class="lt-widget-copy mt-2">{{ $cards['product_count'] }} active products, {{ number_format((float) $cards['stock_units'], 3) }} units</p>
        </div>
    </section>

    <section class="lt-dashboard-widget">
        <div class="lt-widget-icon lt-widget-icon-collection">
            <svg width="22" height="22" viewBox="0 0 24 24" aria-hidden="true">
                <path d="M4 5h16v14H4V5Zm4 5h8M8 14h5M17 14h.01" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
            </svg>
        </div>
        <div class="min-w-0">
            <div class="d-flex align-items-center justify-content-between gap-3">
                <p class="lt-widget-label mb-0">Collection Rate</p>
                <span class="lt-widget-chip">{{ $collectionRate }}%</span>
            </div>
            <div class="lt-progress mt-3"><span style="width: {{ $collectionRate }}%"></span></div>
            <p class="lt-widget-copy mt-2">Collected {{ $money($cards['period_collection']) }} in selected period</p>
        </div>
    </section>

    <section class="lt-dashboard-widget">
        <div class="lt-widget-icon lt-widget-icon-risk">
            <svg width="22" height="22" viewBox="0 0 24 24" aria-hidden="true">
                <path d="M12 9v4m0 4h.01M10.3 4.3 2.7 18a2 2 0 0 0 1.7 3h15.2a2 2 0 0 0 1.7-3L13.7 4.3a2 2 0 0 0-3.4 0Z" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
            </svg>
        </div>
        <div class="min-w-0">
            <p class="lt-widget-label">Expense Pressure</p>
            <h3 class="lt-widget-value">{{ $expenseRate }}%</h3>
            <p class="lt-widget-copy">Expenses against selected period sales</p>
        </div>
    </section>

    <section class="lt-dashboard-widget">
        <div class="lt-widget-icon lt-widget-icon-worth">
            <svg width="22" height="22" viewBox="0 0 24 24" aria-hidden="true">
                <path d="M12 3v18M5 7h14M6 11h12M8 15h8M10 19h4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
            </svg>
        </div>
        <div class="min-w-0">
            <p class="lt-widget-label">Working Position</p>
            <h3 class="lt-widget-value">{{ $money($netWorth) }}</h3>
            <p class="lt-widget-copy">Cash, bank, stock and receivables less payable</p>
        </div>
    </section>
</div>
