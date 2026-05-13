@extends('layouts.app')

@section('title', 'Supplier Details')

@section('content')
    <div class="mb-5 flex items-center justify-between">
        <div>
            <h2 class="text-lg font-semibold text-gray-900">{{ $supplier->name }}</h2>
            <p class="text-sm text-gray-500">Supplier master record.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('suppliers.edit', $supplier) }}" class="rounded bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">Edit</a>
            <a href="{{ route('suppliers.index') }}" class="rounded border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Back</a>
        </div>
    </div>

    <div class="rounded bg-white p-6 shadow">
        <div class="grid gap-5 md:grid-cols-3">
            <div>
                <p class="text-xs font-semibold uppercase text-gray-500">Phone</p>
                <p class="mt-1 text-gray-900">{{ $supplier->phone ?: '-' }}</p>
            </div>
            <div>
                <p class="text-xs font-semibold uppercase text-gray-500">Email</p>
                <p class="mt-1 text-gray-900">{{ $supplier->email ?: '-' }}</p>
            </div>
            <div>
                <p class="text-xs font-semibold uppercase text-gray-500">GST Number</p>
                <p class="mt-1 text-gray-900">{{ $supplier->gst_number ?: '-' }}</p>
            </div>
            <div>
                <p class="text-xs font-semibold uppercase text-gray-500">Opening Balance</p>
                <p class="mt-1 text-gray-900">Rs. {{ number_format((float) $supplier->opening_balance, 2) }} {{ ucfirst($supplier->balance_type) }}</p>
            </div>
            <div>
                <p class="text-xs font-semibold uppercase text-gray-500">Current Balance</p>
                <p class="mt-1 text-gray-900">Rs. {{ number_format((float) $supplier->current_balance, 2) }}</p>
            </div>
            <div>
                <p class="text-xs font-semibold uppercase text-gray-500">Status</p>
                <p class="mt-1">
                    <span class="rounded px-2 py-1 text-xs font-semibold {{ $supplier->status === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-600' }}">
                        {{ ucfirst($supplier->status) }}
                    </span>
                </p>
            </div>
            <div class="md:col-span-3">
                <p class="text-xs font-semibold uppercase text-gray-500">Address</p>
                <p class="mt-1 whitespace-pre-line text-gray-900">{{ $supplier->address ?: '-' }}</p>
            </div>
        </div>
    </div>
@endsection
