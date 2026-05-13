<?php

namespace App\Services;

use App\Models\Cashbook;
use App\Models\Customer;
use App\Models\Ledger;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;
use App\Models\Sale;
use App\Models\SalesReturn;
use App\Models\SalesReturnItem;
use App\Models\StockMovement;
use App\Models\Supplier;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReturnPostingService
{
    public function recordSalesReturn(array $data): SalesReturn
    {
        return DB::transaction(function () use ($data) {
            $sale = Sale::with('items')->whereKey($data['sale_id'])->lockForUpdate()->firstOrFail();
            $customer = Customer::whereKey($sale->customer_id)->lockForUpdate()->firstOrFail();
            $totals = $this->calculateSalesReturnTotals($sale, $data);
            $this->validateMoneyAllocation($totals['total_amount'], $data);

            $refundAmount = round((float) $data['refund_amount'], 2);
            $adjustmentAmount = round((float) $data['adjustment_amount'], 2);

            if ($adjustmentAmount > round((float) $sale->balance_amount, 2)) {
                throw ValidationException::withMessages([
                    'adjustment_amount' => 'Adjustment amount cannot be greater than selected sale balance.',
                ]);
            }

            $salesReturn = SalesReturn::create([
                'return_no' => $this->nextSalesReturnNo(),
                'sale_id' => $sale->id,
                'customer_id' => $customer->id,
                'return_date' => $data['return_date'],
                'subtotal' => $totals['subtotal'],
                'gst_amount' => $totals['gst_amount'],
                'total_amount' => $totals['total_amount'],
                'refund_amount' => $refundAmount,
                'adjustment_amount' => $adjustmentAmount,
                'payment_mode' => $refundAmount > 0 ? $data['payment_mode'] : null,
                'notes' => $data['notes'] ?? null,
            ]);

            $this->createSalesReturnItemsAndStock($salesReturn, $totals['items']);
            $this->postCustomerReturnAccounting($salesReturn, $sale, $customer);
            $this->updateSaleBalance($sale, $adjustmentAmount);

            return $salesReturn;
        });
    }

    public function recordPurchaseReturn(array $data): PurchaseReturn
    {
        return DB::transaction(function () use ($data) {
            $purchase = Purchase::with('items')->whereKey($data['purchase_id'])->lockForUpdate()->firstOrFail();
            $supplier = Supplier::whereKey($purchase->supplier_id)->lockForUpdate()->firstOrFail();
            $totals = $this->calculatePurchaseReturnTotals($purchase, $data);
            $this->validateMoneyAllocation($totals['total_amount'], $data);

            $refundAmount = round((float) $data['refund_amount'], 2);
            $adjustmentAmount = round((float) $data['adjustment_amount'], 2);

            if ($adjustmentAmount > round((float) $purchase->balance_amount, 2)) {
                throw ValidationException::withMessages([
                    'adjustment_amount' => 'Adjustment amount cannot be greater than selected purchase balance.',
                ]);
            }

            $purchaseReturn = PurchaseReturn::create([
                'return_no' => $this->nextPurchaseReturnNo(),
                'purchase_id' => $purchase->id,
                'supplier_id' => $supplier->id,
                'return_date' => $data['return_date'],
                'subtotal' => $totals['subtotal'],
                'gst_amount' => $totals['gst_amount'],
                'total_amount' => $totals['total_amount'],
                'refund_amount' => $refundAmount,
                'adjustment_amount' => $adjustmentAmount,
                'payment_mode' => $refundAmount > 0 ? $data['payment_mode'] : null,
                'notes' => $data['notes'] ?? null,
            ]);

            $this->createPurchaseReturnItemsAndStock($purchaseReturn, $totals['items']);
            $this->postSupplierReturnAccounting($purchaseReturn, $purchase, $supplier);
            $this->updatePurchaseBalance($purchase, $adjustmentAmount);

            return $purchaseReturn;
        });
    }

    private function calculateSalesReturnTotals(Sale $sale, array $data): array
    {
        $remaining = $this->remainingSaleQuantities($sale);

        return $this->calculateReturnTotals(
            $data,
            $sale->bill_type,
            $remaining,
            'Selected product is not available for return on this sale.',
            'Return quantity cannot be greater than remaining sold quantity.'
        );
    }

    private function calculatePurchaseReturnTotals(Purchase $purchase, array $data): array
    {
        $remaining = $this->remainingPurchaseQuantities($purchase);
        $totals = $this->calculateReturnTotals(
            $data,
            $purchase->bill_type,
            $remaining,
            'Selected product is not available for return on this purchase.',
            'Return quantity cannot be greater than remaining purchased quantity.'
        );

        $requested = collect($totals['items'])->groupBy('product_id')->map(fn ($rows) => $rows->sum('quantity'));
        foreach ($requested as $productId => $quantity) {
            $product = Product::whereKey($productId)->lockForUpdate()->firstOrFail();

            if ((float) $product->current_stock < (float) $quantity) {
                throw ValidationException::withMessages([
                    'items' => 'Insufficient stock for purchase return of '.$product->name.'. Available: '.$product->current_stock.' '.$product->unit,
                ]);
            }
        }

        return $totals;
    }

    private function calculateReturnTotals(array $data, string $billType, array $remaining, string $missingMessage, string $quantityMessage): array
    {
        $items = [];
        $subtotal = 0;
        $gstAmount = 0;
        $requestedByProduct = [];

        foreach ($data['items'] as $item) {
            $productId = (int) $item['product_id'];

            if (! array_key_exists($productId, $remaining)) {
                throw ValidationException::withMessages(['items' => $missingMessage]);
            }

            $quantity = round((float) $item['quantity'], 3);
            $requestedByProduct[$productId] = round(($requestedByProduct[$productId] ?? 0) + $quantity, 3);

            if ($requestedByProduct[$productId] > $remaining[$productId] + 0.0001) {
                throw ValidationException::withMessages(['items' => $quantityMessage]);
            }

            $rate = round((float) $item['rate'], 2);
            $itemSubtotal = round($quantity * $rate, 2);
            $gstPercentage = $billType === 'gst' ? round((float) $item['gst_percentage'], 2) : 0;
            $itemGst = round($itemSubtotal * $gstPercentage / 100, 2);
            $itemTotal = round($itemSubtotal + $itemGst, 2);

            $items[] = [
                'product_id' => $productId,
                'quantity' => $quantity,
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

        return [
            'items' => $items,
            'subtotal' => $subtotal,
            'gst_amount' => $gstAmount,
            'total_amount' => round($subtotal + $gstAmount, 2),
        ];
    }

    private function validateMoneyAllocation(float $totalAmount, array $data): void
    {
        $allocated = round((float) $data['refund_amount'] + (float) $data['adjustment_amount'], 2);

        if (abs($allocated - $totalAmount) > 0.01) {
            throw ValidationException::withMessages([
                'refund_amount' => 'Refund amount plus adjustment amount must equal the return total.',
            ]);
        }
    }

    private function createSalesReturnItemsAndStock(SalesReturn $salesReturn, array $items): void
    {
        foreach ($items as $item) {
            $salesReturn->items()->create($item);

            Product::whereKey($item['product_id'])->increment('current_stock', $item['quantity']);

            StockMovement::create([
                'product_id' => $item['product_id'],
                'movement_type' => 'adjustment',
                'reference_type' => 'sales_return',
                'reference_id' => $salesReturn->id,
                'quantity' => $item['quantity'],
                'rate' => $item['rate'],
                'total_value' => $item['subtotal'],
                'movement_date' => $salesReturn->return_date,
                'remarks' => 'Sales return '.$salesReturn->return_no,
            ]);
        }
    }

    private function createPurchaseReturnItemsAndStock(PurchaseReturn $purchaseReturn, array $items): void
    {
        foreach ($items as $item) {
            $purchaseReturn->items()->create($item);

            Product::whereKey($item['product_id'])->decrement('current_stock', $item['quantity']);

            StockMovement::create([
                'product_id' => $item['product_id'],
                'movement_type' => 'adjustment',
                'reference_type' => 'purchase_return',
                'reference_id' => $purchaseReturn->id,
                'quantity' => $item['quantity'],
                'rate' => $item['rate'],
                'total_value' => $item['subtotal'],
                'movement_date' => $purchaseReturn->return_date,
                'remarks' => 'Purchase return '.$purchaseReturn->return_no,
            ]);
        }
    }

    private function postCustomerReturnAccounting(SalesReturn $salesReturn, Sale $sale, Customer $customer): void
    {
        $balanceAfterReturn = round((float) $customer->current_balance - (float) $salesReturn->total_amount, 2);

        Ledger::create([
            'ledger_date' => $salesReturn->return_date,
            'party_type' => 'customer',
            'party_id' => $customer->id,
            'reference_type' => 'sales_return',
            'reference_id' => $salesReturn->id,
            'debit' => 0,
            'credit' => $salesReturn->total_amount,
            'balance' => $balanceAfterReturn,
            'remarks' => 'Sales return '.$salesReturn->return_no.' for '.$sale->sale_no,
        ]);

        $finalBalance = $balanceAfterReturn;

        if ((float) $salesReturn->refund_amount > 0) {
            $finalBalance = round($balanceAfterReturn + (float) $salesReturn->refund_amount, 2);

            Ledger::create([
                'ledger_date' => $salesReturn->return_date,
                'party_type' => 'customer',
                'party_id' => $customer->id,
                'reference_type' => 'sales_return_refund',
                'reference_id' => $salesReturn->id,
                'debit' => $salesReturn->refund_amount,
                'credit' => 0,
                'balance' => $finalBalance,
                'remarks' => 'Refund for sales return '.$salesReturn->return_no,
            ]);

            Cashbook::create([
                'entry_date' => $salesReturn->return_date,
                'transaction_type' => $salesReturn->payment_mode === 'cash' ? 'cash_out' : 'bank_out',
                'reference_type' => 'sales_return_refund',
                'reference_id' => $salesReturn->id,
                'amount' => $salesReturn->refund_amount,
                'payment_mode' => $salesReturn->payment_mode,
                'remarks' => $salesReturn->return_no.' - '.$customer->name,
            ]);
        }

        $customer->forceFill(['current_balance' => $finalBalance])->save();
    }

    private function postSupplierReturnAccounting(PurchaseReturn $purchaseReturn, Purchase $purchase, Supplier $supplier): void
    {
        $balanceAfterReturn = round((float) $supplier->current_balance - (float) $purchaseReturn->total_amount, 2);

        Ledger::create([
            'ledger_date' => $purchaseReturn->return_date,
            'party_type' => 'supplier',
            'party_id' => $supplier->id,
            'reference_type' => 'purchase_return',
            'reference_id' => $purchaseReturn->id,
            'debit' => $purchaseReturn->total_amount,
            'credit' => 0,
            'balance' => $balanceAfterReturn,
            'remarks' => 'Purchase return '.$purchaseReturn->return_no.' for '.$purchase->purchase_no,
        ]);

        $finalBalance = $balanceAfterReturn;

        if ((float) $purchaseReturn->refund_amount > 0) {
            $finalBalance = round($balanceAfterReturn + (float) $purchaseReturn->refund_amount, 2);

            Ledger::create([
                'ledger_date' => $purchaseReturn->return_date,
                'party_type' => 'supplier',
                'party_id' => $supplier->id,
                'reference_type' => 'purchase_return_refund',
                'reference_id' => $purchaseReturn->id,
                'debit' => 0,
                'credit' => $purchaseReturn->refund_amount,
                'balance' => $finalBalance,
                'remarks' => 'Refund received for purchase return '.$purchaseReturn->return_no,
            ]);

            Cashbook::create([
                'entry_date' => $purchaseReturn->return_date,
                'transaction_type' => $purchaseReturn->payment_mode === 'cash' ? 'cash_in' : 'bank_in',
                'reference_type' => 'purchase_return_refund',
                'reference_id' => $purchaseReturn->id,
                'amount' => $purchaseReturn->refund_amount,
                'payment_mode' => $purchaseReturn->payment_mode,
                'remarks' => $purchaseReturn->return_no.' - '.$supplier->name,
            ]);
        }

        $supplier->forceFill(['current_balance' => $finalBalance])->save();
    }

    private function updateSaleBalance(Sale $sale, float $adjustmentAmount): void
    {
        $newBalance = max(round((float) $sale->balance_amount - $adjustmentAmount, 2), 0);
        $sale->forceFill([
            'balance_amount' => $newBalance,
            'payment_status' => $this->sourcePaymentStatus((float) $sale->total_amount, (float) $sale->paid_amount, $newBalance),
        ])->save();
    }

    private function updatePurchaseBalance(Purchase $purchase, float $adjustmentAmount): void
    {
        $newBalance = max(round((float) $purchase->balance_amount - $adjustmentAmount, 2), 0);
        $purchase->forceFill([
            'balance_amount' => $newBalance,
            'payment_status' => $this->sourcePaymentStatus((float) $purchase->total_amount, (float) $purchase->paid_amount, $newBalance),
        ])->save();
    }

    private function sourcePaymentStatus(float $totalAmount, float $paidAmount, float $balanceAmount): string
    {
        if ($balanceAmount <= 0) {
            return 'paid';
        }

        if ($paidAmount > 0 || $balanceAmount < $totalAmount) {
            return 'partial';
        }

        return 'pending';
    }

    private function remainingSaleQuantities(Sale $sale): array
    {
        $sold = $sale->items
            ->groupBy('product_id')
            ->map(fn ($items) => round((float) $items->sum('quantity'), 3));

        $returned = SalesReturnItem::query()
            ->join('sales_returns', 'sales_return_items.sales_return_id', '=', 'sales_returns.id')
            ->where('sales_returns.sale_id', $sale->id)
            ->select('sales_return_items.product_id')
            ->selectRaw('SUM(sales_return_items.quantity) as returned_quantity')
            ->groupBy('sales_return_items.product_id')
            ->pluck('returned_quantity', 'product_id');

        return $sold
            ->mapWithKeys(fn ($quantity, $productId) => [
                (int) $productId => round((float) $quantity - (float) ($returned[$productId] ?? 0), 3),
            ])
            ->filter(fn ($quantity) => $quantity > 0)
            ->all();
    }

    private function remainingPurchaseQuantities(Purchase $purchase): array
    {
        $purchased = $purchase->items
            ->groupBy('product_id')
            ->map(fn ($items) => round((float) $items->sum('quantity'), 3));

        $returned = PurchaseReturnItem::query()
            ->join('purchase_returns', 'purchase_return_items.purchase_return_id', '=', 'purchase_returns.id')
            ->where('purchase_returns.purchase_id', $purchase->id)
            ->select('purchase_return_items.product_id')
            ->selectRaw('SUM(purchase_return_items.quantity) as returned_quantity')
            ->groupBy('purchase_return_items.product_id')
            ->pluck('returned_quantity', 'product_id');

        return $purchased
            ->mapWithKeys(fn ($quantity, $productId) => [
                (int) $productId => round((float) $quantity - (float) ($returned[$productId] ?? 0), 3),
            ])
            ->filter(fn ($quantity) => $quantity > 0)
            ->all();
    }

    private function nextSalesReturnNo(): string
    {
        $date = now()->format('Ymd');
        $sequence = SalesReturn::where('return_no', 'like', 'SRET-'.$date.'-%')->count() + 1;

        do {
            $returnNo = sprintf('SRET-%s-%05d', $date, $sequence++);
        } while (SalesReturn::where('return_no', $returnNo)->exists());

        return $returnNo;
    }

    private function nextPurchaseReturnNo(): string
    {
        $date = now()->format('Ymd');
        $sequence = PurchaseReturn::where('return_no', 'like', 'PRET-'.$date.'-%')->count() + 1;

        do {
            $returnNo = sprintf('PRET-%s-%05d', $date, $sequence++);
        } while (PurchaseReturn::where('return_no', $returnNo)->exists());

        return $returnNo;
    }
}
