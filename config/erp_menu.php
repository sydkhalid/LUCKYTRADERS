<?php

return [
    [
        'label' => 'Dashboard',
        'route' => 'dashboard',
        'icon' => 'M3 3h7v7H3V3Zm11 0h7v7h-7V3ZM3 14h7v7H3v-7Zm11 0h7v7h-7v-7Z',
        'active' => ['dashboard'],
        'permission' => 'view_dashboard',
    ],
    [
        'label' => 'Masters',
        'icon' => 'M4 5h16M4 12h16M4 19h16',
        'active' => ['customers.*', 'suppliers.*', 'products.*', 'product-categories.*'],
        'children' => [
            ['label' => 'Customers', 'route' => 'customers.index', 'active' => ['customers.*'], 'permission' => 'manage_customers'],
            ['label' => 'Suppliers', 'route' => 'suppliers.index', 'active' => ['suppliers.*'], 'permission' => 'manage_suppliers'],
            ['label' => 'Products', 'route' => 'products.index', 'active' => ['products.*'], 'permission' => 'manage_products'],
            ['label' => 'Product Categories', 'route' => 'product-categories.index', 'active' => ['product-categories.*'], 'permission' => 'manage_products'],
        ],
    ],
    [
        'label' => 'Purchases',
        'icon' => 'M3 3h2l.4 2M7 13h9l4-8H5.4M7 13 5.4 5M7 13l-2 8h13M9 21a1 1 0 1 0 0-2 1 1 0 0 0 0 2Zm8 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z',
        'active' => ['purchases.*', 'purchase-returns.*', 'supplier-payments.*'],
        'children' => [
            ['label' => 'Purchase Invoices', 'route' => 'purchases.index', 'active' => ['purchases.*'], 'permission' => 'manage_purchases'],
            ['label' => 'Purchase Returns', 'route' => 'purchase-returns.index', 'active' => ['purchase-returns.*'], 'permission' => 'manage_returns'],
            ['label' => 'Supplier Payments', 'route' => 'supplier-payments.index', 'active' => ['supplier-payments.*'], 'permission' => 'manage_payments'],
        ],
    ],
    [
        'label' => 'Sales',
        'icon' => 'M4 6h16M4 12h10M4 18h16',
        'active' => ['quotations.*', 'sales.*', 'sales-returns.*', 'receipts.*'],
        'children' => [
            ['label' => 'Quotations', 'route' => 'quotations.index', 'active' => ['quotations.*'], 'permission' => 'manage_quotations'],
            ['label' => 'Sales Invoices', 'route' => 'sales.index', 'active' => ['sales.*'], 'permission' => 'manage_sales'],
            ['label' => 'Sales Returns', 'route' => 'sales-returns.index', 'active' => ['sales-returns.*'], 'permission' => 'manage_returns'],
            ['label' => 'Customer Receipts', 'route' => 'receipts.index', 'active' => ['receipts.*'], 'permission' => 'manage_receipts'],
        ],
    ],
    [
        'label' => 'Inventory',
        'icon' => 'M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16ZM3.3 7 12 12l8.7-5M12 22V12',
        'active' => ['stock-adjustments.*', 'reports.stock-valuation', 'reports.fast-moving-products', 'reports.slow-moving-products'],
        'children' => [
            ['label' => 'Stock Summary', 'route' => 'reports.stock-valuation', 'active' => ['reports.stock-valuation'], 'permission' => 'view_reports'],
            ['label' => 'Stock Ledger', 'route' => 'stock-adjustments.movements', 'active' => ['stock-adjustments.movements', 'stock-adjustments.products.history'], 'permission' => 'manage_stock_adjustments'],
            ['label' => 'Stock Adjustment', 'route' => 'stock-adjustments.index', 'active' => ['stock-adjustments.index', 'stock-adjustments.create', 'stock-adjustments.show'], 'permission' => 'manage_stock_adjustments'],
            ['label' => 'Product Stock Report', 'route' => 'stock-adjustments.product-report', 'active' => ['stock-adjustments.product-report'], 'permission' => 'manage_stock_adjustments'],
        ],
    ],
    [
        'label' => 'Finance',
        'icon' => 'M3 10h18M5 10V7l7-4 7 4v3M6 10v9M10 10v9M14 10v9M18 10v9M4 19h16',
        'active' => ['cashbook.*', 'bankbook.*', 'ledgers.*', 'payments.*', 'receipts.*', 'supplier-payments.*', 'expenses.*', 'expense-categories.*', 'loans.*', 'partners.*', 'reports.daily-business-summary'],
        'children' => [
            ['label' => 'Day Book', 'route' => 'reports.daily-business-summary', 'active' => ['reports.daily-business-summary'], 'permission' => 'view_reports'],
            ['label' => 'Cashbook', 'route' => 'cashbook.index', 'active' => ['cashbook.*'], 'permission' => 'manage_ledgers'],
            ['label' => 'Bankbook', 'route' => 'bankbook.index', 'active' => ['bankbook.*'], 'permission' => 'manage_ledgers'],
            ['label' => 'Ledgers', 'route' => 'ledgers.index', 'active' => ['ledgers.*'], 'permission' => 'manage_ledgers'],
            ['label' => 'Expenses', 'route' => 'expenses.index', 'active' => ['expenses.*'], 'permission' => 'manage_expenses'],
            ['label' => 'Expense Categories', 'route' => 'expense-categories.index', 'active' => ['expense-categories.*'], 'permission' => 'manage_expenses'],
            ['label' => 'Loans', 'route' => 'loans.index', 'active' => ['loans.*'], 'permission' => 'manage_loans'],
            ['label' => 'Partners', 'route' => 'partners.index', 'active' => ['partners.*'], 'permission' => 'manage_partners'],
        ],
    ],
    [
        'label' => 'Reports',
        'icon' => 'M4 19V5M8 19v-8M12 19V9M16 19v-5M20 19V7',
        'active' => ['reports.*', 'gst-reports.*'],
        'children' => [
            ['label' => 'Report Center', 'route' => 'reports.index', 'active' => ['reports.index'], 'permission' => 'view_reports'],
            ['label' => 'Sales Report', 'route' => 'gst-reports.sales', 'active' => ['gst-reports.sales'], 'permission' => 'view_gst_reports'],
            ['label' => 'Purchase Report', 'route' => 'gst-reports.purchases', 'active' => ['gst-reports.purchases'], 'permission' => 'view_gst_reports'],
            ['label' => 'Profit & Loss', 'route' => 'reports.profit-loss', 'active' => ['reports.profit-loss'], 'permission' => 'view_reports'],
            ['label' => 'GST Register', 'route' => 'gst-reports.index', 'active' => ['gst-reports.index'], 'permission' => 'view_gst_reports'],
            ['label' => 'Customer Outstanding', 'route' => 'reports.customer-outstanding', 'active' => ['reports.customer-outstanding'], 'permission' => 'view_reports'],
            ['label' => 'Supplier Outstanding', 'route' => 'reports.supplier-outstanding', 'active' => ['reports.supplier-outstanding'], 'permission' => 'view_reports'],
            ['label' => 'Stock Report', 'route' => 'reports.stock-valuation', 'active' => ['reports.stock-valuation'], 'permission' => 'view_reports'],
        ],
    ],
    [
        'label' => 'Settings',
        'icon' => 'M12 8a4 4 0 1 0 0 8 4 4 0 0 0 0-8Zm0-6v3m0 14v3M4.93 4.93l2.12 2.12m9.9 9.9 2.12 2.12M2 12h3m14 0h3M4.93 19.07l2.12-2.12m9.9-9.9 2.12-2.12',
        'active' => ['settings.*', 'users.*', 'activity-logs.*'],
        'children' => [
            ['label' => 'Company Profile', 'route' => 'settings.company', 'active' => ['settings.company'], 'permission' => 'manage_settings'],
            ['label' => 'Invoice Numbering', 'route' => 'settings.invoice', 'active' => ['settings.invoice'], 'permission' => 'manage_settings'],
            ['label' => 'Bank Details', 'route' => 'settings.bank', 'active' => ['settings.bank'], 'permission' => 'manage_settings'],
            ['label' => 'Terms & Conditions', 'route' => 'settings.terms', 'active' => ['settings.terms'], 'permission' => 'manage_settings'],
            ['label' => 'Logo & Signature', 'route' => 'settings.media', 'active' => ['settings.media'], 'permission' => 'manage_settings'],
            ['label' => 'User Management', 'route' => 'users.index', 'active' => ['users.*'], 'permission' => 'manage_users'],
            ['label' => 'Activity Logs', 'route' => 'activity-logs.index', 'active' => ['activity-logs.*'], 'permission' => 'view_activity_logs'],
            ['label' => 'Backups', 'route' => 'settings.backups.index', 'active' => ['settings.backups.*'], 'permission' => 'manage_backups', 'super_admin' => true],
        ],
    ],
];
