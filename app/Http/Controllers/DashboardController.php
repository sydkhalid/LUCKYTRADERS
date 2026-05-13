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

        $cashIn = Cashbook::where('transaction_type', 'cash_in')->sum('amount');
        $cashOut = Cashbook::where('transaction_type', 'cash_out')->sum('amount');
        $bankIn = Cashbook::where('transaction_type', 'bank_in')->sum('amount');
        $bankOut = Cashbook::where('transaction_type', 'bank_out')->sum('amount');

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
            'today_sales' => (float) Sale::whereDate('sale_date', $today)->sum('total_amount'),
            'month_sales' => (float) Sale::whereDate('sale_date', '>=', $monthStart)->whereDate('sale_date', '<=', $monthEnd)->sum('total_amount'),
            'today_purchase' => (float) Purchase::whereDate('purchase_date', $today)->sum('total_amount'),
            'month_purchase' => (float) Purchase::whereDate('purchase_date', '>=', $monthStart)->whereDate('purchase_date', '<=', $monthEnd)->sum('total_amount'),
            'cash_balance' => (float) $cashIn - (float) $cashOut,
            'bank_balance' => (float) $bankIn - (float) $bankOut,
            'pending_customer_collection' => (float) Sale::where('balance_amount', '>', 0)->sum('balance_amount'),
            'supplier_payable' => (float) Purchase::where('balance_amount', '>', 0)->sum('balance_amount'),
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

        foreach ($months as $month) {
            $rangeStart = $month->copy()->startOfMonth()->max(Carbon::parse($filters['from_date']));
            $rangeEnd = $month->copy()->endOfMonth()->min(Carbon::parse($filters['to_date']));
            $labels[] = $month->format('M Y');
            $data[] = (float) $modelClass::whereDate($dateColumn, '>=', $rangeStart->toDateString())
                ->whereDate($dateColumn, '<=', $rangeEnd->toDateString())
                ->sum($amountColumn);
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
        $query = Cashbook::whereDate('entry_date', '>=', $filters['from_date'])
            ->whereDate('entry_date', '<=', $filters['to_date']);

        return [
            'labels' => ['Cash In', 'Cash Out', 'Bank In', 'Bank Out'],
            'data' => [
                (float) (clone $query)->where('transaction_type', 'cash_in')->sum('amount'),
                (float) (clone $query)->where('transaction_type', 'cash_out')->sum('amount'),
                (float) (clone $query)->where('transaction_type', 'bank_in')->sum('amount'),
                (float) (clone $query)->where('transaction_type', 'bank_out')->sum('amount'),
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
}
