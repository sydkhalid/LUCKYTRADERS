<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Loan;
use App\Models\Partner;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\Quotation;
use App\Models\Sale;
use App\Models\SalesReturn;
use App\Models\StockAdjustment;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Spatie\Activitylog\Models\Activity;
use Yajra\DataTables\Facades\DataTables;

class ErpDataTableController extends Controller
{
    public function __invoke(Request $request, string $module): JsonResponse
    {
        return match ($module) {
            'product-categories' => $this->productCategories($request),
            'products' => $this->products($request),
            'customers' => $this->customers($request),
            'suppliers' => $this->suppliers($request),
            'purchases' => $this->purchases($request),
            'sales' => $this->sales($request),
            'quotations' => $this->quotations($request),
            'receipts' => $this->payments($request, 'receipt', 'customer', 'manage_receipts'),
            'payments' => $this->payments($request, null, null, ['manage_receipts', 'manage_payments']),
            'supplier-payments' => $this->payments($request, 'payment', 'supplier', 'manage_payments'),
            'loans' => $this->loans($request),
            'partners' => $this->partners($request),
            'expenses' => $this->expenses($request),
            'expense-categories' => $this->expenseCategories($request),
            'stock-adjustments' => $this->stockAdjustments($request),
            'sales-returns' => $this->salesReturns($request),
            'purchase-returns' => $this->purchaseReturns($request),
            'gst-sales' => $this->sales($request, 'gst', 'view_gst_reports'),
            'non-gst-sales' => $this->sales($request, 'non_gst', 'view_gst_reports'),
            'gst-purchases' => $this->purchases($request, 'gst', 'view_gst_reports'),
            'gst-sales-returns' => $this->salesReturns($request, true),
            'gst-purchase-returns' => $this->purchaseReturns($request, true),
            'users' => $this->users($request),
            'activity-logs' => $this->activityLogs($request),
            default => abort(404),
        };
    }

    private function productCategories(Request $request): JsonResponse
    {
        $this->authorizeAny($request, 'manage_products');

        $query = ProductCategory::query()
            ->withCount('products')
            ->latest();

        $this->applyDateFilter($query, $request, 'created_at');
        $this->applyStatusFilter($query, $request);

        return DataTables::eloquent($query)
            ->editColumn('status', fn (ProductCategory $category) => $this->badge($category->status))
            ->addColumn('actions', fn (ProductCategory $category) => $this->actions([
                ['View', route('product-categories.show', $category)],
                ['Edit', route('product-categories.edit', $category)],
            ], route('product-categories.destroy', $category), 'Delete this category?'))
            ->rawColumns(['status', 'actions'])
            ->toJson();
    }

    private function products(Request $request): JsonResponse
    {
        $this->authorizeAny($request, 'manage_products');

        $query = Product::query()
            ->with('category')
            ->select('products.*')
            ->latest('products.id');

        $this->applyDateFilter($query, $request, 'created_at');
        $this->applyStatusFilter($query, $request);

        return DataTables::eloquent($query)
            ->addColumn('category', fn (Product $product) => $product->category?->name ?: '-')
            ->editColumn('current_stock', fn (Product $product) => $this->quantity($product->current_stock).' '.$product->unit)
            ->editColumn('selling_price', fn (Product $product) => $this->money($product->selling_price))
            ->editColumn('status', fn (Product $product) => $this->badge($product->status))
            ->addColumn('actions', fn (Product $product) => $this->actions([
                ['View', route('products.show', $product)],
                ['Edit', route('products.edit', $product)],
                ['History', route('stock-adjustments.products.history', $product)],
            ], route('products.destroy', $product), 'Delete this product?'))
            ->rawColumns(['status', 'actions'])
            ->toJson();
    }

    private function customers(Request $request): JsonResponse
    {
        $this->authorizeAny($request, 'manage_customers');

        $query = Customer::query()->latest();
        $this->applyDateFilter($query, $request, 'created_at');
        $this->applyStatusFilter($query, $request);

        return DataTables::eloquent($query)
            ->editColumn('opening_balance', fn (Customer $customer) => $this->money($customer->opening_balance).' '.ucfirst($customer->balance_type))
            ->editColumn('status', fn (Customer $customer) => $this->badge($customer->status))
            ->addColumn('actions', fn (Customer $customer) => $this->actions([
                ['View', route('customers.show', $customer)],
                ['Edit', route('customers.edit', $customer)],
            ], route('customers.destroy', $customer), 'Delete this customer?'))
            ->rawColumns(['status', 'actions'])
            ->toJson();
    }

