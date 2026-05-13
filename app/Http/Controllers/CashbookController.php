<?php

namespace App\Http\Controllers;

use App\Models\Cashbook;

class CashbookController extends Controller
{
    public function cashbook()
    {
        $query = Cashbook::whereIn('transaction_type', ['cash_in', 'cash_out']);
        $totalIn = (clone $query)->where('transaction_type', 'cash_in')->sum('amount');
        $totalOut = (clone $query)->where('transaction_type', 'cash_out')->sum('amount');
        $balance = $totalIn - $totalOut;

        $entries = $query
            ->latest('entry_date')
            ->latest('id')
            ->paginate(30);

        return view('cashbooks.cashbook', compact('entries', 'totalIn', 'totalOut', 'balance'));
    }

    public function bankbook()
    {
        $query = Cashbook::whereIn('transaction_type', ['bank_in', 'bank_out']);
        $totalIn = (clone $query)->where('transaction_type', 'bank_in')->sum('amount');
        $totalOut = (clone $query)->where('transaction_type', 'bank_out')->sum('amount');
        $balance = $totalIn - $totalOut;

        $entries = $query
            ->latest('entry_date')
            ->latest('id')
            ->paginate(30);

        return view('cashbooks.bankbook', compact('entries', 'totalIn', 'totalOut', 'balance'));
    }
}
