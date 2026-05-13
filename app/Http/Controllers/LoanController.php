<?php

namespace App\Http\Controllers;

use App\Http\Requests\Loans\StoreLoanRequest;
use App\Models\Loan;
use App\Models\Partner;
use App\Services\LoanPostingService;

class LoanController extends Controller
{
    public function index()
    {
        $loans = Loan::with('partner')
            ->latest('loan_date')
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
        $partners = Partner::active()
            ->orderBy('name')
            ->get(['id', 'name', 'phone']);

        return view('loans.create', compact('loanTypes', 'partners'));
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
        $loan->load('partner');

        $transactions = $loan->transactions()
            ->latest('transaction_date')
            ->latest('id')
            ->paginate(15);
        $transactionTypes = $postingService->allowedTransactionTypes($loan);

        return view('loans.show', compact('loan', 'transactions', 'transactionTypes'));
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
        $loans = Loan::with('partner')
            ->where('status', $status)
            ->latest('loan_date')
            ->latest('id')
            ->paginate(30);

        $totalPrincipal = Loan::where('status', $status)->sum('principal_amount');
        $totalInterest = Loan::where('status', $status)->sum('total_interest');
        $totalBalance = Loan::where('status', $status)->sum('balance_amount');

        return view('loans.report', compact('loans', 'status', 'totalPrincipal', 'totalInterest', 'totalBalance'));
    }
}
