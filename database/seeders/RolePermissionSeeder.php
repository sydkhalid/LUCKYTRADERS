<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    /**
     * @var list<string>
     */
    private array $permissions = [
        'view_dashboard',
        'manage_products',
        'manage_customers',
        'manage_suppliers',
        'manage_purchases',
        'manage_sales',
        'print_invoice',
        'manage_payments',
        'manage_loans',
        'manage_partners',
        'view_gst_reports',
        'export_gst_reports',
        'manage_expenses',
        'manage_stock_adjustments',
        'delete_records',
        'edit_old_records',
        'manage_users',
        'manage_settings',
        'manage_backups',
    ];

    /**
     * @return array<string, list<string>>
     */
    private function rolePermissions(): array
    {
        return [
            'Super Admin' => $this->permissions,
            'Admin' => array_values(array_diff($this->permissions, ['manage_backups'])),
            'Partner' => [
                'view_dashboard',
                'manage_partners',
                'view_gst_reports',
            ],
            'Accountant' => [
                'view_dashboard',
                'manage_payments',
                'view_gst_reports',
                'export_gst_reports',
                'manage_expenses',
            ],
            'Billing Staff' => [
                'view_dashboard',
                'manage_sales',
                'print_invoice',
            ],
            'Stock Staff' => [
                'view_dashboard',
                'manage_products',
                'manage_purchases',
                'manage_stock_adjustments',
            ],
            'Viewer' => [
                'view_dashboard',
                'view_gst_reports',
            ],
        ];
    }

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach ($this->permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        foreach ($this->rolePermissions() as $roleName => $permissions) {
            Role::firstOrCreate([
                'name' => $roleName,
                'guard_name' => 'web',
            ])->syncPermissions($permissions);
        }

        if (Schema::hasColumn('users', 'is_admin')) {
            User::where('is_admin', true)->each(function (User $user): void {
                if (! $user->hasAnyRole(Role::pluck('name')->all())) {
                    $user->assignRole('Super Admin');
                }

                if (Schema::hasColumn('users', 'role')) {
                    $user->forceFill(['role' => $user->primaryRoleName()])->save();
                }
            });
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
