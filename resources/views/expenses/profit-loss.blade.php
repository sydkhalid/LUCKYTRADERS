@extends('layouts.erp')

@section('title', 'Profit & Loss')

@section('content')
    <div class="mb-5 flex items-center justify-between">
        <div>
            <h2 class="text-lg font-semibold text-gray-900">Profit & Loss Report</h2>
            <p class="text-sm text-gray-500">Gross product profit minus business expenses for the selected period.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('expenses.report', $filters) }}" class="rounded border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Expense Report</a>
            <a href="{{ route('expenses.index') }}" class="rounded bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">Expenses</a>
        </div>
    </div>

    @include('expenses.partials.filters', ['action' => route('expenses.profit-loss'), 'filters' => $filters])

    <div class="mb-5 grid grid-cols-1 gap-4 md:grid-cols-3">
        <div class="rounded bg-white p-5 shadow">
            <p class="text-sm text-gray-500">Gross Profit</p>
            <h3 class="mt-1 text-2xl font-bold text-emerald-700">Rs. {{ number_format((float) $grossProfit, 2) }}</h3>
        </div>
        <div class="rounded bg-white p-5 shadow">
            <p class="text-sm text-gray-500">Expenses</p>
            <h3 class="mt-1 text-2xl font-bold text-red-700">Rs. {{ number_format((float) $expenseTotal, 2) }}</h3>
        </div>
        <div class="rounded bg-white p-5 shadow">
            <p class="text-sm text-gray-500">Net Profit</p>
            <h3 class="mt-1 text-2xl font-bold {{ $netProfit >= 0 ? 'text-gray-900' : 'text-red-700' }}">Rs. {{ number_format((float) $netProfit, 2) }}</h3>
        </div>
    </div>

    <div class="overflow-hidden rounded bg-white shadow">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-slate-100 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                <tr>
                    <th class="px-4 py-3">Expense Category</th>
                    <th class="px-4 py-3 text-right">Amount</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($categoryRows as $row)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-900">{{ $row->name }}</td>
                        <td class="px-4 py-3 text-right font-semibold text-gray-900">Rs. {{ number_format((float) $row->total_amount, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2" class="px-4 py-8 text-center text-gray-500">No expenses found for the selected dates.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
