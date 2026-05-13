<?php

namespace App\Http\Requests\Loans;

use App\Models\Loan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLoanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'loan_type' => ['required', Rule::in(array_keys(Loan::TYPES))],
            'party_name' => ['required', 'string', 'max:255'],
            'party_phone' => ['nullable', 'string', 'max:50'],
            'partner_id' => ['nullable', 'integer', 'min:1'],
            'loan_date' => ['required', 'date'],
            'principal_amount' => ['required', 'numeric', 'min:0.01'],
            'interest_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'interest_type' => ['required', Rule::in(['none', 'monthly', 'yearly', 'fixed'])],
            'payment_mode' => ['required', Rule::in(['cash', 'bank', 'upi', 'cheque'])],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
