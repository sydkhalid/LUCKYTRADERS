<?php

use App\Http\Controllers\CashbookController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CustomerReceiptController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentPdfController;
use App\Http\Controllers\ExpenseCategoryController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\GSTReportController;
use App\Http\Controllers\LedgerController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\PartnerController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProductCategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\PurchaseReturnController;
use App\Http\Controllers\QuotationController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SalesReturnController;
use App\Http\Controllers\StockAdjustmentController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\SupplierPaymentController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->middleware('permission:view_dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index'])->middleware('permission:view_dashboard')->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::middleware('permission:manage_users')->group(function () {
        Route::resource('users', UserController::class)->only(['index', 'create', 'store', 'edit', 'update']);
    });

    Route::middleware('permission:manage_products')->group(function () {
        Route::resource('product-categories', ProductCategoryController::class)->except(['show']);
        Route::resource('products', ProductController::class)->except(['show']);
    });

    Route::resource('customers', CustomerController::class)->except(['show'])->middleware('permission:manage_customers');
    Route::resource('suppliers', SupplierController::class)->except(['show'])->middleware('permission:manage_suppliers');
    Route::middleware('permission:manage_purchases')->group(function () {
        Route::get('/purchases/{purchase}/pdf', [DocumentPdfController::class, 'purchase'])->name('purchases.pdf');
        Route::resource('purchases', PurchaseController::class);
        Route::get('/purchase-returns/report', [PurchaseReturnController::class, 'report'])->name('purchase-returns.report');
        Route::resource('purchase-returns', PurchaseReturnController::class)->only(['index', 'create', 'store', 'show']);
    });

    Route::middleware('permission:manage_stock_adjustments')->group(function () {
        Route::get('/stock-adjustments/movements', [StockAdjustmentController::class, 'movementReport'])->name('stock-adjustments.movements');
        Route::get('/stock-adjustments/products/{product}/history', [StockAdjustmentController::class, 'productHistory'])->name('stock-adjustments.products.history');
        Route::resource('stock-adjustments', StockAdjustmentController::class)->only(['index', 'create', 'store', 'show']);
    });

    Route::middleware('permission:manage_sales')->group(function () {
        Route::get('/quotations/{quotation}/pdf', [DocumentPdfController::class, 'quotation'])->name('quotations.pdf');
        Route::get('/quotations/{quotation}/print', [QuotationController::class, 'print'])->name('quotations.print');
        Route::get('/quotations/{quotation}/convert', [QuotationController::class, 'convert'])->name('quotations.convert');
        Route::post('/quotations/{quotation}/convert', [QuotationController::class, 'storeConversion'])->name('quotations.convert.store');
        Route::resource('quotations', QuotationController::class)->except(['destroy']);
        Route::resource('sales', SaleController::class);
        Route::get('/sales-returns/report', [SalesReturnController::class, 'report'])->name('sales-returns.report');
        Route::resource('sales-returns', SalesReturnController::class)->only(['index', 'create', 'store', 'show']);
    });

    Route::get('/sales/{sale}/print', [SaleController::class, 'printInvoice'])
        ->middleware('permission:print_invoice')
        ->name('sales.print');
    Route::get('/sales/{sale}/pdf', [DocumentPdfController::class, 'sale'])
        ->middleware('permission:print_invoice')
        ->name('sales.pdf');

    Route::middleware('permission:manage_sales|manage_payments')->group(function () {
        Route::get('/receipts/create', [CustomerReceiptController::class, 'create'])->name('receipts.create');
        Route::post('/receipts', [CustomerReceiptController::class, 'store'])->name('receipts.store');
    });

    Route::middleware('permission:manage_payments')->group(function () {
        Route::get('/payments/{payment}/pdf', [DocumentPdfController::class, 'payment'])->name('payments.pdf');
        Route::get('/supplier-payments/create', [SupplierPaymentController::class, 'create'])->name('supplier-payments.create');
        Route::post('/supplier-payments', [SupplierPaymentController::class, 'store'])->name('supplier-payments.store');
        Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
        Route::get('/ledgers', [LedgerController::class, 'index'])->name('ledgers.index');
        Route::get('/ledgers/customers', [LedgerController::class, 'customers'])->name('ledgers.customers.index');
        Route::get('/ledgers/customers/{customer}', [LedgerController::class, 'customerShow'])->name('ledgers.customers.show');
        Route::get('/ledgers/suppliers', [LedgerController::class, 'suppliers'])->name('ledgers.suppliers.index');
        Route::get('/ledgers/suppliers/{supplier}', [LedgerController::class, 'supplierShow'])->name('ledgers.suppliers.show');
        Route::get('/cashbook', [CashbookController::class, 'cashbook'])->name('cashbook.index');
        Route::get('/bankbook', [CashbookController::class, 'bankbook'])->name('bankbook.index');
    });

    Route::middleware('permission:manage_expenses')->group(function () {
        Route::get('/expenses/{expense}/pdf', [DocumentPdfController::class, 'expense'])->name('expenses.pdf');
        Route::resource('expense-categories', ExpenseCategoryController::class)->only(['index', 'create', 'store']);
        Route::get('/expenses/report', [ExpenseController::class, 'report'])->name('expenses.report');
        Route::get('/expenses/category-report', [ExpenseController::class, 'categoryReport'])->name('expenses.category-report');
        Route::resource('expenses', ExpenseController::class)->only(['index', 'create', 'store']);
    });

    Route::middleware('permission:manage_loans')->group(function () {
        Route::get('/loans/{loan}/pdf', [DocumentPdfController::class, 'loan'])->name('loans.pdf');
        Route::get('/loans/{loan}/transactions/{transaction}/pdf', [DocumentPdfController::class, 'loanTransaction'])->name('loans.transactions.pdf');
        Route::get('/loans/reports/active', [LoanController::class, 'activeReport'])->name('loans.reports.active');
        Route::get('/loans/reports/closed', [LoanController::class, 'closedReport'])->name('loans.reports.closed');
        Route::get('/loans/{loan}/transactions', [LoanController::class, 'transactions'])->name('loans.transactions.index');
        Route::get('/loans/{loan}/transactions/create', [LoanController::class, 'createTransaction'])->name('loans.transactions.create');
        Route::post('/loans/{loan}/transactions', [LoanController::class, 'storeTransaction'])->name('loans.transactions.store');
        Route::resource('loans', LoanController::class)->only(['index', 'create', 'store', 'show']);
    });

    Route::middleware('permission:manage_partners')->group(function () {
        Route::get('/partners/{partner}/transactions/{transaction}/pdf', [DocumentPdfController::class, 'partnerTransaction'])->name('partners.transactions.pdf');
        Route::get('/partners/profit-share', [PartnerController::class, 'profitShareReport'])->name('partners.profit-share');
        Route::get('/partners/{partner}/investments/create', [PartnerController::class, 'createInvestment'])->name('partners.investments.create');
        Route::get('/partners/{partner}/withdrawals/create', [PartnerController::class, 'createWithdrawal'])->name('partners.withdrawals.create');
        Route::get('/partners/{partner}/transactions/create', [PartnerController::class, 'createTransaction'])->name('partners.transactions.create');
        Route::post('/partners/{partner}/transactions', [PartnerController::class, 'storeTransaction'])->name('partners.transactions.store');
        Route::resource('partners', PartnerController::class)->only(['index', 'create', 'store', 'show']);
    });

    Route::middleware('permission:view_gst_reports')->group(function () {
        Route::get('/gst-reports/pdf', [DocumentPdfController::class, 'gstReport'])->name('gst-reports.pdf');
        Route::get('/gst-reports', [GSTReportController::class, 'index'])->name('gst-reports.index');
        Route::get('/gst-reports/sales', [GSTReportController::class, 'sales'])->name('gst-reports.sales');
        Route::get('/gst-reports/purchases', [GSTReportController::class, 'purchases'])->name('gst-reports.purchases');
        Route::get('/gst-reports/non-gst-sales', [GSTReportController::class, 'nonGstSales'])->name('gst-reports.non-gst-sales');
    });

    Route::get('/gst-reports/export', [GSTReportController::class, 'export'])
        ->middleware('permission:export_gst_reports')
        ->name('gst-reports.export');
});

require __DIR__.'/auth.php';
