@extends('layouts.erp')

@section('title', 'Loans')

@section('content')
    <div class="mb-5 flex items-center justify-between">
        <div>
            <h2 class="text-lg font-semibold text-gray-900">Loan Management</h2>
            <p class="text-sm text-gray-500">Track borrowed money, money given, partner withdrawals, and partner deposits.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('loans.reports.active') }}" class="rounded border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Active Report</a>
            <a href="{{ route('loans.create') }}" class="rounded bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">Create Loan</a>
        </div>
    </div>

    <div class="mb-5 grid grid-cols-1 gap-4 md:grid-cols-3">
        <div class="rounded bg-white p-5 shadow">
            <p class="text-sm text-gray-500">Active Loans</p>
            <h3 class="mt-1 text-2xl font-bold text-gray-900">{{ $activeCount }}</h3>
        </div>
        <div class="rounded bg-white p-5 shadow">
            <p class="text-sm text-gray-500">Closed Loans</p>
            <h3 class="mt-1 text-2xl font-bold text-gray-900">{{ $closedCount }}</h3>
        </div>
        <div class="rounded bg-white p-5 shadow">
            <p class="text-sm text-gray-500">Active Balance</p>
            <h3 class="mt-1 text-2xl font-bold text-red-700">Rs. {{ number_format((float) $activeBalance, 2) }}</h3>
        </div>
    </div>

    <div class="overflow-hidden rounded bg-white shadow">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-slate-100 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                <tr>
                    <th class="px-4 py-3">Loan No</th>
                    <th class="px-4 py-3">Date</th>
                    <th class="px-4 py-3">Type</th>
                    <th class="px-4 py-3">Party</th>
                    <th class="px-4 py-3 text-right">Total</th>
                    <th class="px-4 py-3 text-right">Paid</th>
                    <th class="px-4 py-3 text-right">Balance</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3 text-right">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($loans as $loan)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-900">{{ $loan->loan_no }}</td>
                        <td class="px-4 py-3 text-gray-700">{{ $loan->loan_date?->format('d M Y') }}</td>
                        <td class="px-4 py-3 text-gray-700">{{ $loan->typeLabel() }}</td>
                        <td class="px-4 py-3 text-gray-700">
                            {{ $loan->party_name }}
                            @if ($loan->party_phone)
                                <span class="block text-xs text-gray-500">{{ $loan->party_phone }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right text-gray-900">Rs. {{ number_format((float) $loan->total_amount, 2) }}</td>
                        <td class="px-4 py-3 text-right text-gray-900">Rs. {{ number_format((float) $loan->paid_amount, 2) }}</td>
                        <td class="px-4 py-3 text-right font-semibold text-gray-900">Rs. {{ number_format((float) $loan->balance_amount, 2) }}</td>
                        <td class="px-4 py-3">
                            <span class="rounded px-2 py-1 text-xs font-semibold {{ $loan->status === 'closed' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                                {{ ucfirst($loan->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('loans.show', $loan) }}" class="font-semibold text-slate-700 hover:text-slate-900">View</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-4 py-8 text-center text-gray-500">No loans recorded yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-5">
        {{ $loans->links() }}
    </div>
@endsection
