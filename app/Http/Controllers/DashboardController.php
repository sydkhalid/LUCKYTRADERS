<?php

namespace App\Http\Controllers;

use App\Models\Cashbook;
use App\Models\Expense;
use App\Models\Loan;
use App\Models\Partner;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\SaleItem;
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

        return view('dashboard', [
            'filters' => $filters,
            'cards' => $this->cards($filters),
            'charts' => $this->charts($filters),
            'tables' => $this->tables($filters),
        ]);
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

        $grossProfit = (float) SaleItem::query()
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->whereBetween('sales.sale_date', [$filters['from_date'], $filters['to_date']])
            ->sum('sale_items.profit_amount');
        $expenses = Schema::hasTable('expenses')
            ? (float) Expense::whereBetween('expense_date', [$filters['from_date'], $filters['to_date']])
                ->sum('amount')
            : 0;

        return [
            'today_sales' => (float) $salesSummary->today_sales,
            'month_sales' => (float) $salesSummary->month_sales,
            'today_purchase' => (float) $purchaseSummary->today_purchase,
            'month_purchase' => (float) $purchaseSummary->month_purchase,
            'cash_balance' => (float) ($cashbookTotals['cash_in'] ?? 0) - (float) ($cashbookTotals['cash_out'] ?? 0),
            'bank_balance' => (float) ($cashbookTotals['bank_in'] ?? 0) - (float) ($cashbookTotals['bank_out'] ?? 0),
            'pending_customer_collection' => (float) $salesSummary->pending_customer_collection,
            'supplier_payable' => (float) $purchaseSummary->supplier_payable,
            'stock_value' => (float) Product::selectRaw('COALESCE(SUM(current_stock * purchase_price), 0) as value')->value('value'),
            'total_expense' => $expenses,
            'active_loans' => Schema::hasTable('loans') ? (float) Loan::where('status', 'active')->sum('balance_amount') : 0,
            'partner_investment' => Schema::hasTable('partners') ? (float) Partner::where('status', 'active')->sum('current_investment') : 0,
            'net_profit' => (float) $grossProfit - $expenses,
        ];
    }

    private function charts(array $filters): array
    {
        return [
            'monthly_sales' => $this->monthlyDataset(Sale::class, 'sale_date', 'total_amount', $filters),
            'monthly_purchases' => $this->monthlyDataset(Purchase::class, 'purchase_date', 'total_amount', $filters),
            'gst_split' => $this->gstSplit($filters),
            'cash_flow' => $this->cashFlow($filters),
            'top_products' => $this->topProducts($filters),
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
                ->whereBetween('sale_date', [$filters['from_date'], $filters['to_date']])
                ->where('balance_amount', '>', 0)
                ->oldest('sale_date')
                ->limit(8)
                ->get(),
            'pending_supplier_payments' => Purchase::with('supplier')
                ->whereBetween('purchase_date', [$filters['from_date'], $filters['to_date']])
                ->where('balance_amount', '>', 0)
                ->oldest('purchase_date')
                ->limit(8)
                ->get(),
            'active_loans' => Schema::hasTable('loans')
                ? Loan::where('status', 'active')->latest('loan_date')->limit(8)->get()
                : collect(),
        ];
    }

    private function monthlyDataset(string $modelClass, string $dateColumn, string $amountColumn, array $filters): array
    {
        $start = Carbon::parse($filters['from_date'])->startOfMonth();
        $end = Carbon::parse($filters['to_date'])->startOfMonth();
        $months = CarbonPeriod::create($start, '1 month', $end);
        $labels = [];
        $data = [];
        $monthExpression = $this->monthExpression($dateColumn);
        $totals = $modelClass::query()
            ->whereBetween($dateColumn, [$filters['from_date'], $filters['to_date']])
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

    private function cashFlow(array $filters): array
    {
        $totals = Cashbook::query()
            ->whereBetween('entry_date', [$filters['from_date'], $filters['to_date']])
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
            ->whereBetween('sales.sale_date', [$filters['from_date'], $filters['to_date']])
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

    private function periodSales(array $filters)
    {
        return Sale::whereBetween('sale_date', [$filters['from_date'], $filters['to_date']]);
    }

    private function periodPurchases(array $filters)
    {
        return Purchase::whereBetween('purchase_date', [$filters['from_date'], $filters['to_date']]);
    }

    private function monthExpression(string $column): string
    {
        return DB::connection()->getDriverName() === 'sqlite'
            ? "strftime('%Y-%m', {$column})"
            : "DATE_FORMAT({$column}, '%Y-%m')";
    }
}
