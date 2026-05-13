@extends('layouts.erp')

@section('title', 'Ledgers')

@section('content')
    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
        <a href="{{ route('ledgers.customers.index') }}" class="rounded bg-white p-6 shadow hover:shadow-md">
            <p class="text-sm font-medium text-gray-500">Customer Ledgers</p>
            <h2 class="mt-2 text-3xl font-bold text-gray-900">{{ $customerCount }}</h2>
            <p class="mt-3 text-sm text-gray-600">Track receipts, invoices, debit, credit, and customer balances.</p>
        </a>

        <a href="{{ route('ledgers.suppliers.index') }}" class="rounded bg-white p-6 shadow hover:shadow-md">
            <p class="text-sm font-medium text-gray-500">Supplier Ledgers</p>
            <h2 class="mt-2 text-3xl font-bold text-gray-900">{{ $supplierCount }}</h2>
            <p class="mt-3 text-sm text-gray-600">Track purchase payable, supplier payments, debit, credit, and balances.</p>
        </a>
    </div>
@endsection
