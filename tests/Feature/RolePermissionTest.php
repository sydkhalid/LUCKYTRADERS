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

        $this->assertTrue(Role::findByName('Super Admin')->hasPermissionTo('delete_records'));
        $this->assertTrue(Role::findByName('Admin')->hasPermissionTo('manage_users'));
        $this->assertTrue(Role::findByName('Accountant')->hasPermissionTo('manage_expenses'));
        $this->assertTrue(Role::findByName('Billing Staff')->hasPermissionTo('print_invoice'));
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

    public function test_billing_staff_can_access_sales_but_not_expenses(): void
    {
        $billingUser = $this->userWithRole('Billing Staff');

        $this->actingAs($billingUser)
            ->get(route('sales.index'))
            ->assertOk()
            ->assertSee('Sales / Billing')
            ->assertDontSee('Expenses');

        $this->actingAs($billingUser)
            ->get(route('expenses.index'))
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
