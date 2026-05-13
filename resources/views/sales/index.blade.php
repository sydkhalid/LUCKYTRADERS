@extends('layouts.erp')

@section('title', 'Sales / Billing')

@section('content')
    <x-erp.page-header
        title="Sales / Billing"
        description="GST invoices and normal bills with stock, payment, and profit posting."
        kicker="Billing"
    >
        <x-slot:actions>
            <a href="{{ route('sales.create') }}" class="erp-primary-button">Create Sale</a>
        </x-slot:actions>
    </x-erp.page-header>

    <div class="overflow-hidden rounded bg-white shadow">
        <table
            class="min-w-full divide-y divide-gray-200 text-sm"
            data-erp-datatable
            data-ajax-url="{{ route('erp.datatables', 'sales') }}"
            data-search-placeholder="Search invoice, customer, bill..."
            data-empty="No sales found."
        >
            <thead class="bg-slate-100 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                <tr>
                    <th class="px-4 py-3" data-column="sale_no">Sale No</th>
                    <th class="px-4 py-3" data-column="customer" data-orderable="false" data-searchable="false">Customer</th>
                    <th class="px-4 py-3" data-column="sale_date">Date</th>
                    <th class="px-4 py-3" data-column="bill_type">Bill</th>
                    <th class="px-4 py-3 text-right" data-column="total_amount">Total</th>
                    <th class="px-4 py-3 text-right" data-column="paid_amount">Paid</th>
                    <th class="px-4 py-3 text-right" data-column="balance_amount">Balance</th>
                    <th class="px-4 py-3" data-column="payment_status">Status</th>
                    <th class="px-4 py-3 text-right" data-column="actions" data-orderable="false" data-searchable="false">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($sales as $sale)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-900">{{ $sale->sale_no }}</td>
                        <td class="px-4 py-3 text-gray-700">{{ $sale->customer?->name ?: '-' }}</td>
                        <td class="px-4 py-3 text-gray-700">{{ $sale->sale_date?->format('d M Y') }}</td>
                        <td class="px-4 py-3 text-gray-700">{{ $sale->bill_type === 'gst' ? 'GST Invoice' : 'Normal Bill' }}</td>
                        <td class="px-4 py-3 text-right text-gray-900">Rs. {{ number_format((float) $sale->total_amount, 2) }}</td>
                        <td class="px-4 py-3 text-right text-gray-900">Rs. {{ number_format((float) $sale->paid_amount, 2) }}</td>
                        <td class="px-4 py-3 text-right font-semibold text-gray-900">Rs. {{ number_format((float) $sale->balance_amount, 2) }}</td>
                        <td class="px-4 py-3">
                            <x-erp.status-badge :value="ucfirst($sale->payment_status)" />
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex justify-end gap-3">
                                <a href="{{ route('sales.show', $sale) }}" class="font-semibold text-slate-700 hover:text-slate-900">View</a>
                                <a href="{{ route('sales.print', $sale) }}" class="font-semibold text-slate-700 hover:text-slate-900">Print</a>
                                <a href="{{ route('sales.pdf', $sale) }}" target="_blank" class="font-semibold text-slate-700 hover:text-slate-900">PDF</a>
                                @can('edit_old_records')
                                    <a href="{{ route('sales.edit', $sale) }}" class="font-semibold text-slate-700 hover:text-slate-900">Edit</a>
                                @endcan
                                @if (auth()->user()?->hasRole('Super Admin') || auth()->user()?->hasRole('Admin'))
                                    <form method="POST" action="{{ route('sales.destroy', $sale) }}" data-confirm-delete data-confirm-title="Cancel this sale and reverse stock?">
                                        @csrf
                                        @method('DELETE')
                                        <button class="font-semibold text-red-600 hover:text-red-800">Cancel</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-4 py-8 text-center text-gray-500">No sales found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-5">
        {{ $sales->links() }}
    </div>
@endsection
