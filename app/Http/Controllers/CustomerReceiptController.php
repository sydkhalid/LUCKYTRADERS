<?php

namespace App\Http\Controllers;

use App\Http\Requests\Payments\StoreCustomerReceiptRequest;
use App\Models\Customer;
use App\Services\PaymentPostingService;

class CustomerReceiptController extends Controller
{
    public function create()
    {
        $customers = Customer::where('status', 'active')->orderBy('name')->get();

        return view('receipts.create', compact('customers'));
    }

    public function store(StoreCustomerReceiptRequest $request, PaymentPostingService $postingService)
    {
        $customer = Customer::findOrFail($request->integer('customer_id'));

        $payment = $postingService->recordCustomerReceipt($customer, $request->validated());

        return redirect()
            ->route('payments.index')
            ->with('success', 'Customer receipt '.$payment->payment_no.' saved successfully.');
    }
}
