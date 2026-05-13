<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\BackupManager;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BackupManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_super_admin_can_view_backup_dashboard_settings_and_delete_backup(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('lucky-traders-erp/manual-test.zip', 'backup-content');

        $superAdmin = $this->userWithRole('Super Admin');

        $this->actingAs($superAdmin)
            ->get(route('settings.backups.index'))
            ->assertOk()
            ->assertSee('Backup System')
            ->assertSee('manual-test.zip')
            ->assertSee('Backup');

        $this->actingAs($superAdmin)
            ->get(route('settings.backups.settings'))
            ->assertOk()
            ->assertSee('Backup Settings')
            ->assertSee('Daily database backup')
            ->assertSee('php artisan schedule:run');

        $backup = app(BackupManager::class)->files()[0];

        $this->actingAs($superAdmin)
            ->delete(route('settings.backups.destroy', $backup['encoded']))
            ->assertRedirect();

        Storage::disk('local')->assertMissing('lucky-traders-erp/manual-test.zip');
    }

    public function test_admin_cannot_access_backup_system(): void
    {
        $admin = $this->userWithRole('Admin');

        $this->actingAs($admin)
            ->get(route('settings.backups.index'))
            ->assertForbidden();
    }

    public function test_manual_database_backup_records_status(): void
    {
        Storage::fake('local');
        $superAdmin = $this->userWithRole('Super Admin');

        Artisan::shouldReceive('call')
            ->once()
            ->with('backup:run', [
                '--only-db' => true,
                '--disable-notifications' => true,
            ])
            ->andReturn(0);
        Artisan::shouldReceive('output')->andReturn('database backup complete');

        $this->actingAs($superAdmin)
            ->post(route('settings.backups.store'), [
                'backup_type' => 'database',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        Storage::disk('local')->assertExists('backups/status.json');
        $status = json_decode(Storage::disk('local')->get('backups/status.json'), true);

        $this->assertSame('success', $status['status']);
        $this->assertSame('database', $status['type']);
    }

    public function test_manual_full_backup_and_cleanup_commands_record_status(): void
    {
        Storage::fake('local');
        $superAdmin = $this->userWithRole('Super Admin');

        Artisan::shouldReceive('call')
            ->once()
            ->with('backup:run', [
                '--disable-notifications' => true,
            ])
            ->andReturn(0);
        Artisan::shouldReceive('output')->andReturn('full backup complete');

        $this->actingAs($superAdmin)
            ->post(route('settings.backups.store'), [
                'backup_type' => 'full',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $status = json_decode(Storage::disk('local')->get('backups/status.json'), true);
        $this->assertSame('full', $status['type']);

        Artisan::shouldReceive('call')
            ->once()
            ->with('backup:clean', [
                '--disable-notifications' => true,
            ])
            ->andReturn(0);
        Artisan::shouldReceive('output')->andReturn('cleanup complete');

        $this->actingAs($superAdmin)
            ->post(route('settings.backups.cleanup'))
            ->assertRedirect()
            ->assertSessionHas('success');

        $status = json_decode(Storage::disk('local')->get('backups/status.json'), true);
        $this->assertSame('cleanup', $status['type']);
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
