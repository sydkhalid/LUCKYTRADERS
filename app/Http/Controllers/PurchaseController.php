<?php

namespace App\Http\Controllers;

use App\Models\Ledger;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\StockMovement;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PurchaseController extends Controller
{
    public function index()
    {
        $purchases = Purchase::with('supplier')
            ->latest('purchase_date')
            ->latest('id')
            ->paginate(15);

        return view('purchases.index', compact('purchases'));
    }

    public function create()
    {
        return view('purchases.create', $this->formData());
    }

    public function store(Request $request)
    {
        $data = $this->validatedData($request);
        $totals = $this->calculateTotals($data);

        $purchase = DB::transaction(function () use ($data, $totals) {
            $purchase = Purchase::create([
                'purchase_no' => $this->nextPurchaseNo(),
                'supplier_id' => $data['supplier_id'],
                'purchase_date' => $data['purchase_date'],
                'bill_type' => $data['bill_type'],
                'supplier_invoice_no' => $data['supplier_invoice_no'] ?? null,
                'subtotal' => $totals['subtotal'],
                'gst_amount' => $totals['gst_amount'],
                'total_amount' => $totals['total_amount'],
                'paid_amount' => $totals['paid_amount'],
                'balance_amount' => $totals['balance_amount'],
                'payment_status' => $this->paymentStatus($totals['paid_amount'], $totals['total_amount']),
                'notes' => $data['notes'] ?? null,
            ]);

            $this->createItemsAndStock($purchase, $totals['items']);
            $this->increaseSupplierPayable($purchase);

            return $purchase;
        });

        return redirect()
            ->route('purchases.index')
            ->with('success', 'Purchase '.$purchase->purchase_no.' created successfully.');
    }

    public function show(Purchase $purchase)
    {
        $purchase->load(['supplier', 'items.product']);

        return view('purchases.show', compact('purchase'));
    }

    public function edit(Purchase $purchase)
    {
        $purchase->load('items');

        return view('purchases.edit', array_merge($this->formData(), compact('purchase')));
    }

    public function update(Request $request, Purchase $purchase)
    {
        $data = $this->validatedData($request);
        $totals = $this->calculateTotals($data);

        DB::transaction(function () use ($purchase, $data, $totals) {
            $purchase->load('items');
            $this->reversePurchasePosting($purchase);

            $purchase->update([
                'supplier_id' => $data['supplier_id'],
                'purchase_date' => $data['purchase_date'],
                'bill_type' => $data['bill_type'],
                'supplier_invoice_no' => $data['supplier_invoice_no'] ?? null,
                'subtotal' => $totals['subtotal'],
                'gst_amount' => $totals['gst_amount'],
                'total_amount' => $totals['total_amount'],
                'paid_amount' => $totals['paid_amount'],
                'balance_amount' => $totals['balance_amount'],
                'payment_status' => $this->paymentStatus($totals['paid_amount'], $totals['total_amount']),
                'notes' => $data['notes'] ?? null,
            ]);

            $purchase->items()->delete();
            StockMovement::where('reference_type', 'purchase')
                ->where('reference_id', $purchase->id)
                ->delete();

            $this->createItemsAndStock($purchase, $totals['items']);
            $this->increaseSupplierPayable($purchase);
        });

        return redirect()
            ->route('purchases.index')
            ->with('success', 'Purchase '.$purchase->purchase_no.' updated successfully.');
    }

    public function destroy(Purchase $purchase)
    {
        if (Payment::where('reference_type', 'purchase')->where('reference_id', $purchase->id)->exists()) {
            return back()->with('error', 'Cannot delete purchase while supplier payments are linked to it.');
        }

        DB::transaction(function () use ($purchase) {
            $purchase->load('items');
            $this->reversePurchasePosting($purchase);

            StockMovement::where('reference_type', 'purchase')
                ->where('reference_id', $purchase->id)
                ->delete();
            Ledger::where('reference_type', 'purchase')
                ->where('reference_id', $purchase->id)
                ->delete();

            $purchase->delete();
        });

        return redirect()
            ->route('purchases.index')
            ->with('success', 'Purchase deleted successfully.');
    }

    private function formData(): array
    {
        $suppliers = Supplier::where('status', 'active')->orderBy('name')->get();
        $products = Product::where('status', 'active')->orderBy('name')->get();
        $productData = $products->mapWithKeys(fn (Product $product) => [
            $product->id => [
                'unit' => $product->unit,
                'rate' => (float) $product->purchase_price,
                'gst_percentage' => (float) $product->gst_percentage,
            ],
        ]);

        return compact('suppliers', 'products', 'productData');
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'purchase_date' => ['required', 'date'],
            'bill_type' => ['required', Rule::in(['gst', 'non_gst'])],
            'supplier_invoice_no' => ['nullable', 'string', 'max:255'],
            'paid_amount' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.001'],
            'items.*.unit' => ['required', 'string', 'max:50'],
            'items.*.rate' => ['required', 'numeric', 'min:0'],
            'items.*.gst_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);
    }

    private function calculateTotals(array $data): array
    {
        $items = [];
        $subtotal = 0;
        $gstAmount = 0;

        foreach ($data['items'] as $item) {
            $quantity = round((float) $item['quantity'], 3);
            $rate = round((float) $item['rate'], 2);
            $itemSubtotal = round($quantity * $rate, 2);
            $gstPercentage = $data['bill_type'] === 'gst' ? round((float) $item['gst_percentage'], 2) : 0;
            $itemGst = round($itemSubtotal * $gstPercentage / 100, 2);
            $itemTotal = round($itemSubtotal + $itemGst, 2);

            $items[] = [
                'product_id' => (int) $item['product_id'],
                'quantity' => $quantity,
                'unit' => $item['unit'],
                'rate' => $rate,
                'subtotal' => $itemSubtotal,
                'gst_percentage' => $gstPercentage,
                'gst_amount' => $itemGst,
                'total' => $itemTotal,
            ];

            $subtotal += $itemSubtotal;
            $gstAmount += $itemGst;
        }

        $subtotal = round($subtotal, 2);
        $gstAmount = round($gstAmount, 2);
        $totalAmount = round($subtotal + $gstAmount, 2);
        $paidAmount = round((float) $data['paid_amount'], 2);

        if ($paidAmount > $totalAmount) {
            throw ValidationException::withMessages([
                'paid_amount' => 'Paid amount cannot be greater than purchase total.',
            ]);
        }

        return [
            'items' => $items,
            'subtotal' => $subtotal,
            'gst_amount' => $gstAmount,
            'total_amount' => $totalAmount,
            'paid_amount' => $paidAmount,
            'balance_amount' => round($totalAmount - $paidAmount, 2),
        ];
    }

    private function createItemsAndStock(Purchase $purchase, array $items): void
    {
        foreach ($items as $item) {
            $purchase->items()->create($item);

            Product::whereKey($item['product_id'])->increment('current_stock', $item['quantity']);

            StockMovement::create([
                'product_id' => $item['product_id'],
                'movement_type' => 'purchase_in',
                'reference_type' => 'purchase',
                'reference_id' => $purchase->id,
                'quantity' => $item['quantity'],
                'rate' => $item['rate'],
                'total_value' => $item['subtotal'],
                'movement_date' => $purchase->purchase_date,
                'remarks' => 'Purchase '.$purchase->purchase_no,
            ]);
        }
    }

    private function increaseSupplierPayable(Purchase $purchase): void
    {
        $supplier = Supplier::lockForUpdate()->findOrFail($purchase->supplier_id);
        $newBalance = round((float) $supplier->current_balance + (float) $purchase->balance_amount, 2);
        $supplier->forceFill(['current_balance' => $newBalance])->save();

        if ((float) $purchase->balance_amount > 0) {
            Ledger::create([
                'ledger_date' => $purchase->purchase_date,
                'party_type' => 'supplier',
                'party_id' => $supplier->id,
                'reference_type' => 'purchase',
                'reference_id' => $purchase->id,
                'debit' => 0,
                'credit' => $purchase->balance_amount,
                'balance' => $newBalance,
                'remarks' => 'Purchase payable '.$purchase->purchase_no,
            ]);
        }
    }

    private function reversePurchasePosting(Purchase $purchase): void
    {
        foreach ($purchase->items as $item) {
            Product::whereKey($item->product_id)->decrement('current_stock', $item->quantity);
        }

        $supplier = Supplier::lockForUpdate()->findOrFail($purchase->supplier_id);
        $supplier->forceFill([
            'current_balance' => round((float) $supplier->current_balance - (float) $purchase->balance_amount, 2),
        ])->save();

        Ledger::where('reference_type', 'purchase')
            ->where('reference_id', $purchase->id)
            ->delete();
    }

    private function paymentStatus(float $paidAmount, float $totalAmount): string
    {
        if ($paidAmount <= 0) {
            return 'pending';
        }

        return $paidAmount >= $totalAmount ? 'paid' : 'partial';
    }

    private function nextPurchaseNo(): string
    {
        $date = now()->format('Ymd');
        $sequence = Purchase::where('purchase_no', 'like', 'PUR-'.$date.'-%')->count() + 1;

        do {
            $purchaseNo = sprintf('PUR-%s-%05d', $date, $sequence++);
        } while (Purchase::where('purchase_no', $purchaseNo)->exists());

        return $purchaseNo;
    }
}