    private function suppliers(Request $request): JsonResponse
    {
        $this->authorizeAny($request, 'manage_suppliers');

        $query = Supplier::query()->latest();
        $this->applyDateFilter($query, $request, 'created_at');
        $this->applyStatusFilter($query, $request);

        return DataTables::eloquent($query)
            ->editColumn('opening_balance', fn (Supplier $supplier) => $this->money($supplier->opening_balance).' '.ucfirst($supplier->balance_type))
            ->editColumn('status', fn (Supplier $supplier) => $this->badge($supplier->status))
            ->addColumn('actions', fn (Supplier $supplier) => $this->actions([
                ['View', route('suppliers.show', $supplier)],
                ['Edit', route('suppliers.edit', $supplier)],
            ], route('suppliers.destroy', $supplier), 'Delete this supplier?'))
            ->rawColumns(['status', 'actions'])
            ->toJson();
    }

    private function sales(Request $request, ?string $billType = null, string|array $permission = 'manage_sales'): JsonResponse
    {
        $this->authorizeAny($request, $permission);

        $query = Sale::query()
            ->with('customer')
            ->select('sales.*')
            ->when($billType, fn (Builder $query) => $query->where('bill_type', $billType))
            ->latest('sale_date')
            ->latest('id');

        $this->applyDateFilter($query, $request, 'sale_date');
        $this->applyPaymentFilter($query, $request);
        $this->applyBillTypeFilter($query, $request);

        return DataTables::eloquent($query)
            ->addColumn('customer', fn (Sale $sale) => $sale->customer?->name ?: '-')
            ->editColumn('sale_date', fn (Sale $sale) => $this->date($sale->sale_date))
            ->editColumn('bill_type', fn (Sale $sale) => $this->badge($sale->bill_type === 'gst' ? 'GST' : 'Non-GST'))
            ->editColumn('subtotal', fn (Sale $sale) => $this->money($sale->subtotal))
            ->editColumn('gst_amount', fn (Sale $sale) => $this->money($sale->gst_amount))
            ->editColumn('total_amount', fn (Sale $sale) => $this->money($sale->total_amount))
            ->editColumn('paid_amount', fn (Sale $sale) => $this->money($sale->paid_amount))
            ->editColumn('balance_amount', fn (Sale $sale) => $this->money($sale->balance_amount))
            ->editColumn('payment_status', fn (Sale $sale) => $this->badge($sale->payment_status))
            ->addColumn('actions', fn (Sale $sale) => $this->actions([
                ['View', route('sales.show', $sale)],
                ['Print', route('sales.print', $sale)],
                ['PDF', route('sales.pdf', $sale)],
                ['Edit', Route::has('sales.edit') ? route('sales.edit', $sale) : null],
            ], route('sales.destroy', $sale), 'Cancel this sale and reverse stock?'))
            ->rawColumns(['bill_type', 'payment_status', 'actions'])
            ->toJson();
    }

