<?php

namespace App\Http\Requests\Partners;

use App\Models\PartnerTransaction;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePartnerTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'transaction_date' => ['required', 'date'],
            'transaction_type' => ['required', Rule::in(array_keys(PartnerTransaction::TYPES))],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_mode' => ['required', Rule::in(['cash', 'bank', 'upi', 'cheque'])],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
