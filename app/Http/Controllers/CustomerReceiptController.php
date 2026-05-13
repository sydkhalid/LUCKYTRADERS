<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\RespondsToAjax;
use App\Http\Requests\Payments\StoreCustomerReceiptRequest;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\Sale;
use App\Services\PaymentPostingService;

class CustomerReceiptController extends Controller
{
    use RespondsToAjax;

    public function index()
    {
        $payments = Payment::where('transaction_type', 'receipt')
            ->where('party_type', 'customer')
            ->latest('payment_date')
            ->latest('id')
            ->paginate(20);
        $customerNames = Customer::whereIn('id', $payments->pluck('party_id')->filter())
            ->pluck('name', 'id');
        $supplierNames = collect();
        $title = 'Customer Receipts';
        $description = 'Customer receipt history posted to ledgers and cashbook.';
        $showFilters = false;

        return view('payments.index', compact('payments', 'customerNames', 'supplierNames', 'title', 'description', 'showFilters'));
    }

    public function create()
    {
        $customers = Customer::where('status', 'active')->orderBy('name')->get();
        $pendingSales = Sale::with('customer')
            ->where('balance_amount', '>', 0)
            ->orderBy('sale_date')
            ->orderBy('sale_no')
            ->get();

        return view('receipts.create', compact('customers', 'pendingSales'));
    }

    public function show(Payment $payment)
    {
        abort_unless($payment->transaction_type === 'receipt' && $payment->party_type === 'customer', 404);

        return view('payments.show', [
            'payment' => $payment,
            'party' => Customer::find($payment->party_id),
            'reference' => $this->reference($payment),
            'backRoute' => route('receipts.index'),
        ]);
    }

    public function store(StoreCustomerReceiptRequest $request, PaymentPostingService $postingService)
    {
        $customer = Customer::findOrFail($request->integer('customer_id'));

        $payment = $postingService->recordCustomerReceipt($customer, $request->validated());

        return $this->successResponse($request, 'Customer receipt '.$payment->payment_no.' saved successfully.', route('receipts.index'));
    }

    private function reference(Payment $payment): ?Sale
    {
        if (! in_array($payment->reference_type, ['sale', 'gst_invoice', 'normal_bill'], true)) {
            return null;
        }

        return Sale::find($payment->reference_id);
    }
}
