<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\RespondsToAjax;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    use RespondsToAjax;

    public function index(Request $request)
    {
        $search = trim((string) $request->query('search'));
        $products = Product::with('category')
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', '%'.$search.'%')
                        ->orWhere('code', 'like', '%'.$search.'%')
                        ->orWhere('size', 'like', '%'.$search.'%')
                        ->orWhere('thickness', 'like', '%'.$search.'%')
                        ->orWhere('hsn_code', 'like', '%'.$search.'%')
                        ->orWhereHas('category', fn ($query) => $query->where('name', 'like', '%'.$search.'%'));
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('products.index', compact('products', 'search'));
    }

    public function create()
    {
        $categories = ProductCategory::where('status', 'active')->orderBy('name')->get();

        return view('products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $this->validatedData($request);
        $data['current_stock'] = $data['opening_stock'];

        Product::create($data);

        return $this->successResponse($request, 'Product created successfully.', route('products.index'));
    }

    public function edit(Product $product)
    {
        $categories = ProductCategory::orderBy('name')->get();

        return view('products.edit', compact('product', 'categories'));
    }

    public function show(Product $product)
    {
        $product->load('category');

        return view('products.show', compact('product'));
    }

    public function lookup(Product $product)
    {
        return response()->json([
            'success' => true,
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'code' => $product->code,
                'unit' => $product->unit,
                'selling_price' => (float) $product->selling_price,
                'purchase_price' => (float) $product->purchase_price,
                'gst_percentage' => (float) $product->gst_percentage,
                'current_stock' => (float) $product->current_stock,
            ],
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $product->update($this->validatedData($request, $product));

        return $this->successResponse($request, 'Product updated successfully.', route('products.index'));
    }

    public function destroy(Request $request, Product $product)
    {
        $this->authorizeDelete($request);

        $product->delete();

        return $this->successResponse($request, 'Product deleted successfully.', route('products.index'));
    }

    private function validatedData(Request $request, ?Product $product = null): array
    {
        return $request->validate([
            'product_category_id' => ['required', 'exists:product_categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:100', Rule::unique('products', 'code')->ignore($product)->withoutTrashed()],
            'size' => ['nullable', 'string', 'max:100'],
            'thickness' => ['nullable', 'string', 'max:100'],
            'unit' => ['required', 'string', 'max:50'],
            'weight_per_unit' => ['required', 'numeric', 'min:0'],
            'hsn_code' => ['nullable', 'string', 'max:50'],
            'gst_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            'purchase_price' => ['required', 'numeric', 'min:0'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'opening_stock' => ['required', 'numeric', 'min:0'],
            'current_stock' => [$product ? 'required' : 'nullable', 'numeric', 'min:0'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);
    }

    private function authorizeDelete(Request $request): void
    {
        abort_unless($request->user()?->can('delete_records'), 403);
    }
}
