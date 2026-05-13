<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class ProductManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_product_can_be_created_updated_and_listed(): void
    {
        $admin = $this->userWithRole('Admin');
        $category = ProductCategory::create([
            'name' => 'Steel',
            'status' => 'active',
        ]);

        $this->actingAs($admin)
            ->post(route('products.store'), $this->productPayload($category, [
                'code' => 'MS-TEST',
                'current_stock' => 99,
            ]))
            ->assertRedirect(route('products.index'));

        $product = Product::where('code', 'MS-TEST')->firstOrFail();

        $this->assertSame('MS Rod', $product->name);
        $this->assertSame(10.0, (float) $product->current_stock);

        $this->actingAs($admin)
            ->put(route('products.update', $product), $this->productPayload($category, [
                'name' => 'MS Rod Updated',
                'code' => 'MS-TEST',
                'selling_price' => 130,
            ]))
            ->assertRedirect(route('products.index'));

        $this->assertSame('MS Rod Updated', $product->fresh()->name);
        $this->assertSame(130.0, (float) $product->fresh()->selling_price);

        $this->actingAs($admin)
            ->get(route('products.index', ['search' => 'Updated']))
            ->assertOk()
            ->assertSee('MS Rod Updated');

        $this->actingAs($admin)
            ->get(route('products.show', $product))
            ->assertOk()
            ->assertSee('MS-TEST')
            ->assertSee('Current Stock');
    }

    public function test_product_category_search_show_and_soft_delete_flow(): void
    {
        $admin = $this->userWithRole('Admin');

        $this->actingAs($admin)
            ->post(route('product-categories.store'), [
                'name' => 'Angles',
                'description' => 'MS angle stock',
                'status' => 'active',
            ])
            ->assertRedirect(route('product-categories.index'));

        $category = ProductCategory::where('name', 'Angles')->firstOrFail();

        $this->actingAs($admin)
            ->get(route('product-categories.index', ['search' => 'angle']))
            ->assertOk()
            ->assertSee('Angles');

        $this->actingAs($admin)
            ->get(route('product-categories.show', $category))
            ->assertOk()
            ->assertSee('Linked Products');

        $this->actingAs($admin)
            ->delete(route('product-categories.destroy', $category))
            ->assertRedirect(route('product-categories.index'));

        $this->assertSoftDeleted('product_categories', ['id' => $category->id]);
    }

    public function test_master_create_and_edit_pages_render(): void
    {
        $admin = $this->userWithRole('Admin');
        $category = ProductCategory::create([
            'name' => 'Sheets',
            'status' => 'active',
        ]);
        $product = Product::create($this->productPayload($category, [
            'code' => 'SHEET-001',
        ]));
        $customer = Customer::create($this->partyPayload([
            'name' => 'Render Customer',
            'phone' => '9000000011',
        ]));
        $supplier = Supplier::create($this->partyPayload([
            'name' => 'Render Supplier',
            'phone' => '9000000012',
            'balance_type' => 'credit',
        ]));

        $this->actingAs($admin)
            ->get(route('product-categories.create'))
            ->assertOk()
            ->assertSee('Save Category');

        $this->actingAs($admin)
            ->get(route('product-categories.edit', $category))
            ->assertOk()
            ->assertSee('Sheets');

        $this->actingAs($admin)
            ->get(route('products.create'))
            ->assertOk()
            ->assertSee('Current stock will start from opening stock');

        $this->actingAs($admin)
            ->get(route('products.edit', $product))
            ->assertOk()
            ->assertSee('Current Stock');

        $this->actingAs($admin)
            ->get(route('customers.create'))
            ->assertOk()
            ->assertSee('Save Customer');

        $this->actingAs($admin)
            ->get(route('customers.edit', $customer))
            ->assertOk()
            ->assertSee('Render Customer');

        $this->actingAs($admin)
            ->get(route('suppliers.create'))
            ->assertOk()
            ->assertSee('Save Supplier');

        $this->actingAs($admin)
            ->get(route('suppliers.edit', $supplier))
            ->assertOk()
            ->assertSee('Render Supplier');
    }

    public function test_customer_crud_search_show_and_duplicate_phone_validation(): void
    {
        $admin = $this->userWithRole('Admin');

        $this->actingAs($admin)
            ->post(route('customers.store'), $this->partyPayload([
                'name' => 'Arun Steel',
                'phone' => '9000000001',
            ]))
            ->assertRedirect(route('customers.index'));

        $customer = Customer::where('phone', '9000000001')->firstOrFail();

        $this->actingAs($admin)
            ->from(route('customers.create'))
            ->post(route('customers.store'), $this->partyPayload([
                'name' => 'Duplicate Customer',
                'phone' => '9000000001',
            ]))
            ->assertRedirect(route('customers.create'))
            ->assertSessionHasErrors('phone');

        $this->actingAs($admin)
            ->get(route('customers.index', ['search' => 'Arun']))
            ->assertOk()
            ->assertSee('Arun Steel');

        $this->actingAs($admin)
            ->get(route('customers.show', $customer))
            ->assertOk()
            ->assertSee('Customer Details')
            ->assertSee('9000000001');
    }

    public function test_supplier_crud_search_show_and_duplicate_phone_validation(): void
    {
        $admin = $this->userWithRole('Admin');

        $this->actingAs($admin)
            ->post(route('suppliers.store'), $this->partyPayload([
                'name' => 'Lucky Supplier',
                'phone' => '9000000002',
            ]))
            ->assertRedirect(route('suppliers.index'));

        $supplier = Supplier::where('phone', '9000000002')->firstOrFail();

        $this->actingAs($admin)
            ->from(route('suppliers.create'))
            ->post(route('suppliers.store'), $this->partyPayload([
                'name' => 'Duplicate Supplier',
                'phone' => '9000000002',
            ]))
            ->assertRedirect(route('suppliers.create'))
            ->assertSessionHasErrors('phone');

        $this->actingAs($admin)
            ->get(route('suppliers.index', ['search' => 'Lucky']))
            ->assertOk()
            ->assertSee('Lucky Supplier');

        $this->actingAs($admin)
            ->get(route('suppliers.show', $supplier))
            ->assertOk()
            ->assertSee('Supplier Details')
            ->assertSee('9000000002');
    }

    public function test_product_validation_blocks_negative_stock_and_prices(): void
    {
        $admin = $this->userWithRole('Admin');
        $category = ProductCategory::create([
            'name' => 'Steel',
            'status' => 'active',
        ]);

        $this->actingAs($admin)
            ->from(route('products.create'))
            ->post(route('products.store'), $this->productPayload($category, [
                'purchase_price' => -1,
                'current_stock' => -5,
            ]))
            ->assertRedirect(route('products.create'))
            ->assertSessionHasErrors(['purchase_price', 'current_stock']);

        $this->assertSame(0, Product::count());
    }

    public function test_delete_requires_permission_soft_deletes_and_logs_activity(): void
    {
        $admin = $this->userWithRole('Admin');
        $stockStaff = $this->userWithRole('Stock Staff');
        $category = ProductCategory::create([
            'name' => 'Steel',
            'status' => 'active',
        ]);
        $product = Product::create($this->productPayload($category, [
            'code' => 'DEL-001',
        ]));

        $this->actingAs($stockStaff)
            ->delete(route('products.destroy', $product))
            ->assertForbidden();

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'deleted_at' => null,
        ]);

        $this->actingAs($admin)
            ->put(route('products.update', $product), $this->productPayload($category, [
                'name' => 'Audit Updated Product',
                'code' => 'DEL-001',
            ]))
            ->assertRedirect(route('products.index'));

        $this->actingAs($admin)
            ->delete(route('products.destroy', $product))
            ->assertRedirect(route('products.index'));

        $this->assertSoftDeleted('products', ['id' => $product->id]);
        $this->assertDatabaseHas('activity_log', [
            'event' => 'deleted',
            'subject_type' => Product::class,
            'subject_id' => $product->id,
            'causer_type' => User::class,
            'causer_id' => $admin->id,
        ]);

        $this->assertTrue(Activity::where('subject_type', Product::class)->where('event', 'updated')->exists());
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

    private function productPayload(ProductCategory $category, array $overrides = []): array
    {
        return array_merge([
            'product_category_id' => $category->id,
            'name' => 'MS Rod',
            'code' => 'MS-001',
            'size' => '10mm',
            'thickness' => null,
            'unit' => 'Kg',
            'weight_per_unit' => 1,
            'hsn_code' => '7214',
            'gst_percentage' => 18,
            'purchase_price' => 80,
            'selling_price' => 120,
            'opening_stock' => 10,
            'current_stock' => 10,
            'status' => 'active',
        ], $overrides);
    }

    private function partyPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Party Name',
            'phone' => null,
            'email' => null,
            'gst_number' => null,
            'address' => 'Krishnagiri',
            'opening_balance' => 0,
            'balance_type' => 'debit',
            'status' => 'active',
        ], $overrides);
    }
}
