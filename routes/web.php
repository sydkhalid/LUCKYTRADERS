<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\AdvancedReportController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\CashbookController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CustomerReceiptController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpenseCategoryController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\GlobalSearchController;
use App\Http\Controllers\GSTReportController;
use App\Http\Controllers\LedgerController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\LoanTransactionController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PartnerController;
use App\Http\Controllers\PartnerTransactionController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PdfController;
use App\Http\Controllers\ProductCategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductionReadinessController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\PurchaseReturnController;
use App\Http\Controllers\QuotationController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SalesReturnController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\StockAdjustmentController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\SupplierPaymentController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'throttle:erp'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->middleware('permission:view_dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index'])->middleware('permission:view_dashboard')->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/api/global-search', [GlobalSearchController::class, 'search'])->name('global-search.search');

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/dropdown', [NotificationController::class, 'dropdown'])->name('notifications.dropdown');
    Route::patch('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
    Route::patch('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::patch('/notifications/{notification}/unread', [NotificationController::class, 'markAsUnread'])->name('notifications.unread');

    Route::middleware('permission:manage_users')->group(function () {
        Route::resource('users', UserController::class)->only(['index', 'create', 'store', 'edit', 'update']);
    });

    Route::middleware('permission:view_activity_logs')->prefix('activity-logs')->name('activity-logs.')->group(function () {
        Route::get('/', [ActivityLogController::class, 'index'])->name('index');
        Route::get('/users', [ActivityLogController::class, 'users'])->name('users');
        Route::get('/modules', [ActivityLogController::class, 'modules'])->name('modules');
        Route::get('/dates', [ActivityLogController::class, 'dates'])->name('dates');
        Route::delete('/{activity}', [ActivityLogController::class, 'destroy'])->middleware('role:Super Admin')->name('destroy');
    });

    Route::middleware('permission:view_reports')->prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [AdvancedReportController::class, 'index'])->name('index');
        Route::get('/profit-loss', [AdvancedReportController::class, 'profitLoss'])->name('profit-loss');
        Route::get('/product-profit', [AdvancedReportController::class, 'productProfit'])->name('product-profit');
        Route::get('/customer-outstanding', [AdvancedReportController::class, 'customerOutstanding'])->name('customer-outstanding');
        Route::get('/supplier-outstanding', [AdvancedReportController::class, 'supplierOutstanding'])->name('supplier-outstanding');
        Route::get('/stock-valuation', [AdvancedReportController::class, 'stockValuation'])->name('stock-valuation');
        Route::get('/fast-moving-products', [AdvancedReportController::class, 'fastMovingProducts'])->name('fast-moving-products');
        Route::get('/slow-moving-products', [AdvancedReportController::class, 'slowMovingProducts'])->name('slow-moving-products');
        Route::get('/expense-summary', [AdvancedReportController::class, 'expenseSummary'])->name('expense-summary');
        Route::get('/partner-balance', [AdvancedReportController::class, 'partnerBalance'])->name('partner-balance');
        Route::get('/loan-summary', [AdvancedReportController::class, 'loanSummary'])->name('loan-summary');
        Route::get('/gst-summary', [AdvancedReportController::class, 'gstSummary'])->name('gst-summary');
        Route::get('/daily-business-summary', [AdvancedReportController::class, 'dailyBusinessSummary'])->name('daily-business-summary');
        Route::get('/{report}/export/{format}', [AdvancedReportController::class, 'export'])
            ->whereIn('format', ['pdf', 'csv', 'excel'])
            ->middleware('permission:export_reports')
            ->name('export');
    });

    Route::middleware('permission:manage_settings')->prefix('settings')->name('settings.')->group(function () {
        Route::get('/company', [SettingController::class, 'company'])->name('company');
        Route::patch('/company', [SettingController::class, 'updateCompany'])->name('company.update');
        Route::get('/invoice', [SettingController::class, 'invoice'])->name('invoice');
        Route::patch('/invoice', [SettingController::class, 'updateInvoice'])->name('invoice.update');
        Route::get('/bank', [SettingController::class, 'bank'])->name('bank');
        Route::patch('/bank', [SettingController::class, 'updateBank'])->name('bank.update');
        Route::get('/terms', [SettingController::class, 'terms'])->name('terms');
        Route::patch('/terms', [SettingController::class, 'updateTerms'])->name('terms.update');
        Route::get('/media', [SettingController::class, 'media'])->name('media');
        Route::patch('/media', [SettingController::class, 'updateMedia'])->name('media.update');
        Route::get('/testing-checklist', [ProductionReadinessController::class, 'checklist'])->name('testing-checklist');
        Route::patch('/testing-checklist', [ProductionReadinessController::class, 'updateChecklist'])->name('testing-checklist.update');
        Route::post('/testing-bugs', [ProductionReadinessController::class, 'storeBug'])->name('testing-bugs.store');
        Route::patch('/testing-bugs/{bug}', [ProductionReadinessController::class, 'updateBug'])->name('testing-bugs.update');

        Route::middleware('role:Super Admin')->prefix('backups')->name('backups.')->group(function () {
            Route::get('/', [BackupController::class, 'index'])->name('index');
            Route::get('/settings', [BackupController::class, 'settings'])->name('settings');
            Route::post('/', [BackupController::class, 'store'])->name('store');
            Route::post('/cleanup', [BackupController::class, 'cleanup'])->name('cleanup');
            Route::get('/{file}', [BackupController::class, 'download'])->name('download');
            Route::delete('/{file}', [BackupController::class, 'destroy'])->name('destroy');
        });
    });

    Route::middleware('permission:manage_products')->group(function () {
        Route::resource('product-categories', ProductCategoryController::class);
        Route::resource('products', ProductController::class);
    });

    Route::resource('customers', CustomerController::class)->middleware('permission:manage_customers');
    Route::resource('suppliers', SupplierController::class)->middleware('permission:manage_suppliers');
    Route::middleware('permission:manage_purchases')->group(function () {
        Route::get('/purchases/{purchase}/pdf', [PdfController::class, 'purchase'])->name('purchases.pdf');
        Route::get('/purchases/{purchase}/print', [PurchaseController::class, 'print'])->name('purchases.print');
        Route::resource('purchases', PurchaseController::class);
    });

    Route::middleware('permission:manage_returns')->group(function () {
        Route::get('/purchase-returns/report', [PurchaseReturnController::class, 'report'])->name('purchase-returns.report');
        Route::get('/purchase-returns/{purchaseReturn}/print', [PurchaseReturnController::class, 'print'])->name('purchase-returns.print');
        Route::resource('purchase-returns', PurchaseReturnController::class)->only(['index', 'create', 'store', 'show']);
    });

    Route::middleware('permission:manage_stock_adjustments')->group(function () {
        Route::get('/stock-adjustments/product-report', [StockAdjustmentController::class, 'productReport'])->name('stock-adjustments.product-report');
        Route::get('/stock-adjustments/movements', [StockAdjustmentController::class, 'movementReport'])->name('stock-adjustments.movements');
        Route::get('/stock-adjustments/products/{product}/history', [StockAdjustmentController::class, 'productHistory'])->name('stock-adjustments.products.history');
        Route::resource('stock-adjustments', StockAdjustmentController::class)->only(['index', 'create', 'store', 'show']);
    });

    Route::middleware('permission:manage_quotations')->group(function () {
        Route::get('/quotations/{quotation}/pdf', [PdfController::class, 'quotation'])->name('quotations.pdf');
        Route::get('/quotations/{quotation}/print', [QuotationController::class, 'print'])->name('quotations.print');
        Route::get('/quotations/{quotation}/convert', [QuotationController::class, 'convert'])->name('quotations.convert');
        Route::post('/quotations/{quotation}/convert', [QuotationController::class, 'storeConversion'])->name('quotations.convert.store');
        Route::resource('quotations', QuotationController::class)->except(['destroy']);
    });

    Route::middleware('permission:manage_sales')->group(function () {
        Route::resource('sales', SaleController::class);
    });

    Route::middleware('permission:manage_returns')->group(function () {
        Route::get('/sales-returns/report', [SalesReturnController::class, 'report'])->name('sales-returns.report');
        Route::get('/sales-returns/{salesReturn}/print', [SalesReturnController::class, 'print'])->name('sales-returns.print');
        Route::resource('sales-returns', SalesReturnController::class)->only(['index', 'create', 'store', 'show']);
    });

    Route::get('/sales/{sale}/print', [SaleController::class, 'printInvoice'])
        ->middleware('permission:print_invoice')
        ->name('sales.print');
    Route::get('/sales/{sale}/pdf', [PdfController::class, 'sale'])
        ->middleware('permission:print_invoice')
        ->name('sales.pdf');

    Route::middleware('permission:manage_receipts')->group(function () {
        Route::get('/receipts', [CustomerReceiptController::class, 'index'])->name('receipts.index');
        Route::get('/receipts/create', [CustomerReceiptController::class, 'create'])->name('receipts.create');
        Route::post('/receipts', [CustomerReceiptController::class, 'store'])->name('receipts.store');
        Route::get('/receipts/{payment}', [CustomerReceiptController::class, 'show'])->name('receipts.show');
    });

    Route::middleware('permission:manage_payments')->group(function () {
        Route::get('/payments/{payment}', [PaymentController::class, 'show'])->name('payments.show');
        Route::get('/supplier-payments', [SupplierPaymentController::class, 'index'])->name('supplier-payments.index');
        Route::get('/supplier-payments/create', [SupplierPaymentController::class, 'create'])->name('supplier-payments.create');
        Route::post('/supplier-payments', [SupplierPaymentController::class, 'store'])->name('supplier-payments.store');
        Route::get('/supplier-payments/{payment}', [SupplierPaymentController::class, 'show'])->name('supplier-payments.show');
        Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
    });

    Route::get('/payments/{payment}/pdf', [PdfController::class, 'payment'])
        ->middleware('permission:manage_receipts|manage_payments')
        ->name('payments.pdf');

    Route::middleware('permission:manage_ledgers')->group(function () {
        Route::get('/ledgers', [LedgerController::class, 'index'])->name('ledgers.index');
        Route::get('/ledgers/customers', [LedgerController::class, 'customers'])->name('ledgers.customers.index');
        Route::get('/ledgers/customers/{customer}', [LedgerController::class, 'customerShow'])->name('ledgers.customers.show');
        Route::get('/ledgers/suppliers', [LedgerController::class, 'suppliers'])->name('ledgers.suppliers.index');
        Route::get('/ledgers/suppliers/{supplier}', [LedgerController::class, 'supplierShow'])->name('ledgers.suppliers.show');
        Route::get('/cashbook', [CashbookController::class, 'cashbook'])->name('cashbook.index');
        Route::get('/bankbook', [CashbookController::class, 'bankbook'])->name('bankbook.index');
    });

    Route::middleware('permission:manage_expenses')->group(function () {
        Route::get('/expenses/{expense}/pdf', [PdfController::class, 'expense'])->name('expenses.pdf');
        Route::resource('expense-categories', ExpenseCategoryController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
        Route::get('/expenses/profit-loss', [ExpenseController::class, 'profitLoss'])->name('expenses.profit-loss');
        Route::get('/expenses/report', [ExpenseController::class, 'report'])->name('expenses.report');
        Route::get('/expenses/category-report', [ExpenseController::class, 'categoryReport'])->name('expenses.category-report');
        Route::resource('expenses', ExpenseController::class)->only(['index', 'create', 'store', 'show']);
    });

    Route::middleware('permission:manage_loans')->group(function () {
        Route::get('/loans/{loan}/pdf', [PdfController::class, 'loan'])->name('loans.pdf');
        Route::get('/loans/{loan}/transactions/{transaction}/pdf', [PdfController::class, 'loanTransaction'])->name('loans.transactions.pdf');
        Route::get('/loans/reports/active', [LoanController::class, 'activeReport'])->name('loans.reports.active');
        Route::get('/loans/reports/closed', [LoanController::class, 'closedReport'])->name('loans.reports.closed');
        Route::get('/loans/{loan}/transactions', [LoanTransactionController::class, 'index'])->name('loans.transactions.index');
        Route::get('/loans/{loan}/transactions/create', [LoanTransactionController::class, 'create'])->name('loans.transactions.create');
        Route::post('/loans/{loan}/transactions', [LoanTransactionController::class, 'store'])->name('loans.transactions.store');
        Route::resource('loans', LoanController::class)->only(['index', 'create', 'store', 'show']);
    });

    Route::middleware('permission:manage_partners')->group(function () {
        Route::get('/partners/{partner}/transactions/{transaction}/pdf', [PdfController::class, 'partnerTransaction'])->name('partners.transactions.pdf');
        Route::get('/partners/profit-share', [PartnerController::class, 'profitShareReport'])->name('partners.profit-share');
        Route::get('/partners/{partner}/investments/create', [PartnerTransactionController::class, 'create'])->defaults('transaction_type', 'investment')->name('partners.investments.create');
        Route::get('/partners/{partner}/withdrawals/create', [PartnerTransactionController::class, 'create'])->defaults('transaction_type', 'withdrawal')->name('partners.withdrawals.create');
        Route::get('/partners/{partner}/transactions', [PartnerTransactionController::class, 'index'])->name('partners.transactions.index');
        Route::get('/partners/{partner}/transactions/create', [PartnerTransactionController::class, 'create'])->name('partners.transactions.create');
        Route::post('/partners/{partner}/transactions', [PartnerTransactionController::class, 'store'])->name('partners.transactions.store');
        Route::resource('partners', PartnerController::class)->only(['index', 'create', 'store', 'show', 'edit', 'update']);
    });

    Route::middleware('permission:view_gst_reports')->group(function () {
        Route::get('/gst-reports/pdf', [PdfController::class, 'gstReport'])->name('gst-reports.pdf');
        Route::get('/gst-reports', [GSTReportController::class, 'index'])->name('gst-reports.index');
        Route::get('/gst-reports/sales', [GSTReportController::class, 'sales'])->name('gst-reports.sales');
        Route::get('/gst-reports/purchases', [GSTReportController::class, 'purchases'])->name('gst-reports.purchases');
        Route::get('/gst-reports/sales-returns', [GSTReportController::class, 'salesReturns'])->name('gst-reports.sales-returns');
        Route::get('/gst-reports/purchase-returns', [GSTReportController::class, 'purchaseReturns'])->name('gst-reports.purchase-returns');
        Route::get('/gst-reports/non-gst-sales', [GSTReportController::class, 'nonGstSales'])->name('gst-reports.non-gst-sales');
    });

    Route::get('/gst-reports/export', [GSTReportController::class, 'export'])
        ->middleware('permission:export_gst_reports')
        ->name('gst-reports.export');
});

require __DIR__.'/auth.php';
