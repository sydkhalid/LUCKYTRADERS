@if ($errors->any())
    <div class="mb-5 rounded border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
        <ul class="list-disc pl-5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ $action }}" data-ajax-form>
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="space-y-5">
        <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">Name</label>
            <input type="text" name="name" value="{{ old('name', $category->name ?? '') }}" class="w-full rounded border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500" required>
        </div>

        <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">Description</label>
            <textarea name="description" rows="4" class="w-full rounded border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500">{{ old('description', $category->description ?? '') }}</textarea>
        </div>

        <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">Status</label>
            <select name="status" class="w-full rounded border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500" required>
                <option value="active" @selected(old('status', $category->status ?? 'active') === 'active')>Active</option>
                <option value="inactive" @selected(old('status', $category->status ?? 'active') === 'inactive')>Inactive</option>
            </select>
        </div>
    </div>

    <div class="mt-6 flex justify-end gap-3">
        <a href="{{ route('product-categories.index') }}" class="rounded border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Cancel</a>
        <button class="rounded bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">Save Category</button>
    </div>
</form>
