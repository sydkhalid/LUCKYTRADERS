<?php

namespace App\Http\Controllers;

use App\Http\Requests\Expenses\StoreExpenseCategoryRequest;
use App\Models\ExpenseCategory;

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
}
