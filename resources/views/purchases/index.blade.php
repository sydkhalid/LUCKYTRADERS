@extends('layouts.erp')

@section('title', 'Purchases')

@section('content')
    <x-erp.page-header
        title="Purchases"
        description="Supplier invoices, stock inward, GST input, and payable balance."
        kicker="Procurement"
    >
        <x-slot:actions>
            <a href="{{ route('purchases.create') }}" class="erp-primary-button">New Purchase</a>
        </x-slot:actions>
    </x-erp.page-header>

    <div class="overflow-hidden rounded bg-white shadow">
        <table
            class="min-w-full divide-y divide-gray-200 text-sm"
            data-erp-datatable
            data-ajax-url="{{ route('erp.datatables', 'purchases') }}"
            data-search-placeholder="Search purchase, supplier, bill..."
            data-empty="No purchases found."
        >
            <thead class="bg-slate-100 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                <tr>
                    <th class="px-4 py-3" data-column="purchase_no">Purchase No</th>
                    <th class="px-4 py-3" data-column="supplier" data-orderable="false" data-searchable="false">Supplier</th>
                    <th class="px-4 py-3" data-column="purchase_date">Date</th>
                    <th class="px-4 py-3" data-column="bill_type">Bill</th>
                    <th class="px-4 py-3 text-right" data-column="total_amount">Total</th>
                    <th class="px-4 py-3 text-right" data-column="paid_amount">Paid</th>
                    <th class="px-4 py-3 text-right" data-column="balance_amount">Balance</th>
                    <th class="px-4 py-3" data-column="payment_status">Status</th>
                    <th class="px-4 py-3 text-right" data-column="actions" data-orderable="false" data-searchable="false">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($purchases as $purchase)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-900">{{ $purchase->purchase_no }}</td>
                        <td class="px-4 py-3 text-gray-700">{{ $purchase->supplier?->name ?: '-' }}</td>
                        <td class="px-4 py-3 text-gray-700">{{ $purchase->purchase_date?->format('d M Y') }}</td>
                        <td class="px-4 py-3 text-gray-700">{{ strtoupper(str_replace('_', ' ', $purchase->bill_type)) }}</td>
                        <td class="px-4 py-3 text-right text-gray-900">Rs. {{ number_format((float) $purchase->total_amount, 2) }}</td>
                        <td class="px-4 py-3 text-right text-gray-900">Rs. {{ number_format((float) $purchase->paid_amount, 2) }}</td>
                        <td class="px-4 py-3 text-right font-semibold text-gray-900">Rs. {{ number_format((float) $purchase->balance_amount, 2) }}</td>
                        <td class="px-4 py-3">
                            <x-erp.status-badge :value="ucfirst($purchase->payment_status)" />
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex justify-end gap-3">
                                <a href="{{ route('purchases.show', $purchase) }}" class="font-semibold text-slate-700 hover:text-slate-900">View</a>
                                <a href="{{ route('purchases.print', $purchase) }}" target="_blank" class="font-semibold text-slate-700 hover:text-slate-900">Print</a>
                                <a href="{{ route('purchases.pdf', $purchase) }}" target="_blank" class="font-semibold text-slate-700 hover:text-slate-900">PDF</a>
                                @can('edit_old_records')
                                    <a href="{{ route('purchases.edit', $purchase) }}" class="font-semibold text-slate-700 hover:text-slate-900">Edit</a>
                                @endcan
                                @can('delete_records')
                                    <form method="POST" action="{{ route('purchases.destroy', $purchase) }}" data-confirm-delete data-confirm-title="Delete this purchase and reverse stock?">
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
                        <td colspan="9" class="px-4 py-8 text-center text-gray-500">No purchases found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-5">
        {{ $purchases->links() }}
    </div>
@endsection
