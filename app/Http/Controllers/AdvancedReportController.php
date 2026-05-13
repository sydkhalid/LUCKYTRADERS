<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\ExpenseCategory;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Supplier;
use App\Services\ActivityLogger;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdvancedReportController extends Controller
{
    public function index(Request $request)
    {
        return view('reports.index', [
            'reports' => $this->reports(),
            'filters' => $this->filters($request),
            'filterOptions' => $this->filterOptions(),
            'activeReport' => null,
            'reportData' => null,
        ]);
    }

    public function profitLoss(Request $request)
    {
        return $this->showReport($request, 'profit-loss');
    }

    public function productProfit(Request $request)
    {
        return $this->showReport($request, 'product-profit');
    }

    public function customerOutstanding(Request $request)
    {
        return $this->showReport($request, 'customer-outstanding');
    }

    public function supplierOutstanding(Request $request)
    {
        return $this->showReport($request, 'supplier-outstanding');
    }

    public function stockValuation(Request $request)
    {
        return $this->showReport($request, 'stock-valuation');
    }

    public function fastMovingProducts(Request $request)
    {
        return $this->showReport($request, 'fast-moving-products');
    }

    public function slowMovingProducts(Request $request)
    {
        return $this->showReport($request, 'slow-moving-products');
    }

    public function expenseSummary(Request $request)
    {
        return $this->showReport($request, 'expense-summary');
    }

    public function partnerBalance(Request $request)
    {
        return $this->showReport($request, 'partner-balance');
    }

    public function loanSummary(Request $request)
    {
        return $this->showReport($request, 'loan-summary');
    }

    public function gstSummary(Request $request)
    {
        return $this->showReport($request, 'gst-summary');
    }

    public function dailyBusinessSummary(Request $request)
    {
        return $this->showReport($request, 'daily-business-summary');
    }

    public function export(Request $request, string $report, string $format)
    {
        abort_unless(array_key_exists($report, $this->reports()), 404);
        abort_unless(in_array($format, ['pdf', 'csv', 'excel'], true), 404);

        $filters = $this->filters($request);
        $reportData = $this->buildReport($report, $filters, false);
        $fileName = Str::slug($reportData['title'].'-'.now()->format('YmdHis'));

        app(ActivityLogger::class)->log(
            'export_report',
            'advanced_reports',
            $reportData['title'].' exported',
            null,
            [],
            ['report' => $report, 'format' => $format, 'filters' => $filters]
        );

        return match ($format) {
            'pdf' => Pdf::loadView('reports.pdf', [
                'reportData' => $reportData,
                'filters' => $filters,
                'generatedAt' => now(),
            ])->setPaper('a4', 'landscape')->download($fileName.'.pdf'),
            'excel' => $this->excelResponse($reportData, $filters, $fileName.'.xls'),
            default => $this->csvResponse($reportData, $filters, $fileName.'.csv'),
        };
    }

    private function showReport(Request $request, string $report)
    {
        $filters = $this->filters($request);

        return view('reports.index', [
            'reports' => $this->reports(),
            'filters' => $filters,
            'filterOptions' => $this->filterOptions(),
            'activeReport' => $report,
            'reportData' => $this->buildReport($report, $filters),
        ]);
    }

    private function reports(): array
    {
        return [
            'profit-loss' => ['title' => 'Profit & Loss', 'route' => 'reports.profit-loss'],
            'product-profit' => ['title' => 'Product-wise Profit', 'route' => 'reports.product-profit'],
            'customer-outstanding' => ['title' => 'Customer Outstanding', 'route' => 'reports.customer-outstanding'],
            'supplier-outstanding' => ['title' => 'Supplier Outstanding', 'route' => 'reports.supplier-outstanding'],
            'stock-valuation' => ['title' => 'Stock Valuation', 'route' => 'reports.stock-valuation'],
            'fast-moving-products' => ['title' => 'Fast Moving Products', 'route' => 'reports.fast-moving-products'],
            'slow-moving-products' => ['title' => 'Slow Moving Products', 'route' => 'reports.slow-moving-products'],
            'expense-summary' => ['title' => 'Expense Summary', 'route' => 'reports.expense-summary'],
            'partner-balance' => ['title' => 'Partner Balance', 'route' => 'reports.partner-balance'],
            'loan-summary' => ['title' => 'Loan Summary', 'route' => 'reports.loan-summary'],
            'gst-summary' => ['title' => 'GST Summary', 'route' => 'reports.gst-summary'],
            'daily-business-summary' => ['title' => 'Daily Business Summary', 'route' => 'reports.daily-business-summary'],
        ];
    }

    private function buildReport(string $report, array $filters, bool $paginate = true): array
    {
        return match ($report) {
            'profit-loss' => $this->profitLossReport($filters, $paginate),
            'product-profit' => $this->productProfitReport($filters, $paginate),
            'customer-outstanding' => $this->customerOutstandingReport($filters, $paginate),
            'supplier-outstanding' => $this->supplierOutstandingReport($filters, $paginate),
            'stock-valuation' => $this->stockValuationReport($filters, $paginate),
            'fast-moving-products' => $this->movingProductsReport($filters, $paginate, true),
            'slow-moving-products' => $this->movingProductsReport($filters, $paginate, false),
            'expense-summary' => $this->expenseSummaryReport($filters, $paginate),
            'partner-balance' => $this->partnerBalanceReport($filters, $paginate),
            'loan-summary' => $this->loanSummaryReport($filters, $paginate),
            'gst-summary' => $this->gstSummaryReport($filters, $paginate),
            'daily-business-summary' => $this->dailyBusinessSummaryReport($filters, $paginate),
            default => abort(404),
        };
    }

    private function profitLossReport(array $filters, bool $paginate): array
    {
        $totalSales = (float) $this->salesBase($filters)->sum('total_amount');
        $totalPurchase = (float) $this->purchasesBase($filters)->sum('total_amount');
        $grossProfit = (float) $this->saleItemsBase($filters)->sum('sale_items.profit_amount');
        $expenses = (float) $this->expensesBase($filters)->sum('amount');
        $netProfit = $grossProfit - $expenses;

        $rows = collect([
            ['metric' => 'Total Sales', 'amount' => $totalSales],
            ['metric' => 'Total Purchase', 'amount' => $totalPurchase],
            ['metric' => 'Gross Profit', 'amount' => $grossProfit],
            ['metric' => 'Expenses', 'amount' => $expenses],
            ['metric' => 'Net Profit', 'amount' => $netProfit],
        ]);

        return $this->reportPayload('Profit & Loss Report', [
            ['label' => 'Total Sales', 'value' => $totalSales, 'type' => 'currency'],
            ['label' => 'Total Purchase', 'value' => $totalPurchase, 'type' => 'currency'],
            ['label' => 'Gross Profit', 'value' => $grossProfit, 'type' => 'currency'],
            ['label' => 'Expenses', 'value' => $expenses, 'type' => 'currency'],
            ['label' => 'Net Profit', 'value' => $netProfit, 'type' => 'currency'],
        ], [
            ['key' => 'metric', 'label' => 'Metric', 'type' => 'text'],
            ['key' => 'amount', 'label' => 'Amount', 'type' => 'currency'],
        ], $paginate ? $this->paginateCollection($rows) : $rows);
    }

    private function productProfitReport(array $filters, bool $paginate): array
    {
        $query = $this->saleItemsBase($filters)
            ->select('products.id', 'products.name as product', 'products.code', 'product_categories.name as category')
            ->selectRaw('SUM(sale_items.quantity) as quantity_sold')
            ->selectRaw('SUM(sale_items.purchase_cost) as purchase_cost')
            ->selectRaw('SUM(sale_items.subtotal) as sales_amount')
            ->selectRaw('SUM(sale_items.profit_amount) as profit_amount')
            ->groupBy('products.id', 'products.name', 'products.code', 'product_categories.name')
            ->orderByDesc(DB::raw('SUM(sale_items.profit_amount)'));

        $rows = $this->transformRows(
            $this->rows($query, $paginate),
            fn ($row) => $this->withProfitPercentage($row)
        );
        $summaryQuery = $this->saleItemsBase($filters);

        return $this->reportPayload('Product-wise Profit Report', [
            ['label' => 'Sales Amount', 'value' => (float) (clone $summaryQuery)->sum('sale_items.subtotal'), 'type' => 'currency'],
            ['label' => 'Profit Amount', 'value' => (float) (clone $summaryQuery)->sum('sale_items.profit_amount'), 'type' => 'currency'],
        ], $this->productProfitColumns(), $rows);
    }

    private function customerOutstandingReport(array $filters, bool $paginate): array
    {
        $lastPayment = DB::table('payments')
            ->select('party_id')
            ->selectRaw('MAX(payment_date) as last_payment_date')
            ->where('party_type', 'customer')
            ->where('transaction_type', 'receipt')
            ->groupBy('party_id');

        $query = DB::table('customers')
            ->leftJoin('sales', function ($join) use ($filters): void {
                $join->on('customers.id', '=', 'sales.customer_id')
                    ->whereNull('sales.deleted_at');
                $this->applyDateToJoin($join, 'sales.sale_date', $filters);
                $this->applyPaymentStatusToJoin($join, 'sales.payment_status', $filters);
            })
            ->leftJoinSub($lastPayment, 'last_payments', 'last_payments.party_id', '=', 'customers.id')
            ->whereNull('customers.deleted_at')
            ->when($filters['customer_id'], fn ($query, $id) => $query->where('customers.id', $id))
            ->select('customers.id', 'customers.name as customer', 'customers.phone')
            ->selectRaw('COALESCE(SUM(sales.total_amount), 0) as invoice_amount')
            ->selectRaw('COALESCE(SUM(sales.paid_amount), 0) as paid_amount')
            ->selectRaw('COALESCE(SUM(sales.balance_amount), 0) as pending_amount')
            ->selectRaw('MAX(last_payments.last_payment_date) as last_payment_date')
            ->groupBy('customers.id', 'customers.name', 'customers.phone')
            ->orderByDesc(DB::raw('COALESCE(SUM(sales.balance_amount), 0)'));

        return $this->reportPayload('Customer Outstanding Report', [
            ['label' => 'Invoice Amount', 'value' => $this->sumReportColumn($query, 'invoice_amount'), 'type' => 'currency'],
            ['label' => 'Paid Amount', 'value' => $this->sumReportColumn($query, 'paid_amount'), 'type' => 'currency'],
            ['label' => 'Pending Amount', 'value' => $this->sumReportColumn($query, 'pending_amount'), 'type' => 'currency'],
        ], [
            ['key' => 'customer', 'label' => 'Customer', 'type' => 'text'],
            ['key' => 'phone', 'label' => 'Phone', 'type' => 'text'],
            ['key' => 'invoice_amount', 'label' => 'Total Invoice', 'type' => 'currency'],
            ['key' => 'paid_amount', 'label' => 'Paid Amount', 'type' => 'currency'],
            ['key' => 'pending_amount', 'label' => 'Pending Amount', 'type' => 'currency'],
            ['key' => 'last_payment_date', 'label' => 'Last Payment Date', 'type' => 'date'],
        ], $this->rows($query, $paginate));
    }

    private function supplierOutstandingReport(array $filters, bool $paginate): array
    {
        $lastPayment = DB::table('payments')
            ->select('party_id')
            ->selectRaw('MAX(payment_date) as last_payment_date')
            ->where('party_type', 'supplier')
            ->where('transaction_type', 'payment')
            ->groupBy('party_id');

        $query = DB::table('suppliers')
            ->leftJoin('purchases', function ($join) use ($filters): void {
                $join->on('suppliers.id', '=', 'purchases.supplier_id')
                    ->whereNull('purchases.deleted_at');
                $this->applyDateToJoin($join, 'purchases.purchase_date', $filters);
                $this->applyPaymentStatusToJoin($join, 'purchases.payment_status', $filters);
            })
            ->leftJoinSub($lastPayment, 'last_payments', 'last_payments.party_id', '=', 'suppliers.id')
            ->whereNull('suppliers.deleted_at')
            ->when($filters['supplier_id'], fn ($query, $id) => $query->where('suppliers.id', $id))
            ->select('suppliers.id', 'suppliers.name as supplier', 'suppliers.phone')
            ->selectRaw('COALESCE(SUM(purchases.total_amount), 0) as purchase_total')
            ->selectRaw('COALESCE(SUM(purchases.paid_amount), 0) as paid_amount')
            ->selectRaw('COALESCE(SUM(purchases.balance_amount), 0) as pending_payable')
            ->selectRaw('MAX(last_payments.last_payment_date) as last_payment_date')
            ->groupBy('suppliers.id', 'suppliers.name', 'suppliers.phone')
            ->orderByDesc(DB::raw('COALESCE(SUM(purchases.balance_amount), 0)'));

        return $this->reportPayload('Supplier Outstanding Report', [
            ['label' => 'Purchase Total', 'value' => $this->sumReportColumn($query, 'purchase_total'), 'type' => 'currency'],
            ['label' => 'Paid Amount', 'value' => $this->sumReportColumn($query, 'paid_amount'), 'type' => 'currency'],
            ['label' => 'Pending Payable', 'value' => $this->sumReportColumn($query, 'pending_payable'), 'type' => 'currency'],
        ], [
            ['key' => 'supplier', 'label' => 'Supplier', 'type' => 'text'],
            ['key' => 'phone', 'label' => 'Phone', 'type' => 'text'],
            ['key' => 'purchase_total', 'label' => 'Purchase Total', 'type' => 'currency'],
            ['key' => 'paid_amount', 'label' => 'Paid Amount', 'type' => 'currency'],
            ['key' => 'pending_payable', 'label' => 'Pending Payable', 'type' => 'currency'],
            ['key' => 'last_payment_date', 'label' => 'Last Payment Date', 'type' => 'date'],
        ], $this->rows($query, $paginate));
    }

    private function stockValuationReport(array $filters, bool $paginate): array
    {
        $query = DB::table('products')
            ->leftJoin('product_categories', 'products.product_category_id', '=', 'product_categories.id')
            ->whereNull('products.deleted_at')
            ->when($filters['product_id'], fn ($query, $id) => $query->where('products.id', $id))
            ->when($filters['product_category_id'], fn ($query, $id) => $query->where('products.product_category_id', $id))
            ->select('products.id', 'products.name as product', 'products.code', 'products.unit', 'product_categories.name as category')
            ->selectRaw('products.current_stock, products.purchase_price')
            ->selectRaw('(products.current_stock * products.purchase_price) as stock_value')
            ->orderByDesc(DB::raw('(products.current_stock * products.purchase_price)'));

        return $this->reportPayload('Stock Valuation Report', [
            ['label' => 'Stock Value', 'value' => $this->sumReportColumn($query, 'stock_value'), 'type' => 'currency'],
        ], [
            ['key' => 'product', 'label' => 'Product', 'type' => 'text'],
            ['key' => 'code', 'label' => 'Code', 'type' => 'text'],
            ['key' => 'category', 'label' => 'Category', 'type' => 'text'],
            ['key' => 'current_stock', 'label' => 'Current Stock', 'type' => 'quantity'],
            ['key' => 'unit', 'label' => 'Unit', 'type' => 'text'],
            ['key' => 'purchase_price', 'label' => 'Purchase Price', 'type' => 'currency'],
            ['key' => 'stock_value', 'label' => 'Total Stock Value', 'type' => 'currency'],
        ], $this->rows($query, $paginate));
    }

    private function movingProductsReport(array $filters, bool $paginate, bool $fast): array
    {
        $query = DB::table('products')
            ->leftJoin('product_categories', 'products.product_category_id', '=', 'product_categories.id')
            ->leftJoin('sale_items', 'products.id', '=', 'sale_items.product_id')
            ->leftJoin('sales', function ($join) use ($filters): void {
                $join->on('sale_items.sale_id', '=', 'sales.id')
                    ->whereNull('sales.deleted_at');
                $this->applyDateToJoin($join, 'sales.sale_date', $filters);
                $this->applyPaymentStatusToJoin($join, 'sales.payment_status', $filters);
                if ($filters['customer_id']) {
                    $join->where('sales.customer_id', $filters['customer_id']);
                }
            })
            ->whereNull('products.deleted_at')
            ->when($filters['product_id'], fn ($query, $id) => $query->where('products.id', $id))
            ->when($filters['product_category_id'], fn ($query, $id) => $query->where('products.product_category_id', $id))
            ->select('products.id', 'products.name as product', 'products.code', 'product_categories.name as category', 'products.current_stock')
            ->selectRaw('COALESCE(SUM(CASE WHEN sales.id IS NULL THEN 0 ELSE sale_items.quantity END), 0) as sold_quantity')
            ->selectRaw('COALESCE(SUM(CASE WHEN sales.id IS NULL THEN 0 ELSE sale_items.subtotal END), 0) as sales_amount')
            ->selectRaw('MAX(sales.sale_date) as last_sale_date')
            ->groupBy('products.id', 'products.name', 'products.code', 'product_categories.name', 'products.current_stock');

        $fast
            ? $query->orderByDesc('sold_quantity')->orderByDesc('sales_amount')
            : $query->orderBy('sold_quantity')->orderBy('last_sale_date');

        return $this->reportPayload($fast ? 'Fast Moving Products Report' : 'Slow Moving Products Report', [
            ['label' => 'Products', 'value' => $this->countReportRows($query), 'type' => 'number'],
        ], [
            ['key' => 'product', 'label' => 'Product', 'type' => 'text'],
            ['key' => 'code', 'label' => 'Code', 'type' => 'text'],
            ['key' => 'category', 'label' => 'Category', 'type' => 'text'],
            ['key' => 'sold_quantity', 'label' => 'Quantity Sold', 'type' => 'quantity'],
            ['key' => 'sales_amount', 'label' => 'Sales Amount', 'type' => 'currency'],
            ['key' => 'current_stock', 'label' => 'Current Stock', 'type' => 'quantity'],
            ['key' => 'last_sale_date', 'label' => 'Last Sale Date', 'type' => 'date'],
        ], $this->rows($query, $paginate));
    }

    private function expenseSummaryReport(array $filters, bool $paginate): array
    {
        $query = $this->expensesBase($filters)
            ->join('expense_categories', 'expenses.expense_category_id', '=', 'expense_categories.id')
            ->select('expense_categories.id', 'expense_categories.name as category')
            ->selectRaw('COUNT(expenses.id) as expense_count')
            ->selectRaw('SUM(expenses.amount) as total_amount')
            ->groupBy('expense_categories.id', 'expense_categories.name')
            ->orderByDesc(DB::raw('SUM(expenses.amount)'));

        return $this->reportPayload('Expense Summary Report', [
            ['label' => 'Total Expense', 'value' => $this->sumReportColumn($query, 'total_amount'), 'type' => 'currency'],
        ], [
            ['key' => 'category', 'label' => 'Category', 'type' => 'text'],
            ['key' => 'expense_count', 'label' => 'Entries', 'type' => 'number'],
            ['key' => 'total_amount', 'label' => 'Total Amount', 'type' => 'currency'],
        ], $this->rows($query, $paginate));
    }

    private function partnerBalanceReport(array $filters, bool $paginate): array
    {
        $query = DB::table('partners')
            ->leftJoin('partner_transactions', function ($join) use ($filters): void {
                $join->on('partners.id', '=', 'partner_transactions.partner_id');
                $this->applyDateToJoin($join, 'partner_transactions.transaction_date', $filters);
            })
            ->whereNull('partners.deleted_at')
            ->select('partners.id', 'partners.name as partner', 'partners.share_percentage', 'partners.current_investment')
            ->selectRaw("SUM(CASE WHEN partner_transactions.transaction_type = 'investment' THEN partner_transactions.amount ELSE 0 END) as investment")
            ->selectRaw("SUM(CASE WHEN partner_transactions.transaction_type = 'withdrawal' THEN partner_transactions.amount ELSE 0 END) as withdrawal")
            ->selectRaw("SUM(CASE WHEN partner_transactions.transaction_type = 'profit_share' THEN partner_transactions.amount ELSE 0 END) as profit_share")
            ->selectRaw("SUM(CASE WHEN partner_transactions.transaction_type = 'return' THEN partner_transactions.amount ELSE 0 END) as return_amount")
            ->selectRaw("(partners.current_investment + SUM(CASE WHEN partner_transactions.transaction_type = 'profit_share' THEN partner_transactions.amount ELSE 0 END) - SUM(CASE WHEN partner_transactions.transaction_type = 'return' THEN partner_transactions.amount ELSE 0 END)) as current_balance")
            ->groupBy('partners.id', 'partners.name', 'partners.share_percentage', 'partners.current_investment')
            ->orderBy('partners.name');

        return $this->reportPayload('Partner Balance Report', [
            ['label' => 'Partner Investment', 'value' => (float) DB::table('partners')->whereNull('deleted_at')->sum('current_investment'), 'type' => 'currency'],
        ], [
            ['key' => 'partner', 'label' => 'Partner', 'type' => 'text'],
            ['key' => 'share_percentage', 'label' => 'Share %', 'type' => 'percent'],
            ['key' => 'investment', 'label' => 'Investment', 'type' => 'currency'],
            ['key' => 'withdrawal', 'label' => 'Withdrawal', 'type' => 'currency'],
            ['key' => 'profit_share', 'label' => 'Profit Share', 'type' => 'currency'],
            ['key' => 'return_amount', 'label' => 'Return', 'type' => 'currency'],
            ['key' => 'current_balance', 'label' => 'Current Balance', 'type' => 'currency'],
        ], $this->rows($query, $paginate));
    }

    private function loanSummaryReport(array $filters, bool $paginate): array
    {
        $query = DB::table('loans')
            ->leftJoin('partners', 'loans.partner_id', '=', 'partners.id')
            ->whereNull('loans.deleted_at')
            ->when($filters['from_date'], fn ($query, $date) => $query->whereDate('loans.loan_date', '>=', $date))
            ->when($filters['to_date'], fn ($query, $date) => $query->whereDate('loans.loan_date', '<=', $date))
            ->select('loans.id', 'loans.loan_no', 'loans.loan_type', 'loans.party_name', 'partners.name as partner', 'loans.status')
            ->selectRaw('loans.principal_amount, loans.total_interest, loans.total_amount, loans.paid_amount, loans.balance_amount')
            ->orderByDesc('loans.loan_date')
            ->orderByDesc('loans.id');

        $summaryQuery = DB::table('loans')->whereNull('deleted_at')
            ->when($filters['from_date'], fn ($query, $date) => $query->whereDate('loan_date', '>=', $date))
            ->when($filters['to_date'], fn ($query, $date) => $query->whereDate('loan_date', '<=', $date));

        return $this->reportPayload('Loan Summary Report', [
            ['label' => 'Active Loans', 'value' => (clone $summaryQuery)->where('status', 'active')->count(), 'type' => 'number'],
            ['label' => 'Closed Loans', 'value' => (clone $summaryQuery)->where('status', 'closed')->count(), 'type' => 'number'],
            ['label' => 'Pending Balance', 'value' => (float) (clone $summaryQuery)->sum('balance_amount'), 'type' => 'currency'],
            ['label' => 'Interest Summary', 'value' => (float) (clone $summaryQuery)->sum('total_interest'), 'type' => 'currency'],
        ], [
            ['key' => 'loan_no', 'label' => 'Loan No', 'type' => 'text'],
            ['key' => 'loan_type', 'label' => 'Loan Type', 'type' => 'headline'],
            ['key' => 'party_name', 'label' => 'Party', 'type' => 'text'],
            ['key' => 'partner', 'label' => 'Partner', 'type' => 'text'],
            ['key' => 'principal_amount', 'label' => 'Principal', 'type' => 'currency'],
            ['key' => 'total_interest', 'label' => 'Interest', 'type' => 'currency'],
            ['key' => 'paid_amount', 'label' => 'Paid', 'type' => 'currency'],
            ['key' => 'balance_amount', 'label' => 'Balance', 'type' => 'currency'],
            ['key' => 'status', 'label' => 'Status', 'type' => 'headline'],
        ], $this->rows($query, $paginate));
    }

    private function gstSummaryReport(array $filters, bool $paginate): array
    {
        $salesGst = (float) $this->salesBase($filters)->where('bill_type', 'gst')->sum('gst_amount');
        $purchaseGst = (float) $this->purchasesBase($filters)->where('bill_type', 'gst')->sum('gst_amount');
        $salesReturnGst = (float) $this->salesReturnsGst($filters);
        $purchaseReturnGst = (float) $this->purchaseReturnsGst($filters);
        $outputGst = $salesGst - $salesReturnGst;
        $inputGst = $purchaseGst - $purchaseReturnGst;
        $netGst = $outputGst - $inputGst;

        $rows = collect([
            ['metric' => 'GST Sales Output GST', 'amount' => $salesGst],
            ['metric' => 'Less Sales Return GST', 'amount' => $salesReturnGst],
            ['metric' => 'Output GST', 'amount' => $outputGst],
            ['metric' => 'GST Purchase Input GST', 'amount' => $purchaseGst],
            ['metric' => 'Less Purchase Return GST', 'amount' => $purchaseReturnGst],
            ['metric' => 'Input GST', 'amount' => $inputGst],
            ['metric' => 'Net GST Payable', 'amount' => $netGst],
        ]);

        return $this->reportPayload('GST Summary Report', [
            ['label' => 'Output GST', 'value' => $outputGst, 'type' => 'currency'],
            ['label' => 'Input GST', 'value' => $inputGst, 'type' => 'currency'],
            ['label' => 'Net GST Payable', 'value' => $netGst, 'type' => 'currency'],
        ], [
            ['key' => 'metric', 'label' => 'Metric', 'type' => 'text'],
            ['key' => 'amount', 'label' => 'Amount', 'type' => 'currency'],
        ], $paginate ? $this->paginateCollection($rows) : $rows);
    }

    private function dailyBusinessSummaryReport(array $filters, bool $paginate): array
    {
        $dates = CarbonPeriod::create($filters['from_date'], '1 day', $filters['to_date']);
        $sales = $this->dailyTotals($this->salesBase($filters), 'sale_date', 'total_amount');
        $purchases = $this->dailyTotals($this->purchasesBase($filters), 'purchase_date', 'total_amount');
        $collections = $this->dailyTotals($this->paymentsBase($filters)->where('transaction_type', 'receipt'), 'payment_date', 'amount');
        $payments = $this->dailyTotals($this->paymentsBase($filters)->where('transaction_type', 'payment'), 'payment_date', 'amount');
        $cashIn = $this->cashbookDailyTotals($filters, 'cash_in');
        $cashOut = $this->cashbookDailyTotals($filters, 'cash_out');
        $bankIn = $this->cashbookDailyTotals($filters, 'bank_in');
        $bankOut = $this->cashbookDailyTotals($filters, 'bank_out');

        $rows = collect();
        foreach ($dates as $date) {
            $key = $date->toDateString();
            $inflow = ($cashIn[$key] ?? 0) + ($bankIn[$key] ?? 0);
            $outflow = ($cashOut[$key] ?? 0) + ($bankOut[$key] ?? 0);

            $rows->push([
                'date' => $key,
                'daily_sales' => $sales[$key] ?? 0,
                'daily_purchase' => $purchases[$key] ?? 0,
                'daily_collection' => $collections[$key] ?? 0,
                'daily_payment' => $payments[$key] ?? 0,
                'cash_in' => $cashIn[$key] ?? 0,
                'cash_out' => $cashOut[$key] ?? 0,
                'bank_in' => $bankIn[$key] ?? 0,
                'bank_out' => $bankOut[$key] ?? 0,
                'cash_flow' => $inflow - $outflow,
            ]);
        }

        return $this->reportPayload('Daily Business Summary', [
            ['label' => 'Daily Sales', 'value' => (float) $rows->sum('daily_sales'), 'type' => 'currency'],
            ['label' => 'Daily Purchase', 'value' => (float) $rows->sum('daily_purchase'), 'type' => 'currency'],
            ['label' => 'Daily Collection', 'value' => (float) $rows->sum('daily_collection'), 'type' => 'currency'],
            ['label' => 'Daily Payment', 'value' => (float) $rows->sum('daily_payment'), 'type' => 'currency'],
            ['label' => 'Cash Flow', 'value' => (float) $rows->sum('cash_flow'), 'type' => 'currency'],
        ], [
            ['key' => 'date', 'label' => 'Date', 'type' => 'date'],
            ['key' => 'daily_sales', 'label' => 'Daily Sales', 'type' => 'currency'],
            ['key' => 'daily_purchase', 'label' => 'Daily Purchase', 'type' => 'currency'],
            ['key' => 'daily_collection', 'label' => 'Daily Collection', 'type' => 'currency'],
            ['key' => 'daily_payment', 'label' => 'Daily Payment', 'type' => 'currency'],
            ['key' => 'cash_in', 'label' => 'Cash In', 'type' => 'currency'],
            ['key' => 'cash_out', 'label' => 'Cash Out', 'type' => 'currency'],
            ['key' => 'bank_in', 'label' => 'Bank In', 'type' => 'currency'],
            ['key' => 'bank_out', 'label' => 'Bank Out', 'type' => 'currency'],
            ['key' => 'cash_flow', 'label' => 'Cash Flow', 'type' => 'currency'],
        ], $paginate ? $this->paginateCollection($rows->sortByDesc('date')->values()) : $rows);
    }

    private function salesBase(array $filters)
    {
        return DB::table('sales')
            ->whereNull('deleted_at')
            ->whereDate('sale_date', '>=', $filters['from_date'])
            ->whereDate('sale_date', '<=', $filters['to_date'])
            ->when($filters['customer_id'], fn ($query, $id) => $query->where('customer_id', $id))
            ->when($filters['payment_status'], fn ($query, $status) => $query->where('payment_status', $status));
    }

    private function purchasesBase(array $filters)
    {
        return DB::table('purchases')
            ->whereNull('deleted_at')
            ->whereDate('purchase_date', '>=', $filters['from_date'])
            ->whereDate('purchase_date', '<=', $filters['to_date'])
            ->when($filters['supplier_id'], fn ($query, $id) => $query->where('supplier_id', $id))
            ->when($filters['payment_status'], fn ($query, $status) => $query->where('payment_status', $status));
    }

    private function saleItemsBase(array $filters)
    {
        return DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->leftJoin('product_categories', 'products.product_category_id', '=', 'product_categories.id')
            ->whereNull('sales.deleted_at')
            ->whereNull('products.deleted_at')
            ->whereDate('sales.sale_date', '>=', $filters['from_date'])
            ->whereDate('sales.sale_date', '<=', $filters['to_date'])
            ->when($filters['product_id'], fn ($query, $id) => $query->where('products.id', $id))
            ->when($filters['product_category_id'], fn ($query, $id) => $query->where('products.product_category_id', $id))
            ->when($filters['customer_id'], fn ($query, $id) => $query->where('sales.customer_id', $id))
            ->when($filters['payment_status'], fn ($query, $status) => $query->where('sales.payment_status', $status));
    }

    private function expensesBase(array $filters)
    {
        return DB::table('expenses')
            ->whereNull('expenses.deleted_at')
            ->whereDate('expense_date', '>=', $filters['from_date'])
            ->whereDate('expense_date', '<=', $filters['to_date'])
            ->when($filters['category_id'], fn ($query, $id) => $query->where('expense_category_id', $id));
    }

    private function paymentsBase(array $filters)
    {
        return DB::table('payments')
            ->whereNull('deleted_at')
            ->whereDate('payment_date', '>=', $filters['from_date'])
            ->whereDate('payment_date', '<=', $filters['to_date'])
            ->when($filters['customer_id'], fn ($query, $id) => $query->where('party_type', 'customer')->where('party_id', $id))
            ->when($filters['supplier_id'], fn ($query, $id) => $query->where('party_type', 'supplier')->where('party_id', $id));
    }

    private function salesReturnsGst(array $filters): float
    {
        return (float) DB::table('sales_returns')
            ->join('sales', 'sales_returns.sale_id', '=', 'sales.id')
            ->whereNull('sales_returns.deleted_at')
            ->whereNull('sales.deleted_at')
            ->where('sales.bill_type', 'gst')
            ->whereDate('sales_returns.return_date', '>=', $filters['from_date'])
            ->whereDate('sales_returns.return_date', '<=', $filters['to_date'])
            ->when($filters['customer_id'], fn ($query, $id) => $query->where('sales_returns.customer_id', $id))
            ->when($filters['payment_status'], fn ($query, $status) => $query->where('sales.payment_status', $status))
            ->sum('sales_returns.gst_amount');
    }

    private function purchaseReturnsGst(array $filters): float
    {
        return (float) DB::table('purchase_returns')
            ->join('purchases', 'purchase_returns.purchase_id', '=', 'purchases.id')
            ->whereNull('purchase_returns.deleted_at')
            ->whereNull('purchases.deleted_at')
            ->where('purchases.bill_type', 'gst')
            ->whereDate('purchase_returns.return_date', '>=', $filters['from_date'])
            ->whereDate('purchase_returns.return_date', '<=', $filters['to_date'])
            ->when($filters['supplier_id'], fn ($query, $id) => $query->where('purchase_returns.supplier_id', $id))
            ->when($filters['payment_status'], fn ($query, $status) => $query->where('purchases.payment_status', $status))
            ->sum('purchase_returns.gst_amount');
    }

    private function dailyTotals($query, string $dateColumn, string $amountColumn): array
    {
        return $query
            ->selectRaw('DATE('.$dateColumn.') as report_date')
            ->selectRaw('SUM('.$amountColumn.') as total_amount')
            ->groupByRaw('DATE('.$dateColumn.')')
            ->pluck('total_amount', 'report_date')
            ->map(fn ($value) => (float) $value)
            ->all();
    }

    private function cashbookDailyTotals(array $filters, string $type): array
    {
        return DB::table('cashbooks')
            ->where('transaction_type', $type)
            ->whereDate('entry_date', '>=', $filters['from_date'])
            ->whereDate('entry_date', '<=', $filters['to_date'])
            ->selectRaw('DATE(entry_date) as report_date')
            ->selectRaw('SUM(amount) as total_amount')
            ->groupByRaw('DATE(entry_date)')
            ->pluck('total_amount', 'report_date')
            ->map(fn ($value) => (float) $value)
            ->all();
    }

    private function filters(Request $request): array
    {
        $validated = $request->validate([
            'from_date' => ['nullable', 'date'],
            'to_date' => ['nullable', 'date', 'after_or_equal:from_date'],
            'product_id' => ['nullable', 'exists:products,id'],
            'product_category_id' => ['nullable', 'exists:product_categories,id'],
            'customer_id' => ['nullable', 'exists:customers,id'],
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'category_id' => ['nullable', 'exists:expense_categories,id'],
            'payment_status' => ['nullable', 'in:pending,partial,paid'],
        ]);

        return [
            'from_date' => $validated['from_date'] ?? now()->startOfMonth()->toDateString(),
            'to_date' => $validated['to_date'] ?? now()->toDateString(),
            'product_id' => $validated['product_id'] ?? null,
            'product_category_id' => $validated['product_category_id'] ?? null,
            'customer_id' => $validated['customer_id'] ?? null,
            'supplier_id' => $validated['supplier_id'] ?? null,
            'category_id' => $validated['category_id'] ?? null,
            'payment_status' => $validated['payment_status'] ?? null,
        ];
    }

    private function filterOptions(): array
    {
        return [
            'products' => Product::orderBy('name')->get(['id', 'name', 'code']),
            'productCategories' => ProductCategory::orderBy('name')->get(['id', 'name']),
            'customers' => Customer::orderBy('name')->get(['id', 'name']),
            'suppliers' => Supplier::orderBy('name')->get(['id', 'name']),
            'expenseCategories' => ExpenseCategory::orderBy('name')->get(['id', 'name']),
            'paymentStatuses' => ['pending' => 'Pending', 'partial' => 'Partial', 'paid' => 'Paid'],
        ];
    }

    private function rows($query, bool $paginate)
    {
        return $paginate
            ? $query->paginate(25)->withQueryString()
            : $query->get();
    }

    private function transformRows($rows, callable $callback)
    {
        if ($rows instanceof LengthAwarePaginator) {
            $rows->getCollection()->transform($callback);

            return $rows;
        }

        return $rows->transform($callback);
    }

    private function sumReportColumn($query, string $column): float
    {
        return (float) DB::query()
            ->fromSub(clone $query, 'report_rows')
            ->sum($column);
    }

    private function countReportRows($query): int
    {
        return (int) DB::query()
            ->fromSub(clone $query, 'report_rows')
            ->count();
    }

    private function paginateCollection(Collection $rows): LengthAwarePaginator
    {
        $page = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 25;

        return new LengthAwarePaginator(
            $rows->forPage($page, $perPage)->values(),
            $rows->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );
    }

    private function reportPayload(string $title, array $summaryCards, array $columns, $rows): array
    {
        return compact('title', 'summaryCards', 'columns', 'rows');
    }

    private function withProfitPercentage($row)
    {
        $row->profit_percentage = (float) $row->sales_amount > 0
            ? round(((float) $row->profit_amount / (float) $row->sales_amount) * 100, 2)
            : 0;

        return $row;
    }

    private function productProfitColumns(): array
    {
        return [
            ['key' => 'product', 'label' => 'Product', 'type' => 'text'],
            ['key' => 'code', 'label' => 'Code', 'type' => 'text'],
            ['key' => 'category', 'label' => 'Category', 'type' => 'text'],
            ['key' => 'quantity_sold', 'label' => 'Quantity Sold', 'type' => 'quantity'],
            ['key' => 'purchase_cost', 'label' => 'Purchase Cost', 'type' => 'currency'],
            ['key' => 'sales_amount', 'label' => 'Sales Amount', 'type' => 'currency'],
            ['key' => 'profit_amount', 'label' => 'Profit Amount', 'type' => 'currency'],
            ['key' => 'profit_percentage', 'label' => 'Profit %', 'type' => 'percent'],
        ];
    }

    private function applyDateToJoin($join, string $column, array $filters): void
    {
        if ($filters['from_date']) {
            $join->whereDate($column, '>=', $filters['from_date']);
        }

        if ($filters['to_date']) {
            $join->whereDate($column, '<=', $filters['to_date']);
        }
    }

    private function applyPaymentStatusToJoin($join, string $column, array $filters): void
    {
        if ($filters['payment_status']) {
            $join->where($column, $filters['payment_status']);
        }
    }

    private function csvResponse(array $reportData, array $filters, string $fileName): StreamedResponse
    {
        return response()->streamDownload(function () use ($reportData, $filters): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [$reportData['title']]);
            fputcsv($handle, ['From Date', $filters['from_date'], 'To Date', $filters['to_date']]);
            fputcsv($handle, []);

            foreach ($reportData['summaryCards'] as $card) {
                fputcsv($handle, [$card['label'], $this->formatValue($card['value'], $card['type'])]);
            }

            fputcsv($handle, []);
            fputcsv($handle, collect($reportData['columns'])->pluck('label')->all());

            foreach ($reportData['rows'] as $row) {
                fputcsv($handle, collect($reportData['columns'])
                    ->map(fn ($column) => $this->formatValue(data_get($row, $column['key']), $column['type']))
                    ->all());
            }

            fclose($handle);
        }, $fileName, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function excelResponse(array $reportData, array $filters, string $fileName)
    {
        return response()
            ->view('reports.excel', compact('reportData', 'filters'))
            ->header('Content-Type', 'application/vnd.ms-excel; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="'.$fileName.'"');
    }

    public static function formatForView(mixed $value, string $type): string
    {
        if ($value === null || $value === '') {
            return '-';
        }

        return match ($type) {
            'currency' => 'Rs. '.number_format((float) $value, 2),
            'quantity' => number_format((float) $value, 3),
            'percent' => number_format((float) $value, 2).'%',
            'number' => number_format((float) $value),
            'date' => Carbon::parse($value)->format('d M Y'),
            'headline' => Str::headline((string) $value),
            default => (string) $value,
        };
    }

    private function formatValue(mixed $value, string $type): string
    {
        if ($value === null || $value === '') {
            return '-';
        }

        return match ($type) {
            'currency' => 'Rs. '.number_format((float) $value, 2),
            'quantity' => number_format((float) $value, 3),
            'percent' => number_format((float) $value, 2).'%',
            'number' => number_format((float) $value),
            'date' => Carbon::parse($value)->format('d M Y'),
            'headline' => Str::headline((string) $value),
            default => (string) $value,
        };
    }
}
