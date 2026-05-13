<?php

use App\Http\Controllers\CashbookController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CustomerReceiptController;
use App\Http\Controllers\DashboardController;
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
use App\Http\Controllers\QuotationController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\StockAdjustmentController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\SupplierPaymentController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index']);
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('product-categories', ProductCategoryController::class)->except(['show']);
    Route::resource('products', ProductController::class)->except(['show']);
    Route::resource('customers', CustomerController::class)->except(['show']);
    Route::resource('suppliers', SupplierController::class)->except(['show']);
    Route::resource('purchases', PurchaseController::class);
    Route::get('/stock-adjustments/movements', [StockAdjustmentController::class, 'movementReport'])->name('stock-adjustments.movements');
    Route::get('/stock-adjustments/products/{product}/history', [StockAdjustmentController::class, 'productHistory'])->name('stock-adjustments.products.history');
    Route::resource('stock-adjustments', StockAdjustmentController::class)->only(['index', 'create', 'store', 'show']);
    Route::get('/quotations/{quotation}/print', [QuotationController::class, 'print'])->name('quotations.print');
    Route::get('/quotations/{quotation}/convert', [QuotationController::class, 'convert'])->name('quotations.convert');
    Route::post('/quotations/{quotation}/convert', [QuotationController::class, 'storeConversion'])->name('quotations.convert.store');
    Route::resource('quotations', QuotationController::class)->except(['destroy']);
    Route::get('/sales/{sale}/print', [SaleController::class, 'printInvoice'])->name('sales.print');
    Route::resource('sales', SaleController::class);

    Route::get('/receipts/create', [CustomerReceiptController::class, 'create'])->name('receipts.create');
    Route::post('/receipts', [CustomerReceiptController::class, 'store'])->name('receipts.store');

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

    Route::resource('expense-categories', ExpenseCategoryController::class)->only(['index', 'create', 'store']);
    Route::get('/expenses/report', [ExpenseController::class, 'report'])->name('expenses.report');
    Route::get('/expenses/category-report', [ExpenseController::class, 'categoryReport'])->name('expenses.category-report');
    Route::resource('expenses', ExpenseController::class)->only(['index', 'create', 'store']);

    Route::get('/loans/reports/active', [LoanController::class, 'activeReport'])->name('loans.reports.active');
    Route::get('/loans/reports/closed', [LoanController::class, 'closedReport'])->name('loans.reports.closed');
    Route::get('/loans/{loan}/transactions', [LoanController::class, 'transactions'])->name('loans.transactions.index');
    Route::get('/loans/{loan}/transactions/create', [LoanController::class, 'createTransaction'])->name('loans.transactions.create');
    Route::post('/loans/{loan}/transactions', [LoanController::class, 'storeTransaction'])->name('loans.transactions.store');
    Route::resource('loans', LoanController::class)->only(['index', 'create', 'store', 'show']);

    Route::get('/partners/profit-share', [PartnerController::class, 'profitShareReport'])->name('partners.profit-share');
    Route::get('/partners/{partner}/investments/create', [PartnerController::class, 'createInvestment'])->name('partners.investments.create');
    Route::get('/partners/{partner}/withdrawals/create', [PartnerController::class, 'createWithdrawal'])->name('partners.withdrawals.create');
    Route::get('/partners/{partner}/transactions/create', [PartnerController::class, 'createTransaction'])->name('partners.transactions.create');
    Route::post('/partners/{partner}/transactions', [PartnerController::class, 'storeTransaction'])->name('partners.transactions.store');
    Route::resource('partners', PartnerController::class)->only(['index', 'create', 'store', 'show']);

    Route::get('/gst-reports', [GSTReportController::class, 'index'])->name('gst-reports.index');
    Route::get('/gst-reports/sales', [GSTReportController::class, 'sales'])->name('gst-reports.sales');
    Route::get('/gst-reports/purchases', [GSTReportController::class, 'purchases'])->name('gst-reports.purchases');
    Route::get('/gst-reports/non-gst-sales', [GSTReportController::class, 'nonGstSales'])->name('gst-reports.non-gst-sales');
    Route::get('/gst-reports/export', [GSTReportController::class, 'export'])->name('gst-reports.export');
});

require __DIR__.'/auth.php';
