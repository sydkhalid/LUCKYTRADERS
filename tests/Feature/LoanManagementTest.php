<?php

namespace Tests\Feature;

use App\Models\Cashbook;
use App\Models\Ledger;
use App\Models\Loan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoanManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_loan_taken_posts_cash_in_ledger_credit_and_open_balance(): void
    {
        $response = $this->actingAs(User::factory()->create())->post(route('loans.store'), [
            'loan_type' => 'loan_taken',
            'party_name' => 'City Finance',
            'party_phone' => '9000000001',
            'loan_date' => '2026-05-13',
            'principal_amount' => 10000,
            'interest_percentage' => 10,
            'interest_type' => 'fixed',
            'payment_mode' => 'cash',
            'notes' => 'Working capital',
        ]);

        $loan = Loan::firstOrFail();
        $response->assertRedirect(route('loans.show', $loan));

        $this->assertSame('loan_taken', $loan->loan_type);
        $this->assertSame(1000.0, (float) $loan->total_interest);
        $this->assertSame(11000.0, (float) $loan->total_amount);
        $this->assertSame(0.0, (float) $loan->paid_amount);
        $this->assertSame(11000.0, (float) $loan->balance_amount);
        $this->assertSame('active', $loan->status);

        $transaction = $loan->transactions()->firstOrFail();
        $this->assertSame('received', $transaction->transaction_type);
        $this->assertSame(10000.0, (float) $transaction->amount);

        $cashbook = Cashbook::firstOrFail();
        $this->assertSame('cash_in', $cashbook->transaction_type);
        $this->assertSame(10000.0, (float) $cashbook->amount);

        $ledger = Ledger::firstOrFail();
        $this->assertSame('loan', $ledger->party_type);
        $this->assertSame($loan->id, $ledger->party_id);
        $this->assertSame(0.0, (float) $ledger->debit);
        $this->assertSame(11000.0, (float) $ledger->credit);
        $this->assertSame(11000.0, (float) $ledger->balance);
    }

    public function test_loan_given_posts_bank_out_and_received_transaction_closes_loan(): void
    {
        $this->actingAs(User::factory()->create())->post(route('loans.store'), [
            'loan_type' => 'loan_given',
            'party_name' => 'Kumar Traders',
            'loan_date' => '2026-05-13',
            'principal_amount' => 5000,
            'interest_percentage' => 0,
            'interest_type' => 'none',
            'payment_mode' => 'bank',
        ]);

        $loan = Loan::firstOrFail();
        $this->assertSame('bank_out', Cashbook::firstOrFail()->transaction_type);

        $response = $this->actingAs(User::factory()->create())->post(route('loans.transactions.store', $loan), [
            'transaction_date' => '2026-05-14',
            'transaction_type' => 'received',
            'amount' => 5000,
            'payment_mode' => 'upi',
            'notes' => 'Full return',
        ]);

        $response->assertRedirect(route('loans.show', $loan));

        $loan->refresh();
        $this->assertSame(5000.0, (float) $loan->paid_amount);
        $this->assertSame(0.0, (float) $loan->balance_amount);
        $this->assertSame('closed', $loan->status);
        $this->assertSame(2, $loan->transactions()->count());

        $cashbook = Cashbook::latest('id')->firstOrFail();
        $this->assertSame('bank_in', $cashbook->transaction_type);
        $this->assertSame('upi', $cashbook->payment_mode);

        $ledger = Ledger::latest('id')->firstOrFail();
        $this->assertSame(0.0, (float) $ledger->debit);
        $this->assertSame(5000.0, (float) $ledger->credit);
        $this->assertSame(0.0, (float) $ledger->balance);
    }

    public function test_partner_withdrawal_posts_cash_out_and_return_posts_cash_in(): void
    {
        $this->actingAs(User::factory()->create())->post(route('loans.store'), [
            'loan_type' => 'partner_withdrawal',
            'party_name' => 'Owner',
            'loan_date' => '2026-05-13',
            'principal_amount' => 2000,
            'interest_percentage' => 0,
            'interest_type' => 'none',
            'payment_mode' => 'cash',
        ]);

        $loan = Loan::firstOrFail();
        $this->assertSame('cash_out', Cashbook::firstOrFail()->transaction_type);
        $this->assertDatabaseHas('ledgers', [
            'party_type' => 'owner',
            'reference_type' => 'loan_transaction',
        ]);

        $this->actingAs(User::factory()->create())->post(route('loans.transactions.store', $loan), [
            'transaction_date' => '2026-05-15',
            'transaction_type' => 'return',
            'amount' => 2000,
            'payment_mode' => 'cash',
        ]);

        $loan->refresh();
        $this->assertSame('closed', $loan->status);
        $this->assertSame('cash_in', Cashbook::latest('id')->firstOrFail()->transaction_type);
    }

    public function test_partner_deposit_posts_bank_in_and_return_posts_bank_out(): void
    {
        $this->actingAs(User::factory()->create())->post(route('loans.store'), [
            'loan_type' => 'partner_deposit',
            'party_name' => 'Partner A',
            'partner_id' => 7,
            'loan_date' => '2026-05-13',
            'principal_amount' => 3000,
            'interest_percentage' => 0,
            'interest_type' => 'none',
            'payment_mode' => 'upi',
        ]);

        $loan = Loan::firstOrFail();
        $this->assertSame('bank_in', Cashbook::firstOrFail()->transaction_type);
        $this->assertDatabaseHas('ledgers', [
            'party_type' => 'partner',
            'party_id' => 7,
            'reference_type' => 'loan_transaction',
        ]);

        $this->actingAs(User::factory()->create())->post(route('loans.transactions.store', $loan), [
            'transaction_date' => '2026-05-16',
            'transaction_type' => 'return',
            'amount' => 3000,
            'payment_mode' => 'cheque',
        ]);

        $loan->refresh();
        $this->assertSame('closed', $loan->status);

        $cashbook = Cashbook::latest('id')->firstOrFail();
        $this->assertSame('bank_out', $cashbook->transaction_type);
        $this->assertSame('cheque', $cashbook->payment_mode);
    }

    public function test_loan_transaction_cannot_exceed_balance(): void
    {
        $this->actingAs(User::factory()->create())->post(route('loans.store'), [
            'loan_type' => 'loan_taken',
            'party_name' => 'Private Lender',
            'loan_date' => '2026-05-13',
            'principal_amount' => 1000,
            'interest_percentage' => 0,
            'interest_type' => 'none',
            'payment_mode' => 'cash',
        ]);

        $loan = Loan::firstOrFail();

        $response = $this
            ->actingAs(User::factory()->create())
            ->from(route('loans.transactions.create', $loan))
            ->post(route('loans.transactions.store', $loan), [
                'transaction_date' => '2026-05-14',
                'transaction_type' => 'repayment',
                'amount' => 1000.01,
                'payment_mode' => 'cash',
            ]);

        $response->assertRedirect(route('loans.transactions.create', $loan));
        $response->assertSessionHasErrors('amount');

        $this->assertSame(1, $loan->transactions()->count());
        $this->assertSame('active', $loan->fresh()->status);
    }
}
