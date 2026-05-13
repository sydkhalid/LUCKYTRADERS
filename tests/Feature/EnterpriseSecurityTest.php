<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\ExpenseCategory;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Supplier;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class EnterpriseSecurityTest extends TestCase
{
    use RefreshDatabase;

    private const STRONG_PASSWORD = 'Str0ng!Pass123';

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_core_security_middleware_and_session_defaults_are_enabled(): void
    {
        $this->get(route('login'))
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('Content-Security-Policy', "base-uri 'self'; object-src 'none'; frame-ancestors 'self'");

        $webLoginPost = collect(Route::getRoutes())->first(
            fn ($route) => $route->uri() === 'login' && in_array('POST', $route->methods(), true)
        );

        $this->assertContains('throttle:erp', Route::getRoutes()->getByName('dashboard')->gatherMiddleware());
        $this->assertContains('throttle:auth', $webLoginPost->gatherMiddleware());
        $this->assertSame(30, config('session.lifetime'));
        $this->assertTrue(config('session.encrypt'));
    }

    public function test_strong_passwords_are_required_for_web_and_api_registration(): void
    {
        $this->post('/register', [
            'name' => 'Weak Admin',
            'email' => 'weak@example.test',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertSessionHasErrors('password');

        $this->postJson('/api/register', [
            'name' => 'API Admin',
            'email' => 'api-admin@example.test',
            'password' => self::STRONG_PASSWORD,
            'password_confirmation' => self::STRONG_PASSWORD,
        ])->assertCreated()
            ->assertJsonStructure(['user', 'token', 'token_type']);

        $this->assertDatabaseHas('users', [
            'email' => 'api-admin@example.test',
            'role' => 'Super Admin',
            'is_admin' => true,
        ]);

        $this->postJson('/api/register', [
            'name' => 'Second API Admin',
            'email' => 'second-api-admin@example.test',
            'password' => self::STRONG_PASSWORD,
            'password_confirmation' => self::STRONG_PASSWORD,
        ])->assertUnprocessable()
            ->assertJsonValidationErrors('email');
    }

    public function test_delete_actions_require_delete_records_permission(): void
    {
        $accountant = $this->userWithRole('Accountant');
        $category = ExpenseCategory::create([
            'name' => 'Security Delete Test',
            'status' => 'active',
        ]);

        $this->actingAs($accountant)
            ->delete(route('expense-categories.destroy', $category))
            ->assertForbidden();

        $this->assertDatabaseHas('expense_categories', [
            'id' => $category->id,
            'deleted_at' => null,
        ]);
    }

    public function test_blade_escapes_user_supplied_content(): void
    {
        $admin = $this->userWithRole('Admin');

        ProductCategory::create([
            'name' => '<script>alert(1)</script>',
            'status' => 'active',
        ]);

        $this->actingAs($admin)
            ->get(route('product-categories.index'))
            ->assertOk()
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false);
    }

    public function test_database_blocks_duplicate_invoice_numbers(): void
    {
        $customer = Customer::create([
            'name' => 'Invoice Security Customer',
            'phone' => '9000001001',
            'status' => 'active',
        ]);

        $payload = [
            'sale_no' => 'GST-00001',
            'customer_id' => $customer->id,
            'sale_date' => '2026-05-13',
            'bill_type' => 'gst',
            'subtotal' => 100,
            'gst_amount' => 18,
            'total_amount' => 118,
            'paid_amount' => 0,
            'balance_amount' => 118,
            'payment_status' => 'pending',
            'payment_mode' => 'credit',
        ];

        Sale::create($payload);

        $this->expectException(QueryException::class);

        Sale::create($payload);
    }

    public function test_purchase_delete_cannot_reverse_stock_below_zero(): void
    {
        $admin = $this->userWithRole('Admin');
        [$supplier, $customer, $product] = $this->tradingFixtures();

        $this->actingAs($admin)
            ->post(route('purchases.store'), [
                'supplier_id' => $supplier->id,
                'purchase_date' => '2026-05-13',
                'bill_type' => 'non_gst',
                'paid_amount' => 0,
                'payment_mode' => 'credit',
                'items' => [
                    [
                        'product_id' => $product->id,
                        'quantity' => 5,
                        'unit' => 'Kg',
                        'rate' => 50,
                        'gst_percentage' => 0,
                    ],
                ],
            ]);

        $purchase = Purchase::firstOrFail();

        $this->actingAs($admin)
            ->post(route('sales.store'), [
                'customer_id' => $customer->id,
                'sale_date' => '2026-05-13',
                'bill_type' => 'non_gst',
                'paid_amount' => 0,
                'payment_mode' => 'credit',
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

        $this->actingAs($admin)
            ->from(route('purchases.index'))
            ->delete(route('purchases.destroy', $purchase))
            ->assertRedirect(route('purchases.index'))
            ->assertSessionHasErrors('items');

        $this->assertDatabaseHas('purchases', ['id' => $purchase->id]);
        $this->assertSame(0.0, (float) $product->refresh()->current_stock);
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

    private function tradingFixtures(): array
    {
        $supplier = Supplier::create([
            'name' => 'Security Supplier',
            'status' => 'active',
        ]);

        $customer = Customer::create([
            'name' => 'Security Customer',
            'phone' => '9000001002',
            'status' => 'active',
        ]);

        $category = ProductCategory::create([
            'name' => 'Security Steel',
            'status' => 'active',
        ]);

        $product = Product::create([
            'product_category_id' => $category->id,
            'name' => 'Security Rod',
            'code' => 'SEC-ROD',
            'unit' => 'Kg',
            'purchase_price' => 50,
            'selling_price' => 100,
            'gst_percentage' => 0,
            'current_stock' => 0,
            'status' => 'active',
        ]);

        return [$supplier, $customer, $product];
    }
}
