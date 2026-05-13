<?php

namespace App\Http\Controllers;

use App\Http\Requests\Returns\StoreSalesReturnRequest;
use App\Models\Sale;
use App\Models\SalesReturn;
use App\Models\SalesReturnItem;
use App\Services\ReturnPostingService;
use App\Services\SystemSettingService;
use App\Support\AmountInWords;
use Illuminate\Http\Request;

class SalesReturnController extends Controller
{
    public function index()
    {
        $returns = SalesReturn::with(['sale', 'customer'])
            ->latest('return_date')
            ->latest('id')
            ->paginate(20);

        $totalAmount = SalesReturn::sum('total_amount');
        $refundAmount = SalesReturn::sum('refund_amount');
        $adjustmentAmount = SalesReturn::sum('adjustment_amount');

        return view('sales-returns.index', compact('returns', 'totalAmount', 'refundAmount', 'adjustmentAmount'));
    }

    public function create()
    {
        $sales = Sale::with(['customer', 'items.product'])
            ->latest('sale_date')
            ->latest('id')
            ->limit(200)
            ->get();
        $sourceData = $this->sourceData($sales);

        return view('sales-returns.create', compact('sales', 'sourceData'));
    }

    public function store(StoreSalesReturnRequest $request, ReturnPostingService $postingService)
    {
        $salesReturn = $postingService->recordSalesReturn($request->validated());

        return redirect()
            ->route('sales-returns.show', $salesReturn)
            ->with('success', 'Sales return '.$salesReturn->return_no.' saved successfully.');
    }

    public function show(SalesReturn $salesReturn)
    {
        $salesReturn->load(['sale', 'customer', 'items.product']);

        return view('sales-returns.show', ['return' => $salesReturn]);
    }

    public function print(SalesReturn $salesReturn)
    {
        $salesReturn->load(['sale.customer', 'customer', 'items.product']);
        $settings = app(SystemSettingService::class);

        return view('sales-returns.print', [
            'return' => $salesReturn,
            'company' => $settings->company(),
            'amountWords' => AmountInWords::rupees($salesReturn->total_amount),
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

        $query = SalesReturn::with(['sale', 'customer'])
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

        return view('sales-returns.report', compact('returns', 'filters', 'totals'));
    }

    private function sourceData($sales): array
    {
        $saleIds = $sales->pluck('id');
        $returned = SalesReturnItem::query()
            ->join('sales_returns', 'sales_return_items.sales_return_id', '=', 'sales_returns.id')
            ->whereIn('sales_returns.sale_id', $saleIds)
            ->select('sales_returns.sale_id', 'sales_return_items.product_id')
            ->selectRaw('SUM(sales_return_items.quantity) as returned_quantity')
            ->groupBy('sales_returns.sale_id', 'sales_return_items.product_id')
            ->get()
            ->groupBy('sale_id')
            ->map(fn ($rows) => $rows->pluck('returned_quantity', 'product_id'));

        return $sales->mapWithKeys(function (Sale $sale) use ($returned) {
            $returnedForSale = $returned[$sale->id] ?? collect();
            $items = $sale->items
                ->groupBy('product_id')
                ->map(function ($items, $productId) use ($sale, $returnedForSale) {
                    $first = $items->first();
                    $remaining = round((float) $items->sum('quantity') - (float) ($returnedForSale[$productId] ?? 0), 3);

                    return [
                        'product_id' => (int) $productId,
                        'name' => $first->product?->name,
                        'code' => $first->product?->code,
                        'unit' => $first->product?->unit,
                        'remaining_quantity' => $remaining,
                        'rate' => (float) $first->rate,
                        'gst_percentage' => $sale->bill_type === 'gst' ? (float) $first->gst_percentage : 0,
                    ];
                })
                ->filter(fn (array $item) => $item['remaining_quantity'] > 0)
                ->values();

            return [
                $sale->id => [
                    'party' => $sale->customer?->name,
                    'sale_no' => $sale->sale_no,
                    'bill_type' => $sale->bill_type,
                    'balance_amount' => (float) $sale->balance_amount,
                    'items' => $items,
                ],
            ];
        })->all();
    }
}
