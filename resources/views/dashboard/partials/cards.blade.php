@php
    $money = fn ($value) => ($erpCurrency['symbol'] ?? 'Rs.').' '.number_format((float) $value, 2);
    $number = fn ($value) => number_format((float) $value, 0);
    $cardItems = [
        ['label' => 'Today Sales', 'value' => $money($cards['today_sales']), 'hint' => 'Current day billing', 'icon' => 'sales', 'tone' => 'emerald'],
        ['label' => 'This Month Sales', 'value' => $money($cards['month_sales']), 'hint' => 'Month to date', 'icon' => 'trend', 'tone' => 'cyan'],
        ['label' => 'Period Sales', 'value' => $money($cards['period_sales']), 'hint' => $filters['label'], 'icon' => 'invoice', 'tone' => 'blue'],
        ['label' => 'Today Collection', 'value' => $money($cards['today_collection']), 'hint' => 'Cash and bank inflow', 'icon' => 'receipt', 'tone' => 'green'],
        ['label' => 'Today Purchase', 'value' => $money($cards['today_purchase']), 'hint' => 'Current day buying', 'icon' => 'cart', 'tone' => 'rose'],
        ['label' => 'This Month Purchase', 'value' => $money($cards['month_purchase']), 'hint' => 'Month to date', 'icon' => 'truck', 'tone' => 'orange'],
        ['label' => 'Outstanding Receivables', 'value' => $money($cards['pending_customer_collection']), 'hint' => 'Customer balance due', 'icon' => 'wallet', 'tone' => 'amber'],
        ['label' => 'Outstanding Payables', 'value' => $money($cards['supplier_payable']), 'hint' => 'Supplier balance due', 'icon' => 'payable', 'tone' => 'red'],
        ['label' => 'Cash in Hand', 'value' => $money($cards['cash_balance']), 'hint' => 'Cashbook net', 'icon' => 'cash', 'tone' => 'slate'],
        ['label' => 'Bank Balance', 'value' => $money($cards['bank_balance']), 'hint' => 'Bankbook net', 'icon' => 'bank', 'tone' => 'indigo'],
        ['label' => 'Stock Value', 'value' => $money($cards['stock_value']), 'hint' => $number($cards['stock_units']).' stock units', 'icon' => 'stock', 'tone' => 'violet'],
        ['label' => 'Total Expense', 'value' => $money($cards['total_expense']), 'hint' => 'Filtered period', 'icon' => 'expense', 'tone' => 'pink'],
        ['label' => 'Active Loans', 'value' => $money($cards['active_loans']), 'hint' => 'Open balances', 'icon' => 'loan', 'tone' => 'purple'],
        ['label' => 'Partner Investment', 'value' => $money($cards['partner_investment']), 'hint' => 'Capital balance', 'icon' => 'partner', 'tone' => 'teal'],
        ['label' => 'Net Profit', 'value' => $money($cards['net_profit']), 'hint' => number_format((float) $cards['profit_margin'], 2).'% margin', 'icon' => 'profit', 'tone' => $cards['net_profit'] >= 0 ? 'emerald' : 'red'],
    ];
    $icons = [
        'sales' => '<path d="M4 19V5m0 14h16M8 16V9m4 7V5m4 11v-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" />',
        'trend' => '<path d="m4 16 6-6 4 4 6-8M15 6h5v5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />',
        'invoice' => '<path d="M7 3h10a2 2 0 0 1 2 2v16l-3-2-3 2-3-2-3 2-3-2V5a2 2 0 0 1 2-2Zm3 6h6M10 13h6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" />',
        'receipt' => '<path d="M4 5a2 2 0 0 1 2-2h12v18l-3-2-3 2-3-2-3 2V5Zm4 5h8M8 14h5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" />',
        'cart' => '<path d="M3 4h2l2.2 10.4A2 2 0 0 0 9.2 16H17a2 2 0 0 0 1.9-1.4L21 8H7M10 20h.01M17 20h.01" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" />',
        'truck' => '<path d="M3 6h11v9H3V6Zm11 3h4l3 3v3h-7V9ZM7 19a2 2 0 1 0 0-4 2 2 0 0 0 0 4Zm11 0a2 2 0 1 0 0-4 2 2 0 0 0 0 4Z" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />',
        'wallet' => '<path d="M4 7h15a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H5a3 3 0 0 1-3-3V6a3 3 0 0 1 3-3h12v4M17 14h.01" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" />',
        'payable' => '<path d="M12 3v18m4-14.5A4 4 0 0 0 12 5c-2.2 0-4 1.1-4 2.7 0 4.1 8 1.9 8 6.2 0 1.7-1.8 3.1-4 3.1a5 5 0 0 1-4.5-2.2" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" />',
        'cash' => '<path d="M4 7h16v10H4V7Zm4 0a4 4 0 0 1-4 4m16 0a4 4 0 0 1-4-4m0 10a4 4 0 0 1 4-4M4 13a4 4 0 0 1 4 4m4-3a2 2 0 1 0 0-4 2 2 0 0 0 0 4Z" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" />',
        'bank' => '<path d="M3 10h18L12 4 3 10Zm3 0v8m4-8v8m4-8v8m4-8v8M4 20h16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />',
        'stock' => '<path d="m12 3 8 4.5v9L12 21l-8-4.5v-9L12 3Zm0 9 8-4.5M12 12 4 7.5M12 12v9" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round" />',
        'expense' => '<path d="M12 3v18m5-14H9.5a3.5 3.5 0 0 0 0 7H15a3.5 3.5 0 0 1 0 7H7" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" />',
        'loan' => '<path d="M4 18V8a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v10M7 10h10M7 14h6M3 20h18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" />',
        'partner' => '<path d="M16 11a4 4 0 1 0-8 0m8 0a4 4 0 1 1 4 4M8 11a4 4 0 1 0-4 4m4 2H6a4 4 0 0 0-4 4m20 0a4 4 0 0 0-4-4h-2m-8 0h8a4 4 0 0 1 4 4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" />',
        'profit' => '<path d="M4 18 10 9l4 4 6-8M16 5h4v4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />',
    ];
@endphp

<div class="lt-kpi-grid">
    @foreach ($cardItems as $item)
        <article class="lt-kpi-card lt-kpi-{{ $item['tone'] }}">
            <div class="lt-kpi-icon">
                <svg width="22" height="22" viewBox="0 0 24 24" aria-hidden="true">{!! $icons[$item['icon']] !!}</svg>
            </div>
            <div class="min-w-0">
                <p class="lt-kpi-label">{{ $item['label'] }}</p>
                <h3 class="lt-kpi-value">{{ $item['value'] }}</h3>
                <p class="lt-kpi-hint">{{ $item['hint'] }}</p>
            </div>
        </article>
    @endforeach
</div>