    private function purchases(Request $request, ?string $billType = null, string|array $permission = 'manage_purchases'): JsonResponse
    {
        $this->authorizeAny($request, $permission);

        $query = Purchase::query()
            ->with('supplier')
            ->select('purchases.*')
            ->when($billType, fn (Builder $query) => $query->where('bill_type', $billType))
            ->latest('purchase_date')
            ->latest('id');

        $this->applyDateFilter($query, $request, 'purchase_date');
        $this->applyPaymentFilter($query, $request);
        $this->applyBillTypeFilter($query, $request);

        return DataTables::eloquent($query)
            ->addColumn('supplier', fn (Purchase $purchase) => $purchase->supplier?->name ?: '-')
            ->editColumn('purchase_date', fn (Purchase $purchase) => $this->date($purchase->purchase_date))
            ->editColumn('bill_type', fn (Purchase $purchase) => $this->badge($purchase->bill_type === 'gst' ? 'GST' : 'Non-GST'))
            ->editColumn('subtotal', fn (Purchase $purchase) => $this->money($purchase->subtotal))
            ->editColumn('gst_amount', fn (Purchase $purchase) => $this->money($purchase->gst_amount))
            ->editColumn('total_amount', fn (Purchase $purchase) => $this->money($purchase->total_amount))
            ->editColumn('paid_amount', fn (Purchase $purchase) => $this->money($purchase->paid_amount))
            ->editColumn('balance_amount', fn (Purchase $purchase) => $this->money($purchase->balance_amount))
            ->editColumn('payment_status', fn (Purchase $purchase) => $this->badge($purchase->payment_status))
            ->addColumn('actions', fn (Purchase $purchase) => $this->actions([
                ['View', route('purchases.show', $purchase)],
                ['Print', route('purchases.print', $purchase)],
                ['PDF', route('purchases.pdf', $purchase)],
                ['Edit', Route::has('purchases.edit') ? route('purchases.edit', $purchase) : null],
            ], route('purchases.destroy', $purchase), 'Delete this purchase and reverse stock?'))
            ->rawColumns(['bill_type', 'payment_status', 'actions'])
            ->toJson();
    }

    private function quotations(Request $request): JsonResponse
    {
        $this->authorizeAny($request, 'manage_quotations');

        $query = Quotation::query()
            ->with('customer')
            ->select('quotations.*')
            ->latest('quotation_date')
            ->latest('id');

        $this->applyDateFilter($query, $request, 'quotation_date');
        $this->applyStatusFilter($query, $request);

        return DataTables::eloquent($query)
            ->addColumn('customer', fn (Quotation $quotation) => $quotation->customer?->name ?: '-')
            ->editColumn('quotation_date', fn (Quotation $quotation) => $this->date($quotation->quotation_date))
            ->editColumn('valid_until', fn (Quotation $quotation) => $this->date($quotation->valid_until))
            ->editColumn('total_amount', fn (Quotation $quotation) => $this->money($quotation->total_amount))
            ->editColumn('status', fn (Quotation $quotation) => $this->badge($quotation->statusLabel()))
            ->addColumn('actions', fn (Quotation $quotation) => $this->actions([
                ['View', route('quotations.show', $quotation)],
                ['Edit', route('quotations.edit', $quotation)],
                ['Print', route('quotations.print', $quotation)],
                ['PDF', route('quotations.pdf', $quotation)],
                ['Convert', route('quotations.convert', $quotation)],
            ]))
            ->rawColumns(['status', 'actions'])
            ->toJson();
    }

    private function payments(Request $request, ?string $transactionType, ?string $partyType, string|array $permission): JsonResponse
    {
        $this->authorizeAny($request, $permission);

        $query = Payment::query()
            ->when($transactionType, fn (Builder $query) => $query->where('transaction_type', $transactionType))
            ->when($partyType, fn (Builder $query) => $query->where('party_type', $partyType))
            ->latest('payment_date')
            ->latest('id');

        $this->applyDateFilter($query, $request, 'payment_date');
        $query->when($request->filled('payment_mode'), fn (Builder $query) => $query->where('payment_mode', $request->input('payment_mode')));
        $query->when($request->filled('transaction_type'), fn (Builder $query) => $query->where('transaction_type', $request->input('transaction_type')));

        return DataTables::eloquent($query)
            ->addColumn('party', fn (Payment $payment) => $this->partyName($payment))
            ->addColumn('reference', fn (Payment $payment) => $payment->reference_type
                ? ucfirst(str_replace('_', ' ', $payment->reference_type)).($payment->reference_id ? ' #'.$payment->reference_id : '')
                : '-')
            ->editColumn('payment_date', fn (Payment $payment) => $this->date($payment->payment_date))
            ->editColumn('transaction_type', fn (Payment $payment) => $this->badge($payment->transaction_type))
            ->editColumn('payment_mode', fn (Payment $payment) => $this->badge($payment->payment_mode))
            ->editColumn('amount', fn (Payment $payment) => $this->money($payment->amount))
            ->addColumn('actions', fn (Payment $payment) => $this->actions([
                ['View', $this->paymentShowRoute($payment, $transactionType, $partyType)],
                ['PDF', route('payments.pdf', $payment)],
            ]))
            ->rawColumns(['transaction_type', 'payment_mode', 'actions'])
            ->toJson();
    }

