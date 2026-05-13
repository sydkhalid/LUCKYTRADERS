@extends('layouts.erp')

@section('title', 'Category Expense Report')

@section('content')
    <div class="mb-5 flex items-center justify-between">
        <div>
            <h2 class="text-lg font-semibold text-gray-900">Category-wise Expense Report</h2>
            <p class="text-sm text-gray-500">Expense totals grouped by business expense head.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('expenses.report', $filters) }}" class="rounded border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Expense Report</a>
            <a href="{{ route('expenses.index') }}" class="rounded bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">Expenses</a>
        </div>
    </div>

    @include('expenses.partials.filters', ['action' => route('expenses.category-report'), 'filters' => $filters])

    <div class="mb-5 rounded bg-white p-5 shadow">
        <p class="text-sm text-gray-500">Filtered Expense Total</p>
        <h3 class="mt-1 text-2xl font-bold text-red-700">Rs. {{ number_format((float) $totalAmount, 2) }}</h3>
    </div>

    <div class="overflow-hidden rounded bg-white shadow">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-slate-100 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                <tr>
                    <th class="px-4 py-3">Category</th>
                    <th class="px-4 py-3 text-right">Entries</th>
                    <th class="px-4 py-3 text-right">Total Amount</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($rows as $row)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-900">{{ $row->name }}</td>
                        <td class="px-4 py-3 text-right text-gray-900">{{ $row->expense_count }}</td>
                        <td class="px-4 py-3 text-right font-semibold text-gray-900">Rs. {{ number_format((float) $row->total_amount, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-4 py-8 text-center text-gray-500">No category totals found for the selected dates.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
