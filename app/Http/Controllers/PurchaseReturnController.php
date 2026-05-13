<?php

namespace App\Http\Controllers;

use App\Http\Requests\Returns\StorePurchaseReturnRequest;
use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;
use App\Services\ReturnPostingService;
use App\Services\SystemSettingService;
use App\Support\AmountInWords;
use Illuminate\Http\Request;

class PurchaseReturnController extends Controller
{
    public function index()
    {
        $returns = PurchaseReturn::with(['purchase', 'supplier'])
            ->latest('return_date')
            ->latest('id')
            ->paginate(20);

        $totalAmount = PurchaseReturn::sum('total_amount');
        $refundAmount = PurchaseReturn::sum('refund_amount');
        $adjustmentAmount = PurchaseReturn::sum('adjustment_amount');

        return view('purchase-returns.index', compact('returns', 'totalAmount', 'refundAmount', 'adjustmentAmount'));
    }

    public function create()
    {
        $purchases = Purchase::with(['supplier', 'items.product'])
            ->latest('purchase_date')
            ->latest('id')
            ->limit(200)
            ->get();
        $sourceData = $this->sourceData($purchases);

        return view('purchase-returns.create', compact('purchases', 'sourceData'));
    }

    public function store(StorePurchaseReturnRequest $request, ReturnPostingService $postingService)
    {
        $purchaseReturn = $postingService->recordPurchaseReturn($request->validated());

        return redirect()
            ->route('purchase-returns.show', $purchaseReturn)
            ->with('success', 'Purchase return '.$purchaseReturn->return_no.' saved successfully.');
    }

    public function show(PurchaseReturn $purchaseReturn)
    {
        $purchaseReturn->load(['purchase', 'supplier', 'items.product']);

        return view('purchase-returns.show', ['return' => $purchaseReturn]);
    }

    public function print(PurchaseReturn $purchaseReturn)
    {
        $purchaseReturn->load(['purchase.supplier', 'supplier', 'items.product']);
        $settings = app(SystemSettingService::class);

        return view('purchase-returns.print', [
            'return' => $purchaseReturn,
            'company' => $settings->company(),
            'amountWords' => AmountInWords::rupees($purchaseReturn->total_amount),
            'termsAndConditions' => $settings->termsAndConditions(),
            'signatureImagePath' => $settings->signatureImagePath(),
        ]);
    }

    public function report(Request $request)
    {
        $filters = $request->validate([
            'from_date' => ['nullable', 'date'],
            'to_date' => ['nullable', 'date', 'after_or_equal:from_date'],
        ]);

        $query = PurchaseReturn::with(['purchase', 'supplier'])
            ->when($filters['from_date'] ?? null, fn ($query, $date) => $query->whereDate('return_date', '>=', $date))
            ->when($filters['to_date'] ?? null, fn ($query, $date) => $query->whereDate('return_date', '<=', $date));

        $totals = [
            'subtotal' => (float) (clone $query)->sum('subtotal'),
            'gst' => (float) (clone $query)->sum('gst_amount'),
            'total' => (float) (clone $query)->sum('total_amount'),
            'refund' => (float) (clone $query)->sum('refund_amount'),
            'adjustment' => (float) (clone $query)->sum('adjustment_amount'),
        ];
        $returns = $query
            ->latest('return_date')
            ->latest('id')
            ->paginate(30)
            ->withQueryString();

        return view('purchase-returns.report', compact('returns', 'filters', 'totals'));
    }

    private function sourceData($purchases): array
    {
        $purchaseIds = $purchases->pluck('id');
        $returned = PurchaseReturnItem::query()
            ->join('purchase_returns', 'purchase_return_items.purchase_return_id', '=', 'purchase_returns.id')
            ->whereIn('purchase_returns.purchase_id', $purchaseIds)
            ->select('purchase_returns.purchase_id', 'purchase_return_items.product_id')
            ->selectRaw('SUM(purchase_return_items.quantity) as returned_quantity')
            ->groupBy('purchase_returns.purchase_id', 'purchase_return_items.product_id')
            ->get()
            ->groupBy('purchase_id')
            ->map(fn ($rows) => $rows->pluck('returned_quantity', 'product_id'));

        return $purchases->mapWithKeys(function (Purchase $purchase) use ($returned) {
            $returnedForPurchase = $returned[$purchase->id] ?? collect();
            $items = $purchase->items
                ->groupBy('product_id')
                ->map(function ($items, $productId) use ($purchase, $returnedForPurchase) {
                    $first = $items->first();
                    $remaining = round((float) $items->sum('quantity') - (float) ($returnedForPurchase[$productId] ?? 0), 3);

                    return [
                        'product_id' => (int) $productId,
                        'name' => $first->product?->name,
                        'code' => $first->product?->code,
                        'unit' => $first->product?->unit,
                        'current_stock' => (float) $first->product?->current_stock,
                        'remaining_quantity' => $remaining,
                        'rate' => (float) $first->rate,
                        'gst_percentage' => $purchase->bill_type === 'gst' ? (float) $first->gst_percentage : 0,
                    ];
                })
                ->filter(fn (array $item) => $item['remaining_quantity'] > 0)
                ->values();

            return [
                $purchase->id => [
                    'party' => $purchase->supplier?->name,
                    'purchase_no' => $purchase->purchase_no,
                    'supplier_invoice_no' => $purchase->supplier_invoice_no,
                    'bill_type' => $purchase->bill_type,
                    'balance_amount' => (float) $purchase->balance_amount,
                    'items' => $items,
                ],
            ];
        })->all();
    }
}
