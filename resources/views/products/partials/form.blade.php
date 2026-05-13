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

    <x-erp.ajax-errors class="mb-5" />

    <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
        <div>
            <label class="erp-required mb-1 block text-sm font-medium text-gray-700">Category</label>
            <select name="product_category_id" data-searchable data-placeholder="Search category" class="w-full rounded border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500" required>
                <option value="">Select category</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected(old('product_category_id', $product->product_category_id ?? '') == $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="erp-required mb-1 block text-sm font-medium text-gray-700">Product Name</label>
            <input type="text" name="name" value="{{ old('name', $product->name ?? '') }}" class="w-full rounded border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500" required>
        </div>

        <div>
            <label class="erp-required mb-1 block text-sm font-medium text-gray-700">Code</label>
            <input type="text" name="code" value="{{ old('code', $product->code ?? '') }}" class="w-full rounded border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500" required>
        </div>

        <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">Size</label>
            <input type="text" name="size" value="{{ old('size', $product->size ?? '') }}" class="w-full rounded border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500">
        </div>

        <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">Thickness</label>
            <input type="text" name="thickness" value="{{ old('thickness', $product->thickness ?? '') }}" class="w-full rounded border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500">
        </div>

        <div>
            <label class="erp-required mb-1 block text-sm font-medium text-gray-700">Unit</label>
            <input type="text" name="unit" value="{{ old('unit', $product->unit ?? 'Kg') }}" class="w-full rounded border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500" required>
        </div>

        <div>
            <label class="erp-required mb-1 block text-sm font-medium text-gray-700">Weight Per Unit</label>
            <input type="number" name="weight_per_unit" value="{{ old('weight_per_unit', $product->weight_per_unit ?? '0') }}" step="0.001" min="0" class="w-full rounded border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500" required>
        </div>

        <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">HSN Code</label>
            <input type="text" name="hsn_code" value="{{ old('hsn_code', $product->hsn_code ?? '') }}" class="w-full rounded border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500">
        </div>

        <div>
            <label class="erp-required mb-1 block text-sm font-medium text-gray-700">GST %</label>
            <input type="number" name="gst_percentage" value="{{ old('gst_percentage', $product->gst_percentage ?? '18') }}" step="0.01" min="0" max="100" class="w-full rounded border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500" required>
        </div>

        <div>
            <label class="erp-required mb-1 block text-sm font-medium text-gray-700">Purchase Price</label>
            <input type="number" name="purchase_price" value="{{ old('purchase_price', $product->purchase_price ?? '0') }}" step="0.01" min="0" class="w-full rounded border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500" required>
        </div>

        <div>
            <label class="erp-required mb-1 block text-sm font-medium text-gray-700">Selling Price</label>
            <input type="number" name="selling_price" value="{{ old('selling_price', $product->selling_price ?? '0') }}" step="0.01" min="0" class="w-full rounded border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500" required>
        </div>

        <div>
            <label class="erp-required mb-1 block text-sm font-medium text-gray-700">Opening Stock</label>
            <input type="number" name="opening_stock" value="{{ old('opening_stock', $product->opening_stock ?? '0') }}" step="0.001" min="0" class="w-full rounded border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500" required>
        </div>

        @isset($product)
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Current Stock</label>
                <input type="number" name="current_stock" value="{{ old('current_stock', $product->current_stock) }}" step="0.001" min="0" class="w-full rounded border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500" required>
            </div>
        @else
            <div class="rounded border border-slate-200 bg-slate-50 p-4 text-sm text-slate-700">
                Current stock will start from opening stock when the product is saved.
            </div>
        @endisset

        <div>
            <label class="erp-required mb-1 block text-sm font-medium text-gray-700">Status</label>
            <select name="status" data-searchable class="w-full rounded border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500" required>
                <option value="active" @selected(old('status', $product->status ?? 'active') === 'active')>Active</option>
                <option value="inactive" @selected(old('status', $product->status ?? 'active') === 'inactive')>Inactive</option>
            </select>
        </div>
    </div>

    <div class="mt-6 flex justify-end gap-3">
        <a href="{{ route('products.index') }}" class="rounded border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Cancel</a>
        <button class="rounded bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">Save Product</button>
    </div>
</form>
