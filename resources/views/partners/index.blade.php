@extends('layouts.erp')

@section('title', 'Partners')

@section('content')
    <div class="mb-5 flex items-center justify-between">
        <div>
            <h2 class="text-lg font-semibold text-gray-900">Partner Management</h2>
            <p class="text-sm text-gray-500">Track partner capital, withdrawals, returns, and profit share.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('partners.profit-share') }}" class="rounded border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Profit Share</a>
            <a href="{{ route('partners.create') }}" class="rounded bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">Create Partner</a>
        </div>
    </div>

    <div class="mb-5 grid grid-cols-1 gap-4 md:grid-cols-3">
        <div class="rounded bg-white p-5 shadow">
            <p class="text-sm text-gray-500">Active Partners</p>
            <h3 class="mt-1 text-2xl font-bold text-gray-900">{{ $activeCount }}</h3>
        </div>
        <div class="rounded bg-white p-5 shadow">
            <p class="text-sm text-gray-500">Active Share</p>
            <h3 class="mt-1 text-2xl font-bold text-gray-900">{{ number_format((float) $totalShare, 2) }}%</h3>
        </div>
        <div class="rounded bg-white p-5 shadow">
            <p class="text-sm text-gray-500">Returnable Capital</p>
            <h3 class="mt-1 text-2xl font-bold text-red-700">Rs. {{ number_format((float) $totalInvestment, 2) }}</h3>
        </div>
    </div>

    <div class="overflow-hidden rounded bg-white shadow">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-slate-100 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                <tr>
                    <th class="px-4 py-3">Partner</th>
                    <th class="px-4 py-3">Phone</th>
                    <th class="px-4 py-3">Email</th>
                    <th class="px-4 py-3 text-right">Share</th>
                    <th class="px-4 py-3 text-right">Opening</th>
                    <th class="px-4 py-3 text-right">Current</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3 text-right">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($partners as $partner)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-900">{{ $partner->name }}</td>
                        <td class="px-4 py-3 text-gray-700">{{ $partner->phone ?: '-' }}</td>
                        <td class="px-4 py-3 text-gray-700">{{ $partner->email ?: '-' }}</td>
                        <td class="px-4 py-3 text-right text-gray-900">{{ number_format((float) $partner->share_percentage, 2) }}%</td>
                        <td class="px-4 py-3 text-right text-gray-900">Rs. {{ number_format((float) $partner->opening_investment, 2) }}</td>
                        <td class="px-4 py-3 text-right font-semibold text-gray-900">Rs. {{ number_format((float) $partner->current_investment, 2) }}</td>
                        <td class="px-4 py-3">
                            <span class="rounded px-2 py-1 text-xs font-semibold {{ $partner->status === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-700' }}">
                                {{ ucfirst($partner->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex justify-end gap-3">
                                <a href="{{ route('partners.show', $partner) }}" class="font-semibold text-slate-700 hover:text-slate-900">View</a>
                                <a href="{{ route('partners.edit', $partner) }}" class="font-semibold text-slate-700 hover:text-slate-900">Edit</a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-8 text-center text-gray-500">No partners found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-5">
        {{ $partners->links() }}
    </div>
@endsection
