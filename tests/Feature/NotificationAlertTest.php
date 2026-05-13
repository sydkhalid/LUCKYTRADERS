<?php

namespace Tests\Feature;

use App\Models\Cashbook;
use App\Models\Customer;
use App\Models\Loan;
use App\Models\Notification;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Supplier;
use App\Models\SystemSetting;
use App\Models\User;
use App\Services\NotificationAlertService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class NotificationAlertTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_low_stock_alert_uses_configured_threshold_and_relevant_users(): void
    {
        $stockStaff = $this->userWithRole('Stock Staff');
        $billingStaff = $this->userWithRole('Billing Staff');
        $product = $this->product(currentStock: 5);

        SystemSetting::current()->forceFill(['low_stock_threshold' => 4])->save();
        app(NotificationAlertService::class)->generateAll();

        $this->assertDatabaseMissing('notifications', [
            'user_id' => $stockStaff->id,
            'type' => 'low_stock',
            'reference_id' => $product->id,
        ]);

        SystemSetting::current()->forceFill(['low_stock_threshold' => 5])->save();
        app(NotificationAlertService::class)->generateAll();

        $this->assertDatabaseHas('notifications', [
            'user_id' => $stockStaff->id,
            'type' => 'low_stock',
            'reference_id' => $product->id,
        ]);

        $this->assertDatabaseMissing('notifications', [
            'user_id' => $billingStaff->id,
            'type' => 'low_stock',
            'reference_id' => $product->id,
        ]);
    }

    public function test_generation_creates_customer_supplier_loan_backup_gst_and_daily_alerts(): void
    {
        Storage::fake('local');
        $billingStaff = $this->userWithRole('Billing Staff');
        $accountant = $this->userWithRole('Accountant');
        $admin = $this->userWithRole('Admin');
        $superAdmin = $this->userWithRole('Super Admin');
        $this->seedAlertData();
        Storage::disk('local')->put('backups/status.json', json_encode([
            'status' => 'failed',
            'type' => 'database',
            'message' => 'Database backup failed.',
            'ran_at' => now()->toDateTimeString(),
            'output' => 'backup error',
        ]));

        app(NotificationAlertService::class)->generateAll();

        $this->assertDatabaseHas('notifications', [
            'user_id' => $billingStaff->id,
            'type' => 'pending_customer_payment',
        ]);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $billingStaff->id,
            'type' => 'daily_summary',
        ]);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $accountant->id,
            'type' => 'supplier_payable_due',
        ]);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $accountant->id,
            'type' => 'gst_filing_reminder',
        ]);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $admin->id,
            'type' => 'loan_due',
        ]);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $superAdmin->id,
            'type' => 'backup_failure',
        ]);
    }

    public function test_notification_dropdown_and_read_unread_actions(): void
    {
        $admin = $this->userWithRole('Admin');
        $notification = Notification::create([
            'user_id' => $admin->id,
            'type' => 'daily_summary',
            'module' => 'dashboard',
            'severity' => 'info',
            'title' => 'Daily business summary',
            'message' => 'Sales Rs. 100.00, purchases Rs. 50.00.',
            'action_url' => route('dashboard'),
            'fingerprint' => 'test-daily-summary',
        ]);

        $this->actingAs($admin)
            ->getJson(route('notifications.dropdown'))
            ->assertOk()
            ->assertJsonPath('unread_count', 1)
            ->assertJsonFragment(['title' => 'Daily business summary']);

        $this->actingAs($admin)
            ->patchJson(route('notifications.read', $notification))
            ->assertOk()
            ->assertJsonPath('unread_count', 0);

        $this->assertNotNull($notification->fresh()->read_at);

        $this->actingAs($admin)
            ->patchJson(route('notifications.unread', $notification))
            ->assertOk()
            ->assertJsonPath('unread_count', 1);

        $this->actingAs($admin)
            ->get(route('notifications.index', ['status' => 'unread']))
            ->assertOk()
            ->assertSee('Daily business summary');

        $this->actingAs($admin)
            ->patch(route('notifications.read-all'))
            ->assertRedirect();

        $this->assertSame(0, $admin->erpNotifications()->unread()->count());
    }

    public function test_user_cannot_update_another_users_notification(): void
    {
        $admin = $this->userWithRole('Admin');
        $viewer = $this->userWithRole('Viewer');
        $notification = Notification::create([
            'user_id' => $admin->id,
            'type' => 'daily_summary',
            'module' => 'dashboard',
            'severity' => 'info',
            'title' => 'Daily business summary',
            'message' => 'Private alert',
            'fingerprint' => 'private-alert',
        ]);

        $this->actingAs($viewer)
            ->patchJson(route('notifications.read', $notification))
            ->assertForbidden();
    }

    private function seedAlertData(): void
    {
        $product = $this->product(currentStock: 5);
        $customer = Customer::create([
            'name' => 'Arun Steel',
            'phone' => '9000000001',
            'opening_balance' => 0,
            'balance_type' => 'debit',
            'status' => 'active',
        ]);
        $supplier = Supplier::create([
            'name' => 'Lucky Supplier',
            'phone' => '9000000002',
            'opening_balance' => 0,
            'balance_type' => 'credit',
            'status' => 'active',
        ]);
        Sale::create([
            'sale_no' => 'GST-001',
            'customer_id' => $customer->id,
            'sale_date' => now()->toDateString(),
            'bill_type' => 'gst',
            'subtotal' => 100,
            'gst_amount' => 18,
            'total_amount' => 118,
            'paid_amount' => 20,
            'balance_amount' => 98,
            'payment_status' => 'partial',
            'payment_mode' => 'cash',
        ]);
        Purchase::create([
            'purchase_no' => 'PUR-001',
            'supplier_id' => $supplier->id,
            'purchase_date' => now()->toDateString(),
            'bill_type' => 'gst',
            'supplier_invoice_no' => 'SIN-001',
            'subtotal' => 100,
            'gst_amount' => 18,
            'total_amount' => 118,
            'paid_amount' => 18,
            'balance_amount' => 100,
            'payment_status' => 'partial',
            'payment_mode' => 'bank',
        ]);
        Loan::create([
            'loan_no' => 'LN-001',
            'loan_type' => 'loan_taken',
            'party_name' => 'Finance Party',
            'loan_date' => now()->toDateString(),
            'principal_amount' => 1000,
            'interest_percentage' => 0,
            'interest_type' => 'none',
            'total_interest' => 0,
            'total_amount' => 1000,
            'paid_amount' => 200,
            'balance_amount' => 800,
            'status' => 'active',
        ]);
        Cashbook::create([
            'entry_date' => now()->toDateString(),
            'transaction_type' => 'cash_in',
            'reference_type' => 'sale',
            'reference_id' => 1,
            'amount' => 20,
            'payment_mode' => 'cash',
            'remarks' => $product->name,
        ]);
    }

    private function product(float $currentStock): Product
    {
        $category = ProductCategory::firstOrCreate([
            'name' => 'Steel',
        ], [
            'status' => 'active',
        ]);

        return Product::create([
            'product_category_id' => $category->id,
            'name' => 'MS Rod',
            'code' => 'MS-001',
            'unit' => 'Kg',
            'purchase_price' => 50,
            'selling_price' => 70,
            'opening_stock' => $currentStock,
            'current_stock' => $currentStock,
            'status' => 'active',
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
