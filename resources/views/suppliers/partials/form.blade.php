@if ($errors->any())
    <div class="mb-5 rounded border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
        <ul class="list-disc pl-5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ $action }}">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
        <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">Name</label>
            <input type="text" name="name" value="{{ old('name', $supplier->name ?? '') }}" class="w-full rounded border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500" required>
        </div>

        <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">Phone</label>
            <input type="text" name="phone" value="{{ old('phone', $supplier->phone ?? '') }}" class="w-full rounded border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500">
        </div>

        <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">Email</label>
            <input type="email" name="email" value="{{ old('email', $supplier->email ?? '') }}" class="w-full rounded border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500">
        </div>

        <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">GST Number</label>
            <input type="text" name="gst_number" value="{{ old('gst_number', $supplier->gst_number ?? '') }}" class="w-full rounded border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500">
        </div>

        <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">Opening Balance</label>
            <input type="number" name="opening_balance" value="{{ old('opening_balance', $supplier->opening_balance ?? '0') }}" step="0.01" min="0" class="w-full rounded border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500" required>
        </div>

        <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">Balance Type</label>
            <select name="balance_type" class="w-full rounded border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500" required>
                <option value="credit" @selected(old('balance_type', $supplier->balance_type ?? 'credit') === 'credit')>Credit</option>
                <option value="debit" @selected(old('balance_type', $supplier->balance_type ?? 'credit') === 'debit')>Debit</option>
            </select>
        </div>

        <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">Status</label>
            <select name="status" class="w-full rounded border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500" required>
                <option value="active" @selected(old('status', $supplier->status ?? 'active') === 'active')>Active</option>
                <option value="inactive" @selected(old('status', $supplier->status ?? 'active') === 'inactive')>Inactive</option>
            </select>
        </div>

        <div class="md:col-span-2">
            <label class="mb-1 block text-sm font-medium text-gray-700">Address</label>
            <textarea name="address" rows="3" class="w-full rounded border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500">{{ old('address', $supplier->address ?? '') }}</textarea>
        </div>
    </div>

    <div class="mt-6 flex justify-end gap-3">
        <a href="{{ route('suppliers.index') }}" class="rounded border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Cancel</a>
        <button class="rounded bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">Save Supplier</button>
    </div>
</form>
