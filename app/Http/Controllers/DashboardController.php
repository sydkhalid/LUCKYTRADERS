<?php

namespace App\Http\Controllers;

use App\Models\Cashbook;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\Loan;
use App\Models\Partner;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Supplier;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $filters = $this->filters($request);

        return view('dashboard', $this->dashboardData($filters));
    }

    public function chartData(Request $request)
    {
        return response()->json($this->charts($this->filters($request)));
    }

    public function data(Request $request)
    {
        $payload = $this->dashboardData($this->filters($request));

        return response()->json([
            'filters' => $payload['filters'],
            'cards' => $payload['cards'],
            'charts' => $payload['charts'],
            'cards_html' => view('dashboard.partials.cards', $payload)->render(),
            'widgets_html' => view('dashboard.partials.widgets', $payload)->render(),
            'tables_html' => view('dashboard.partials.tables', $payload)->render(),
        ]);
    }

    private function dashboardData(array $filters): array
    {
        return [
            'filters' => $filters,
            'cards' => $this->cards($filters),
            'charts' => $this->charts($filters),
            'tables' => $this->tables($filters),
        ];
    }

    private function filters(Request $request): array
    {
        $validated = $request->validate([
            'period' => ['nullable', 'in:today,this_month,custom'],
            'from_date' => ['nullable', 'date'],
            'to_date' => ['nullable', 'date', 'after_or_equal:from_date'],
        ]);

        $period = $validated['period'] ?? 'this_month';
        $today = now()->toDateString();

        if ($period === 'today') {
            $fromDate = $today;
            $toDate = $today;
        } elseif ($period === 'custom') {
            $fromDate = $validated['from_date'] ?? now()->startOfMonth()->toDateString();
            $toDate = $validated['to_date'] ?? $today;
        } else {
            $fromDate = now()->startOfMonth()->toDateString();
            $toDate = now()->endOfMonth()->toDateString();
            $period = 'this_month';
        }

        return [
            'period' => $period,
            'from_date' => $fromDate,
            'to_date' => $toDate,
            'label' => Carbon::parse($fromDate)->format('d M Y').' - '.Carbon::parse($toDate)->format('d M Y'),
        ];
    }

    private function cards(array $filters): array
    {
        $today = now()->toDateString();
        $monthStart = now()->startOfMonth()->toDateString();
        $monthEnd = now()->endOfMonth()->toDateString();

        $cashbookTotals = Cashbook::query()
            ->select('transaction_type')
            ->selectRaw('COALESCE(SUM(amount), 0) as total_amount')
            ->groupBy('transaction_type')
            ->pluck('total_amount', 'transaction_type');

        $salesSummary = Sale::query()
            ->selectRaw('COALESCE(SUM(CASE WHEN sale_date = ? THEN total_amount ELSE 0 END), 0) as today_sales', [$today])
            ->selectRaw('COALESCE(SUM(CASE WHEN sale_date BETWEEN ? AND ? THEN total_amount ELSE 0 END), 0) as month_sales', [$monthStart, $monthEnd])
            ->selectRaw('COALESCE(SUM(CASE WHEN balance_amount > 0 THEN balance_amount ELSE 0 END), 0) as pending_customer_collection')
            ->first();

        $purchaseSummary = Purchase::query()
            ->selectRaw('COALESCE(SUM(CASE WHEN purchase_date = ? THEN total_amount ELSE 0 END), 0) as today_purchase', [$today])
            ->selectRaw('COALESCE(SUM(CASE WHEN purchase_date BETWEEN ? AND ? THEN total_amount ELSE 0 END), 0) as month_purchase', [$monthStart, $monthEnd])
            ->selectRaw('COALESCE(SUM(CASE WHEN balance_amount > 0 THEN balance_amount ELSE 0 END), 0) as supplier_payable')
            ->first();

        $periodSales = (float) $this->periodSales($filters)->sum('total_amount');
        $periodPurchases = (float) $this->periodPurchases($filters)->sum('total_amount');
        $periodCollection = (float) Cashbook::query()
            ->whereDate('entry_date', '>=', $filters['from_date'])
            ->whereDate('entry_date', '<=', $filters['to_date'])
            ->whereIn('transaction_type', ['cash_in', 'bank_in'])
            ->sum('amount');
        $todayCollection = (float) Cashbook::query()
            ->whereDate('entry_date', $today)
            ->whereIn('transaction_type', ['cash_in', 'bank_in'])
            ->sum('amount');
        $lowStockCount = (int) Product::where('status', 'active')->where('current_stock', '<=', 10)->count();
        $stockUnits = (float) Product::where('status', 'active')->sum('current_stock');
        $grossProfit = (float) SaleItem::query()
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->whereDate('sales.sale_date', '>=', $filters['from_date'])
            ->whereDate('sales.sale_date', '<=', $filters['to_date'])
            ->sum('sale_items.profit_amount');
        $expenses = Schema::hasTable('expenses')
            ? (float) Expense::whereDate('expense_date', '>=', $filters['from_date'])
                ->whereDate('expense_date', '<=', $filters['to_date'])
                ->sum('amount')
            : 0;

        return [
            'today_sales' => (float) $salesSummary->today_sales,
            'month_sales' => (float) $salesSummary->month_sales,
            'period_sales' => $periodSales,
            'today_purchase' => (float) $purchaseSummary->today_purchase,
            'month_purchase' => (float) $purchaseSummary->month_purchase,
            'period_purchases' => $periodPurchases,
            'today_collection' => $todayCollection,
            'period_collection' => $periodCollection,
            'cash_balance' => (float) ($cashbookTotals['cash_in'] ?? 0) - (float) ($cashbookTotals['cash_out'] ?? 0),
            'bank_balance' => (float) ($cashbookTotals['bank_in'] ?? 0) - (float) ($cashbookTotals['bank_out'] ?? 0),
            'pending_customer_collection' => (float) $salesSummary->pending_customer_collection,
            'supplier_payable' => (float) $purchaseSummary->supplier_payable,
            'stock_value' => (float) Product::selectRaw('COALESCE(SUM(current_stock * purchase_price), 0) as value')->value('value'),
            'stock_units' => $stockUnits,
            'low_stock_count' => $lowStockCount,
            'total_expense' => $expenses,
            'active_loans' => Schema::hasTable('loans') ? (float) Loan::where('status', 'active')->sum('balance_amount') : 0,
            'partner_investment' => Schema::hasTable('partners') ? (float) Partner::where('status', 'active')->sum('current_investment') : 0,
            'net_profit' => (float) $grossProfit - $expenses,
            'customer_count' => (int) Customer::where('status', 'active')->count(),
            'supplier_count' => (int) Supplier::where('status', 'active')->count(),
            'product_count' => (int) Product::where('status', 'active')->count(),
            'profit_margin' => $periodSales > 0 ? round((((float) $grossProfit - $expenses) / $periodSales) * 100, 2) : 0,
        ];
    }

    private function charts(array $filters): array
    {
        return [
            'monthly_sales' => $this->monthlyDataset(Sale::class, 'sale_date', 'total_amount', $filters),
            'monthly_purchases' => $this->monthlyDataset(Purchase::class, 'purchase_date', 'total_amount', $filters),
            'sales_vs_purchases' => $this->salesVsPurchases($filters),
            'gst_split' => $this->gstSplit($filters),
            'cash_flow' => $this->cashFlow($filters),
            'top_products' => $this->topProducts($filters),
            'expense_categories' => $this->expenseCategories($filters),
            'stock_value' => $this->stockValueByCategory(),
            'pending_payments' => $this->pendingPayments(),
            'period_business_mix' => $this->periodBusinessMix($filters),
            'stacked_business_flow' => $this->stackedBusinessFlow($filters),
            'profit_vs_expense' => $this->profitVsExpense($filters),
            'stock_units_by_category' => $this->stockUnitsByCategory(),
        ];
    }

    private function tables(array $filters): array
    {
        return [
            'recent_sales' => $this->periodSales($filters)
                ->with('customer')
                ->latest('sale_date')
                ->latest('id')
                ->limit(8)
                ->get(),
            'recent_purchases' => $this->periodPurchases($filters)
                ->with('supplier')
                ->latest('purchase_date')
                ->latest('id')
                ->limit(8)
                ->get(),
            'low_stock_products' => Product::with('category')
                ->where('status', 'active')
                ->where('current_stock', '<=', 10)
                ->orderBy('current_stock')
                ->orderBy('name')
                ->limit(8)
                ->get(),
            'pending_customer_payments' => Sale::with('customer')
                ->whereDate('sale_date', '>=', $filters['from_date'])
                ->whereDate('sale_date', '<=', $filters['to_date'])
                ->where('balance_amount', '>', 0)
                ->oldest('sale_date')
                ->limit(8)
                ->get(),
            'pending_supplier_payments' => Purchase::with('supplier')
                ->whereDate('purchase_date', '>=', $filters['from_date'])
                ->whereDate('purchase_date', '<=', $filters['to_date'])
                ->where('balance_amount', '>', 0)
                ->oldest('purchase_date')
                ->limit(8)
                ->get(),
            'top_customers' => $this->topCustomers($filters),
            'recent_payments' => $this->recentPayments($filters),
            'active_loans' => Schema::hasTable('loans')
                ? Loan::where('status', 'active')->latest('loan_date')->limit(8)->get()
                : collect(),
        ];
    }

    private function monthlyDataset(string $modelClass, string $dateColumn, string $amountColumn, array $filters): array
    {
        return $this->monthlyTotalsFromQuery($modelClass::query(), $dateColumn, $amountColumn, $filters);
    }

    private function monthlyTotalsFromQuery($query, string $dateColumn, string $amountColumn, array $filters): array
    {
        $start = Carbon::parse($filters['from_date'])->startOfMonth();
        $end = Carbon::parse($filters['to_date'])->startOfMonth();
        $months = CarbonPeriod::create($start, '1 month', $end);
        $labels = [];
        $data = [];
        $monthExpression = $this->monthExpression($dateColumn);
        $totals = (clone $query)
            ->whereDate($dateColumn, '>=', $filters['from_date'])
            ->whereDate($dateColumn, '<=', $filters['to_date'])
            ->selectRaw($monthExpression.' as month_key')
            ->selectRaw('COALESCE(SUM('.$amountColumn.'), 0) as total_amount')
            ->groupByRaw($monthExpression)
            ->pluck('total_amount', 'month_key');

        foreach ($months as $month) {
            $monthKey = $month->format('Y-m');
            $labels[] = $month->format('M Y');
            $data[] = (float) ($totals[$monthKey] ?? 0);
        }

        return compact('labels', 'data');
    }

    private function emptyMonthlyDataset(array $labels): array
    {
        return [
            'labels' => $labels,
            'data' => array_fill(0, count($labels), 0),
        ];
    }

    private function gstSplit(array $filters): array
    {
        return [
            'labels' => ['GST Sales', 'Non-GST Sales'],
            'data' => [
                (float) $this->periodSales($filters)->where('bill_type', 'gst')->sum('total_amount'),
                (float) $this->periodSales($filters)->where('bill_type', 'non_gst')->sum('total_amount'),
            ],
        ];
    }

    private function salesVsPurchases(array $filters): array
    {
        $sales = $this->monthlyDataset(Sale::class, 'sale_date', 'total_amount', $filters);
        $purchases = $this->monthlyDataset(Purchase::class, 'purchase_date', 'total_amount', $filters);

        return [
            'labels' => $sales['labels'],
            'sales' => $sales['data'],
            'purchases' => $purchases['data'],
            'data' => array_merge($sales['data'], $purchases['data']),
        ];
    }

    private function cashFlow(array $filters): array
    {
        $totals = Cashbook::query()
            ->whereDate('entry_date', '>=', $filters['from_date'])
            ->whereDate('entry_date', '<=', $filters['to_date'])
            ->select('transaction_type')
            ->selectRaw('COALESCE(SUM(amount), 0) as total_amount')
            ->groupBy('transaction_type')
            ->pluck('total_amount', 'transaction_type');

        return [
            'labels' => ['Cash In', 'Cash Out', 'Bank In', 'Bank Out'],
            'data' => [
                (float) ($totals['cash_in'] ?? 0),
                (float) ($totals['cash_out'] ?? 0),
                (float) ($totals['bank_in'] ?? 0),
                (float) ($totals['bank_out'] ?? 0),
            ],
        ];
    }

    private function topProducts(array $filters): array
    {
        $rows = SaleItem::query()
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->whereDate('sales.sale_date', '>=', $filters['from_date'])
            ->whereDate('sales.sale_date', '<=', $filters['to_date'])
            ->select('products.name')
            ->selectRaw('SUM(sale_items.quantity) as sold_quantity')
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('sold_quantity')
            ->limit(8)
            ->get();

        return [
            'labels' => $rows->pluck('name')->values()->all(),
            'data' => $rows->pluck('sold_quantity')->map(fn ($value) => round((float) $value, 3))->values()->all(),
        ];
    }

    private function expenseCategories(array $filters): array
    {
        if (! Schema::hasTable('expenses') || ! Schema::hasTable('expense_categories')) {
            return ['labels' => [], 'data' => []];
        }

        $rows = Expense::query()
            ->leftJoin('expense_categories', 'expenses.expense_category_id', '=', 'expense_categories.id')
            ->whereDate('expenses.expense_date', '>=', $filters['from_date'])
            ->whereDate('expenses.expense_date', '<=', $filters['to_date'])
            ->selectRaw("COALESCE(expense_categories.name, 'Uncategorised') as category_name")
            ->selectRaw('COALESCE(SUM(expenses.amount), 0) as total_amount')
            ->groupBy('category_name')
            ->orderByDesc('total_amount')
            ->limit(8)
            ->get();

        return [
            'labels' => $rows->pluck('category_name')->values()->all(),
            'data' => $rows->pluck('total_amount')->map(fn ($value) => (float) $value)->values()->all(),
        ];
    }

    private function stockValueByCategory(): array
    {
        $rows = Product::query()
            ->leftJoin('product_categories', 'products.product_category_id', '=', 'product_categories.id')
            ->selectRaw("COALESCE(product_categories.name, 'Uncategorised') as category_name")
            ->selectRaw('COALESCE(SUM(products.current_stock * products.purchase_price), 0) as stock_value')
            ->groupBy('category_name')
            ->orderByDesc('stock_value')
            ->limit(8)
            ->get();

        return [
            'labels' => $rows->pluck('category_name')->values()->all(),
            'data' => $rows->pluck('stock_value')->map(fn ($value) => (float) $value)->values()->all(),
        ];
    }

    private function pendingPayments(): array
    {
        $customerPending = (float) Sale::where('balance_amount', '>', 0)->sum('balance_amount');
        $supplierPending = (float) Purchase::where('balance_amount', '>', 0)->sum('balance_amount');
        $activeLoans = Schema::hasTable('loans') ? (float) Loan::where('status', 'active')->sum('balance_amount') : 0;

        return [
            'labels' => ['Customer Collection', 'Supplier Payable', 'Active Loans'],
            'data' => [$customerPending, $supplierPending, $activeLoans],
        ];
    }

    private function periodBusinessMix(array $filters): array
    {
        $sales = (float) $this->periodSales($filters)->sum('total_amount');
        $purchases = (float) $this->periodPurchases($filters)->sum('total_amount');
        $collection = (float) Cashbook::query()
            ->whereDate('entry_date', '>=', $filters['from_date'])
            ->whereDate('entry_date', '<=', $filters['to_date'])
            ->whereIn('transaction_type', ['cash_in', 'bank_in'])
            ->sum('amount');
        $expenses = Schema::hasTable('expenses')
            ? (float) Expense::whereDate('expense_date', '>=', $filters['from_date'])
                ->whereDate('expense_date', '<=', $filters['to_date'])
                ->sum('amount')
            : 0;

        return [
            'labels' => ['Sales', 'Purchases', 'Collection', 'Expenses'],
            'data' => [$sales, $purchases, $collection, $expenses],
        ];
    }

    private function stackedBusinessFlow(array $filters): array
    {
        $sales = $this->monthlyDataset(Sale::class, 'sale_date', 'total_amount', $filters);
        $purchases = $this->monthlyDataset(Purchase::class, 'purchase_date', 'total_amount', $filters);
        $collections = $this->monthlyTotalsFromQuery(
            Cashbook::query()->whereIn('transaction_type', ['cash_in', 'bank_in']),
            'entry_date',
            'amount',
            $filters
        );
        $expenses = Schema::hasTable('expenses')
            ? $this->monthlyDataset(Expense::class, 'expense_date', 'amount', $filters)
            : $this->emptyMonthlyDataset($sales['labels']);

        return [
            'labels' => $sales['labels'],
            'sales' => $sales['data'],
            'purchases' => $purchases['data'],
            'collections' => $collections['data'],
            'expenses' => $expenses['data'],
            'data' => array_merge($sales['data'], $purchases['data'], $collections['data'], $expenses['data']),
        ];
    }

    private function profitVsExpense(array $filters): array
    {
        $grossProfit = (float) SaleItem::query()
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->whereDate('sales.sale_date', '>=', $filters['from_date'])
            ->whereDate('sales.sale_date', '<=', $filters['to_date'])
            ->sum('sale_items.profit_amount');
        $expenses = Schema::hasTable('expenses')
            ? (float) Expense::whereDate('expense_date', '>=', $filters['from_date'])
                ->whereDate('expense_date', '<=', $filters['to_date'])
                ->sum('amount')
            : 0;

        return [
            'labels' => ['Gross Profit', 'Expenses', 'Net Profit'],
            'data' => [$grossProfit, $expenses, $grossProfit - $expenses],
        ];
    }

    private function stockUnitsByCategory(): array
    {
        $rows = Product::query()
            ->leftJoin('product_categories', 'products.product_category_id', '=', 'product_categories.id')
            ->selectRaw("COALESCE(product_categories.name, 'Uncategorised') as category_name")
            ->selectRaw('COALESCE(SUM(products.current_stock), 0) as stock_units')
            ->groupBy('category_name')
            ->orderByDesc('stock_units')
            ->limit(8)
            ->get();

        return [
            'labels' => $rows->pluck('category_name')->values()->all(),
            'data' => $rows->pluck('stock_units')->map(fn ($value) => round((float) $value, 3))->values()->all(),
        ];
    }

    private function topCustomers(array $filters)
    {
        return Sale::query()
            ->join('customers', 'sales.customer_id', '=', 'customers.id')
            ->whereDate('sales.sale_date', '>=', $filters['from_date'])
            ->whereDate('sales.sale_date', '<=', $filters['to_date'])
            ->select('customers.name')
            ->selectRaw('COALESCE(SUM(sales.total_amount), 0) as total_amount')
            ->selectRaw('COUNT(sales.id) as invoices_count')
            ->groupBy('customers.id', 'customers.name')
            ->orderByDesc('total_amount')
            ->limit(8)
            ->get();
    }

    private function recentPayments(array $filters)
    {
        if (! Schema::hasTable('payments')) {
            return collect();
        }

        $payments = Payment::query()
            ->whereDate('payment_date', '>=', $filters['from_date'])
            ->whereDate('payment_date', '<=', $filters['to_date'])
            ->latest('payment_date')
            ->latest('id')
            ->limit(8)
            ->get();

        $customerNames = Customer::whereIn('id', $payments->where('party_type', 'customer')->pluck('party_id')->filter()->unique())
            ->pluck('name', 'id');
        $supplierNames = Supplier::whereIn('id', $payments->where('party_type', 'supplier')->pluck('party_id')->filter()->unique())
            ->pluck('name', 'id');

        return $payments->map(function (Payment $payment) use ($customerNames, $supplierNames) {
            $payment->party_name = match ($payment->party_type) {
                'customer' => $customerNames[$payment->party_id] ?? 'Customer #'.$payment->party_id,
                'supplier' => $supplierNames[$payment->party_id] ?? 'Supplier #'.$payment->party_id,
                default => ucfirst((string) $payment->party_type),
            };

            return $payment;
        });
    }

    private function periodSales(array $filters)
    {
        return Sale::whereDate('sale_date', '>=', $filters['from_date'])
            ->whereDate('sale_date', '<=', $filters['to_date']);
    }

    private function periodPurchases(array $filters)
    {
        return Purchase::whereDate('purchase_date', '>=', $filters['from_date'])
            ->whereDate('purchase_date', '<=', $filters['to_date']);
    }

    private function monthExpression(string $column): string
    {
        return DB::connection()->getDriverName() === 'sqlite'
            ? "strftime('%Y-%m', {$column})"
            : "DATE_FORMAT({$column}, '%Y-%m')";
    }
}
