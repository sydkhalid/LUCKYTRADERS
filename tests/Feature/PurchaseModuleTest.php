<?php

namespace Tests\Feature;

use App\Models\Cashbook;
use App\Models\Ledger;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Purchase;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseModuleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_gst_purchase_creates_items_stock_movement_supplier_ledger_and_payment_status(): void
    {
        $admin = $this->userWithRole('Admin');
        $supplier = Supplier::create([
            'name' => 'Kavi Steel Supplier',
            'current_balance' => 0,
            'status' => 'active',
        ]);
        $product = $this->product([
            'name' => 'MS Plate',
            'code' => 'PUR-GST-001',
            'gst_percentage' => 18,
            'current_stock' => 2,
            'opening_stock' => 2,
        ]);

        $response = $this->actingAs($admin)->post(route('purchases.store'), [
            'supplier_id' => $supplier->id,
            'purchase_date' => '2026-05-13',
            'bill_type' => 'gst',
            'supplier_invoice_no' => 'SUP-GST-77',
            'paid_amount' => 180,
            'payment_mode' => 'cash',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 10,
                    'unit' => 'Kg',
                    'rate' => 100,
                    'gst_percentage' => 0,
                ],
            ],
        ]);

        $response->assertRedirect(route('purchases.index'));

        $purchase = Purchase::with('items')->firstOrFail();
        $this->assertSame('gst', $purchase->bill_type);
        $this->assertSame(1000.0, (float) $purchase->subtotal);
        $this->assertSame(180.0, (float) $purchase->gst_amount);
        $this->assertSame(1180.0, (float) $purchase->total_amount);
        $this->assertSame(180.0, (float) $purchase->paid_amount);
        $this->assertSame(1000.0, (float) $purchase->balance_amount);
        $this->assertSame('partial', $purchase->payment_status);

        $item = $purchase->items->first();
        $this->assertSame(18.0, (float) $item->gst_percentage);
        $this->assertSame(180.0, (float) $item->gst_amount);
        $this->assertSame(12.0, (float) $product->fresh()->current_stock);

        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'movement_type' => 'purchase_in',
            'reference_type' => 'purchase',
            'reference_id' => $purchase->id,
        ]);
        $this->assertSame(1000.0, (float) $supplier->fresh()->current_balance);

        $purchaseLedger = Ledger::where('reference_type', 'purchase')->firstOrFail();
        $this->assertSame(1180.0, (float) $purchaseLedger->credit);

        $paymentLedger = Ledger::where('reference_type', 'purchase_direct_payment')->firstOrFail();
        $this->assertSame(180.0, (float) $paymentLedger->debit);

        $cashbook = Cashbook::firstOrFail();
        $this->assertSame('cash_out', $cashbook->transaction_type);
        $this->assertSame(180.0, (float) $cashbook->amount);
    }

    public function test_non_gst_purchase_keeps_gst_zero_and_marks_pending_when_unpaid(): void
    {
        $admin = $this->userWithRole('Admin');
        $supplier = Supplier::create([
            'name' => 'Non GST Supplier',
            'current_balance' => 0,
            'status' => 'active',
        ]);
        $product = $this->product([
            'code' => 'PUR-NONGST-001',
            'gst_percentage' => 18,
        ]);

        $this->actingAs($admin)->post(route('purchases.store'), [
            'supplier_id' => $supplier->id,
            'purchase_date' => '2026-05-13',
            'bill_type' => 'non_gst',
            'supplier_invoice_no' => 'SUP-NORMAL-1',
            'paid_amount' => 0,
            'payment_mode' => 'credit',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 3,
                    'unit' => 'Kg',
                    'rate' => 200,
                    'gst_percentage' => 18,
                ],
            ],
        ])->assertRedirect(route('purchases.index'));

        $purchase = Purchase::firstOrFail();

        $this->assertSame('non_gst', $purchase->bill_type);
        $this->assertSame(600.0, (float) $purchase->subtotal);
        $this->assertSame(0.0, (float) $purchase->gst_amount);
        $this->assertSame(600.0, (float) $purchase->total_amount);
        $this->assertSame(600.0, (float) $purchase->balance_amount);
        $this->assertSame('pending', $purchase->payment_status);
        $this->assertSame(0, Cashbook::count());
    }

    public function test_purchase_validation_blocks_zero_quantity_negative_amount_and_overpayment(): void
    {
        $admin = $this->userWithRole('Admin');
        $supplier = Supplier::create([
            'name' => 'Validation Supplier',
            'status' => 'active',
        ]);
        $product = $this->product(['code' => 'PUR-VALID-001']);

        $this->actingAs($admin)
            ->from(route('purchases.create'))
            ->post(route('purchases.store'), $this->purchasePayload($supplier, $product, [
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
            ->assertRedirect(route('purchases.create'))
            ->assertSessionHasErrors('items.0.quantity');

        $this->actingAs($admin)
            ->from(route('purchases.create'))
            ->post(route('purchases.store'), $this->purchasePayload($supplier, $product, [
                'paid_amount' => -1,
            ]))
            ->assertRedirect(route('purchases.create'))
            ->assertSessionHasErrors('paid_amount');

        $this->actingAs($admin)
            ->from(route('purchases.create'))
            ->post(route('purchases.store'), $this->purchasePayload($supplier, $product, [
                'paid_amount' => 9999,
            ]))
            ->assertRedirect(route('purchases.create'))
            ->assertSessionHasErrors('paid_amount');
    }

    public function test_purchase_pages_render_for_list_create_view_and_print(): void
    {
        $admin = $this->userWithRole('Admin');
        $supplier = Supplier::create([
            'name' => 'Render Purchase Supplier',
            'current_balance' => 0,
            'status' => 'active',
        ]);
        $product = $this->product(['code' => 'PUR-RENDER-001']);

        $this->actingAs($admin)
            ->get(route('purchases.index'))
            ->assertOk()
            ->assertSee('Purchases');

        $this->actingAs($admin)
            ->get(route('purchases.create'))
            ->assertOk()
            ->assertSee('Save Purchase');

        $this->actingAs($admin)
            ->post(route('purchases.store'), $this->purchasePayload($supplier, $product))
            ->assertRedirect(route('purchases.index'));

        $purchase = Purchase::firstOrFail();

        $this->actingAs($admin)
            ->get(route('purchases.show', $purchase))
            ->assertOk()
            ->assertSee($purchase->purchase_no)
            ->assertSee('Print');

        $this->actingAs($admin)
            ->get(route('purchases.print', $purchase))
            ->assertOk()
            ->assertSee($purchase->purchase_no)
            ->assertSee('Authorized Signature');
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create([
            'is_admin' => in_array($role, ['Super Admin', 'Admin'], true),
            'role' => $role,
        ]);

        $user->syncRoles([$role]);

        return $user->refresh();
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
            'code' => 'PUR-001',
            'unit' => 'Kg',
            'weight_per_unit' => 1,
            'hsn_code' => '7214',
            'gst_percentage' => 18,
            'purchase_price' => 100,
            'selling_price' => 120,
            'opening_stock' => 0,
            'current_stock' => 0,
            'status' => 'active',
        ], $overrides));
    }

    private function purchasePayload(Supplier $supplier, Product $product, array $overrides = []): array
    {
        return array_merge([
            'supplier_id' => $supplier->id,
            'purchase_date' => '2026-05-13',
            'bill_type' => 'gst',
            'supplier_invoice_no' => 'SUP-DEFAULT',
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
