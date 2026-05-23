@extends('layouts.app')

@section('title', 'Customer Ledgers')

@section('content')
    <div class="mb-5 flex items-center justify-between">
        <div>
            <h2 class="text-lg font-semibold text-gray-900">Customers</h2>
            <p class="text-sm text-gray-500">Open any customer to view ledger transactions.</p>
        </div>
        <a href="{{ route('receipts.create') }}" class="rounded bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">New Receipt</a>
    </div>

    <div class="overflow-hidden rounded bg-white shadow">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-slate-100 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                <tr>
                    <th class="px-4 py-3">Customer</th>
                    <th class="px-4 py-3">Phone</th>
                    <th class="px-4 py-3">GST</th>
                    <th class="px-4 py-3 text-right">Current Balance</th>
                    <th class="px-4 py-3 text-right">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($customers as $customer)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-900">{{ $customer->name }}</td>
                        <td class="px-4 py-3 text-gray-700">{{ $customer->phone ?: '-' }}</td>
                        <td class="px-4 py-3 text-gray-700">{{ $customer->gst_number ?: '-' }}</td>
                        <td class="px-4 py-3 text-right font-semibold text-gray-900">₹ {{ number_format((float) $customer->current_balance, 2) }}</td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('ledgers.customers.show', $customer) }}" class="font-semibold text-slate-700 hover:text-slate-900">View Ledger</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-gray-500">No customers found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-5">
        {{ $customers->links() }}
    </div>
@endsection
