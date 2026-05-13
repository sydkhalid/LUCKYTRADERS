<?php

namespace Tests\Feature;

use App\Models\ProductCategory;
use App\Models\User;
use App\Services\ActivityLogger;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class ActivityLogTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_login_and_logout_are_logged_with_user_context(): void
    {
        $user = $this->userWithRole('Admin');

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('dashboard', absolute: false));

        $this->assertDatabaseHas('activity_log', [
            'event' => 'login',
            'log_name' => 'auth',
            'causer_type' => User::class,
            'causer_id' => $user->id,
        ]);

        $this->actingAs($user)
            ->post('/logout')
            ->assertRedirect();

        $this->assertDatabaseHas('activity_log', [
            'event' => 'logout',
            'log_name' => 'auth',
            'causer_type' => User::class,
            'causer_id' => $user->id,
        ]);
    }

    public function test_model_changes_store_old_new_values_ip_and_role(): void
    {
        $admin = $this->userWithRole('Admin');

        $this->actingAs($admin)
            ->post(route('product-categories.store'), [
                'name' => 'Audit Steel',
                'description' => 'Initial category',
                'status' => 'active',
            ])
            ->assertRedirect(route('product-categories.index'));

        $category = ProductCategory::where('name', 'Audit Steel')->firstOrFail();

        $this->actingAs($admin)
            ->put(route('product-categories.update', $category), [
                'name' => 'Audit Steel Updated',
                'description' => 'Updated category',
                'status' => 'inactive',
            ])
            ->assertRedirect(route('product-categories.index'));

        $activity = Activity::where('subject_type', ProductCategory::class)
            ->where('subject_id', $category->id)
            ->where('event', 'updated')
            ->latest()
            ->firstOrFail();

        $this->assertSame('product_categories', $activity->log_name);
        $this->assertSame('Admin', $activity->properties->get('role'));
        $this->assertSame('127.0.0.1', $activity->properties->get('ip_address'));
        $this->assertSame('Audit Steel', data_get($activity->properties->get('old'), 'name'));
        $this->assertSame('Audit Steel Updated', data_get($activity->properties->get('attributes'), 'name'));
    }

    public function test_activity_logs_are_filterable_and_only_super_admin_can_delete(): void
    {
        $admin = $this->userWithRole('Admin');
        $superAdmin = $this->userWithRole('Super Admin');

        $activity = app(ActivityLogger::class)->log(
            'export_report',
            'gst_reports',
            'GST auditor export generated',
            null,
            [],
            ['format' => 'csv'],
            $admin
        );

        $this->actingAs($admin)
            ->get(route('activity-logs.index', [
                'user_id' => $admin->id,
                'module' => 'gst_reports',
                'action' => 'export_report',
                'from_date' => now()->toDateString(),
                'to_date' => now()->toDateString(),
            ]))
            ->assertOk()
            ->assertSee('GST auditor export generated');

        $this->actingAs($admin)
            ->delete(route('activity-logs.destroy', $activity))
            ->assertForbidden();

        $this->actingAs($superAdmin)
            ->delete(route('activity-logs.destroy', $activity))
            ->assertRedirect();

        $this->assertDatabaseMissing('activity_log', [
            'id' => $activity->id,
        ]);
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
