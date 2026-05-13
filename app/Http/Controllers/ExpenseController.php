<?php

namespace App\Http\Controllers;

use App\Http\Requests\Expenses\StoreExpenseRequest;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Services\ExpensePostingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExpenseController extends Controller
{
    public function index()
    {
        $expenses = Expense::with('category')
            ->latest('expense_date')
            ->latest('id')
            ->paginate(20);

        $todayTotal = Expense::whereDate('expense_date', now()->toDateString())->sum('amount');
        $monthTotal = Expense::whereDate('expense_date', '>=', now()->startOfMonth()->toDateString())
            ->whereDate('expense_date', '<=', now()->endOfMonth()->toDateString())
            ->sum('amount');
        $overallTotal = Expense::sum('amount');

        return view('expenses.index', compact('expenses', 'todayTotal', 'monthTotal', 'overallTotal'));
    }

    public function create()
    {
        $categories = ExpenseCategory::active()->orderBy('name')->get();

        return view('expenses.create', compact('categories'));
    }

    public function store(StoreExpenseRequest $request, ExpensePostingService $postingService)
    {
        $expense = $postingService->recordExpense($request->validated());

        return redirect()
            ->route('expenses.index')
            ->with('success', 'Expense '.$expense->expense_no.' saved successfully.');
    }

    public function report(Request $request)
    {
        $filters = $this->filters($request);
        $query = $this->filteredExpenses($filters)->with('category');
        $totalAmount = (clone $query)->sum('amount');
        $expenses = $query
            ->latest('expense_date')
            ->latest('id')
            ->paginate(30)
            ->withQueryString();

        return view('expenses.report', compact('expenses', 'filters', 'totalAmount'));
    }

    public function categoryReport(Request $request)
    {
        $filters = $this->filters($request);
        $rows = $this->filteredExpenses($filters)
            ->join('expense_categories', 'expenses.expense_category_id', '=', 'expense_categories.id')
            ->select('expense_categories.id', 'expense_categories.name')
            ->selectRaw('COUNT(expenses.id) as expense_count')
            ->selectRaw('SUM(expenses.amount) as total_amount')
            ->groupBy('expense_categories.id', 'expense_categories.name')
            ->orderByDesc(DB::raw('SUM(expenses.amount)'))
            ->get();
        $totalAmount = $rows->sum('total_amount');

        return view('expenses.category-report', compact('rows', 'filters', 'totalAmount'));
    }

    private function filters(Request $request): array
    {
        $validated = $request->validate([
            'from_date' => ['nullable', 'date'],
            'to_date' => ['nullable', 'date', 'after_or_equal:from_date'],
        ]);

        return [
            'from_date' => $validated['from_date'] ?? now()->startOfMonth()->toDateString(),
            'to_date' => $validated['to_date'] ?? now()->toDateString(),
        ];
    }

    private function filteredExpenses(array $filters)
    {
        return Expense::query()
            ->whereDate('expense_date', '>=', $filters['from_date'])
            ->whereDate('expense_date', '<=', $filters['to_date']);
    }
}