    private function loans(Request $request): JsonResponse
    {
        $this->authorizeAny($request, 'manage_loans');

        $query = Loan::query()->latest('loan_date')->latest('id');
        $this->applyDateFilter($query, $request, 'loan_date');
        $this->applyStatusFilter($query, $request);

        return DataTables::eloquent($query)
            ->editColumn('loan_date', fn (Loan $loan) => $this->date($loan->loan_date))
            ->editColumn('loan_type', fn (Loan $loan) => $this->badge($loan->typeLabel()))
            ->editColumn('total_amount', fn (Loan $loan) => $this->money($loan->total_amount))
            ->editColumn('balance_amount', fn (Loan $loan) => $this->money($loan->balance_amount))
            ->editColumn('status', fn (Loan $loan) => $this->badge($loan->status))
            ->addColumn('actions', fn (Loan $loan) => $this->actions([
                ['View', route('loans.show', $loan)],
                ['Transaction', route('loans.transactions.create', $loan)],
                ['PDF', route('loans.pdf', $loan)],
            ]))
            ->rawColumns(['loan_type', 'status', 'actions'])
            ->toJson();
    }

    private function partners(Request $request): JsonResponse
    {
        $this->authorizeAny($request, 'manage_partners');

        $query = Partner::query()->latest();
        $this->applyDateFilter($query, $request, 'created_at');
        $this->applyStatusFilter($query, $request);

        return DataTables::eloquent($query)
            ->editColumn('current_investment', fn (Partner $partner) => $this->money($partner->current_investment))
            ->editColumn('opening_investment', fn (Partner $partner) => $this->money($partner->opening_investment))
            ->editColumn('share_percentage', fn (Partner $partner) => number_format((float) $partner->share_percentage, 2).'%')
            ->editColumn('status', fn (Partner $partner) => $this->badge($partner->status))
            ->addColumn('actions', fn (Partner $partner) => $this->actions([
                ['View', route('partners.show', $partner)],
                ['Edit', route('partners.edit', $partner)],
                ['Transaction', route('partners.transactions.create', $partner)],
            ]))
            ->rawColumns(['status', 'actions'])
            ->toJson();
    }

    private function expenses(Request $request): JsonResponse
    {
        $this->authorizeAny($request, 'manage_expenses');

        $query = Expense::query()
            ->with('category')
            ->select('expenses.*')
            ->latest('expense_date')
            ->latest('id');

        $this->applyDateFilter($query, $request, 'expense_date');
        $query->when($request->filled('payment_mode'), fn (Builder $query) => $query->where('payment_mode', $request->input('payment_mode')));

        return DataTables::eloquent($query)
            ->addColumn('category', fn (Expense $expense) => $expense->category?->name ?: '-')
            ->editColumn('expense_date', fn (Expense $expense) => $this->date($expense->expense_date))
            ->editColumn('payment_mode', fn (Expense $expense) => $this->badge($expense->payment_mode))
            ->editColumn('amount', fn (Expense $expense) => $this->money($expense->amount))
            ->addColumn('actions', fn (Expense $expense) => $this->actions([
                ['View', route('expenses.show', $expense)],
                ['PDF', route('expenses.pdf', $expense)],
            ]))
            ->rawColumns(['payment_mode', 'actions'])
            ->toJson();
    }

    private function expenseCategories(Request $request): JsonResponse
    {
        $this->authorizeAny($request, 'manage_expenses');

        $query = ExpenseCategory::query()
            ->withCount('expenses')
            ->latest();
        $this->applyDateFilter($query, $request, 'created_at');
        $this->applyStatusFilter($query, $request);

        return DataTables::eloquent($query)
            ->editColumn('status', fn (ExpenseCategory $category) => $this->badge($category->status))
            ->addColumn('actions', fn (ExpenseCategory $category) => $this->actions([
                ['Edit', route('expense-categories.edit', $category)],
            ], route('expense-categories.destroy', $category), 'Delete this expense category?'))
            ->rawColumns(['status', 'actions'])
            ->toJson();
    }

