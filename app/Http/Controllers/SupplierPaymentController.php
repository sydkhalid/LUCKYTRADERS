<?php

namespace App\Http\Controllers;

use App\Http\Requests\Payments\StoreSupplierPaymentRequest;
use App\Models\Supplier;
use App\Services\PaymentPostingService;

class SupplierPaymentController extends Controller
{
    public function create()
    {
        $suppliers = Supplier::where('status', 'active')->orderBy('name')->get();

        return view('supplier-payments.create', compact('suppliers'));
    }

    public function store(StoreSupplierPaymentRequest $request, PaymentPostingService $postingService)
    {
        $supplier = Supplier::findOrFail($request->integer('supplier_id'));

        $payment = $postingService->recordSupplierPayment($supplier, $request->validated());

        return redirect()
            ->route('payments.index')
            ->with('success', 'Supplier payment '.$payment->payment_no.' saved successfully.');
    }
}
