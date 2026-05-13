<?php

namespace App\Services;

use App\Models\Cashbook;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Ledger;
use Illuminate\Support\Facades\DB;

class ExpensePostingService
{
    public function recordExpense(array $data): Expense
    {
        return DB::transaction(function () use ($data) {
            $category = ExpenseCategory::whereKey($data['expense_category_id'])
                ->lockForUpdate()
                ->firstOrFail();
            $amount = round((float) $data['amount'], 2);

            $expense = Expense::create([
                'expense_no' => $this->nextExpenseNo(),
                'expense_date' => $data['expense_date'],
                'expense_category_id' => $category->id,
                'amount' => $amount,
                'payment_mode' => $data['payment_mode'],
                'paid_to' => $data['paid_to'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            $this->postCashbook($expense, $category);
            $this->postLedger($expense, $category);

            return $expense;
        });
    }

    private function postCashbook(Expense $expense, ExpenseCategory $category): void
    {
        Cashbook::create([
            'entry_date' => $expense->expense_date,
            'transaction_type' => $expense->payment_mode === 'cash' ? 'cash_out' : 'bank_out',
            'reference_type' => 'expense',
            'reference_id' => $expense->id,
            'amount' => $expense->amount,
            'payment_mode' => $expense->payment_mode,
            'remarks' => $expense->expense_no.' - '.$category->name.($expense->paid_to ? ' - '.$expense->paid_to : ''),
        ]);
    }

    private function postLedger(Expense $expense, ExpenseCategory $category): void
    {
        $balance = $this->expenseCategoryBalance($category, (float) $expense->amount);

        Ledger::create([
            'ledger_date' => $expense->expense_date,
            'party_type' => 'expense',
            'party_id' => $category->id,
            'reference_type' => 'expense',
            'reference_id' => $expense->id,
            'debit' => $expense->amount,
            'credit' => 0,
            'balance' => $balance,
            'remarks' => $expense->expense_no.' - '.$category->name,
        ]);
    }

    private function expenseCategoryBalance(ExpenseCategory $category, float $amount): float
    {
        $lastBalance = Ledger::where('party_type', 'expense')
            ->where('party_id', $category->id)
            ->latest('ledger_date')
            ->latest('id')
            ->value('balance');

        return round((float) $lastBalance + $amount, 2);
    }

    private function nextExpenseNo(): string
    {
        $date = now()->format('Ymd');
        $sequence = Expense::where('expense_no', 'like', 'EXP-'.$date.'-%')->count() + 1;

        do {
            $expenseNo = sprintf('EXP-%s-%05d', $date, $sequence++);
        } while (Expense::where('expense_no', $expenseNo)->exists());

        return $expenseNo;
    }
}
