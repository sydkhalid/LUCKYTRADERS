<?php

namespace App\Http\Controllers;

use App\Http\Requests\StockAdjustments\StoreStockAdjustmentRequest;
use App\Models\Product;
use App\Models\StockAdjustment;
use App\Models\StockMovement;
use App\Services\StockAdjustmentService;
use Illuminate\Http\Request;

class StockAdjustmentController extends Controller
{
    public function index()
    {
        $adjustments = StockAdjustment::with('product')
            ->latest('adjustment_date')
            ->latest('id')
            ->paginate(20);

        $increaseTotal = StockAdjustment::where('adjustment_type', 'increase')->sum('quantity');
        $decreaseTotal = StockAdjustment::where('adjustment_type', 'decrease')->sum('quantity');

        return view('stock-adjustments.index', compact('adjustments', 'increaseTotal', 'decreaseTotal'));
    }

    public function create()
    {
        $products = Product::where('status', 'active')->orderBy('name')->get();
        $types = StockAdjustment::TYPES;
        $reasons = StockAdjustment::REASONS;

        return view('stock-adjustments.create', compact('products', 'types', 'reasons'));
    }

    public function store(StoreStockAdjustmentRequest $request, StockAdjustmentService $stockAdjustmentService)
    {
        $adjustment = $stockAdjustmentService->recordAdjustment($request->validated());

        return redirect()
            ->route('stock-adjustments.show', $adjustment)
            ->with('success', 'Stock adjustment '.$adjustment->adjustment_no.' saved successfully.');
    }

    public function show(StockAdjustment $stockAdjustment)
    {
        $stockAdjustment->load('product.category');

        return view('stock-adjustments.show', ['adjustment' => $stockAdjustment]);
    }

    public function productHistory(Product $product)
    {
        $product->load('category');
        $movements = StockMovement::where('product_id', $product->id)
            ->latest('movement_date')
            ->latest('id')
            ->paginate(30);
        $adjustmentsById = $this->adjustmentsForMovements($movements->getCollection());

        return view('stock-adjustments.product-history', compact('product', 'movements', 'adjustmentsById'));
    }

    public function productReport(Request $request)
    {
        $filters = $request->validate([
            'from_date' => ['nullable', 'date'],
            'to_date' => ['nullable', 'date', 'after_or_equal:from_date'],
            'product_id' => ['nullable', 'exists:products,id'],
            'adjustment_type' => ['nullable', 'in:increase,decrease'],
            'reason' => ['nullable', 'in:damage,shortage,excess,return,wastage,correction,other'],
        ]);

        $query = StockAdjustment::query()
            ->when($filters['from_date'] ?? null, fn ($query, $date) => $query->whereDate('adjustment_date', '>=', $date))
            ->when($filters['to_date'] ?? null, fn ($query, $date) => $query->whereDate('adjustment_date', '<=', $date))
            ->when($filters['product_id'] ?? null, fn ($query, $productId) => $query->where('product_id', $productId))
            ->when($filters['adjustment_type'] ?? null, fn ($query, $type) => $query->where('adjustment_type', $type))
            ->when($filters['reason'] ?? null, fn ($query, $reason) => $query->where('reason', $reason));

        $increaseTotal = (clone $query)->where('adjustment_type', 'increase')->sum('quantity');
        $decreaseTotal = (clone $query)->where('adjustment_type', 'decrease')->sum('quantity');

        $summaries = $query
            ->select('product_id')
            ->selectRaw('COUNT(*) as adjustments_count')
            ->selectRaw("SUM(CASE WHEN adjustment_type = 'increase' THEN quantity ELSE 0 END) as increase_quantity")
            ->selectRaw("SUM(CASE WHEN adjustment_type = 'decrease' THEN quantity ELSE 0 END) as decrease_quantity")
            ->selectRaw('MAX(adjustment_date) as last_adjustment_date')
            ->with('product')
            ->groupBy('product_id')
            ->orderByDesc('last_adjustment_date')
            ->paginate(25)
            ->withQueryString();

        $products = Product::orderBy('name')->get();
        $types = StockAdjustment::TYPES;
        $reasons = StockAdjustment::REASONS;

        return view('stock-adjustments.product-report', compact('summaries', 'products', 'types', 'reasons', 'filters', 'increaseTotal', 'decreaseTotal'));
    }

    public function movementReport(Request $request)
    {
        $filters = $request->validate([
            'from_date' => ['nullable', 'date'],
            'to_date' => ['nullable', 'date', 'after_or_equal:from_date'],
            'product_id' => ['nullable', 'exists:products,id'],
            'movement_type' => ['nullable', 'in:purchase_in,sale_out,sales_return_in,adjustment'],
        ]);

        $query = StockMovement::with('product')
            ->when($filters['from_date'] ?? null, fn ($query, $date) => $query->whereDate('movement_date', '>=', $date))
            ->when($filters['to_date'] ?? null, fn ($query, $date) => $query->whereDate('movement_date', '<=', $date))
            ->when($filters['product_id'] ?? null, fn ($query, $productId) => $query->where('product_id', $productId))
            ->when($filters['movement_type'] ?? null, fn ($query, $movementType) => $query->where('movement_type', $movementType));

        $totalQuantity = (clone $query)->sum('quantity');
        $totalValue = (clone $query)->sum('total_value');
        $movements = $query
            ->latest('movement_date')
            ->latest('id')
            ->paginate(30)
            ->withQueryString();
        $adjustmentsById = $this->adjustmentsForMovements($movements->getCollection());
        $products = Product::orderBy('name')->get();

        return view('stock-adjustments.movement-report', compact('movements', 'adjustmentsById', 'products', 'filters', 'totalQuantity', 'totalValue'));
    }

    private function adjustmentsForMovements($movements)
    {
        $adjustmentIds = $movements
            ->where('reference_type', 'stock_adjustment')
            ->pluck('reference_id')
            ->filter()
            ->unique()
            ->values();

        if ($adjustmentIds->isEmpty()) {
            return collect();
        }

        return StockAdjustment::whereIn('id', $adjustmentIds)->get()->keyBy('id');
    }
}
