<?php

namespace Tests\Feature;

use App\Models\Cashbook;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Ledger;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpenseManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_expense_category_can_be_created(): void
    {
        $response = $this->actingAs(User::factory()->create())->post(route('expense-categories.store'), [
            'name' => 'Transport',
            'description' => 'Local vehicle and delivery expenses',
            'status' => 'active',
        ]);

        $response->assertRedirect(route('expense-categories.index'));

        $this->assertDatabaseHas('expense_categories', [
            'name' => 'Transport',
            'status' => 'active',
        ]);
    }

    public function test_expense_category_can_be_updated_and_soft_deleted(): void
    {
        $category = $this->category('Repair');
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('expense-categories.edit', $category))
            ->assertOk()
            ->assertSee('Edit Expense Category')
            ->assertSee('Repair');

        $response = $this->actingAs($user)->put(route('expense-categories.update', $category), [
            'name' => 'Repair & Maintenance',
            'description' => 'Machine and shop repair',
            'status' => 'inactive',
        ]);

        $response->assertRedirect(route('expense-categories.index'));

        $category->refresh();
        $this->assertSame('Repair & Maintenance', $category->name);
        $this->assertSame('inactive', $category->status);

        $this->actingAs($user)->delete(route('expense-categories.destroy', $category))
            ->assertRedirect(route('expense-categories.index'));

        $this->assertSoftDeleted('expense_categories', ['id' => $category->id]);
    }

    public function test_cash_expense_posts_cash_out_and_expense_ledger_debit(): void
    {
        $category = $this->category('Rent');

        $response = $this->actingAs(User::factory()->create())->post(route('expenses.store'), [
            'expense_date' => '2026-05-13',
            'expense_category_id' => $category->id,
            'amount' => 1250,
            'payment_mode' => 'cash',
            'paid_to' => 'Building Owner',
            'notes' => 'May rent',
        ]);

        $response->assertRedirect(route('expenses.index'));

        $expense = Expense::firstOrFail();
        $this->assertStringStartsWith('EXP-', $expense->expense_no);
        $this->assertSame('Rent', $expense->category->name);
        $this->assertSame(1250.0, (float) $expense->amount);

        $cashbook = Cashbook::firstOrFail();
        $this->assertSame('cash_out', $cashbook->transaction_type);
        $this->assertSame('expense', $cashbook->reference_type);
        $this->assertSame($expense->id, $cashbook->reference_id);
        $this->assertSame(1250.0, (float) $cashbook->amount);

        $ledger = Ledger::firstOrFail();
        $this->assertSame('expense', $ledger->party_type);
        $this->assertSame($category->id, $ledger->party_id);
        $this->assertSame('expense', $ledger->reference_type);
        $this->assertSame($expense->id, $ledger->reference_id);
        $this->assertSame(1250.0, (float) $ledger->debit);
        $this->assertSame(0.0, (float) $ledger->credit);
        $this->assertSame(1250.0, (float) $ledger->balance);
    }

    public function test_expense_show_page_displays_posted_cashbook_and_ledger_details(): void
    {
        $expense = $this->createExpense($this->category('Electricity'), '2026-05-13', 750, 'cheque', 'TNEB');

        $response = $this->actingAs(User::factory()->create())->get(route('expenses.show', $expense));

        $response->assertOk();
        $response->assertSee($expense->expense_no);
        $response->assertSee('Electricity');
        $response->assertSee('Bankbook Out');
        $response->assertSee('Rs. 750.00');
    }

    public function test_bank_modes_post_bank_out(): void
    {
        $category = $this->category('Salary');

        $this->actingAs(User::factory()->create())->post(route('expenses.store'), [
            'expense_date' => '2026-05-13',
            'expense_category_id' => $category->id,
            'amount' => 5000,
            'payment_mode' => 'upi',
            'paid_to' => 'Staff',
        ]);

        $cashbook = Cashbook::firstOrFail();
        $this->assertSame('bank_out', $cashbook->transaction_type);
        $this->assertSame('upi', $cashbook->payment_mode);
    }

    public function test_inactive_category_cannot_be_used_for_expense(): void
    {
        $category = $this->category('Old Category', 'inactive');

        $response = $this->actingAs(User::factory()->create())
            ->from(route('expenses.create'))
            ->post(route('expenses.store'), [
                'expense_date' => '2026-05-13',
                'expense_category_id' => $category->id,
                'amount' => 100,
                'payment_mode' => 'cash',
            ]);

        $response->assertRedirect(route('expenses.create'));
        $response->assertSessionHasErrors('expense_category_id');
        $this->assertSame(0, Expense::count());
        $this->assertSame(0, Cashbook::count());
        $this->assertSame(0, Ledger::count());
    }

    public function test_expense_validation_blocks_zero_or_negative_amount(): void
    {
        $category = $this->category('Office Expense');

        $response = $this->actingAs(User::factory()->create())
            ->from(route('expenses.create'))
            ->post(route('expenses.store'), [
                'expense_date' => '2026-05-13',
                'expense_category_id' => $category->id,
                'amount' => 0,
                'payment_mode' => 'cash',
            ]);

        $response->assertRedirect(route('expenses.create'));
        $response->assertSessionHasErrors('amount');
        $this->assertSame(0, Expense::count());
        $this->assertSame(0, Cashbook::count());
        $this->assertSame(0, Ledger::count());
    }

    public function test_expense_report_filters_by_date(): void
    {
        $category = $this->category('Fuel');

        $this->createExpense($category, '2026-05-13', 200, 'cash', 'May Fuel');
        $this->createExpense($category, '2026-04-30', 500, 'bank', 'April Fuel');

        $response = $this->actingAs(User::factory()->create())->get(route('expenses.report', [
            'from_date' => '2026-05-01',
            'to_date' => '2026-05-31',
        ]));

        $response->assertOk();
        $response->assertSee('May Fuel');
        $response->assertDontSee('April Fuel');
        $response->assertSee('Rs. 200.00');
    }

    public function test_category_report_groups_filtered_expenses(): void
    {
        $rent = $this->category('Rent');
        $fuel = $this->category('Fuel');

        $this->createExpense($rent, '2026-05-13', 1000, 'cash', 'May Rent');
        $this->createExpense($fuel, '2026-05-14', 250, 'upi', 'May Fuel');
        $this->createExpense($fuel, '2026-04-30', 900, 'cash', 'Old Fuel');

        $response = $this->actingAs(User::factory()->create())->get(route('expenses.category-report', [
            'from_date' => '2026-05-01',
            'to_date' => '2026-05-31',
        ]));

        $response->assertOk();
        $response->assertSee('Rent');
        $response->assertSee('Fuel');
        $response->assertSee('Rs. 1,000.00');
        $response->assertSee('Rs. 250.00');
        $response->assertDontSee('Rs. 900.00');
    }

    public function test_dashboard_net_profit_subtracts_expenses(): void
    {
        $this->seedSaleProfit(500);
        $this->createExpense($this->category('Office Expense'), '2026-05-13', 125, 'cash', 'Stationery');

        $response = $this->actingAs(User::factory()->create())->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Net Profit');
        $response->assertSee('Rs. 375.00');
    }

    public function test_profit_loss_report_subtracts_filtered_expenses_from_gross_profit(): void
    {
        $this->seedSaleProfit(1000);
        $this->createExpense($this->category('Fuel'), '2026-05-13', 250, 'cash', 'May Fuel');
        $this->createExpense($this->category('Old Rent'), '2026-04-30', 900, 'cash', 'April Rent');

        $response = $this->actingAs(User::factory()->create())->get(route('expenses.profit-loss', [
            'from_date' => '2026-05-01',
            'to_date' => '2026-05-31',
        ]));

        $response->assertOk();
        $response->assertSee('Profit & Loss Report', false);
        $response->assertSee('Rs. 1,000.00');
        $response->assertSee('Rs. 250.00');
        $response->assertSee('Rs. 750.00');
        $response->assertSee('Fuel');
        $response->assertDontSee('Old Rent');
    }

    private function category(string $name, string $status = 'active'): ExpenseCategory
    {
        return ExpenseCategory::create([
            'name' => $name,
            'status' => $status,
        ]);
    }

    private function createExpense(ExpenseCategory $category, string $date, float $amount, string $paymentMode, string $paidTo): Expense
    {
        $this->actingAs(User::factory()->create())->post(route('expenses.store'), [
            'expense_date' => $date,
            'expense_category_id' => $category->id,
            'amount' => $amount,
            'payment_mode' => $paymentMode,
            'paid_to' => $paidTo,
        ]);

        return Expense::latest('id')->firstOrFail();
    }

    private function seedSaleProfit(float $profitAmount): void
    {
        $customer = Customer::create(['name' => 'Profit Customer']);
        $category = ProductCategory::create([
            'name' => 'Steel',
            'status' => 'active',
        ]);
        $product = Product::create([
            'product_category_id' => $category->id,
            'name' => 'MS Rod',
            'code' => 'MS-ROD',
            'unit' => 'Kg',
            'purchase_price' => 50,
            'selling_price' => 75,
            'current_stock' => 20,
            'status' => 'active',
        ]);
        $sale = Sale::create([
            'sale_no' => 'PROFIT-001',
            'customer_id' => $customer->id,
            'sale_date' => '2026-05-13',
            'bill_type' => 'non_gst',
            'subtotal' => 1000,
            'gst_amount' => 0,
            'total_amount' => 1000,
            'paid_amount' => 1000,
            'balance_amount' => 0,
            'payment_status' => 'paid',
            'payment_mode' => 'cash',
        ]);

        SaleItem::create([
            'sale_id' => $sale->id,
            'product_id' => $product->id,
            'quantity' => 10,
            'unit' => 'Kg',
            'rate' => 100,
            'subtotal' => 1000,
            'gst_percentage' => 0,
            'gst_amount' => 0,
            'total' => 1000,
            'purchase_cost' => 500,
            'profit_amount' => $profitAmount,
        ]);
    }
}