    private function stockAdjustments(Request $request): JsonResponse
    {
        $this->authorizeAny($request, 'manage_stock_adjustments');

        $query = StockAdjustment::query()
            ->with('product')
            ->select('stock_adjustments.*')
            ->latest('adjustment_date')
            ->latest('id');

        $this->applyDateFilter($query, $request, 'adjustment_date');
        $query->when($request->filled('status'), fn (Builder $query) => $query->where('adjustment_type', $request->input('status')));

        return DataTables::eloquent($query)
            ->addColumn('product', fn (StockAdjustment $adjustment) => $adjustment->product?->name ?: '-')
            ->editColumn('adjustment_date', fn (StockAdjustment $adjustment) => $this->date($adjustment->adjustment_date))
            ->editColumn('adjustment_type', fn (StockAdjustment $adjustment) => $this->badge($adjustment->typeLabel()))
            ->editColumn('reason', fn (StockAdjustment $adjustment) => $adjustment->reasonLabel())
            ->editColumn('quantity', fn (StockAdjustment $adjustment) => $this->quantity($adjustment->quantity))
            ->addColumn('actions', fn (StockAdjustment $adjustment) => $this->actions([
                ['View', route('stock-adjustments.show', $adjustment)],
            ]))
            ->rawColumns(['adjustment_type', 'actions'])
            ->toJson();
    }

    private function salesReturns(Request $request, bool $gstOnly = false): JsonResponse
    {
        $this->authorizeAny($request, $gstOnly ? 'view_gst_reports' : 'manage_returns');

        $query = SalesReturn::query()
            ->with(['sale', 'customer'])
            ->select('sales_returns.*')
            ->when($gstOnly, fn (Builder $query) => $query->whereHas('sale', fn (Builder $sale) => $sale->where('bill_type', 'gst')))
            ->latest('return_date')
            ->latest('id');

        $this->applyDateFilter($query, $request, 'return_date');

        return DataTables::eloquent($query)
            ->addColumn('sale', fn (SalesReturn $return) => $return->sale?->sale_no ?: '-')
            ->addColumn('customer', fn (SalesReturn $return) => $return->customer?->name ?: '-')
            ->editColumn('return_date', fn (SalesReturn $return) => $this->date($return->return_date))
            ->editColumn('subtotal', fn (SalesReturn $return) => $this->money($return->subtotal))
            ->editColumn('gst_amount', fn (SalesReturn $return) => $this->money($return->gst_amount))
            ->editColumn('total_amount', fn (SalesReturn $return) => $this->money($return->total_amount))
            ->editColumn('refund_amount', fn (SalesReturn $return) => $this->money($return->refund_amount))
            ->editColumn('adjustment_amount', fn (SalesReturn $return) => $this->money($return->adjustment_amount))
            ->addColumn('actions', fn (SalesReturn $return) => $this->actions([
                ['View', route('sales-returns.show', $return)],
                ['Print', route('sales-returns.print', $return)],
            ]))
            ->rawColumns(['actions'])
            ->toJson();
    }

    private function purchaseReturns(Request $request, bool $gstOnly = false): JsonResponse
    {
        $this->authorizeAny($request, $gstOnly ? 'view_gst_reports' : 'manage_returns');

        $query = PurchaseReturn::query()
            ->with(['purchase', 'supplier'])
            ->select('purchase_returns.*')
            ->when($gstOnly, fn (Builder $query) => $query->whereHas('purchase', fn (Builder $purchase) => $purchase->where('bill_type', 'gst')))
            ->latest('return_date')
            ->latest('id');

        $this->applyDateFilter($query, $request, 'return_date');

        return DataTables::eloquent($query)
            ->addColumn('purchase', fn (PurchaseReturn $return) => $return->purchase?->purchase_no ?: '-')
            ->addColumn('supplier', fn (PurchaseReturn $return) => $return->supplier?->name ?: '-')
            ->editColumn('return_date', fn (PurchaseReturn $return) => $this->date($return->return_date))
            ->editColumn('subtotal', fn (PurchaseReturn $return) => $this->money($return->subtotal))
            ->editColumn('gst_amount', fn (PurchaseReturn $return) => $this->money($return->gst_amount))
            ->editColumn('total_amount', fn (PurchaseReturn $return) => $this->money($return->total_amount))
            ->editColumn('refund_amount', fn (PurchaseReturn $return) => $this->money($return->refund_amount))
            ->editColumn('adjustment_amount', fn (PurchaseReturn $return) => $this->money($return->adjustment_amount))
            ->addColumn('actions', fn (PurchaseReturn $return) => $this->actions([
                ['View', route('purchase-returns.show', $return)],
                ['Print', route('purchase-returns.print', $return)],
            ]))
            ->rawColumns(['actions'])
            ->toJson();
    }

