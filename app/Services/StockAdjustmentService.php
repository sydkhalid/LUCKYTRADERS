<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockAdjustment;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StockAdjustmentService
{
    public function recordAdjustment(array $data): StockAdjustment
    {
        return DB::transaction(function () use ($data) {
            $product = Product::whereKey($data['product_id'])->lockForUpdate()->firstOrFail();
            $quantity = round((float) $data['quantity'], 3);
            $oldStock = round((float) $product->current_stock, 3);

            if ($data['adjustment_type'] === 'decrease' && $quantity > $oldStock) {
                throw ValidationException::withMessages([
                    'quantity' => 'Decrease quantity cannot be greater than available stock.',
                ]);
            }

            $newStock = $data['adjustment_type'] === 'increase'
                ? round($oldStock + $quantity, 3)
                : round($oldStock - $quantity, 3);

            $adjustment = StockAdjustment::create([
                'adjustment_no' => $this->nextAdjustmentNo(),
                'adjustment_date' => $data['adjustment_date'],
                'product_id' => $product->id,
                'adjustment_type' => $data['adjustment_type'],
                'reason' => $data['reason'],
                'quantity' => $quantity,
                'old_stock' => $oldStock,
                'new_stock' => $newStock,
                'remarks' => $data['remarks'] ?? null,
            ]);

            $product->forceFill(['current_stock' => $newStock])->save();

            StockMovement::create([
                'product_id' => $product->id,
                'movement_type' => 'adjustment',
                'reference_type' => 'stock_adjustment',
                'reference_id' => $adjustment->id,
                'quantity' => $quantity,
                'rate' => $product->purchase_price,
                'total_value' => round($quantity * (float) $product->purchase_price, 2),
                'movement_date' => $adjustment->adjustment_date,
                'remarks' => $adjustment->adjustment_no.' - '.$adjustment->typeLabel().' - '.$adjustment->reasonLabel(),
            ]);

            return $adjustment;
        });
    }

    private function nextAdjustmentNo(): string
    {
        $date = now()->format('Ymd');
        $sequence = StockAdjustment::where('adjustment_no', 'like', 'ADJ-'.$date.'-%')->count() + 1;

        do {
            $adjustmentNo = sprintf('ADJ-%s-%05d', $date, $sequence++);
        } while (StockAdjustment::where('adjustment_no', $adjustmentNo)->exists());

        return $adjustmentNo;
    }
}
