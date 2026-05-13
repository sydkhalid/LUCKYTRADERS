<?php

namespace Tests\Feature;

use App\Models\Cashbook;
use App\Models\Ledger;
use App\Models\Partner;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PartnerManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_partner_opening_investment_posts_capital_cashbook_and_ledger(): void
    {
        $response = $this->actingAs(User::factory()->create())->post(route('partners.store'), [
            'name' => 'Partner A',
            'phone' => '9000000001',
            'email' => 'partner@example.test',
            'address' => 'Chennai',
            'share_percentage' => 40,
            'opening_investment' => 10000,
            'opening_payment_mode' => 'cash',
            'transaction_date' => '2026-05-13',
            'status' => 'active',
        ]);

        $partner = Partner::firstOrFail();
        $response->assertRedirect(route('partners.show', $partner));

        $this->assertSame(10000.0, (float) $partner->current_investment);
        $this->assertSame(10000.0, (float) $partner->opening_investment);
        $this->assertSame(1, $partner->transactions()->count());

        $transaction = $partner->transactions()->firstOrFail();
        $this->assertSame('investment', $transaction->transaction_type);

        $cashbook = Cashbook::firstOrFail();
        $this->assertSame('cash_in', $cashbook->transaction_type);
        $this->assertSame(10000.0, (float) $cashbook->amount);

        $ledger = Ledger::firstOrFail();
        $this->assertSame('partner', $ledger->party_type);
        $this->assertSame($partner->id, $ledger->party_id);
        $this->assertSame(0.0, (float) $ledger->debit);
        $this->assertSame(10000.0, (float) $ledger->credit);
        $this->assertSame(10000.0, (float) $ledger->balance);
    }

    public function test_partner_withdrawal_decreases_capital_and_posts_bank_out(): void
    {
        $partner = Partner::create([
            'name' => 'Partner B',
            'share_percentage' => 60,
            'opening_investment' => 5000,
            'current_investment' => 5000,
            'status' => 'active',
        ]);

        $response = $this->actingAs(User::factory()->create())->post(route('partners.transactions.store', $partner), [
            'transaction_date' => '2026-05-13',
            'transaction_type' => 'withdrawal',
            'amount' => 1500,
            'payment_mode' => 'bank',
            'notes' => 'Personal withdrawal',
        ]);

        $response->assertRedirect(route('partners.show', $partner));

        $partner->refresh();
        $this->assertSame(3500.0, (float) $partner->current_investment);

        $cashbook = Cashbook::firstOrFail();
        $this->assertSame('bank_out', $cashbook->transaction_type);
        $this->assertSame('bank', $cashbook->payment_mode);

        $ledger = Ledger::firstOrFail();
        $this->assertSame(1500.0, (float) $ledger->debit);
        $this->assertSame(0.0, (float) $ledger->credit);
        $this->assertSame(3500.0, (float) $ledger->balance);
    }

    public function test_partner_return_cannot_exceed_current_investment(): void
    {
        $partner = Partner::create([
            'name' => 'Partner C',
            'share_percentage' => 10,
            'opening_investment' => 1000,
            'current_investment' => 1000,
            'status' => 'active',
        ]);

        $response = $this
            ->actingAs(User::factory()->create())
            ->from(route('partners.transactions.create', ['partner' => $partner, 'transaction_type' => 'return']))
            ->post(route('partners.transactions.store', $partner), [
                'transaction_date' => '2026-05-13',
                'transaction_type' => 'return',
                'amount' => 1000.01,
                'payment_mode' => 'cash',
            ]);

        $response->assertRedirect(route('partners.transactions.create', ['partner' => $partner, 'transaction_type' => 'return']));
        $response->assertSessionHasErrors('amount');
        $this->assertSame(1000.0, (float) $partner->fresh()->current_investment);
        $this->assertSame(0, Cashbook::count());
    }

    public function test_profit_share_report_calculates_partner_share_percentage(): void
    {
        Partner::create([
            'name' => 'Partner A',
            'share_percentage' => 40,
            'opening_investment' => 0,
            'current_investment' => 0,
            'status' => 'active',
        ]);
        Partner::create([
            'name' => 'Partner B',
            'share_percentage' => 60,
            'opening_investment' => 0,
            'current_investment' => 0,
            'status' => 'active',
        ]);

        $response = $this->actingAs(User::factory()->create())->get(route('partners.profit-share', [
            'profit_amount' => 25000,
        ]));

        $response->assertOk();
        $response->assertSee('Rs. 10,000.00');
        $response->assertSee('Rs. 15,000.00');
        $response->assertSee('100.00%');
    }

    public function test_profit_share_transaction_posts_cash_out_without_changing_capital(): void
    {
        $partner = Partner::create([
            'name' => 'Partner D',
            'share_percentage' => 50,
            'opening_investment' => 7000,
            'current_investment' => 7000,
            'status' => 'active',
        ]);

        $response = $this->actingAs(User::factory()->create())->post(route('partners.transactions.store', $partner), [
            'transaction_date' => '2026-05-13',
            'transaction_type' => 'profit_share',
            'amount' => 2500,
            'payment_mode' => 'upi',
            'notes' => 'Monthly profit share',
        ]);

        $response->assertRedirect(route('partners.show', $partner));

        $partner->refresh();
        $this->assertSame(7000.0, (float) $partner->current_investment);

        $cashbook = Cashbook::firstOrFail();
        $this->assertSame('bank_out', $cashbook->transaction_type);
        $this->assertSame('upi', $cashbook->payment_mode);

        $ledger = Ledger::firstOrFail();
        $this->assertSame(2500.0, (float) $ledger->debit);
        $this->assertSame(7000.0, (float) $ledger->balance);
    }
}
