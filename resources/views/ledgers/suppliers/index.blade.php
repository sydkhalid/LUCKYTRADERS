@extends('layouts.app')

@section('title', 'Supplier Ledgers')

@section('content')
    <div class="mb-5 flex items-center justify-between">
        <div>
            <h2 class="text-lg font-semibold text-gray-900">Suppliers</h2>
            <p class="text-sm text-gray-500">Open any supplier to view ledger transactions.</p>
        </div>
        <a href="{{ route('supplier-payments.create') }}" class="rounded bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700">New Supplier Payment</a>
    </div>

    <div class="overflow-hidden rounded bg-white shadow">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-slate-100 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                <tr>
                    <th class="px-4 py-3">Supplier</th>
                    <th class="px-4 py-3">Phone</th>
                    <th class="px-4 py-3">GST</th>
                    <th class="px-4 py-3 text-right">Current Balance</th>
                    <th class="px-4 py-3 text-right">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($suppliers as $supplier)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-900">{{ $supplier->name }}</td>
                        <td class="px-4 py-3 text-gray-700">{{ $supplier->phone ?: '-' }}</td>
                        <td class="px-4 py-3 text-gray-700">{{ $supplier->gst_number ?: '-' }}</td>
                        <td class="px-4 py-3 text-right font-semibold text-gray-900">₹ {{ number_format((float) $supplier->current_balance, 2) }}</td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('ledgers.suppliers.show', $supplier) }}" class="font-semibold text-slate-700 hover:text-slate-900">View Ledger</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-gray-500">No suppliers found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-5">
        {{ $suppliers->links() }}
    </div>
@endsection
