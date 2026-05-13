<?php

namespace App\Http\Requests\Quotations;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ConvertQuotationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'sale_date' => ['required', 'date'],
            'bill_type' => ['required', Rule::in(['gst', 'non_gst'])],
        ];
    }
}
