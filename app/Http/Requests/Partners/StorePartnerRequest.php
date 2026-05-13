<?php

namespace App\Http\Requests\Partners;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StorePartnerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:1000'],
            'share_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            'opening_investment' => ['required', 'numeric', 'min:0'],
            'opening_payment_mode' => ['nullable', Rule::in(['cash', 'bank', 'upi', 'cheque'])],
            'transaction_date' => ['nullable', 'date'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ((float) $this->input('opening_investment', 0) <= 0) {
                    return;
                }

                if (! $this->filled('opening_payment_mode')) {
                    $validator->errors()->add('opening_payment_mode', 'Opening payment mode is required when opening investment is entered.');
                }

                if (! $this->filled('transaction_date')) {
                    $validator->errors()->add('transaction_date', 'Opening transaction date is required when opening investment is entered.');
                }

                if ($this->input('status') !== 'active') {
                    $validator->errors()->add('status', 'Opening investment can only be posted for an active partner.');
                }
            },
        ];
    }
}
