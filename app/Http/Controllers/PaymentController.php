<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Payment;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Supplier;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'transaction_type' => ['nullable', 'in:receipt,payment'],
            'payment_mode' => ['nullable', 'in:cash,bank,upi,cheque'],
        ]);

        $payments = Payment::query()
            ->when($filters['transaction_type'] ?? null, fn ($query, $type) => $query->where('transaction_type', $type))
            ->when($filters['payment_mode'] ?? null, fn ($query, $mode) => $query->where('payment_mode', $mode))
            ->latest('payment_date')
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        $customerNames = Customer::whereIn('id', $payments->pluck('party_id')->filter())
            ->pluck('name', 'id');
        $supplierNames = Supplier::whereIn('id', $payments->pluck('party_id')->filter())
            ->pluck('name', 'id');
        $title = 'Payments';
        $description = 'All customer receipts and supplier payments posted to ledger and cashbook.';

        return view('payments.index', compact('payments', 'customerNames', 'supplierNames', 'title', 'description', 'filters'));
    }

    public function show(Payment $payment)
    {
        return view('payments.show', [
            'payment' => $payment,
            'party' => $this->party($payment),
            'reference' => $this->reference($payment),
            'backRoute' => route('payments.index'),
        ]);
    }

    private function party(Payment $payment): Customer|Supplier|null
    {
        return match ($payment->party_type) {
            'customer' => Customer::find($payment->party_id),
            'supplier' => Supplier::find($payment->party_id),
            default => null,
        };
    }

    private function reference(Payment $payment): Sale|Purchase|null
    {
        return match ($payment->reference_type) {
            'sale', 'gst_invoice', 'normal_bill', 'sale_direct_payment' => Sale::find($payment->reference_id),
            'purchase', 'purchase_direct_payment' => Purchase::find($payment->reference_id),
            default => null,
        };
    }
}
