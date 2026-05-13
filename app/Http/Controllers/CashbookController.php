<?php

namespace App\Http\Controllers;

use App\Models\Cashbook;
use Illuminate\Http\Request;

class CashbookController extends Controller
{
    public function cashbook(Request $request)
    {
        $filters = $this->filters($request);
        $query = $this->reportQuery(['cash_in', 'cash_out'], $filters);
        $totalIn = (clone $query)->where('transaction_type', 'cash_in')->sum('amount');
        $totalOut = (clone $query)->where('transaction_type', 'cash_out')->sum('amount');
        $balance = $totalIn - $totalOut;

        $entries = $query
            ->latest('entry_date')
            ->latest('id')
            ->paginate(30)
            ->withQueryString();

        return view('cashbooks.cashbook', compact('entries', 'totalIn', 'totalOut', 'balance', 'filters'));
    }

    public function bankbook(Request $request)
    {
        $filters = $this->filters($request);
        $query = $this->reportQuery(['bank_in', 'bank_out'], $filters);
        $totalIn = (clone $query)->where('transaction_type', 'bank_in')->sum('amount');
        $totalOut = (clone $query)->where('transaction_type', 'bank_out')->sum('amount');
        $balance = $totalIn - $totalOut;

        $entries = $query
            ->latest('entry_date')
            ->latest('id')
            ->paginate(30)
            ->withQueryString();

        return view('cashbooks.bankbook', compact('entries', 'totalIn', 'totalOut', 'balance', 'filters'));
    }

    private function filters(Request $request): array
    {
        return $request->validate([
            'from_date' => ['nullable', 'date'],
            'to_date' => ['nullable', 'date', 'after_or_equal:from_date'],
        ]);
    }

    private function reportQuery(array $transactionTypes, array $filters)
    {
        return Cashbook::whereIn('transaction_type', $transactionTypes)
            ->when($filters['from_date'] ?? null, fn ($query, $date) => $query->whereDate('entry_date', '>=', $date))
            ->when($filters['to_date'] ?? null, fn ($query, $date) => $query->whereDate('entry_date', '<=', $date));
    }
}
