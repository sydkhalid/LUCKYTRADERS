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
            ['label' => 'Product Categories', 'route' => 'product-categories.index', 'active' => ['product-categories.*'], 'permission' => 'manage_products'],
            ['label' => 'Products', 'route' => 'products.index', 'active' => ['products.*'], 'permission' => 'manage_products'],
            ['label' => 'Customers', 'route' => 'customers.index', 'active' => ['customers.*'], 'permission' => 'manage_customers'],
            ['label' => 'Suppliers', 'route' => 'suppliers.index', 'active' => ['suppliers.*'], 'permission' => 'manage_suppliers'],
        ],
    ],
    [
        'label' => 'Inventory',
        'icon' => 'M4 7.5 12 3l8 4.5v9L12 21l-8-4.5v-9Zm8 4.5 8-4.5M12 12v9M12 12 4 7.5',
        'children' => [
            ['label' => 'Stock Summary', 'route' => 'reports.stock-valuation', 'active' => ['reports.stock-valuation'], 'permission' => 'manage_products|manage_stock_adjustments|view_reports'],
            ['label' => 'Stock Movements', 'route' => 'stock-adjustments.movements', 'active' => ['stock-adjustments.movements', 'stock-adjustments.products.history'], 'permission' => 'manage_stock_adjustments'],
            ['label' => 'Stock Adjustments', 'route' => 'stock-adjustments.index', 'active' => ['stock-adjustments.index', 'stock-adjustments.create', 'stock-adjustments.show', 'stock-adjustments.product-report'], 'permission' => 'manage_stock_adjustments'],
        ],
    ],
    [
        'label' => 'Purchases',
        'icon' => 'M6 6h15l-2 8H8L6 6ZM6 6 5 3H3M8 18h.01M18 18h.01',
        'children' => [
            ['label' => 'Purchase Bills', 'route' => 'purchases.index', 'active' => ['purchases.index', 'purchases.show', 'purchases.edit'], 'permission' => 'manage_purchases'],
            ['label' => 'Create Purchase', 'route' => 'purchases.create', 'active' => ['purchases.create'], 'permission' => 'manage_purchases'],
            ['label' => 'Purchase Returns', 'route' => 'purchase-returns.index', 'active' => ['purchase-returns.*'], 'permission' => 'manage_returns'],
            ['label' => 'Supplier Payments', 'route' => 'supplier-payments.index', 'active' => ['supplier-payments.*', 'payments.show'], 'permission' => 'manage_payments'],
            ['label' => 'Supplier Outstanding', 'route' => 'reports.supplier-outstanding', 'active' => ['reports.supplier-outstanding'], 'permission' => 'manage_purchases|manage_payments|view_reports'],
        ],
    ],
    [
        'label' => 'Sales',
        'icon' => 'M4 6h16M4 10h16M6 14h5M6 18h8M17 14l3 3-3 3',
        'children' => [
            ['label' => 'Quotations', 'route' => 'quotations.index', 'active' => ['quotations.index', 'quotations.show', 'quotations.edit', 'quotations.convert'], 'permission' => 'manage_quotations'],
            ['label' => 'Create Quotation', 'route' => 'quotations.create', 'active' => ['quotations.create'], 'permission' => 'manage_quotations'],
            ['label' => 'Sales Bills', 'route' => 'sales.index', 'active' => ['sales.index', 'sales.show', 'sales.edit'], 'permission' => 'manage_sales'],
            ['label' => 'Create Bill', 'route' => 'sales.create', 'active' => ['sales.create'], 'permission' => 'manage_sales'],
            ['label' => 'Sales Returns', 'route' => 'sales-returns.index', 'active' => ['sales-returns.*'], 'permission' => 'manage_returns'],
            ['label' => 'Customer Receipts', 'route' => 'receipts.index', 'active' => ['receipts.*'], 'permission' => 'manage_receipts'],
            ['label' => 'Customer Outstanding', 'route' => 'reports.customer-outstanding', 'active' => ['reports.customer-outstanding'], 'permission' => 'manage_sales|manage_receipts|view_reports'],
        ],
    ],
    [
        'label' => 'Accounts',
        'icon' => 'M4 6h16v12H4V6Zm3 4h4M7 14h4M15 10h2M15 14h2',
        'children' => [
            ['label' => 'Cashbook', 'route' => 'cashbook.index', 'active' => ['cashbook.*'], 'permission' => 'manage_ledgers'],
            ['label' => 'Bankbook', 'route' => 'bankbook.index', 'active' => ['bankbook.*'], 'permission' => 'manage_ledgers'],
            ['label' => 'Ledgers', 'route' => 'ledgers.index', 'active' => ['ledgers.*'], 'permission' => 'manage_ledgers'],
            ['label' => 'Expenses', 'route' => 'expenses.index', 'active' => ['expenses.*', 'expense-categories.*'], 'permission' => 'manage_expenses'],
            ['label' => 'Profit & Loss', 'route' => 'reports.profit-loss', 'active' => ['reports.profit-loss'], 'permission' => 'manage_expenses|view_reports'],
        ],
    ],
    [
        'label' => 'Loans',
        'icon' => 'M5 8h14M7 4h10v16H7V4Zm3 8h4M10 16h4',
        'children' => [
            ['label' => 'Loan List', 'route' => 'loans.index', 'active' => ['loans.index', 'loans.show'], 'permission' => 'manage_loans'],
            ['label' => 'Create Loan', 'route' => 'loans.create', 'active' => ['loans.create'], 'permission' => 'manage_loans'],
            ['label' => 'Active Loans', 'route' => 'loans.reports.active', 'active' => ['loans.reports.active'], 'permission' => 'manage_loans'],
            ['label' => 'Closed Loans', 'route' => 'loans.reports.closed', 'active' => ['loans.reports.closed'], 'permission' => 'manage_loans'],
        ],
    ],
    [
        'label' => 'Partners',
        'icon' => 'M8 11a3 3 0 1 0 0-6 3 3 0 0 0 0 6Zm8 0a3 3 0 1 0 0-6 3 3 0 0 0 0 6ZM3.5 20a4.5 4.5 0 0 1 9 0M11.5 20a4.5 4.5 0 0 1 9 0',
        'children' => [
            ['label' => 'Partner List', 'route' => 'partners.index', 'active' => ['partners.index', 'partners.show', 'partners.edit'], 'permission' => 'manage_partners'],
            ['label' => 'Add Partner', 'route' => 'partners.create', 'active' => ['partners.create'], 'permission' => 'manage_partners'],
            ['label' => 'Profit Share', 'route' => 'partners.profit-share', 'active' => ['partners.profit-share'], 'permission' => 'manage_partners'],
            ['label' => 'Partner Ledger', 'route' => 'reports.partner-balance', 'active' => ['reports.partner-balance', 'partners.transactions.*'], 'permission' => 'manage_partners|view_reports'],
        ],
    ],
    [
        'label' => 'GST',
        'icon' => 'M6 4h12v16H6V4Zm3 4h6M9 12h6M9 16h3',
        'children' => [
            ['label' => 'GST Sales Report', 'route' => 'gst-reports.sales', 'active' => ['gst-reports.sales'], 'permission' => 'view_gst_reports'],
            ['label' => 'GST Purchase Report', 'route' => 'gst-reports.purchases', 'active' => ['gst-reports.purchases'], 'permission' => 'view_gst_reports'],
            ['label' => 'Sales Return GST', 'route' => 'gst-reports.sales-returns', 'active' => ['gst-reports.sales-returns'], 'permission' => 'view_gst_reports'],
            ['label' => 'Purchase Return GST', 'route' => 'gst-reports.purchase-returns', 'active' => ['gst-reports.purchase-returns'], 'permission' => 'view_gst_reports'],
            ['label' => 'GST Summary', 'route' => 'gst-reports.index', 'active' => ['gst-reports.index'], 'permission' => 'view_gst_reports'],
            ['label' => 'Auditor Export', 'route' => 'gst-reports.export', 'active' => ['gst-reports.export'], 'permission' => 'export_gst_reports'],
        ],
    ],
    [
        'label' => 'Reports',
        'icon' => 'M5 19V5m0 14h14M9 15V9m4 6V7m4 8v-4',
        'children' => [
            ['label' => 'Report Center', 'route' => 'reports.index', 'active' => ['reports.index'], 'permission' => 'view_reports'],
            ['label' => 'Stock Report', 'route' => 'reports.stock-valuation', 'active' => ['reports.stock-valuation'], 'permission' => 'view_reports'],
            ['label' => 'Product Profit Report', 'route' => 'reports.product-profit', 'active' => ['reports.product-profit'], 'permission' => 'view_reports'],
            ['label' => 'Customer Report', 'route' => 'reports.customer-outstanding', 'active' => ['reports.customer-outstanding'], 'permission' => 'view_reports'],
            ['label' => 'Supplier Report', 'route' => 'reports.supplier-outstanding', 'active' => ['reports.supplier-outstanding'], 'permission' => 'view_reports'],
            ['label' => 'Expense Report', 'route' => 'reports.expense-summary', 'active' => ['reports.expense-summary'], 'permission' => 'view_reports'],
            ['label' => 'Daily Summary', 'route' => 'reports.daily-business-summary', 'active' => ['reports.daily-business-summary'], 'permission' => 'view_reports'],
        ],
    ],
    [
        'label' => 'Users & Roles',
        'icon' => 'M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm-7 8a7 7 0 0 1 14 0M18 8h3M19.5 6.5v3',
        'children' => [
            ['label' => 'Users', 'route' => 'users.index', 'active' => ['users.*'], 'permission' => 'manage_users'],
            ['label' => 'Activity Logs', 'route' => 'activity-logs.index', 'active' => ['activity-logs.*'], 'permission' => 'view_activity_logs'],
        ],
    ],
    [
        'label' => 'System',
        'icon' => 'M12 8a4 4 0 1 0 0 8 4 4 0 0 0 0-8Zm0-5v3M12 18v3M4.8 4.8l2.1 2.1M17.1 17.1l2.1 2.1M3 12h3M18 12h3M4.8 19.2l2.1-2.1M17.1 6.9l2.1-2.1',
        'children' => [
            ['label' => 'Company Settings', 'route' => 'settings.company', 'active' => ['settings.company'], 'permission' => 'manage_settings'],
            ['label' => 'Invoice Settings', 'route' => 'settings.invoice', 'active' => ['settings.invoice', 'settings.bank', 'settings.terms', 'settings.media'], 'permission' => 'manage_settings'],
            ['label' => 'Backup', 'route' => 'settings.backups.index', 'active' => ['settings.backups.*'], 'permission' => 'manage_settings', 'super_admin' => true],
            ['label' => 'Notifications', 'route' => 'notifications.index', 'active' => ['notifications.*'], 'permission' => null],
        ],
    ],
];
