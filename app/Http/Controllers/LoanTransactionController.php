<?php

namespace App\Http\Controllers;

use App\Http\Requests\Loans\StoreLoanTransactionRequest;
use App\Models\Loan;
use App\Services\LoanPostingService;

class LoanTransactionController extends Controller
{
    public function index(Loan $loan)
    {
        $transactions = $loan->transactions()
            ->latest('transaction_date')
            ->latest('id')
            ->paginate(30);

        return view('loans.transactions', compact('loan', 'transactions'));
    }

    public function create(Loan $loan, LoanPostingService $postingService)
    {
        if ($loan->status === 'closed') {
            return redirect()
                ->route('loans.show', $loan)
                ->with('error', 'This loan is already closed.');
        }

        $transactionTypes = $postingService->allowedTransactionTypes($loan);

        return view('loans.transaction-create', compact('loan', 'transactionTypes'));
    }

    public function store(StoreLoanTransactionRequest $request, Loan $loan, LoanPostingService $postingService)
    {
        $postingService->recordTransaction($loan, $request->validated());

        return redirect()
            ->route('loans.show', $loan)
            ->with('success', 'Loan transaction saved successfully.');
    }
}
