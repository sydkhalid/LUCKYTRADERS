<?php

namespace App\Http\Requests\Quotations;

use App\Models\Quotation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateQuotationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_id' => ['required', 'exists:customers,id'],
            'quotation_date' => ['required', 'date'],
            'valid_until' => ['nullable', 'date', 'after_or_equal:quotation_date'],
            'status' => ['required', Rule::in(['draft', 'sent', 'accepted', 'rejected'])],
            'notes' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.001'],
            'items.*.unit' => ['required', 'string', 'max:50'],
            'items.*.rate' => ['required', 'numeric', 'min:0'],
            'items.*.gst_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
        ];
    }
}
