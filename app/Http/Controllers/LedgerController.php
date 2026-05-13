<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Ledger;
use App\Models\Supplier;

class LedgerController extends Controller
{
    public function index()
    {
        $customerCount = Customer::count();
        $supplierCount = Supplier::count();

        return view('ledgers.index', compact('customerCount', 'supplierCount'));
    }

    public function customers()
    {
        $customers = Customer::orderBy('name')->paginate(20);

        return view('ledgers.customers.index', compact('customers'));
    }

    public function customerShow(Customer $customer)
    {
        $ledgers = Ledger::where('party_type', 'customer')
            ->where('party_id', $customer->id)
            ->orderBy('ledger_date')
            ->orderBy('id')
            ->paginate(30);

        return view('ledgers.customers.show', compact('customer', 'ledgers'));
    }

    public function suppliers()
    {
        $suppliers = Supplier::orderBy('name')->paginate(20);

        return view('ledgers.suppliers.index', compact('suppliers'));
    }

    public function supplierShow(Supplier $supplier)
    {
        $ledgers = Ledger::where('party_type', 'supplier')
            ->where('party_id', $supplier->id)
            ->orderBy('ledger_date')
            ->orderBy('id')
            ->paginate(30);

        return view('ledgers.suppliers.show', compact('supplier', 'ledgers'));
    }
}
