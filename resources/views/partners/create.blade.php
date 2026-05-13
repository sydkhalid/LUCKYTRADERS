@extends('layouts.erp')

@section('title', 'Create Partner')

@section('content')
    <div class="max-w-5xl">
        <div class="mb-5 flex items-center justify-between">
            <div>
                <h2 class="text-lg font-semibold text-gray-900">Create Partner</h2>
                <p class="text-sm text-gray-500">Opening investment will post to partner ledger and cashbook when entered.</p>
            </div>
            <a href="{{ route('partners.index') }}" class="rounded border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Back</a>
        </div>

        @if ($errors->any())
            <div class="mb-5 rounded border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                <ul class="list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('partners.store') }}" class="rounded bg-white p-6 shadow">
            @csrf

            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Name</label>
                    <input type="text" name="name" value="{{ old('name') }}" class="w-full rounded border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500" required>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Phone</label>
                    <input type="text" name="phone" value="{{ old('phone') }}" class="w-full rounded border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500">
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" class="w-full rounded border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500">
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Share %</label>
                    <input type="number" name="share_percentage" value="{{ old('share_percentage', '0') }}" min="0" max="100" step="0.01" class="w-full rounded border-gray-300 text-right shadow-sm focus:border-slate-500 focus:ring-slate-500" required>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Opening Investment</label>
                    <input type="number" name="opening_investment" value="{{ old('opening_investment', '0') }}" min="0" step="0.01" class="w-full rounded border-gray-300 text-right shadow-sm focus:border-slate-500 focus:ring-slate-500" required>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Opening Payment Mode</label>
                    <select name="opening_payment_mode" class="w-full rounded border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500">
                        <option value="">Select mode</option>
                        <option value="cash" @selected(old('opening_payment_mode') === 'cash')>Cash</option>
                        <option value="bank" @selected(old('opening_payment_mode') === 'bank')>Bank</option>
                        <option value="upi" @selected(old('opening_payment_mode') === 'upi')>UPI</option>
                        <option value="cheque" @selected(old('opening_payment_mode') === 'cheque')>Cheque</option>
                    </select>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Opening Date</label>
                    <input type="date" name="transaction_date" value="{{ old('transaction_date', now()->toDateString()) }}" class="w-full rounded border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500">
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Status</label>
                    <select name="status" class="w-full rounded border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500" required>
                        <option value="active" @selected(old('status', 'active') === 'active')>Active</option>
                        <option value="inactive" @selected(old('status') === 'inactive')>Inactive</option>
                    </select>
                </div>
            </div>

            <div class="mt-5">
                <label class="mb-1 block text-sm font-medium text-gray-700">Address</label>
                <textarea name="address" rows="3" class="w-full rounded border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500">{{ old('address') }}</textarea>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <a href="{{ route('partners.index') }}" class="rounded border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Cancel</a>
                <button class="rounded bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">Save Partner</button>
            </div>
        </form>
    </div>
@endsection
