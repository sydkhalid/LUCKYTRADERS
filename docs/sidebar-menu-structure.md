# ERP Sidebar Menu Structure

The sidebar is rendered from `config/erp_menu.php` in `resources/views/layouts/erp.blade.php`.
Each child item is visible only when both conditions are true:

- the authenticated user has at least one configured permission;
- the named route exists.

Suggested routes that are not implemented yet are kept in the config so the menu item appears automatically when the route is added.

| Parent | Child | Route name | Permission |
| --- | --- | --- | --- |
| Dashboard | Dashboard | `dashboard` | `view_dashboard` |
| Masters | Product Categories | `product-categories.index` | `manage_products` |
| Masters | Products | `products.index` | `manage_products` |
| Masters | Units | `units.index` | `manage_products` |
| Masters | Customers | `customers.index` | `manage_customers` |
| Masters | Suppliers | `suppliers.index` | `manage_suppliers` |
| Inventory | Stock Summary | `reports.stock-valuation` | `manage_products` or `manage_stock_adjustments` or `view_reports` |
| Inventory | Stock Movements | `stock-adjustments.movements` | `manage_stock_adjustments` |
| Inventory | Stock Adjustments | `stock-adjustments.index` | `manage_stock_adjustments` |
| Inventory | Low Stock Alerts | `inventory.low-stock-alerts` | `manage_products` or `manage_stock_adjustments` |
| Purchases | Purchase Bills | `purchases.index` | `manage_purchases` |
| Purchases | Create Purchase | `purchases.create` | `manage_purchases` |
| Purchases | Purchase Returns | `purchase-returns.index` | `manage_returns` |
| Purchases | Supplier Payments | `supplier-payments.index` | `manage_payments` |
| Purchases | Supplier Outstanding | `reports.supplier-outstanding` | `manage_purchases` or `manage_payments` or `view_reports` |
| Sales | Quotations | `quotations.index` | `manage_quotations` |
| Sales | Create Quotation | `quotations.create` | `manage_quotations` |
| Sales | Sales Bills | `sales.index` | `manage_sales` |
| Sales | Create Bill | `sales.create` | `manage_sales` |
| Sales | Sales Returns | `sales-returns.index` | `manage_returns` |
| Sales | Customer Receipts | `receipts.index` | `manage_receipts` |
| Sales | Customer Outstanding | `reports.customer-outstanding` | `manage_sales` or `manage_receipts` or `view_reports` |
| Accounts | Cashbook | `cashbook.index` | `manage_ledgers` |
| Accounts | Bankbook | `bankbook.index` | `manage_ledgers` |
| Accounts | Ledgers | `ledgers.index` | `manage_ledgers` |
| Accounts | Expenses | `expenses.index` | `manage_expenses` |
| Accounts | Profit & Loss | `reports.profit-loss` | `manage_expenses` or `view_reports` |
| Accounts | Journal Entries | `journal-entries.index` | `manage_ledgers` |
| Loans | Loan Taken | `loans.taken` | `manage_loans` |
| Loans | Loan Given | `loans.given` | `manage_loans` |
| Loans | Loan Transactions | `loans.transactions.all` | `manage_loans` |
| Loans | Active Loans | `loans.reports.active` | `manage_loans` |
| Loans | Closed Loans | `loans.reports.closed` | `manage_loans` |
| Partners | Partner List | `partners.index` | `manage_partners` |
| Partners | Investments | `partners.investments.index` | `manage_partners` |
| Partners | Withdrawals | `partners.withdrawals.index` | `manage_partners` |
| Partners | Profit Share | `partners.profit-share` | `manage_partners` |
| Partners | Partner Ledger | `reports.partner-balance` | `manage_partners` or `view_reports` |
| GST | GST Sales Report | `gst-reports.sales` | `view_gst_reports` |
| GST | GST Purchase Report | `gst-reports.purchases` | `view_gst_reports` |
| GST | GST Return Report | `gst-reports.returns` | `view_gst_reports` |
| GST | GST Summary | `gst-reports.index` | `view_gst_reports` |
| GST | Auditor Export | `gst-reports.export` | `export_gst_reports` |
| Reports | Sales Report | `reports.sales` | `view_reports` |
| Reports | Purchase Report | `reports.purchases` | `view_reports` |
| Reports | Stock Report | `reports.stock-valuation` | `view_reports` |
| Reports | Product Profit Report | `reports.product-profit` | `view_reports` |
| Reports | Customer Report | `reports.customer-outstanding` | `view_reports` |
| Reports | Supplier Report | `reports.supplier-outstanding` | `view_reports` |
| Reports | Expense Report | `reports.expense-summary` | `view_reports` |
| Reports | Daily Summary | `reports.daily-business-summary` | `view_reports` |
| Users & Roles | Users | `users.index` | `manage_users` |
| Users & Roles | Roles | `roles.index` | `manage_users` |
| Users & Roles | Permissions | `permissions.index` | `manage_users` |
| Users & Roles | Activity Logs | `activity-logs.index` | `view_activity_logs` |
| System | Company Settings | `settings.company` | `manage_settings` |
| System | Invoice Settings | `settings.invoice` | `manage_settings` |
| System | Backup | `settings.backups.index` | `manage_settings` and `Super Admin` |
| System | Notifications | `notifications.index` | authenticated user |
| System | Security Settings | `settings.security` | `manage_settings` |
