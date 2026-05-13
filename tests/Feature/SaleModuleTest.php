<?php

namespace Tests\Feature;

use App\Models\Cashbook;
use App\Models\Customer;
use App\Models\Ledger;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Sale;
use App\Models\StockMovement;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SaleModuleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_gst_sale_creates_items_reduces_stock_posts_ledger_cashbook_and_profit(): void
    {
        $admin = $this->userWithRole('Admin');
        $customer = $this->customer();
        $product = $this->product([
            'code' => 'SALE-GST-001',
            'purchase_price' => 60,
            'selling_price' => 100,
            'gst_percentage' => 18,
            'opening_stock' => 15,
            'current_stock' => 15,
        ]);

        $response = $this->actingAs($admin)->post(route('sales.store'), [
            'customer_id' => $customer->id,
            'sale_date' => '2026-05-13',
            'bill_type' => 'gst',
            'paid_amount' => 118,
            'payment_mode' => 'cash',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 5,
                    'unit' => 'Wrong Unit',
                    'rate' => 100,
                    'gst_percentage' => 0,
                ],
            ],
        ]);

        $sale = Sale::with('items')->firstOrFail();
        $response->assertRedirect(route('sales.show', $sale));

        $this->assertStringStartsWith('GST-', $sale->sale_no);
        $this->assertSame('gst', $sale->bill_type);
        $this->assertSame(500.0, (float) $sale->subtotal);
        $this->assertSame(90.0, (float) $sale->gst_amount);
        $this->assertSame(590.0, (float) $sale->total_amount);
        $this->assertSame(118.0, (float) $sale->paid_amount);
        $this->assertSame(472.0, (float) $sale->balance_amount);
        $this->assertSame('partial', $sale->payment_status);

        $item = $sale->items->first();
        $this->assertSame('Kg', $item->unit);
        $this->assertSame(18.0, (float) $item->gst_percentage);
        $this->assertSame(90.0, (float) $item->gst_amount);
        $this->assertSame(300.0, (float) $item->purchase_cost);
        $this->assertSame(200.0, (float) $item->profit_amount);
        $this->assertSame(10.0, (float) $product->fresh()->current_stock);
        $this->assertSame(472.0, (float) $customer->fresh()->current_balance);

        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'movement_type' => 'sale_out',
            'reference_type' => 'sale',
            'reference_id' => $sale->id,
        ]);

        $saleLedger = Ledger::where('reference_type', 'sale')->firstOrFail();
        $this->assertSame(590.0, (float) $saleLedger->debit);

        $receiptLedger = Ledger::where('reference_type', 'sale_direct_payment')->firstOrFail();
        $this->assertSame(118.0, (float) $receiptLedger->credit);

        $cashbook = Cashbook::firstOrFail();
        $this->assertSame('cash_in', $cashbook->transaction_type);
        $this->assertSame(118.0, (float) $cashbook->amount);
    }

    public function test_normal_bill_keeps_gst_zero_and_is_excluded_from_gst_sales_report(): void
    {
        $admin = $this->userWithRole('Admin');
        $customer = $this->customer(['name' => 'Normal Bill Customer']);
        $product = $this->product([
            'code' => 'SALE-NORMAL-001',
            'gst_percentage' => 18,
            'current_stock' => 20,
            'opening_stock' => 20,
        ]);

        $this->actingAs($admin)->post(route('sales.store'), $this->salePayload($customer, $product, [
            'bill_type' => 'non_gst',
            'paid_amount' => 0,
            'payment_mode' => 'credit',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 3,
                    'unit' => 'Kg',
                    'rate' => 100,
                    'gst_percentage' => 18,
                ],
            ],
        ]))->assertRedirect();

        $normalSale = Sale::firstOrFail();

        $this->assertStringStartsWith('BILL-', $normalSale->sale_no);
        $this->assertSame('non_gst', $normalSale->bill_type);
        $this->assertSame(300.0, (float) $normalSale->subtotal);
        $this->assertSame(0.0, (float) $normalSale->gst_amount);
        $this->assertSame(300.0, (float) $normalSale->total_amount);
        $this->assertSame('pending', $normalSale->payment_status);
        $this->assertSame(0, Cashbook::count());

        $this->flushSession();

        $this->actingAs($admin)
            ->get(route('gst-reports.sales'))
            ->assertOk()
            ->assertDontSee($normalSale->sale_no);
    }

    public function test_sale_validation_blocks_zero_quantity_negative_amount_overpayment_and_insufficient_stock(): void
    {
        $admin = $this->userWithRole('Admin');
        $customer = $this->customer();
        $product = $this->product([
            'code' => 'SALE-VALID-001',
            'current_stock' => 1,
            'opening_stock' => 1,
        ]);

        $this->actingAs($admin)
            ->from(route('sales.create'))
            ->post(route('sales.store'), $this->salePayload($customer, $product, [
                'items' => [
                    [
                        'product_id' => $product->id,
                        'quantity' => 0,
                        'unit' => 'Kg',
                        'rate' => 100,
                        'gst_percentage' => 18,
                    ],
                ],
            ]))
            ->assertRedirect(route('sales.create'))
            ->assertSessionHasErrors('items.0.quantity');

        $this->actingAs($admin)
            ->from(route('sales.create'))
            ->post(route('sales.store'), $this->salePayload($customer, $product, [
                'paid_amount' => -1,
            ]))
            ->assertRedirect(route('sales.create'))
            ->assertSessionHasErrors('paid_amount');

        $this->actingAs($admin)
            ->from(route('sales.create'))
            ->post(route('sales.store'), $this->salePayload($customer, $product, [
                'paid_amount' => 9999,
            ]))
            ->assertRedirect(route('sales.create'))
            ->assertSessionHasErrors('paid_amount');

        $this->actingAs($admin)
            ->from(route('sales.create'))
            ->post(route('sales.store'), $this->salePayload($customer, $product, [
                'items' => [
                    [
                        'product_id' => $product->id,
                        'quantity' => 2,
                        'unit' => 'Kg',
                        'rate' => 100,
                        'gst_percentage' => 18,
                    ],
                ],
            ]))
            ->assertRedirect(route('sales.create'))
            ->assertSessionHasErrors('items');
    }

    public function test_sale_pages_render_and_normal_bill_print_uses_normal_format(): void
    {
        $admin = $this->userWithRole('Admin');
        $customer = $this->customer();
        $product = $this->product([
            'code' => 'SALE-RENDER-001',
            'current_stock' => 10,
            'opening_stock' => 10,
        ]);

        $this->actingAs($admin)
            ->get(route('sales.index'))
            ->assertOk()
            ->assertSee('Sales / Billing');

        $this->actingAs($admin)
            ->get(route('sales.create'))
            ->assertOk()
            ->assertSee('Save Sale');

        $this->actingAs($admin)
            ->post(route('sales.store'), $this->salePayload($customer, $product, [
                'bill_type' => 'non_gst',
                'paid_amount' => 0,
                'payment_mode' => 'credit',
            ]))
            ->assertRedirect();

        $sale = Sale::firstOrFail();

        $this->actingAs($admin)
            ->get(route('sales.show', $sale))
            ->assertOk()
            ->assertSee('Print Invoice')
            ->assertSee('Cancel Sale');

        $this->actingAs($admin)
            ->get(route('sales.print', $sale))
            ->assertOk()
            ->assertSee('NORMAL BILL')
            ->assertDontSee('HSN')
            ->assertDontSee('GSTIN')
            ->assertDontSee('GST:');
    }

    public function test_sale_cancel_is_admin_only_and_reverses_stock_and_accounting(): void
    {
        $admin = $this->userWithRole('Admin');
        $billingStaff = $this->userWithRole('Billing Staff');
        $customer = $this->customer();
        $product = $this->product([
            'code' => 'SALE-CANCEL-001',
            'current_stock' => 8,
            'opening_stock' => 8,
        ]);

        $this->actingAs($admin)
            ->post(route('sales.store'), $this->salePayload($customer, $product, [
                'bill_type' => 'gst',
                'paid_amount' => 0,
                'payment_mode' => 'credit',
                'items' => [
                    [
                        'product_id' => $product->id,
                        'quantity' => 2,
                        'unit' => 'Kg',
                        'rate' => 100,
                        'gst_percentage' => 18,
                    ],
                ],
            ]))
            ->assertRedirect();

        $sale = Sale::firstOrFail();
        $this->assertSame(6.0, (float) $product->fresh()->current_stock);
        $this->assertSame(236.0, (float) $customer->fresh()->current_balance);

        $this->actingAs($billingStaff)
            ->delete(route('sales.destroy', $sale))
            ->assertForbidden();

        $this->actingAs($admin)
            ->delete(route('sales.destroy', $sale))
            ->assertRedirect(route('sales.index'));

        $this->assertSoftDeleted('sales', ['id' => $sale->id]);
        $this->assertSame(8.0, (float) $product->fresh()->current_stock);
        $this->assertSame(0.0, (float) $customer->fresh()->current_balance);
        $this->assertSame(0, StockMovement::where('reference_type', 'sale')->where('reference_id', $sale->id)->count());
        $this->assertSame(0, Ledger::where('reference_type', 'sale')->where('reference_id', $sale->id)->count());
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create([
            'is_admin' => in_array($role, ['Super Admin', 'Admin'], true),
            'role' => $role,
        ]);

        $user->syncRoles([$role]);
        $user->forceFill([
            'is_admin' => in_array($role, ['Super Admin', 'Admin'], true),
            'role' => $role,
        ])->save();

        return $user->refresh();
    }

    private function customer(array $overrides = []): Customer
    {
        return Customer::create(array_merge([
            'name' => 'Sale Customer',
            'phone' => '9000000900',
            'gst_number' => '33SALECUST001',
            'status' => 'active',
        ], $overrides));
    }

    private function product(array $overrides = []): Product
    {
        $category = ProductCategory::firstOrCreate([
            'name' => 'Steel',
        ], [
            'status' => 'active',
        ]);

        return Product::create(array_merge([
            'product_category_id' => $category->id,
            'name' => 'MS Rod',
            'code' => 'SALE-001',
            'unit' => 'Kg',
            'weight_per_unit' => 1,
            'hsn_code' => '7214',
            'gst_percentage' => 18,
            'purchase_price' => 60,
            'selling_price' => 100,
            'opening_stock' => 10,
            'current_stock' => 10,
            'status' => 'active',
        ], $overrides));
    }

    private function salePayload(Customer $customer, Product $product, array $overrides = []): array
    {
        return array_merge([
            'customer_id' => $customer->id,
            'sale_date' => '2026-05-13',
            'bill_type' => 'gst',
            'paid_amount' => 0,
            'payment_mode' => 'credit',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 2,
                    'unit' => 'Kg',
                    'rate' => 100,
                    'gst_percentage' => 18,
                ],
            ],
        ], $overrides);
    }
}
