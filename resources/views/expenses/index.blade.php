@extends('layouts.erp')

@section('title', 'Expenses')

@section('content')
    <div class="mb-5 flex items-center justify-between">
        <div>
            <h2 class="text-lg font-semibold text-gray-900">Expense Management</h2>
            <p class="text-sm text-gray-500">Record every business expense with cashbook, bankbook, and ledger posting.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('expense-categories.index') }}" class="rounded border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Categories</a>
            <a href="{{ route('expenses.report') }}" class="rounded border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Report</a>
            <a href="{{ route('expenses.create') }}" class="rounded bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">Create Expense</a>
        </div>
    </div>

    <div class="mb-5 grid grid-cols-1 gap-4 md:grid-cols-3">
        <div class="rounded bg-white p-5 shadow">
            <p class="text-sm text-gray-500">Today Expenses</p>
            <h3 class="mt-1 text-2xl font-bold text-red-700">Rs. {{ number_format((float) $todayTotal, 2) }}</h3>
        </div>
        <div class="rounded bg-white p-5 shadow">
            <p class="text-sm text-gray-500">This Month</p>
            <h3 class="mt-1 text-2xl font-bold text-red-700">Rs. {{ number_format((float) $monthTotal, 2) }}</h3>
        </div>
        <div class="rounded bg-white p-5 shadow">
            <p class="text-sm text-gray-500">Total Expenses</p>
            <h3 class="mt-1 text-2xl font-bold text-gray-900">Rs. {{ number_format((float) $overallTotal, 2) }}</h3>
        </div>
    </div>

    <div class="overflow-hidden rounded bg-white shadow">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-slate-100 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                <tr>
                    <th class="px-4 py-3">Expense No</th>
                    <th class="px-4 py-3">Date</th>
                    <th class="px-4 py-3">Category</th>
                    <th class="px-4 py-3">Paid To</th>
                    <th class="px-4 py-3">Mode</th>
                    <th class="px-4 py-3 text-right">Amount</th>
                    <th class="px-4 py-3">Notes</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($expenses as $expense)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-900">{{ $expense->expense_no }}</td>
                        <td class="px-4 py-3 text-gray-700">{{ $expense->expense_date?->format('d M Y') }}</td>
                        <td class="px-4 py-3 text-gray-700">{{ $expense->category?->name ?? '-' }}</td>
                        <td class="px-4 py-3 text-gray-700">{{ $expense->paid_to ?: '-' }}</td>
                        <td class="px-4 py-3 text-gray-700">{{ strtoupper($expense->payment_mode) }}</td>
                        <td class="px-4 py-3 text-right font-semibold text-gray-900">Rs. {{ number_format((float) $expense->amount, 2) }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $expense->notes ?: '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-gray-500">No expenses recorded yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-5">
        {{ $expenses->links() }}
    </div>
@endsection
