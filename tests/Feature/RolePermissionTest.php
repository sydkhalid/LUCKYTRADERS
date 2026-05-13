<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RolePermissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_default_roles_and_permissions_are_seeded(): void
    {
        $this->assertDatabaseHas('roles', ['name' => 'Super Admin']);
        $this->assertDatabaseHas('roles', ['name' => 'Billing Staff']);
        $this->assertDatabaseHas('permissions', ['name' => 'manage_users']);
        $this->assertDatabaseHas('permissions', ['name' => 'manage_receipts']);
        $this->assertDatabaseHas('permissions', ['name' => 'manage_ledgers']);
        $this->assertDatabaseHas('permissions', ['name' => 'manage_quotations']);
        $this->assertDatabaseHas('permissions', ['name' => 'manage_returns']);

        $this->assertTrue(Role::findByName('Super Admin')->hasPermissionTo('delete_records'));
        $this->assertTrue(Role::findByName('Admin')->hasPermissionTo('manage_users'));
        $this->assertTrue(Role::findByName('Admin')->hasPermissionTo('manage_returns'));
        $this->assertTrue(Role::findByName('Accountant')->hasPermissionTo('manage_ledgers'));
        $this->assertTrue(Role::findByName('Accountant')->hasPermissionTo('manage_payments'));
        $this->assertTrue(Role::findByName('Accountant')->hasPermissionTo('manage_expenses'));
        $this->assertFalse(Role::findByName('Accountant')->hasPermissionTo('manage_sales'));
        $this->assertTrue(Role::findByName('Billing Staff')->hasPermissionTo('manage_quotations'));
        $this->assertTrue(Role::findByName('Billing Staff')->hasPermissionTo('manage_receipts'));
        $this->assertTrue(Role::findByName('Billing Staff')->hasPermissionTo('print_invoice'));
        $this->assertFalse(Role::findByName('Billing Staff')->hasPermissionTo('manage_payments'));
        $this->assertTrue(Role::findByName('Stock Staff')->hasPermissionTo('manage_stock_adjustments'));
        $this->assertFalse(Role::findByName('Stock Staff')->hasPermissionTo('manage_returns'));
        $this->assertFalse(Role::findByName('Viewer')->hasPermissionTo('manage_products'));
    }

    public function test_admin_can_create_staff_user_with_role(): void
    {
        $admin = $this->userWithRole('Admin');

        $response = $this
            ->actingAs($admin)
            ->post(route('users.store'), [
                'name' => 'Billing User',
                'email' => 'billing@example.com',
                'password' => 'password',
                'password_confirmation' => 'password',
                'role' => 'Billing Staff',
            ]);

        $response->assertRedirect(route('users.index'));

        $user = User::where('email', 'billing@example.com')->firstOrFail();

        $this->assertTrue($user->hasRole('Billing Staff'));
        $this->assertSame('Billing Staff', $user->role);
        $this->assertFalse((bool) $user->is_admin);
    }

    public function test_billing_staff_can_access_quotations_sales_and_receipts_but_not_payments_or_expenses(): void
    {
        $billingUser = $this->userWithRole('Billing Staff');

        $this->actingAs($billingUser)
            ->get(route('quotations.index'))
            ->assertOk();

        $this->actingAs($billingUser)
            ->get(route('sales.index'))
            ->assertOk()
            ->assertSee('Sales / Billing')
            ->assertDontSee('Expenses');

        $this->actingAs($billingUser)
            ->get(route('receipts.index'))
            ->assertOk();

        $this->actingAs($billingUser)
            ->get(route('payments.index'))
            ->assertForbidden();

        $this->actingAs($billingUser)
            ->get(route('expenses.index'))
            ->assertForbidden();
    }

    public function test_accountant_can_access_accounts_and_gst_but_not_sales(): void
    {
        $accountant = $this->userWithRole('Accountant');

        $this->actingAs($accountant)
            ->get(route('payments.index'))
            ->assertOk();

        $this->actingAs($accountant)
            ->get(route('ledgers.index'))
            ->assertOk();

        $this->actingAs($accountant)
            ->get(route('expenses.index'))
            ->assertOk();

        $this->actingAs($accountant)
            ->get(route('gst-reports.index'))
            ->assertOk();

        $this->actingAs($accountant)
            ->get(route('sales.index'))
            ->assertForbidden();
    }

    public function test_stock_staff_can_manage_stock_modules_but_not_returns(): void
    {
        $stockStaff = $this->userWithRole('Stock Staff');

        $this->actingAs($stockStaff)
            ->get(route('products.index'))
            ->assertOk();

        $this->actingAs($stockStaff)
            ->get(route('purchases.index'))
            ->assertOk();

        $this->actingAs($stockStaff)
            ->get(route('stock-adjustments.index'))
            ->assertOk();

        $this->actingAs($stockStaff)
            ->get(route('sales-returns.index'))
            ->assertForbidden();
    }

    public function test_viewer_can_view_gst_reports_but_cannot_manage_products(): void
    {
        $viewer = $this->userWithRole('Viewer');

        $this->actingAs($viewer)
            ->get(route('gst-reports.index'))
            ->assertOk();

        $this->actingAs($viewer)
            ->get(route('products.index'))
            ->assertForbidden();
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
}
