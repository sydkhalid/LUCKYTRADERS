<?php

namespace Tests\Feature;

use App\Models\Cashbook;
use App\Models\Customer;
use App\Models\Ledger;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentLedgerTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_receipt_updates_sale_customer_ledger_and_cashbook(): void
    {
        $customer = Customer::create([
            'name' => 'Arun Steel',
            'current_balance' => 800,
        ]);

        $sale = Sale::create([
            'sale_no' => 'SAL-TEST-00001',
            'customer_id' => $customer->id,
            'sale_date' => '2026-05-13',
            'bill_type' => 'gst',
            'subtotal' => 1000,
            'gst_amount' => 0,
            'total_amount' => 1000,
            'paid_amount' => 200,
            'balance_amount' => 800,
            'payment_status' => 'partial',
            'payment_mode' => 'credit',
        ]);

        $response = $this->actingAs(User::factory()->create())->post(route('receipts.store'), [
            'customer_id' => $customer->id,
            'payment_date' => '2026-05-13',
            'reference_type' => 'gst_invoice',
            'reference_id' => $sale->id,
            'amount' => 300,
            'payment_mode' => 'cash',
            'notes' => 'Cash received',
        ]);

        $response->assertRedirect(route('payments.index'));

        $this->assertSame(500.0, (float) $customer->fresh()->current_balance);
        $this->assertSame(500.0, (float) $sale->fresh()->paid_amount);
        $this->assertSame(500.0, (float) $sale->fresh()->balance_amount);
        $this->assertSame('partial', $sale->fresh()->payment_status);

        $payment = Payment::where('party_type', 'customer')->firstOrFail();
        $this->assertSame('receipt', $payment->transaction_type);
        $this->assertSame('gst_invoice', $payment->reference_type);

        $ledger = Ledger::where('party_type', 'customer')->latest('id')->firstOrFail();
        $this->assertSame(300.0, (float) $ledger->credit);
        $this->assertSame(500.0, (float) $ledger->balance);

        $cashbook = Cashbook::firstOrFail();
        $this->assertSame('cash_in', $cashbook->transaction_type);
        $this->assertSame(300.0, (float) $cashbook->amount);
    }

    public function test_customer_receipt_pages_show_pending_invoice_and_saved_receipt(): void
    {
        $customer = Customer::create([
            'name' => 'Arun Steel',
            'current_balance' => 600,
        ]);

        $sale = Sale::create([
            'sale_no' => 'SAL-TEST-00003',
            'customer_id' => $customer->id,
            'sale_date' => '2026-05-13',
            'bill_type' => 'non_gst',
            'subtotal' => 600,
            'gst_amount' => 0,
            'total_amount' => 600,
            'paid_amount' => 0,
            'balance_amount' => 600,
            'payment_status' => 'pending',
            'payment_mode' => 'credit',
        ]);

        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('receipts.create'))
            ->assertOk()
            ->assertSee('Receive Customer Payment')
            ->assertSee('SAL-TEST-00003');

        $this->actingAs($user)->post(route('receipts.store'), [
            'customer_id' => $customer->id,
            'payment_date' => '2026-05-13',
            'reference_type' => 'normal_bill',
            'reference_id' => $sale->id,
            'amount' => 250,
            'payment_mode' => 'cheque',
            'notes' => 'Cheque received',
        ]);

        $payment = Payment::where('party_type', 'customer')->firstOrFail();

        $this->actingAs($user)
            ->get(route('receipts.index'))
            ->assertOk()
            ->assertSee('Customer Receipts')
            ->assertSee($payment->payment_no);

        $this->actingAs($user)
            ->get(route('receipts.show', $payment))
            ->assertOk()
            ->assertSee($payment->payment_no)
            ->assertSee('Bankbook');

        $this->assertDatabaseHas('cashbooks', [
            'transaction_type' => 'bank_in',
            'payment_mode' => 'cheque',
            'reference_type' => 'payment',
            'reference_id' => $payment->id,
        ]);
    }

    public function test_customer_receipt_cannot_update_another_customers_sale(): void
    {
        $customer = Customer::create([
            'name' => 'Correct Customer',
            'current_balance' => 0,
        ]);
        $otherCustomer = Customer::create([
            'name' => 'Other Customer',
            'current_balance' => 500,
        ]);
        $sale = Sale::create([
            'sale_no' => 'SAL-TEST-00002',
            'customer_id' => $otherCustomer->id,
            'sale_date' => '2026-05-13',
            'bill_type' => 'gst',
            'subtotal' => 500,
            'gst_amount' => 0,
            'total_amount' => 500,
            'paid_amount' => 0,
            'balance_amount' => 500,
            'payment_status' => 'pending',
            'payment_mode' => 'credit',
        ]);

        $response = $this
            ->actingAs(User::factory()->create())
            ->from(route('receipts.create'))
            ->post(route('receipts.store'), [
                'customer_id' => $customer->id,
                'payment_date' => '2026-05-13',
                'reference_type' => 'sale',
                'reference_id' => $sale->id,
                'amount' => 100,
                'payment_mode' => 'cash',
            ]);

        $response->assertRedirect(route('receipts.create'));
        $response->assertSessionHasErrors('reference_id');
        $this->assertSame(0, Payment::count());
    }

    public function test_customer_receipt_cannot_exceed_sale_balance(): void
    {
        $customer = Customer::create([
            'name' => 'Balance Customer',
            'current_balance' => 100,
        ]);
        $sale = Sale::create([
            'sale_no' => 'SAL-TEST-00004',
            'customer_id' => $customer->id,
            'sale_date' => '2026-05-13',
            'bill_type' => 'gst',
            'subtotal' => 100,
            'gst_amount' => 0,
            'total_amount' => 100,
            'paid_amount' => 0,
            'balance_amount' => 100,
            'payment_status' => 'pending',
            'payment_mode' => 'credit',
        ]);

        $response = $this
            ->actingAs(User::factory()->create())
            ->from(route('receipts.create'))
            ->post(route('receipts.store'), [
                'customer_id' => $customer->id,
                'payment_date' => '2026-05-13',
                'reference_type' => 'gst_invoice',
                'reference_id' => $sale->id,
                'amount' => 101,
                'payment_mode' => 'cash',
            ]);

        $response->assertRedirect(route('receipts.create'));
        $response->assertSessionHasErrors('amount');
        $this->assertSame(0, Payment::count());
        $this->assertSame(100.0, (float) $sale->fresh()->balance_amount);
    }

    public function test_supplier_payment_updates_purchase_supplier_ledger_and_bankbook(): void
    {
        $supplier = Supplier::create([
            'name' => 'Lucky Supplier',
            'current_balance' => 900,
        ]);

        $purchase = Purchase::create([
            'purchase_no' => 'PUR-TEST-00001',
            'supplier_id' => $supplier->id,
            'purchase_date' => '2026-05-13',
            'bill_type' => 'gst',
            'subtotal' => 1000,
            'gst_amount' => 0,
            'total_amount' => 1000,
            'paid_amount' => 100,
            'balance_amount' => 900,
            'payment_status' => 'partial',
            'payment_mode' => 'cash',
        ]);

        $response = $this->actingAs(User::factory()->create())->post(route('supplier-payments.store'), [
            'supplier_id' => $supplier->id,
            'payment_date' => '2026-05-13',
            'reference_type' => 'purchase',
            'reference_id' => $purchase->id,
            'amount' => 400,
            'payment_mode' => 'bank',
            'notes' => 'NEFT',
        ]);

        $response->assertRedirect(route('payments.index'));

        $this->assertSame(500.0, (float) $supplier->fresh()->current_balance);
        $this->assertSame(500.0, (float) $purchase->fresh()->paid_amount);
        $this->assertSame(500.0, (float) $purchase->fresh()->balance_amount);
        $this->assertSame('partial', $purchase->fresh()->payment_status);

        $ledger = Ledger::where('party_type', 'supplier')->latest('id')->firstOrFail();
        $this->assertSame(400.0, (float) $ledger->debit);
        $this->assertSame(500.0, (float) $ledger->balance);

        $cashbook = Cashbook::firstOrFail();
        $this->assertSame('bank_out', $cashbook->transaction_type);
        $this->assertSame('bank', $cashbook->payment_mode);
    }

    public function test_supplier_payment_pages_show_pending_purchase_and_saved_payment(): void
    {
        $supplier = Supplier::create([
            'name' => 'Lucky Supplier',
            'current_balance' => 700,
        ]);

        $purchase = Purchase::create([
            'purchase_no' => 'PUR-TEST-00002',
            'supplier_id' => $supplier->id,
            'purchase_date' => '2026-05-13',
            'bill_type' => 'non_gst',
            'supplier_invoice_no' => 'SUP-100',
            'subtotal' => 700,
            'gst_amount' => 0,
            'total_amount' => 700,
            'paid_amount' => 0,
            'balance_amount' => 700,
            'payment_status' => 'pending',
            'payment_mode' => 'cash',
        ]);

        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('supplier-payments.create'))
            ->assertOk()
            ->assertSee('Pay Supplier')
            ->assertSee('PUR-TEST-00002');

        $this->actingAs($user)->post(route('supplier-payments.store'), [
            'supplier_id' => $supplier->id,
            'payment_date' => '2026-05-13',
            'reference_type' => 'purchase',
            'reference_id' => $purchase->id,
            'amount' => 300,
            'payment_mode' => 'cash',
            'notes' => 'Cash paid',
        ]);

        $payment = Payment::where('party_type', 'supplier')->firstOrFail();

        $this->actingAs($user)
            ->get(route('supplier-payments.index'))
            ->assertOk()
            ->assertSee('Supplier Payments')
            ->assertSee($payment->payment_no);

        $this->actingAs($user)
            ->get(route('supplier-payments.show', $payment))
            ->assertOk()
            ->assertSee($payment->payment_no)
            ->assertSee('Cashbook');

        $this->assertDatabaseHas('cashbooks', [
            'transaction_type' => 'cash_out',
            'payment_mode' => 'cash',
            'reference_type' => 'payment',
            'reference_id' => $payment->id,
        ]);
    }

    public function test_supplier_payment_cannot_exceed_purchase_balance(): void
    {
        $supplier = Supplier::create([
            'name' => 'Balance Supplier',
            'current_balance' => 250,
        ]);
        $purchase = Purchase::create([
            'purchase_no' => 'PUR-TEST-00003',
            'supplier_id' => $supplier->id,
            'purchase_date' => '2026-05-13',
            'bill_type' => 'gst',
            'subtotal' => 250,
            'gst_amount' => 0,
            'total_amount' => 250,
            'paid_amount' => 0,
            'balance_amount' => 250,
            'payment_status' => 'pending',
            'payment_mode' => 'cash',
        ]);

        $response = $this
            ->actingAs(User::factory()->create())
            ->from(route('supplier-payments.create'))
            ->post(route('supplier-payments.store'), [
                'supplier_id' => $supplier->id,
                'payment_date' => '2026-05-13',
                'reference_type' => 'purchase',
                'reference_id' => $purchase->id,
                'amount' => 251,
                'payment_mode' => 'bank',
            ]);

        $response->assertRedirect(route('supplier-payments.create'));
        $response->assertSessionHasErrors('amount');
        $this->assertSame(0, Payment::count());
        $this->assertSame(250.0, (float) $purchase->fresh()->balance_amount);
    }

    public function test_cashbook_and_bankbook_reports_apply_date_filters(): void
    {
        Cashbook::create([
            'entry_date' => '2026-04-30',
            'transaction_type' => 'cash_in',
            'reference_type' => 'payment',
            'reference_id' => 1,
            'amount' => 900,
            'payment_mode' => 'cash',
            'remarks' => 'Old cash entry',
        ]);
        Cashbook::create([
            'entry_date' => '2026-05-01',
            'transaction_type' => 'cash_in',
            'reference_type' => 'payment',
            'reference_id' => 2,
            'amount' => 500,
            'payment_mode' => 'cash',
            'remarks' => 'Current cash in',
        ]);
        Cashbook::create([
            'entry_date' => '2026-05-02',
            'transaction_type' => 'cash_out',
            'reference_type' => 'payment',
            'reference_id' => 3,
            'amount' => 200,
            'payment_mode' => 'cash',
            'remarks' => 'Current cash out',
        ]);
        Cashbook::create([
            'entry_date' => '2026-05-02',
            'transaction_type' => 'bank_in',
            'reference_type' => 'payment',
            'reference_id' => 4,
            'amount' => 800,
            'payment_mode' => 'upi',
            'remarks' => 'Current bank in',
        ]);

        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('cashbook.index', ['from_date' => '2026-05-01', 'to_date' => '2026-05-31']))
            ->assertOk()
            ->assertSee('Rs. 500.00')
            ->assertSee('Rs. 200.00')
            ->assertSee('Rs. 300.00')
            ->assertSee('Current cash in')
            ->assertDontSee('Old cash entry');

        $this->actingAs($user)
            ->get(route('bankbook.index', ['from_date' => '2026-05-01', 'to_date' => '2026-05-31']))
            ->assertOk()
            ->assertSee('Rs. 800.00')
            ->assertSee('Current bank in')
            ->assertDontSee('Old cash entry');
    }

    public function test_purchase_direct_payment_posts_payment_ledger_and_cashbook(): void
    {
        $supplier = Supplier::create([
            'name' => 'Direct Supplier',
            'current_balance' => 0,
        ]);
        $category = ProductCategory::create([
            'name' => 'Sheets',
            'status' => 'active',
        ]);
        $product = Product::create([
            'product_category_id' => $category->id,
            'name' => 'MS Sheet',
            'code' => 'MS-001',
            'unit' => 'Kg',
            'purchase_price' => 100,
            'selling_price' => 120,
            'current_stock' => 0,
            'status' => 'active',
        ]);

        $response = $this->actingAs(User::factory()->create())->post(route('purchases.store'), [
            'supplier_id' => $supplier->id,
            'purchase_date' => '2026-05-13',
            'bill_type' => 'non_gst',
            'supplier_invoice_no' => 'SUP-001',
            'paid_amount' => 100,
            'payment_mode' => 'upi',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 5,
                    'unit' => 'Kg',
                    'rate' => 100,
                    'gst_percentage' => 0,
                ],
            ],
        ]);

        $response->assertRedirect(route('purchases.index'));

        $purchase = Purchase::firstOrFail();
        $this->assertSame(500.0, (float) $purchase->total_amount);
        $this->assertSame(100.0, (float) $purchase->paid_amount);
        $this->assertSame(400.0, (float) $purchase->balance_amount);
        $this->assertSame(400.0, (float) $supplier->fresh()->current_balance);
        $this->assertSame(5.0, (float) $product->fresh()->current_stock);

        $this->assertDatabaseHas('payments', [
            'party_type' => 'supplier',
            'transaction_type' => 'payment',
            'reference_type' => 'purchase_direct_payment',
            'reference_id' => $purchase->id,
            'payment_mode' => 'upi',
        ]);
        $this->assertDatabaseHas('cashbooks', [
            'transaction_type' => 'bank_out',
            'reference_type' => 'purchase_direct_payment',
            'reference_id' => $purchase->id,
            'payment_mode' => 'upi',
        ]);
    }
}
