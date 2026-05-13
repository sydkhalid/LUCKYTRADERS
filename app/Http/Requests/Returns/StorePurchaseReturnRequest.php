<?php

namespace App\Http\Requests\Returns;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePurchaseReturnRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'purchase_id' => ['required', 'exists:purchases,id'],
            'return_date' => ['required', 'date'],
            'refund_amount' => ['required', 'numeric', 'min:0'],
            'adjustment_amount' => ['required', 'numeric', 'min:0'],
            'payment_mode' => [
                'nullable',
                Rule::requiredIf(fn () => (float) $this->input('refund_amount', 0) > 0),
                Rule::in(['cash', 'bank', 'upi', 'cheque']),
            ],
            'notes' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.001'],
            'items.*.rate' => ['required', 'numeric', 'min:0'],
            'items.*.gst_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
        ];
    }
}
