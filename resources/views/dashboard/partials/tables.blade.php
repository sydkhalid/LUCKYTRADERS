@php
    $money = fn ($value) => ($erpCurrency['symbol'] ?? 'Rs.').' '.number_format((float) $value, 2);
    $number = fn ($value) => number_format((float) $value, 3);
@endphp

<div class="lt-dashboard-table-grid">
    <section class="lt-table-card">
        <div class="lt-table-card-header">
            <span class="lt-table-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true"><path d="M7 3h10a2 2 0 0 1 2 2v16l-3-2-3 2-3-2-3 2-3-2V5a2 2 0 0 1 2-2Zm3 6h6M10 13h6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" /></svg>
            </span>
            <h3>Recent Sales</h3>
        </div>
        <div class="lt-table-scroll">
            <table class="lt-dashboard-table">
                <thead><tr><th>Invoice</th><th>Customer</th><th>Type</th><th class="text-end">Total</th></tr></thead>
                <tbody>
                    @forelse ($tables['recent_sales'] as $sale)
                        <tr>
                            <td class="fw-black text-slate-950">{{ $sale->sale_no }}</td>
                            <td>{{ $sale->customer?->name ?: '-' }}</td>
                            <td>{{ strtoupper(str_replace('_', '-', $sale->bill_type)) }}</td>
                            <td class="text-end fw-black">{{ $money($sale->total_amount) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="lt-empty-row">No sales in selected period.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="lt-table-card">
        <div class="lt-table-card-header">
            <span class="lt-table-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true"><path d="M3 6h11v9H3V6Zm11 3h4l3 3v3h-7V9ZM7 19a2 2 0 1 0 0-4 2 2 0 0 0 0 4Zm11 0a2 2 0 1 0 0-4 2 2 0 0 0 0 4Z" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" /></svg>
            </span>
            <h3>Recent Purchases</h3>
        </div>
        <div class="lt-table-scroll">
            <table class="lt-dashboard-table">
                <thead><tr><th>Purchase</th><th>Supplier</th><th>Type</th><th class="text-end">Total</th></tr></thead>
                <tbody>
                    @forelse ($tables['recent_purchases'] as $purchase)
                        <tr>
                            <td class="fw-black text-slate-950">{{ $purchase->purchase_no }}</td>
                            <td>{{ $purchase->supplier?->name ?: '-' }}</td>
                            <td>{{ strtoupper(str_replace('_', '-', $purchase->bill_type)) }}</td>
                            <td class="text-end fw-black">{{ $money($purchase->total_amount) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="lt-empty-row">No purchases in selected period.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="lt-table-card">
        <div class="lt-table-card-header">
            <span class="lt-table-icon lt-table-icon-warning">
                <svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 9v4m0 4h.01M10.3 4.3 2.7 18a2 2 0 0 0 1.7 3h15.2a2 2 0 0 0 1.7-3L13.7 4.3a2 2 0 0 0-3.4 0Z" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" /></svg>
            </span>
            <h3>Low Stock Products</h3>
        </div>
        <div class="lt-table-scroll">
            <table class="lt-dashboard-table">
                <thead><tr><th>Product</th><th>Category</th><th class="text-end">Stock</th><th class="text-end">Value</th></tr></thead>
                <tbody>
                    @forelse ($tables['low_stock_products'] as $product)
                        <tr>
                            <td class="fw-black text-slate-950">{{ $product->name }}</td>
                            <td>{{ $product->category?->name ?: '-' }}</td>
                            <td class="text-end fw-bold text-danger">{{ $number($product->current_stock) }} {{ $product->unit }}</td>
                            <td class="text-end fw-black">{{ $money((float) $product->current_stock * (float) $product->purchase_price) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="lt-empty-row">No low stock products.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="lt-table-card">
        <div class="lt-table-card-header">
            <span class="lt-table-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true"><path d="M16 11a4 4 0 1 0-8 0m8 0a4 4 0 1 1 4 4M8 11a4 4 0 1 0-4 4m4 2H6a4 4 0 0 0-4 4m20 0a4 4 0 0 0-4-4h-2m-8 0h8a4 4 0 0 1 4 4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" /></svg>
            </span>
            <h3>Top Customers</h3>
        </div>
        <div class="lt-table-scroll">
            <table class="lt-dashboard-table">
                <thead><tr><th>Customer</th><th class="text-end">Invoices</th><th class="text-end">Sales</th></tr></thead>
                <tbody>
                    @forelse ($tables['top_customers'] as $customer)
                        <tr>
                            <td class="fw-black text-slate-950">{{ $customer->name }}</td>
                            <td class="text-end">{{ $customer->invoices_count }}</td>
                            <td class="text-end fw-black">{{ $money($customer->total_amount) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="lt-empty-row">No customer sales in selected period.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="lt-table-card">
        <div class="lt-table-card-header">
            <span class="lt-table-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true"><path d="M4 5h16v14H4V5Zm4 5h8M8 14h5M17 14h.01" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" /></svg>
            </span>
            <h3>Recent Payments</h3>
        </div>
        <div class="lt-table-scroll">
            <table class="lt-dashboard-table">
                <thead><tr><th>No</th><th>Party</th><th>Mode</th><th class="text-end">Amount</th></tr></thead>
                <tbody>
                    @forelse ($tables['recent_payments'] as $payment)
                        <tr>
                            <td class="fw-black text-slate-950">{{ $payment->payment_no }}</td>
                            <td>{{ $payment->party_name }}</td>
                            <td>{{ strtoupper($payment->payment_mode) }}</td>
                            <td class="text-end fw-black">{{ $money($payment->amount) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="lt-empty-row">No recent payments in selected period.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="lt-table-card">
        <div class="lt-table-card-header">
            <span class="lt-table-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true"><path d="M4 18V8a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v10M7 10h10M7 14h6M3 20h18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" /></svg>
            </span>
            <h3>Active Loans</h3>
        </div>
        <div class="lt-table-scroll">
            <table class="lt-dashboard-table">
                <thead><tr><th>Loan</th><th>Party</th><th>Type</th><th class="text-end">Balance</th></tr></thead>
                <tbody>
                    @forelse ($tables['active_loans'] as $loan)
                        <tr>
                            <td class="fw-black text-slate-950">{{ $loan->loan_no }}</td>
                            <td>{{ $loan->party_name }}</td>
                            <td>{{ $loan->typeLabel() }}</td>
                            <td class="text-end fw-black text-danger">{{ $money($loan->balance_amount) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="lt-empty-row">No active loans.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="lt-table-card">
        <div class="lt-table-card-header">
            <span class="lt-table-icon lt-table-icon-warning">
                <svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16v10H4V7Zm8 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4Z" fill="none" stroke="currentColor" stroke-width="2" /></svg>
            </span>
            <h3>Pending Customer Payments</h3>
        </div>
        <div class="lt-table-scroll">
            <table class="lt-dashboard-table">
                <thead><tr><th>Invoice</th><th>Customer</th><th class="text-end">Balance</th></tr></thead>
                <tbody>
                    @forelse ($tables['pending_customer_payments'] as $sale)
                        <tr>
                            <td class="fw-black text-slate-950">{{ $sale->sale_no }}</td>
                            <td>{{ $sale->customer?->name ?: '-' }}</td>
                            <td class="text-end fw-black text-danger">{{ $money($sale->balance_amount) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="lt-empty-row">No pending customer payments.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="lt-table-card">
        <div class="lt-table-card-header">
            <span class="lt-table-icon lt-table-icon-warning">
                <svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3v18m4-14.5A4 4 0 0 0 12 5c-2.2 0-4 1.1-4 2.7 0 4.1 8 1.9 8 6.2 0 1.7-1.8 3.1-4 3.1a5 5 0 0 1-4.5-2.2" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" /></svg>
            </span>
            <h3>Pending Supplier Payments</h3>
        </div>
        <div class="lt-table-scroll">
            <table class="lt-dashboard-table">
                <thead><tr><th>Purchase</th><th>Supplier</th><th class="text-end">Balance</th></tr></thead>
                <tbody>
                    @forelse ($tables['pending_supplier_payments'] as $purchase)
                        <tr>
                            <td class="fw-black text-slate-950">{{ $purchase->purchase_no }}</td>
                            <td>{{ $purchase->supplier?->name ?: '-' }}</td>
                            <td class="text-end fw-black text-danger">{{ $money($purchase->balance_amount) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="lt-empty-row">No pending supplier payments.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
