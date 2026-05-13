<?php

use App\Http\Controllers\CashbookController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CustomerReceiptController;
use App\Http\Controllers\LedgerController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProductCategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\SupplierPaymentController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('/', function () {
        return view('dashboard');
    });

    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('product-categories', ProductCategoryController::class)->except(['show']);
    Route::resource('products', ProductController::class)->except(['show']);
    Route::resource('customers', CustomerController::class)->except(['show']);
    Route::resource('suppliers', SupplierController::class)->except(['show']);
    Route::resource('purchases', PurchaseController::class);
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
});

require __DIR__.'/auth.php';
