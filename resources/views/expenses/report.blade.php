@extends('layouts.app')

@section('title', 'Expense Report')

@section('content')
    <div class="mb-5 flex items-center justify-between">
        <div>
            <h2 class="text-lg font-semibold text-gray-900">Expense Report</h2>
            <p class="text-sm text-gray-500">Date filtered expense register for business cost tracking.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('expenses.category-report', $filters) }}" class="rounded border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Category Report</a>
            <a href="{{ route('expenses.profit-loss', $filters) }}" class="rounded border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Profit & Loss</a>
            <a href="{{ route('expenses.index') }}" class="rounded bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">Expenses</a>
        </div>
    </div>

    @include('expenses.partials.filters', ['action' => route('expenses.report'), 'filters' => $filters])

    <div class="mb-5 rounded bg-white p-5 shadow">
        <p class="text-sm text-gray-500">Filtered Expense Total</p>
        <h3 class="mt-1 text-2xl font-bold text-red-700">Rs. {{ number_format((float) $totalAmount, 2) }}</h3>
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
                    <th class="px-4 py-3 text-right">Action</th>
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
                        <td class="px-4 py-3 text-right">
                            <div class="flex justify-end gap-3">
                                <a href="{{ route('expenses.show', $expense) }}" class="font-semibold text-slate-700 hover:text-slate-900">View</a>
                                <a href="{{ route('expenses.pdf', $expense) }}" target="_blank" class="font-semibold text-slate-700 hover:text-slate-900">Voucher</a>
                                <a href="{{ route('expenses.pdf', ['expense' => $expense, 'download' => 1]) }}" class="font-semibold text-slate-700 hover:text-slate-900">Download</a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-8 text-center text-gray-500">No expenses found for the selected dates.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-5">
        {{ $expenses->links() }}
    </div>
@endsection
