<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\RespondsToAjax;
use App\Http\Requests\Payments\StoreSupplierPaymentRequest;
use App\Models\Payment;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Services\PaymentPostingService;

class SupplierPaymentController extends Controller
{
    use RespondsToAjax;

    public function index()
    {
        $payments = Payment::where('transaction_type', 'payment')
            ->where('party_type', 'supplier')
            ->latest('payment_date')
            ->latest('id')
            ->paginate(20);
        $customerNames = collect();
        $supplierNames = Supplier::whereIn('id', $payments->pluck('party_id')->filter())
            ->pluck('name', 'id');
        $title = 'Supplier Payments';
        $description = 'Supplier payment history posted to ledgers and cashbook.';
        $showFilters = false;

        return view('payments.index', compact('payments', 'customerNames', 'supplierNames', 'title', 'description', 'showFilters'));
    }

    public function create()
    {
        $suppliers = Supplier::where('status', 'active')->orderBy('name')->get();
        $pendingPurchases = Purchase::with('supplier')
            ->where('balance_amount', '>', 0)
            ->orderBy('purchase_date')
            ->orderBy('purchase_no')
            ->get();

        return view('supplier-payments.create', compact('suppliers', 'pendingPurchases'));
    }

    public function show(Payment $payment)
    {
        abort_unless($payment->transaction_type === 'payment' && $payment->party_type === 'supplier', 404);

        return view('payments.show', [
            'payment' => $payment,
            'party' => Supplier::find($payment->party_id),
            'reference' => $this->reference($payment),
            'backRoute' => route('supplier-payments.index'),
        ]);
    }

    public function store(StoreSupplierPaymentRequest $request, PaymentPostingService $postingService)
    {
        $supplier = Supplier::findOrFail($request->integer('supplier_id'));

        $payment = $postingService->recordSupplierPayment($supplier, $request->validated());

        return $this->successResponse($request, 'Supplier payment '.$payment->payment_no.' saved successfully.', route('payments.index'));
    }

    private function reference(Payment $payment): ?Purchase
    {
        if ($payment->reference_type !== 'purchase') {
            return null;
        }

        return Purchase::find($payment->reference_id);
    }
}
