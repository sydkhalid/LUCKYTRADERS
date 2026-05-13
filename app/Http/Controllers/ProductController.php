<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with('category')
            ->latest()
            ->paginate(15);

        return view('products.index', compact('products'));
    }

    public function create()
    {
        $categories = ProductCategory::where('status', 'active')->orderBy('name')->get();

        return view('products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        Product::create($this->validatedData($request));

        return redirect()
            ->route('products.index')
            ->with('success', 'Product created successfully.');
    }

    public function edit(Product $product)
    {
        $categories = ProductCategory::orderBy('name')->get();

        return view('products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $product->update($this->validatedData($request, $product));

        return redirect()
            ->route('products.index')
            ->with('success', 'Product updated successfully.');
    }

    public function destroy(Request $request, Product $product)
    {
        $this->authorizeDelete($request);

        $product->delete();

        return redirect()
            ->route('products.index')
            ->with('success', 'Product deleted successfully.');
    }

    private function validatedData(Request $request, ?Product $product = null): array
    {
        return $request->validate([
            'product_category_id' => ['required', 'exists:product_categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:100', Rule::unique('products', 'code')->ignore($product)],
            'size' => ['nullable', 'string', 'max:100'],
            'thickness' => ['nullable', 'string', 'max:100'],
            'unit' => ['required', 'string', 'max:50'],
            'weight_per_unit' => ['required', 'numeric', 'min:0'],
            'hsn_code' => ['nullable', 'string', 'max:50'],
            'gst_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            'purchase_price' => ['required', 'numeric', 'min:0'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'opening_stock' => ['required', 'numeric', 'min:0'],
            'current_stock' => ['required', 'numeric', 'min:0'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);
    }

    private function authorizeDelete(Request $request): void
    {
        abort_unless($request->user()?->can('delete_records'), 403);
    }
}
