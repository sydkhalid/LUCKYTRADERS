<?php

namespace Tests\Feature;

use App\Models\TestingBug;
use App\Models\TestingChecklist;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TestingChecklistSystemTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_admin_can_save_pass_fail_checklist_notes_and_track_bugs(): void
    {
        $admin = $this->userWithRole('Admin');

        $this->actingAs($admin)
            ->get(route('settings.testing-checklist'))
            ->assertOk()
            ->assertSee('Production Testing Checklist')
            ->assertSee('href="'.route('settings.testing-checklist').'"', false)
            ->assertSee('Product CRUD testing')
            ->assertSee('Backup system testing')
            ->assertSee('Bug Tracking Section');

        $this->assertSame(20, TestingChecklist::count());

        $productCrud = TestingChecklist::where('key', 'product_crud')->firstOrFail();
        $gstInvoice = TestingChecklist::where('key', 'gst_invoice')->firstOrFail();

        $this->actingAs($admin)
            ->patch(route('settings.testing-checklist.update'), [
                'items' => [
                    $productCrud->id => [
                        'status' => 'pass',
                        'notes' => 'Product master verified.',
                    ],
                    $gstInvoice->id => [
                        'status' => 'fail',
                        'notes' => 'GST print needs manual review.',
                    ],
                ],
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('testing_checklists', [
            'id' => $productCrud->id,
            'status' => 'pass',
            'notes' => 'Product master verified.',
            'tested_by' => $admin->id,
        ]);
        $this->assertDatabaseHas('testing_checklists', [
            'id' => $gstInvoice->id,
            'status' => 'fail',
            'notes' => 'GST print needs manual review.',
            'tested_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->post(route('settings.testing-bugs.store'), [
                'testing_checklist_id' => $gstInvoice->id,
                'module' => $gstInvoice->module,
                'title' => 'GST invoice missing signature',
                'severity' => 'high',
                'description' => 'Signature area should be visible on print.',
            ])
            ->assertRedirect();

        $bug = TestingBug::firstOrFail();
        $this->assertSame($admin->id, $bug->reported_by);
        $this->assertSame('open', $bug->status);

        $this->actingAs($admin)
            ->patch(route('settings.testing-bugs.update', $bug), [
                'severity' => 'medium',
                'status' => 'resolved',
                'resolution_notes' => 'Signature block verified after PDF update.',
            ])
            ->assertRedirect();

        $bug->refresh();
        $this->assertSame('resolved', $bug->status);
        $this->assertSame('medium', $bug->severity);
        $this->assertSame($admin->id, $bug->resolved_by);
        $this->assertNotNull($bug->resolved_at);
    }

    public function test_testing_checklist_is_restricted_to_settings_permission(): void
    {
        $viewer = $this->userWithRole('Viewer');

        $this->actingAs($viewer)
            ->get(route('settings.testing-checklist'))
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
