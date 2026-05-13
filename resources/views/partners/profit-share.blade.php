@extends('layouts.erp')

@section('title', 'Profit Share')

@section('content')
    <div class="mb-5 flex items-center justify-between">
        <div>
            <h2 class="text-lg font-semibold text-gray-900">Partner Profit Share Report</h2>
            <p class="text-sm text-gray-500">Enter profit amount to calculate partner share using active share percentage.</p>
        </div>
        <a href="{{ route('partners.index') }}" class="rounded border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Partners</a>
    </div>

    <form method="GET" action="{{ route('partners.profit-share') }}" class="mb-5 rounded bg-white p-5 shadow">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <div class="md:col-span-2">
                <label class="mb-1 block text-sm font-medium text-gray-700">Profit Amount</label>
                <input type="number" name="profit_amount" value="{{ request('profit_amount', $profitAmount ?: '') }}" min="0" step="0.01" class="w-full rounded border-gray-300 text-right shadow-sm focus:border-slate-500 focus:ring-slate-500">
            </div>
            <div class="flex items-end">
                <button class="w-full rounded bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">Calculate</button>
            </div>
        </div>
    </form>

    <div class="mb-5 grid grid-cols-1 gap-4 md:grid-cols-3">
        <div class="rounded bg-white p-5 shadow">
            <p class="text-sm text-gray-500">Profit Amount</p>
            <h3 class="mt-1 text-2xl font-bold text-gray-900">Rs. {{ number_format((float) $profitAmount, 2) }}</h3>
        </div>
        <div class="rounded bg-white p-5 shadow">
            <p class="text-sm text-gray-500">Active Share</p>
            <h3 class="mt-1 text-2xl font-bold text-gray-900">{{ number_format((float) $totalSharePercentage, 2) }}%</h3>
        </div>
        <div class="rounded bg-white p-5 shadow">
            <p class="text-sm text-gray-500">Calculated Share</p>
            <h3 class="mt-1 text-2xl font-bold text-emerald-700">Rs. {{ number_format((float) $totalShareAmount, 2) }}</h3>
        </div>
    </div>

    <div class="overflow-hidden rounded bg-white shadow">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-slate-100 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                <tr>
                    <th class="px-4 py-3">Partner</th>
                    <th class="px-4 py-3 text-right">Share %</th>
                    <th class="px-4 py-3 text-right">Current Capital</th>
                    <th class="px-4 py-3 text-right">Profit Share</th>
                    <th class="px-4 py-3 text-right">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($rows as $row)
                    @php
                        $partner = $row['partner'];
                        $shareAmount = $row['share_amount'];
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-900">{{ $partner->name }}</td>
                        <td class="px-4 py-3 text-right text-gray-900">{{ number_format((float) $partner->share_percentage, 2) }}%</td>
                        <td class="px-4 py-3 text-right text-gray-900">Rs. {{ number_format((float) $partner->current_investment, 2) }}</td>
                        <td class="px-4 py-3 text-right font-semibold text-gray-900">Rs. {{ number_format((float) $shareAmount, 2) }}</td>
                        <td class="px-4 py-3 text-right">
                            @if ($shareAmount > 0)
                                <a href="{{ route('partners.transactions.create', ['partner' => $partner, 'transaction_type' => 'profit_share', 'amount' => $shareAmount, 'notes' => 'Profit share']) }}" class="font-semibold text-slate-700 hover:text-slate-900">Record</a>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-gray-500">No active partners found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
