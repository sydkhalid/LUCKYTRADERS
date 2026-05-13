<?php

namespace App\Http\Requests\StockAdjustments;

use App\Models\StockAdjustment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStockAdjustmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'adjustment_date' => ['required', 'date'],
            'product_id' => ['required', 'exists:products,id'],
            'adjustment_type' => ['required', Rule::in(array_keys(StockAdjustment::TYPES))],
            'reason' => ['required', Rule::in(array_keys(StockAdjustment::REASONS))],
            'quantity' => ['required', 'numeric', 'min:0.001'],
            'remarks' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
