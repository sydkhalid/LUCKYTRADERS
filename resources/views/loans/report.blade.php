@extends('layouts.app')

@section('title', ucfirst($status).' Loan Report')

@section('content')
    <div class="mb-5 flex items-center justify-between">
        <div>
            <h2 class="text-lg font-semibold text-gray-900">{{ ucfirst($status) }} Loan Report</h2>
            <p class="text-sm text-gray-500">Loan type, party, principal, interest, paid amount, and balance.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route($status === 'active' ? 'loans.reports.closed' : 'loans.reports.active') }}" class="rounded border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                {{ $status === 'active' ? 'Closed Report' : 'Active Report' }}
            </a>
            <a href="{{ route('loans.index') }}" class="rounded bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">Loans</a>
        </div>
    </div>

    <div class="mb-5 grid grid-cols-1 gap-4 md:grid-cols-3">
        <div class="rounded bg-white p-5 shadow">
            <p class="text-sm text-gray-500">Principal</p>
            <h3 class="mt-1 text-2xl font-bold text-gray-900">₹ {{ number_format((float) $totalPrincipal, 2) }}</h3>
        </div>
        <div class="rounded bg-white p-5 shadow">
            <p class="text-sm text-gray-500">Interest</p>
            <h3 class="mt-1 text-2xl font-bold text-gray-900">₹ {{ number_format((float) $totalInterest, 2) }}</h3>
        </div>
        <div class="rounded bg-white p-5 shadow">
            <p class="text-sm text-gray-500">Balance</p>
            <h3 class="mt-1 text-2xl font-bold text-red-700">₹ {{ number_format((float) $totalBalance, 2) }}</h3>
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
                    <th class="px-4 py-3 text-right">Principal</th>
                    <th class="px-4 py-3 text-right">Interest</th>
                    <th class="px-4 py-3 text-right">Paid</th>
                    <th class="px-4 py-3 text-right">Balance</th>
                    <th class="px-4 py-3 text-right">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($loans as $loan)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-900">{{ $loan->loan_no }}</td>
                        <td class="px-4 py-3 text-gray-700">{{ $loan->loan_date?->format('d M Y') }}</td>
                        <td class="px-4 py-3 text-gray-700">{{ $loan->typeLabel() }}</td>
                        <td class="px-4 py-3 text-gray-700">{{ $loan->party_name }}</td>
                        <td class="px-4 py-3 text-right text-gray-900">₹ {{ number_format((float) $loan->principal_amount, 2) }}</td>
                        <td class="px-4 py-3 text-right text-gray-900">₹ {{ number_format((float) $loan->total_interest, 2) }}</td>
                        <td class="px-4 py-3 text-right text-gray-900">₹ {{ number_format((float) $loan->paid_amount, 2) }}</td>
                        <td class="px-4 py-3 text-right font-semibold text-gray-900">₹ {{ number_format((float) $loan->balance_amount, 2) }}</td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('loans.show', $loan) }}" class="font-semibold text-slate-700 hover:text-slate-900">View</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-4 py-8 text-center text-gray-500">No {{ $status }} loans found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-5">
        {{ $loans->links() }}
    </div>
@endsection
