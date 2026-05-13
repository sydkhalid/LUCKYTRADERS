<?php

namespace App\Http\Requests\Payments;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCustomerReceiptRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_id' => ['required', 'exists:customers,id'],
            'payment_date' => ['required', 'date'],
            'reference_type' => ['nullable', 'string', Rule::in(['sale', 'gst_invoice', 'normal_bill', 'opening_balance', 'other'])],
            'reference_id' => ['nullable', 'required_if:reference_type,sale,gst_invoice,normal_bill', 'integer', 'min:1'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_mode' => ['required', 'in:cash,bank,upi,cheque'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
