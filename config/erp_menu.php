<?php

return [
    [
        'label' => 'Dashboard',
        'icon' => 'M3 13h8V3H3v10Zm10 8h8V3h-8v18ZM3 21h8v-6H3v6Z',
        'route' => 'dashboard',
        'active' => ['dashboard'],
        'permission' => 'view_dashboard',
    ],
    [
        'label' => 'Masters',
        'icon' => 'M4 6.5A2.5 2.5 0 0 1 6.5 4h11A2.5 2.5 0 0 1 20 6.5v11a2.5 2.5 0 0 1-2.5 2.5h-11A2.5 2.5 0 0 1 4 17.5v-11Zm4 1.5h8M8 12h8M8 16h5',
        'children' => [
            ['label' => 'Customers', 'route' => 'customers.index', 'active' => ['customers.*'], 'permission' => 'manage_customers'],
            ['label' => 'Suppliers', 'route' => 'suppliers.index', 'active' => ['suppliers.*'], 'permission' => 'manage_suppliers'],
            ['label' => 'Products', 'route' => 'products.index', 'active' => ['products.*'], 'permission' => 'manage_products'],
            ['label' => 'Product Categories', 'route' => 'product-categories.index', 'active' => ['product-categories.*'], 'permission' => 'manage_products'],
            ['label' => 'Units', 'route' => 'units.index', 'active' => ['units.*'], 'permission' => 'manage_products'],
            ['label' => 'Godowns / Warehouses', 'route' => 'godowns.index', 'active' => ['godowns.*'], 'permission' => 'manage_stock_adjustments'],
        ],
    ],
    [
        'label' => 'Purchases',
        'icon' => 'M6 6h15l-2 8H8L6 6ZM6 6 5 3H3M8 18h.01M18 18h.01',
        'children' => [
            ['label' => 'Purchase Orders', 'route' => 'purchase-orders.index', 'active' => ['purchase-orders.*'], 'permission' => 'manage_purchases'],
            ['label' => 'Purchase Invoices', 'route' => 'purchases.index', 'active' => ['purchases.index', 'purchases.show', 'purchases.edit', 'purchases.create'], 'permission' => 'manage_purchases'],
            ['label' => 'Purchase Returns', 'route' => 'purchase-returns.index', 'active' => ['purchase-returns.*'], 'permission' => 'manage_returns'],
            ['label' => 'Supplier Payments', 'route' => 'supplier-payments.index', 'active' => ['supplier-payments.*', 'payments.show'], 'permission' => 'manage_payments'],
        ],
    ],
    [
        'label' => 'Sales',
        'icon' => 'M4 6h16M4 10h16M6 14h5M6 18h8M17 14l3 3-3 3',
        'children' => [
            ['label' => 'Quotations', 'route' => 'quotations.index', 'active' => ['quotations.*'], 'permission' => 'manage_quotations'],
            ['label' => 'GST Invoices', 'route' => 'sales.index', 'active' => ['sales.index', 'sales.show', 'sales.edit', 'sales.create'], 'permission' => 'manage_sales'],
            ['label' => 'Non-GST Invoices', 'route' => 'gst-reports.non-gst-sales', 'active' => ['gst-reports.non-gst-sales'], 'permission' => 'view_gst_reports'],
            ['label' => 'Sales Returns', 'route' => 'sales-returns.index', 'active' => ['sales-returns.*'], 'permission' => 'manage_returns'],
            ['label' => 'Customer Receipts', 'route' => 'receipts.index', 'active' => ['receipts.*'], 'permission' => 'manage_receipts'],
        ],
    ],
    [
        'label' => 'Inventory',
        'icon' => 'M4 7.5 12 3l8 4.5v9L12 21l-8-4.5v-9Zm8 4.5 8-4.5M12 12v9M12 12 4 7.5',
        'children' => [
            ['label' => 'Stock Summary', 'route' => 'reports.stock-valuation', 'active' => ['reports.stock-valuation'], 'permission' => 'manage_products|manage_stock_adjustments|view_reports'],
            ['label' => 'Stock Ledger', 'route' => 'stock-adjustments.movements', 'active' => ['stock-adjustments.movements', 'stock-adjustments.products.history'], 'permission' => 'manage_stock_adjustments'],
            ['label' => 'Stock Transfer', 'route' => 'stock-transfers.index', 'active' => ['stock-transfers.*'], 'permission' => 'manage_stock_adjustments'],
            ['label' => 'Low Stock Alerts', 'route' => 'stock-adjustments.product-report', 'active' => ['stock-adjustments.product-report'], 'permission' => 'manage_stock_adjustments'],
            ['label' => 'Opening Stock', 'route' => 'stock-adjustments.create', 'active' => ['stock-adjustments.create'], 'permission' => 'manage_stock_adjustments'],
        ],
    ],
    [
        'label' => 'Finance',
        'icon' => 'M4 6h16v12H4V6Zm3 4h4M7 14h4M15 10h2M15 14h2',
        'children' => [
            ['label' => 'Cash Flow', 'route' => 'cashbook.index', 'active' => ['cashbook.*'], 'permission' => 'manage_ledgers'],
            ['label' => 'Income', 'route' => 'receipts.index', 'active' => ['receipts.*'], 'permission' => 'manage_receipts'],
            ['label' => 'Expenses', 'route' => 'expenses.index', 'active' => ['expenses.*', 'expense-categories.*'], 'permission' => 'manage_expenses'],
            ['label' => 'Loans Taken', 'route' => 'loans.index', 'active' => ['loans.index', 'loans.show', 'loans.transactions.*'], 'permission' => 'manage_loans'],
            ['label' => 'Loans Given', 'route' => 'loans.reports.active', 'active' => ['loans.reports.active', 'loans.reports.closed'], 'permission' => 'manage_loans'],
            ['label' => 'Partner Drawings', 'route' => 'partners.profit-share', 'active' => ['partners.profit-share'], 'permission' => 'manage_partners'],
            ['label' => 'Partner Capital', 'route' => 'partners.index', 'active' => ['partners.*'], 'permission' => 'manage_partners'],
            ['label' => 'Bank Accounts', 'route' => 'bankbook.index', 'active' => ['bankbook.*', 'settings.bank'], 'permission' => 'manage_ledgers'],
            ['label' => 'Day Book', 'route' => 'reports.daily-business-summary', 'active' => ['reports.daily-business-summary'], 'permission' => 'view_reports'],
        ],
    ],
    [
        'label' => 'Reports',
        'icon' => 'M5 19V5m0 14h14M9 15V9m4 6V7m4 8v-4',
        'children' => [
            ['label' => 'Sales Report', 'route' => 'gst-reports.sales', 'active' => ['gst-reports.sales'], 'permission' => 'view_gst_reports|view_reports'],
            ['label' => 'Purchase Report', 'route' => 'gst-reports.purchases', 'active' => ['gst-reports.purchases'], 'permission' => 'view_gst_reports|view_reports'],
            ['label' => 'Profit & Loss', 'route' => 'reports.profit-loss', 'active' => ['reports.profit-loss', 'expenses.profit-loss'], 'permission' => 'manage_expenses|view_reports'],
            ['label' => 'GST Report', 'route' => 'gst-reports.index', 'active' => ['gst-reports.*'], 'permission' => 'view_gst_reports'],
            ['label' => 'Customer Outstanding', 'route' => 'reports.customer-outstanding', 'active' => ['reports.customer-outstanding'], 'permission' => 'manage_sales|manage_receipts|view_reports'],
            ['label' => 'Supplier Outstanding', 'route' => 'reports.supplier-outstanding', 'active' => ['reports.supplier-outstanding'], 'permission' => 'manage_purchases|manage_payments|view_reports'],
            ['label' => 'Stock Report', 'route' => 'reports.stock-valuation', 'active' => ['reports.stock-valuation'], 'permission' => 'view_reports'],
            ['label' => 'Cash Flow Report', 'route' => 'cashbook.index', 'active' => ['cashbook.*', 'bankbook.*'], 'permission' => 'manage_ledgers|view_reports'],
        ],
    ],
    [
        'label' => 'Settings',
        'icon' => 'M12 8a4 4 0 1 0 0 8 4 4 0 0 0 0-8Zm0-5v3M12 18v3M4.8 4.8l2.1 2.1M17.1 17.1l2.1 2.1M3 12h3M18 12h3M4.8 19.2l2.1-2.1M17.1 6.9l2.1-2.1',
        'children' => [
            ['label' => 'Company Settings', 'route' => 'settings.company', 'active' => ['settings.company'], 'permission' => 'manage_settings'],
            ['label' => 'Invoice Settings', 'route' => 'settings.invoice', 'active' => ['settings.invoice', 'settings.terms', 'settings.media'], 'permission' => 'manage_settings'],
            ['label' => 'Tax Settings', 'route' => 'settings.invoice', 'active' => ['settings.invoice'], 'permission' => 'manage_settings'],
            ['label' => 'Prefix Settings', 'route' => 'settings.invoice', 'active' => ['settings.invoice'], 'permission' => 'manage_settings'],
            ['label' => 'User Management', 'route' => 'users.index', 'active' => ['users.*'], 'permission' => 'manage_users'],
            ['label' => 'Role & Permissions', 'route' => 'users.index', 'active' => ['users.*'], 'permission' => 'manage_users'],
            ['label' => 'Backup Settings', 'route' => 'settings.backups.index', 'active' => ['settings.backups.*'], 'permission' => 'manage_settings', 'super_admin' => true],
            ['label' => 'Theme Settings', 'route' => 'settings.company', 'active' => ['settings.company'], 'permission' => 'manage_settings'],
        ],
    ],
];
