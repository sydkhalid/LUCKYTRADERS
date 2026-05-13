<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Ledger;
use App\Models\Product;
use App\Models\Quotation;
use App\Models\Sale;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class QuotationService
{
    public function createQuotation(array $data): Quotation
    {
        $totals = $this->calculateQuotationTotals($data);

        return DB::transaction(function () use ($data, $totals) {
            $quotation = Quotation::create([
                'quotation_no' => $this->nextQuotationNo(),
                'customer_id' => $data['customer_id'],
                'quotation_date' => $data['quotation_date'],
                'valid_until' => $data['valid_until'] ?? null,
                'subtotal' => $totals['subtotal'],
                'gst_amount' => $totals['gst_amount'],
                'total_amount' => $totals['total_amount'],
                'status' => $data['status'],
                'notes' => $data['notes'] ?? null,
            ]);

            $this->createQuotationItems($quotation, $totals['items']);

            return $quotation;
        });
    }

    public function updateQuotation(Quotation $quotation, array $data): Quotation
    {
        if ($quotation->status === 'converted') {
            throw ValidationException::withMessages([
                'status' => 'Converted quotations cannot be edited.',
            ]);
        }

        $totals = $this->calculateQuotationTotals($data);

        return DB::transaction(function () use ($quotation, $data, $totals) {
            $quotation = Quotation::whereKey($quotation->id)->lockForUpdate()->firstOrFail();

            if ($quotation->status === 'converted') {
                throw ValidationException::withMessages([
                    'status' => 'Converted quotations cannot be edited.',
                ]);
            }

            $quotation->update([
                'customer_id' => $data['customer_id'],
                'quotation_date' => $data['quotation_date'],
                'valid_until' => $data['valid_until'] ?? null,
                'subtotal' => $totals['subtotal'],
                'gst_amount' => $totals['gst_amount'],
                'total_amount' => $totals['total_amount'],
                'status' => $data['status'],
                'notes' => $data['notes'] ?? null,
            ]);

            $quotation->items()->delete();
            $this->createQuotationItems($quotation, $totals['items']);

            return $quotation;
        });
    }

    public function convertToSale(Quotation $quotation, array $data): Sale
    {
        return DB::transaction(function () use ($quotation, $data) {
            $quotation = Quotation::whereKey($quotation->id)
                ->with('items')
                ->lockForUpdate()
                ->firstOrFail();

            if ($quotation->status !== 'accepted') {
                throw ValidationException::withMessages([
                    'status' => 'Only accepted quotations can be converted to sale.',
                ]);
            }

            $items = $this->saleItemsFromQuotation($quotation, $data['bill_type']);
            $this->assertStockAvailable($items);

            $subtotal = round(collect($items)->sum('subtotal'), 2);
            $gstAmount = round(collect($items)->sum('gst_amount'), 2);
            $totalAmount = round($subtotal + $gstAmount, 2);

            $sale = Sale::create([
                'sale_no' => $this->nextSaleNo(),
                'customer_id' => $quotation->customer_id,
                'sale_date' => $data['sale_date'],
                'bill_type' => $data['bill_type'],
                'subtotal' => $subtotal,
                'gst_amount' => $gstAmount,
                'total_amount' => $totalAmount,
                'paid_amount' => 0,
                'balance_amount' => $totalAmount,
                'payment_status' => 'pending',
                'payment_mode' => 'credit',
                'notes' => trim(($quotation->notes ? $quotation->notes."\n\n" : '').'Converted from quotation '.$quotation->quotation_no),
            ]);

            $this->createSaleItemsAndStock($sale, $items);
            $this->postCustomerSaleLedger($sale);

            $quotation->forceFill(['status' => 'converted'])->save();

            return $sale;
        });
    }

    public function conversionPreview(Quotation $quotation, string $billType): array
    {
        $quotation->loadMissing('items');
        $items = $this->saleItemsFromQuotation($quotation, $billType);
        $subtotal = round(collect($items)->sum('subtotal'), 2);
        $gstAmount = round(collect($items)->sum('gst_amount'), 2);

        return [
            'subtotal' => $subtotal,
            'gst_amount' => $gstAmount,
            'total_amount' => round($subtotal + $gstAmount, 2),
        ];
    }

    private function calculateQuotationTotals(array $data): array
    {
        $productIds = collect($data['items'])->pluck('product_id')->all();
        $products = Product::whereIn('id', $productIds)->get()->keyBy('id');
        $items = [];
        $subtotal = 0;
        $gstAmount = 0;

        foreach ($data['items'] as $item) {
            $product = $products[(int) $item['product_id']];
            $quantity = round((float) $item['quantity'], 3);
            $rate = round((float) $item['rate'], 2);
            $itemSubtotal = round($quantity * $rate, 2);
            $gstPercentage = round((float) $item['gst_percentage'], 2);
            $itemGst = round($itemSubtotal * $gstPercentage / 100, 2);
            $itemTotal = round($itemSubtotal + $itemGst, 2);

            $items[] = [
                'product_id' => $product->id,
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

        return [
            'items' => $items,
            'subtotal' => $subtotal,
            'gst_amount' => $gstAmount,
            'total_amount' => round($subtotal + $gstAmount, 2),
        ];
    }

    private function createQuotationItems(Quotation $quotation, array $items): void
    {
        foreach ($items as $item) {
            $quotation->items()->create($item);
        }
    }

    private function saleItemsFromQuotation(Quotation $quotation, string $billType): array
    {
        $products = Product::whereIn('id', $quotation->items->pluck('product_id'))->get()->keyBy('id');

        return $quotation->items->map(function ($item) use ($billType, $products) {
            $product = $products[(int) $item->product_id];
            $quantity = round((float) $item->quantity, 3);
            $subtotal = round((float) $item->subtotal, 2);
            $gstPercentage = $billType === 'gst' ? round((float) $item->gst_percentage, 2) : 0;
            $gstAmount = round($subtotal * $gstPercentage / 100, 2);
            $purchaseCost = round($quantity * (float) $product->purchase_price, 2);

            return [
                'product_id' => $item->product_id,
                'quantity' => $quantity,
                'unit' => $item->unit,
                'rate' => round((float) $item->rate, 2),
                'subtotal' => $subtotal,
                'gst_percentage' => $gstPercentage,
                'gst_amount' => $gstAmount,
                'total' => round($subtotal + $gstAmount, 2),
                'purchase_cost' => $purchaseCost,
                'profit_amount' => round($subtotal - $purchaseCost, 2),
            ];
        })->all();
    }

    private function assertStockAvailable(array $items): void
    {
        $quantitiesByProduct = collect($items)
            ->groupBy('product_id')
            ->map(fn ($rows) => $rows->sum('quantity'));

        foreach ($quantitiesByProduct as $productId => $quantity) {
            $product = Product::whereKey($productId)->lockForUpdate()->firstOrFail();

            if ((float) $product->current_stock < (float) $quantity) {
                throw ValidationException::withMessages([
                    'items' => 'Insufficient stock for '.$product->name.'. Available: '.$product->current_stock.' '.$product->unit,
                ]);
            }
        }
    }

    private function createSaleItemsAndStock(Sale $sale, array $items): void
    {
        foreach ($items as $item) {
            $sale->items()->create($item);

            Product::whereKey($item['product_id'])->decrement('current_stock', $item['quantity']);

            StockMovement::create([
                'product_id' => $item['product_id'],
                'movement_type' => 'sale_out',
                'reference_type' => 'sale',
                'reference_id' => $sale->id,
                'quantity' => $item['quantity'],
                'rate' => $item['rate'],
                'total_value' => $item['subtotal'],
                'movement_date' => $sale->sale_date,
                'remarks' => 'Sale '.$sale->sale_no.' from quotation',
            ]);
        }
    }

    private function postCustomerSaleLedger(Sale $sale): void
    {
        $customer = Customer::lockForUpdate()->findOrFail($sale->customer_id);
        $balanceAfterSale = round((float) $customer->current_balance + (float) $sale->total_amount, 2);

        Ledger::create([
            'ledger_date' => $sale->sale_date,
            'party_type' => 'customer',
            'party_id' => $customer->id,
            'reference_type' => 'sale',
            'reference_id' => $sale->id,
            'debit' => $sale->total_amount,
            'credit' => 0,
            'balance' => $balanceAfterSale,
            'remarks' => 'Sale '.$sale->sale_no.' converted from quotation',
        ]);

        $customer->forceFill(['current_balance' => $balanceAfterSale])->save();
    }

    private function nextQuotationNo(): string
    {
        $date = now()->format('Ymd');
        $sequence = Quotation::where('quotation_no', 'like', 'QTN-'.$date.'-%')->count() + 1;

        do {
            $quotationNo = sprintf('QTN-%s-%05d', $date, $sequence++);
        } while (Quotation::where('quotation_no', $quotationNo)->exists());

        return $quotationNo;
    }

    private function nextSaleNo(): string
    {
        $date = now()->format('Ymd');
        $sequence = Sale::where('sale_no', 'like', 'SAL-'.$date.'-%')->count() + 1;

        do {
            $saleNo = sprintf('SAL-%s-%05d', $date, $sequence++);
        } while (Sale::where('sale_no', $saleNo)->exists());

        return $saleNo;
    }
}
