<?php

namespace App\Services;

use App\Models\Cashbook;
use App\Models\Ledger;
use App\Models\Partner;
use App\Models\PartnerTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PartnerPostingService
{
    public function createPartner(array $data): Partner
    {
        return DB::transaction(function () use ($data) {
            $openingInvestment = round((float) ($data['opening_investment'] ?? 0), 2);

            $partner = Partner::create([
                'name' => $data['name'],
                'phone' => $data['phone'] ?? null,
                'email' => $data['email'] ?? null,
                'address' => $data['address'] ?? null,
                'share_percentage' => round((float) ($data['share_percentage'] ?? 0), 2),
                'opening_investment' => $openingInvestment,
                'current_investment' => 0,
                'status' => $data['status'] ?? 'active',
            ]);

            if ($openingInvestment > 0) {
                $this->recordTransaction($partner, [
                    'transaction_date' => $data['transaction_date'] ?? $data['created_date'] ?? now()->toDateString(),
                    'transaction_type' => 'investment',
                    'amount' => $openingInvestment,
                    'payment_mode' => $data['opening_payment_mode'],
                    'notes' => 'Opening investment',
                ]);
            }

            return $partner->refresh();
        });
    }

    public function recordTransaction(Partner $partner, array $data): PartnerTransaction
    {
        return DB::transaction(function () use ($partner, $data) {
            $partner = Partner::whereKey($partner->id)->lockForUpdate()->firstOrFail();
            $transactionType = $data['transaction_type'];
            $amount = round((float) $data['amount'], 2);

            $this->validateTransaction($partner, $transactionType, $amount);

            $newBalance = $this->newPartnerBalance($partner, $transactionType, $amount);

            $transaction = $partner->transactions()->create([
                'transaction_date' => $data['transaction_date'],
                'transaction_type' => $transactionType,
                'amount' => $amount,
                'payment_mode' => $data['payment_mode'],
                'notes' => $data['notes'] ?? null,
            ]);

            $partner->forceFill(['current_investment' => $newBalance])->save();

            $this->postCashbook($partner, $transaction);
            $this->postLedger($partner, $transaction, $newBalance);

            return $transaction;
        });
    }

    public function profitShareRows(float $profitAmount)
    {
        return Partner::active()
            ->orderBy('name')
            ->get()
            ->map(function (Partner $partner) use ($profitAmount) {
                return [
                    'partner' => $partner,
                    'share_amount' => round($profitAmount * (float) $partner->share_percentage / 100, 2),
                ];
            });
    }

    private function validateTransaction(Partner $partner, string $transactionType, float $amount): void
    {
        if ($partner->status !== 'active') {
            throw ValidationException::withMessages([
                'partner_id' => 'Only active partners can receive transactions.',
            ]);
        }

        if (in_array($transactionType, ['withdrawal', 'return'], true)
            && $amount > round((float) $partner->current_investment, 2)) {
            throw ValidationException::withMessages([
                'amount' => 'Transaction amount cannot be greater than partner current investment.',
            ]);
        }
    }

    private function newPartnerBalance(Partner $partner, string $transactionType, float $amount): float
    {
        $currentInvestment = (float) $partner->current_investment;

        return match ($transactionType) {
            'investment' => round($currentInvestment + $amount, 2),
            'withdrawal', 'return' => round($currentInvestment - $amount, 2),
            'profit_share' => round($currentInvestment, 2),
            default => round($currentInvestment, 2),
        };
    }

    private function postCashbook(Partner $partner, PartnerTransaction $transaction): void
    {
        $direction = $transaction->transaction_type === 'investment' ? 'in' : 'out';
        $book = $transaction->payment_mode === 'cash' ? 'cash' : 'bank';

        Cashbook::create([
            'entry_date' => $transaction->transaction_date,
            'transaction_type' => $book.'_'.$direction,
            'reference_type' => 'partner_transaction',
            'reference_id' => $transaction->id,
            'amount' => $transaction->amount,
            'payment_mode' => $transaction->payment_mode,
            'remarks' => $partner->name.' - '.$transaction->typeLabel(),
        ]);
    }

    private function postLedger(Partner $partner, PartnerTransaction $transaction, float $balance): void
    {
        $isCredit = $transaction->transaction_type === 'investment';

        Ledger::create([
            'ledger_date' => $transaction->transaction_date,
            'party_type' => 'partner',
            'party_id' => $partner->id,
            'reference_type' => 'partner_transaction',
            'reference_id' => $transaction->id,
            'debit' => $isCredit ? 0 : $transaction->amount,
            'credit' => $isCredit ? $transaction->amount : 0,
            'balance' => $balance,
            'remarks' => $partner->name.' - '.$transaction->typeLabel(),
        ]);
    }
}
