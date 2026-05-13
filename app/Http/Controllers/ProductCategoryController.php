<?php

namespace App\Http\Controllers;

use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductCategoryController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search'));
        $categories = ProductCategory::withCount('products')
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', '%'.$search.'%')
                        ->orWhere('description', 'like', '%'.$search.'%')
                        ->orWhere('status', 'like', '%'.$search.'%');
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('product-categories.index', compact('categories', 'search'));
    }

    public function create()
    {
        return view('product-categories.create');
    }

    public function store(Request $request)
    {
        ProductCategory::create($this->validatedData($request));

        return redirect()
            ->route('product-categories.index')
            ->with('success', 'Product category created successfully.');
    }

    public function edit(ProductCategory $productCategory)
    {
        return view('product-categories.edit', compact('productCategory'));
    }

    public function show(ProductCategory $productCategory)
    {
        $productCategory->loadCount('products');
        $products = $productCategory->products()
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('product-categories.show', compact('productCategory', 'products'));
    }

    public function update(Request $request, ProductCategory $productCategory)
    {
        $productCategory->update($this->validatedData($request));

        return redirect()
            ->route('product-categories.index')
            ->with('success', 'Product category updated successfully.');
    }

    public function destroy(Request $request, ProductCategory $productCategory)
    {
        $this->authorizeDelete($request);

        if ($productCategory->products()->exists()) {
            return back()->with('error', 'Cannot delete category while products are linked to it.');
        }

        $productCategory->delete();

        return redirect()
            ->route('product-categories.index')
            ->with('success', 'Product category deleted successfully.');
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);
    }

    private function authorizeDelete(Request $request): void
    {
        abort_unless($request->user()?->can('delete_records'), 403);
    }
}
