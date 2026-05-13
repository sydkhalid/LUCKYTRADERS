<?php

namespace App\Services;

use App\Models\Cashbook;
use App\Models\Ledger;
use App\Models\Loan;
use App\Models\LoanTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LoanPostingService
{
    public function createLoan(array $data): Loan
    {
        return DB::transaction(function () use ($data) {
            $principalAmount = round((float) $data['principal_amount'], 2);
            $interestPercentage = round((float) ($data['interest_percentage'] ?? 0), 2);
            $totalInterest = $this->calculateInterest($principalAmount, $interestPercentage, $data['interest_type']);
            $totalAmount = round($principalAmount + $totalInterest, 2);

            $loan = Loan::create([
                'loan_no' => $this->nextLoanNo(),
                'loan_type' => $data['loan_type'],
                'party_name' => $data['party_name'],
                'party_phone' => $data['party_phone'] ?? null,
                'partner_id' => $data['partner_id'] ?? null,
                'loan_date' => $data['loan_date'],
                'principal_amount' => $principalAmount,
                'interest_percentage' => $interestPercentage,
                'interest_type' => $data['interest_type'],
                'total_interest' => $totalInterest,
                'total_amount' => $totalAmount,
                'paid_amount' => 0,
                'balance_amount' => $totalAmount,
                'status' => 'active',
                'notes' => $data['notes'] ?? null,
            ]);

            $transaction = $loan->transactions()->create([
                'transaction_date' => $data['loan_date'],
                'transaction_type' => $this->openingTransactionType($loan->loan_type),
                'amount' => $principalAmount,
                'payment_mode' => $data['payment_mode'],
                'notes' => 'Opening '.$loan->typeLabel(),
            ]);

            $this->postCashbook($loan, $transaction);
            $this->postLedger($loan, $transaction, (float) $loan->balance_amount, true);

            return $loan;
        });
    }

    public function recordTransaction(Loan $loan, array $data): LoanTransaction
    {
        return DB::transaction(function () use ($loan, $data) {
            $loan = Loan::whereKey($loan->id)->lockForUpdate()->firstOrFail();
            $transactionType = $data['transaction_type'];
            $amount = round((float) $data['amount'], 2);

            $this->validateTransaction($loan, $transactionType, $amount);

            $newPaidAmount = round((float) $loan->paid_amount + $amount, 2);
            $newBalance = max(round((float) $loan->total_amount - $newPaidAmount, 2), 0);

            $transaction = $loan->transactions()->create([
                'transaction_date' => $data['transaction_date'],
                'transaction_type' => $transactionType,
                'amount' => $amount,
                'payment_mode' => $data['payment_mode'],
                'notes' => $data['notes'] ?? null,
            ]);

            $loan->forceFill([
                'paid_amount' => $newPaidAmount,
                'balance_amount' => $newBalance,
                'status' => $newBalance <= 0 ? 'closed' : 'active',
            ])->save();

            $this->postCashbook($loan, $transaction);
            $this->postLedger($loan, $transaction, $newBalance);

            return $transaction;
        });
    }

    public function allowedTransactionTypes(Loan $loan): array
    {
        return match ($loan->loan_type) {
            'loan_taken' => ['repayment' => 'Repayment'],
            'loan_given' => ['received' => 'Received'],
            'partner_withdrawal' => ['return' => 'Return'],
            'partner_deposit' => ['return' => 'Return'],
            default => [],
        };
    }

    private function validateTransaction(Loan $loan, string $transactionType, float $amount): void
    {
        if ($loan->status === 'closed') {
            throw ValidationException::withMessages([
                'transaction_type' => 'This loan is already closed.',
            ]);
        }

        if (! array_key_exists($transactionType, $this->allowedTransactionTypes($loan))) {
            throw ValidationException::withMessages([
                'transaction_type' => 'This transaction type is not allowed for the selected loan.',
            ]);
        }

        if ($amount > round((float) $loan->balance_amount, 2)) {
            throw ValidationException::withMessages([
                'amount' => 'Transaction amount cannot be greater than the loan balance.',
            ]);
        }
    }

    private function postCashbook(Loan $loan, LoanTransaction $transaction): void
    {
        $direction = $this->isMoneyIn($loan, $transaction) ? 'in' : 'out';
        $book = $transaction->payment_mode === 'cash' ? 'cash' : 'bank';

        Cashbook::create([
            'entry_date' => $transaction->transaction_date,
            'transaction_type' => $book.'_'.$direction,
            'reference_type' => 'loan_transaction',
            'reference_id' => $transaction->id,
            'amount' => $transaction->amount,
            'payment_mode' => $transaction->payment_mode,
            'remarks' => $loan->loan_no.' - '.$loan->party_name.' - '.$transaction->typeLabel(),
        ]);
    }

    private function postLedger(Loan $loan, LoanTransaction $transaction, float $balance, bool $isOpening = false): void
    {
        $amount = $isOpening ? (float) $loan->total_amount : (float) $transaction->amount;
        $isDebit = $isOpening
            ? ! $this->isLiabilityLoan($loan)
            : $this->isLiabilityLoan($loan);

        Ledger::create([
            'ledger_date' => $transaction->transaction_date,
            'party_type' => $this->ledgerPartyType($loan),
            'party_id' => $this->ledgerPartyId($loan),
            'reference_type' => 'loan_transaction',
            'reference_id' => $transaction->id,
            'debit' => $isDebit ? $amount : 0,
            'credit' => $isDebit ? 0 : $amount,
            'balance' => $balance,
            'remarks' => $loan->loan_no.' - '.$loan->party_name.' - '.$transaction->typeLabel(),
        ]);
    }

    private function calculateInterest(float $principalAmount, float $interestPercentage, string $interestType): float
    {
        if ($interestType === 'none' || $interestPercentage <= 0) {
            return 0;
        }

        return round($principalAmount * $interestPercentage / 100, 2);
    }

    private function openingTransactionType(string $loanType): string
    {
        return in_array($loanType, ['loan_taken', 'partner_deposit'], true) ? 'received' : 'given';
    }

    private function isMoneyIn(Loan $loan, LoanTransaction $transaction): bool
    {
        return match ($transaction->transaction_type) {
            'received' => true,
            'given', 'repayment' => false,
            'return' => $loan->loan_type === 'partner_withdrawal',
            default => false,
        };
    }

    private function isLiabilityLoan(Loan $loan): bool
    {
        return in_array($loan->loan_type, ['loan_taken', 'partner_deposit'], true);
    }

    private function ledgerPartyType(Loan $loan): string
    {
        if (in_array($loan->loan_type, ['partner_withdrawal', 'partner_deposit'], true)) {
            return $loan->partner_id ? 'partner' : 'owner';
        }

        return 'loan';
    }

    private function ledgerPartyId(Loan $loan): ?int
    {
        if (in_array($loan->loan_type, ['partner_withdrawal', 'partner_deposit'], true)) {
            return $loan->partner_id;
        }

        return $loan->id;
    }

    private function nextLoanNo(): string
    {
        $date = now()->format('Ymd');
        $sequence = Loan::where('loan_no', 'like', 'LN-'.$date.'-%')->count() + 1;

        do {
            $loanNo = sprintf('LN-%s-%05d', $date, $sequence++);
        } while (Loan::where('loan_no', $loanNo)->exists());

        return $loanNo;
    }
}
