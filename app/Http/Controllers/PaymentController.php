<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Payment;
use App\Models\Supplier;

class PaymentController extends Controller
{
    public function index()
    {
        $payments = Payment::latest('payment_date')
            ->latest('id')
            ->paginate(20);

        $customerNames = Customer::whereIn('id', $payments->pluck('party_id')->filter())
            ->pluck('name', 'id');
        $supplierNames = Supplier::whereIn('id', $payments->pluck('party_id')->filter())
            ->pluck('name', 'id');

        return view('payments.index', compact('payments', 'customerNames', 'supplierNames'));
    }
}
