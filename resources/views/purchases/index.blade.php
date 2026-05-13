@extends('layouts.erp')

@section('title', 'Purchases')

@section('content')
    <div class="mb-5 flex items-center justify-between">
        <div>
            <h2 class="text-lg font-semibold text-gray-900">Purchases</h2>
            <p class="text-sm text-gray-500">Supplier invoices, stock inward, GST input, and payable balance.</p>
        </div>
        <a href="{{ route('purchases.create') }}" class="rounded bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">New Purchase</a>
    </div>

    <div class="overflow-hidden rounded bg-white shadow">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-slate-100 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                <tr>
                    <th class="px-4 py-3">Purchase No</th>
                    <th class="px-4 py-3">Supplier</th>
                    <th class="px-4 py-3">Date</th>
                    <th class="px-4 py-3">Bill</th>
                    <th class="px-4 py-3 text-right">Total</th>
                    <th class="px-4 py-3 text-right">Paid</th>
                    <th class="px-4 py-3 text-right">Balance</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3 text-right">Action</th>
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
                            <span class="rounded px-2 py-1 text-xs font-semibold {{ $purchase->payment_status === 'paid' ? 'bg-emerald-100 text-emerald-700' : ($purchase->payment_status === 'partial' ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-700') }}">
                                {{ ucfirst($purchase->payment_status) }}
                            </span>
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
                                    <form method="POST" action="{{ route('purchases.destroy', $purchase) }}" onsubmit="return confirm('Delete this purchase and reverse stock?')">
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
