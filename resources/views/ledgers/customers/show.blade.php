@extends('layouts.app')

@section('title', 'Customer Ledger')

@section('content')
    <div class="mb-5 flex items-center justify-between">
        <div>
            <h2 class="text-lg font-semibold text-gray-900">{{ $customer->name }}</h2>
            <p class="text-sm text-gray-500">Current balance: Rs. {{ number_format((float) $customer->current_balance, 2) }}</p>
        </div>
        <a href="{{ route('ledgers.customers.index') }}" class="rounded border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Back</a>
    </div>

    <div class="overflow-hidden rounded bg-white shadow">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-slate-100 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                <tr>
                    <th class="px-4 py-3">Date</th>
                    <th class="px-4 py-3">Reference</th>
                    <th class="px-4 py-3 text-right">Debit</th>
                    <th class="px-4 py-3 text-right">Credit</th>
                    <th class="px-4 py-3 text-right">Balance</th>
                    <th class="px-4 py-3">Remarks</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($ledgers as $ledger)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-gray-700">{{ $ledger->ledger_date?->format('d M Y') }}</td>
                        <td class="px-4 py-3 text-gray-700">{{ ucfirst($ledger->reference_type ?? '-') }} @if ($ledger->reference_id)#{{ $ledger->reference_id }}@endif</td>
                        <td class="px-4 py-3 text-right text-gray-900">Rs. {{ number_format((float) $ledger->debit, 2) }}</td>
                        <td class="px-4 py-3 text-right text-gray-900">Rs. {{ number_format((float) $ledger->credit, 2) }}</td>
                        <td class="px-4 py-3 text-right font-semibold text-gray-900">Rs. {{ number_format((float) $ledger->balance, 2) }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $ledger->remarks ?: '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-gray-500">No ledger entries found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-5">
        {{ $ledgers->links() }}
    </div>
@endsection