    private function users(Request $request): JsonResponse
    {
        $this->authorizeAny($request, 'manage_users');

        $query = User::query()
            ->with('roles')
            ->latest();
        $this->applyDateFilter($query, $request, 'created_at');
        $query->when($request->filled('status'), fn (Builder $query) => $query->where('role', $request->input('status')));

        return DataTables::eloquent($query)
            ->editColumn('role', fn (User $user) => $this->badge($user->primaryRoleName()))
            ->editColumn('is_admin', fn (User $user) => $user->is_admin ? $this->badge('Active') : $this->badge('Inactive'))
            ->editColumn('created_at', fn (User $user) => $this->date($user->created_at))
            ->addColumn('actions', fn (User $user) => $this->actions([
                ['Edit', route('users.edit', $user)],
            ]))
            ->rawColumns(['role', 'is_admin', 'actions'])
            ->toJson();
    }

    private function activityLogs(Request $request): JsonResponse
    {
        $this->authorizeAny($request, 'view_activity_logs');

        $query = Activity::query()
            ->with(['causer', 'subject'])
            ->latest();

        $this->applyDateFilter($query, $request, 'created_at');
        $query->when($request->filled('user_id'), fn (Builder $query) => $query->where('causer_id', $request->input('user_id')));
        $query->when($request->filled('module'), fn (Builder $query) => $query->where('log_name', $request->input('module')));
        $query->when($request->filled('action'), fn (Builder $query) => $query->where('event', $request->input('action')));

        return DataTables::eloquent($query)
            ->addColumn('user', fn (Activity $activity) => $activity->causer?->name ?: 'System')
            ->editColumn('log_name', fn (Activity $activity) => $this->badge($activity->log_name ?: 'General'))
            ->editColumn('event', fn (Activity $activity) => $this->badge($activity->event ?: 'activity'))
            ->editColumn('created_at', fn (Activity $activity) => $this->dateTime($activity->created_at))
            ->addColumn('actions', fn (Activity $activity) => auth()->user()?->hasRole('Super Admin')
                ? $this->actions([], route('activity-logs.destroy', $activity), 'Delete this activity log?')
                : '')
            ->rawColumns(['log_name', 'event', 'actions'])
            ->toJson();
    }

    private function applyDateFilter(Builder $query, Request $request, string $column): void
    {
        $query
            ->when($request->filled('from_date'), fn (Builder $query) => $query->whereDate($column, '>=', $request->date('from_date')))
            ->when($request->filled('to_date'), fn (Builder $query) => $query->whereDate($column, '<=', $request->date('to_date')));
    }

    private function applyStatusFilter(Builder $query, Request $request): void
    {
        $query->when($request->filled('status'), fn (Builder $query) => $query->where('status', $request->input('status')));
    }

    private function applyPaymentFilter(Builder $query, Request $request): void
    {
        $query->when($request->filled('payment_status'), fn (Builder $query) => $query->where('payment_status', $request->input('payment_status')));
    }

    private function applyBillTypeFilter(Builder $query, Request $request): void
    {
        $query->when($request->filled('bill_type'), fn (Builder $query) => $query->where('bill_type', $request->input('bill_type')));
    }

    private function authorizeAny(Request $request, string|array $permissions): void
    {
        $user = $request->user();
        $permissions = (array) $permissions;

        abort_unless($user && collect($permissions)->contains(fn (string $permission) => $user->can($permission)), 403);
    }

    private function partyName(Payment $payment): string
    {
        return match ($payment->party_type) {
            'customer' => Customer::whereKey($payment->party_id)->value('name') ?: '-',
            'supplier' => Supplier::whereKey($payment->party_id)->value('name') ?: '-',
            default => '-',
        };
    }

