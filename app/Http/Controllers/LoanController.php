<?php

namespace App\Http\Controllers;

use App\Http\Requests\Loans\StoreLoanRequest;
use App\Http\Requests\Loans\StoreLoanTransactionRequest;
use App\Models\Loan;
use App\Services\LoanPostingService;

class LoanController extends Controller
{
    public function index()
    {
        $loans = Loan::latest('loan_date')
            ->latest('id')
            ->paginate(20);

        $activeCount = Loan::active()->count();
        $closedCount = Loan::closed()->count();
        $activeBalance = Loan::active()->sum('balance_amount');

        return view('loans.index', compact('loans', 'activeCount', 'closedCount', 'activeBalance'));
    }

    public function create()
    {
        $loanTypes = Loan::TYPES;

        return view('loans.create', compact('loanTypes'));
    }

    public function store(StoreLoanRequest $request, LoanPostingService $postingService)
    {
        $loan = $postingService->createLoan($request->validated());

        return redirect()
            ->route('loans.show', $loan)
            ->with('success', 'Loan '.$loan->loan_no.' created successfully.');
    }

    public function show(Loan $loan, LoanPostingService $postingService)
    {
        $transactions = $loan->transactions()
            ->latest('transaction_date')
            ->latest('id')
            ->paginate(15);
        $transactionTypes = $postingService->allowedTransactionTypes($loan);

        return view('loans.show', compact('loan', 'transactions', 'transactionTypes'));
    }

    public function createTransaction(Loan $loan, LoanPostingService $postingService)
    {
        if ($loan->status === 'closed') {
            return redirect()
                ->route('loans.show', $loan)
                ->with('error', 'This loan is already closed.');
        }

        $transactionTypes = $postingService->allowedTransactionTypes($loan);

        return view('loans.transaction-create', compact('loan', 'transactionTypes'));
    }

    public function storeTransaction(StoreLoanTransactionRequest $request, Loan $loan, LoanPostingService $postingService)
    {
        $postingService->recordTransaction($loan, $request->validated());

        return redirect()
            ->route('loans.show', $loan)
            ->with('success', 'Loan transaction saved successfully.');
    }

    public function transactions(Loan $loan)
    {
        $transactions = $loan->transactions()
            ->latest('transaction_date')
            ->latest('id')
            ->paginate(30);

        return view('loans.transactions', compact('loan', 'transactions'));
    }

    public function activeReport()
    {
        return $this->report('active');
    }

    public function closedReport()
    {
        return $this->report('closed');
    }

    private function report(string $status)
    {
        $loans = Loan::where('status', $status)
            ->latest('loan_date')
            ->latest('id')
            ->paginate(30);

        $totalPrincipal = Loan::where('status', $status)->sum('principal_amount');
        $totalInterest = Loan::where('status', $status)->sum('total_interest');
        $totalBalance = Loan::where('status', $status)->sum('balance_amount');

        return view('loans.report', compact('loans', 'status', 'totalPrincipal', 'totalInterest', 'totalBalance'));
    }
}
