<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductCategory;
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
                'current_stock' => 10,
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
            ->get(route('products.index'))
            ->assertOk()
            ->assertSee('MS Rod Updated');
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
}