    private function paymentShowRoute(Payment $payment, ?string $transactionType, ?string $partyType): string
    {
        return match (true) {
            $transactionType === 'receipt' && $partyType === 'customer' => route('receipts.show', $payment),
            $transactionType === 'payment' && $partyType === 'supplier' => route('supplier-payments.show', $payment),
            default => route('payments.show', $payment),
        };
    }

    private function actions(array $links, ?string $deleteUrl = null, string $deleteTitle = 'Delete this record?'): string
    {
        $html = '<div class="erp-row-actions">';

        foreach ($links as [$label, $url]) {
            if (! $url) {
                continue;
            }

            $html .= '<a href="'.e($url).'" class="erp-action-button '.$this->actionClass($label).'" title="'.e($label).'" aria-label="'.e($label).'">'
                .$this->actionIcon($label)
                .'<span class="visually-hidden erp-action-label">'.e($label).'</span>'
                .'</a>';
        }

        if ($deleteUrl && auth()->user()?->can('delete_records')) {
            $html .= '<form method="POST" action="'.e($deleteUrl).'" data-confirm-delete data-confirm-title="'.e($deleteTitle).'">'
                .csrf_field()
                .method_field('DELETE')
                .'<button type="submit" class="erp-action-button erp-action-delete" title="Delete" aria-label="Delete">'
                .$this->actionIcon('Delete')
                .'<span class="visually-hidden erp-action-label">Delete</span>'
                .'</button>'
                .'</form>';
        }

        return $html.'</div>';
    }

    private function actionClass(string $label): string
    {
        return match (strtolower($label)) {
            'view' => 'erp-action-view',
            'edit' => 'erp-action-edit',
            'print' => 'erp-action-print',
            'pdf' => 'erp-action-pdf',
            default => 'erp-action-extra',
        };
    }

    private function actionIcon(string $label): string
    {
        $attrs = 'class="erp-icon" width="16" height="16" aria-hidden="true" focusable="false" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"';

        $paths = match (strtolower($label)) {
            'view' => '<path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6-10-6-10-6z"/><circle cx="12" cy="12" r="3"/>',
            'edit' => '<path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z"/>',
            'delete' => '<path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/>',
            'print' => '<path d="M6 9V2h12v7"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><path d="M6 14h12v8H6z"/>',
            'pdf' => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M8 16h1.5a1.5 1.5 0 0 0 0-3H8v5"/><path d="M13 13v5h1a2.5 2.5 0 0 0 0-5z"/>',
            'history' => '<path d="M3 12a9 9 0 1 0 3-6.7"/><path d="M3 3v6h6"/><path d="M12 7v5l3 2"/>',
            'convert' => '<path d="M7 7h11l-3-3"/><path d="M17 17H6l3 3"/><path d="M18 7l-3 3"/><path d="M6 17l3-3"/>',
            'transaction' => '<path d="M7 11h10"/><path d="M7 15h6"/><rect x="3" y="4" width="18" height="16" rx="2"/>',
            default => '<circle cx="12" cy="12" r="9"/><path d="M12 8v4"/><path d="M12 16h.01"/>',
        };

        return '<svg '.$attrs.'>'.$paths.'</svg>';
    }

    private function badge(string $value): string
    {
        $normalized = strtolower(str_replace([' ', '_'], '-', $value));
        $tone = match ($normalized) {
            'paid', 'active', 'gst', 'accepted', 'converted', 'cash', 'bank', 'upi', 'receipt', 'increase' => 'success',
            'partial', 'pending', 'draft', 'sent', 'cheque', 'credit' => 'warning',
            'inactive', 'cancelled', 'closed', 'rejected', 'non-gst', 'payment', 'decrease' => 'danger',
            default => 'neutral',
        };

        return '<span class="erp-badge erp-badge-'.$tone.'">'.e($value).'</span>';
    }

    private function money(mixed $value): string
    {
        return 'Rs. '.number_format((float) $value, 2);
    }

    private function quantity(mixed $value): string
    {
        return number_format((float) $value, 3);
    }

    private function date(mixed $value): string
    {
        return $value ? $value->format('d M Y') : '-';
    }

    private function dateTime(mixed $value): string
    {
        return $value ? $value->format('d M Y h:i A') : '-';
    }
}
