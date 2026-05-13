@extends('layouts.erp')

@section('title', 'Suppliers')

@section('content')
    <div class="mb-5 flex items-center justify-between">
        <div>
            <h2 class="text-lg font-semibold text-gray-900">Suppliers</h2>
            <p class="text-sm text-gray-500">Steel supplier details and opening payable balances.</p>
        </div>
        <a href="{{ route('suppliers.create') }}" class="rounded bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">Add Supplier</a>
    </div>

    <div class="overflow-hidden rounded bg-white shadow">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-slate-100 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                <tr>
                    <th class="px-4 py-3">Name</th>
                    <th class="px-4 py-3">Phone</th>
                    <th class="px-4 py-3">GST Number</th>
                    <th class="px-4 py-3 text-right">Opening Balance</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3 text-right">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($suppliers as $supplier)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <div class="font-medium text-gray-900">{{ $supplier->name }}</div>
                            <div class="text-xs text-gray-500">{{ $supplier->email ?: '-' }}</div>
                        </td>
                        <td class="px-4 py-3 text-gray-700">{{ $supplier->phone ?: '-' }}</td>
                        <td class="px-4 py-3 text-gray-700">{{ $supplier->gst_number ?: '-' }}</td>
                        <td class="px-4 py-3 text-right text-gray-900">
                            Rs. {{ number_format((float) $supplier->opening_balance, 2) }} {{ ucfirst($supplier->balance_type) }}
                        </td>
                        <td class="px-4 py-3">
                            <span class="rounded px-2 py-1 text-xs font-semibold {{ $supplier->status === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-600' }}">
                                {{ ucfirst($supplier->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex justify-end gap-3">
                                <a href="{{ route('suppliers.edit', $supplier) }}" class="font-semibold text-slate-700 hover:text-slate-900">Edit</a>
                                @can('delete_records')
                                    <form method="POST" action="{{ route('suppliers.destroy', $supplier) }}" onsubmit="return confirm('Delete this supplier?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="font-semibold text-red-600 hover:text-red-800">Delete</button>
                                    </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-gray-500">No suppliers found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-5">
        {{ $suppliers->links() }}
    </div>
@endsection
