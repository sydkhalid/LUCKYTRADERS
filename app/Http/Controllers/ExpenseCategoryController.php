<?php

namespace App\Http\Controllers;

use App\Http\Requests\Expenses\StoreExpenseCategoryRequest;
use App\Http\Requests\Expenses\UpdateExpenseCategoryRequest;
use App\Models\ExpenseCategory;
use Illuminate\Http\Request;

class ExpenseCategoryController extends Controller
{
    public function index()
    {
        $categories = ExpenseCategory::withCount('expenses')
            ->orderBy('name')
            ->paginate(20);

        return view('expense-categories.index', compact('categories'));
    }

    public function create()
    {
        return view('expense-categories.create');
    }

    public function store(StoreExpenseCategoryRequest $request)
    {
        $category = ExpenseCategory::create($request->validated());

        return redirect()
            ->route('expense-categories.index')
            ->with('success', 'Expense category '.$category->name.' created successfully.');
    }

    public function edit(ExpenseCategory $expenseCategory)
    {
        return view('expense-categories.edit', compact('expenseCategory'));
    }

    public function update(UpdateExpenseCategoryRequest $request, ExpenseCategory $expenseCategory)
    {
        $expenseCategory->update($request->validated());

        return redirect()
            ->route('expense-categories.index')
            ->with('success', 'Expense category '.$expenseCategory->name.' updated successfully.');
    }

    public function destroy(Request $request, ExpenseCategory $expenseCategory)
    {
        abort_unless($request->user()?->can('delete_records'), 403);

        $expenseCategory->delete();

        return redirect()
            ->route('expense-categories.index')
            ->with('success', 'Expense category '.$expenseCategory->name.' deleted successfully.');
    }
}
