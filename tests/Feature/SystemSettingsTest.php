<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Purchase;
use App\Models\Quotation;
use App\Models\Sale;
use App\Models\Supplier;
use App\Models\SystemSetting;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SystemSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_admin_can_update_company_and_invoice_settings(): void
    {
        $admin = $this->userWithRole('Admin');

        $this->actingAs($admin)
            ->get(route('settings.company'))
            ->assertOk()
            ->assertSee('Company Profile');

        $this->actingAs($admin)
            ->patch(route('settings.company.update'), [
                'company_name' => 'Lucky Steel Traders',
                'address' => 'Krishnagiri Main Road',
                'phone' => '9876543210',
                'email' => 'accounts@lucky.test',
                'gst_number' => '33ABCDE1234F1Z5',
                'invoice_prefix' => 'INV',
                'quotation_prefix' => 'LQT',
                'purchase_prefix' => 'LPR',
                'receipt_prefix' => 'LRC',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('system_settings', [
            'company_name' => 'Lucky Steel Traders',
            'quotation_prefix' => 'LQT',
            'receipt_prefix' => 'LRC',
        ]);

        $this->actingAs($admin)
            ->get(route('settings.invoice'))
            ->assertOk()
            ->assertSee('Invoice Numbering');

        $this->actingAs($admin)
            ->patch(route('settings.invoice.update'), [
                'gst_invoice_prefix' => 'GSTLT',
                'normal_bill_prefix' => 'BILLLT',
                'next_gst_invoice_no' => 25,
                'next_normal_bill_no' => 40,
                'terms_and_conditions' => 'Payment due on delivery.',
                'bank_details' => 'Bank: Test Bank',
            ])
            ->assertRedirect();

        $settings = SystemSetting::current()->refresh();
        $this->assertSame('GSTLT', $settings->gst_invoice_prefix);
        $this->assertSame(25, $settings->next_gst_invoice_no);
        $this->assertSame('Payment due on delivery.', $settings->terms_and_conditions);
    }

    public function test_backup_pages_are_super_admin_only(): void
    {
        $superAdmin = $this->userWithRole('Super Admin');
        $admin = $this->userWithRole('Admin');

        $this->actingAs($superAdmin)
            ->get(route('settings.backups.index'))
            ->assertOk()
            ->assertSee('Database Backups');

        $this->actingAs($admin)
            ->get(route('settings.backups.index'))
            ->assertForbidden();
    }

    public function test_admin_can_open_production_testing_checklist(): void
    {
        $admin = $this->userWithRole('Admin');

        $this->get(route('settings.testing-checklist'))
            ->assertRedirect('/login');

        $this->actingAs($admin)
            ->get(route('settings.testing-checklist'))
            ->assertOk()
            ->assertSee('Production Testing Checklist')
            ->assertSee('GST invoice testing')
            ->assertSee('php artisan migrate --force');
    }

    public function test_sales_use_separate_gst_and_normal_bill_number_series(): void
    {
        $admin = $this->userWithRole('Admin');
        [$customer, $product] = $this->customerAndProduct();

        SystemSetting::current()->update([
            'gst_invoice_prefix' => 'GSTLT',
            'normal_bill_prefix' => 'BILLLT',
            'next_gst_invoice_no' => 10,
            'next_normal_bill_no' => 20,
        ]);

        $this->actingAs($admin)->post(route('sales.store'), $this->salePayload($customer, $product, 'gst'));
        $this->actingAs($admin)->post(route('sales.store'), $this->salePayload($customer, $product, 'non_gst'));

        $this->assertSame('GSTLT-00010', Sale::where('bill_type', 'gst')->firstOrFail()->sale_no);
        $this->assertSame('BILLLT-00020', Sale::where('bill_type', 'non_gst')->firstOrFail()->sale_no);

        $settings = SystemSetting::current()->refresh();
        $this->assertSame(11, $settings->next_gst_invoice_no);
        $this->assertSame(21, $settings->next_normal_bill_no);
    }

    public function test_quotation_purchase_and_receipt_prefixes_come_from_settings(): void
    {
        $admin = $this->userWithRole('Admin');
        [$customer, $product] = $this->customerAndProduct();
        $supplier = Supplier::create([
            'name' => 'Settings Supplier',
            'status' => 'active',
        ]);

        SystemSetting::current()->update([
            'quotation_prefix' => 'LQT',
            'purchase_prefix' => 'LPR',
            'receipt_prefix' => 'LRC',
        ]);

        $this->actingAs($admin)->post(route('quotations.store'), [
            'customer_id' => $customer->id,
            'quotation_date' => '2026-05-13',
            'valid_until' => '2026-05-20',
            'status' => 'sent',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 1,
                    'unit' => 'Kg',
                    'rate' => 100,
                    'gst_percentage' => 18,
                ],
            ],
        ]);

        $this->actingAs($admin)->post(route('purchases.store'), [
            'supplier_id' => $supplier->id,
            'purchase_date' => '2026-05-13',
            'bill_type' => 'non_gst',
            'paid_amount' => 0,
            'payment_mode' => 'credit',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 1,
                    'unit' => 'Kg',
                    'rate' => 50,
                    'gst_percentage' => 0,
                ],
            ],
        ]);

        $sale = Sale::create([
            'sale_no' => 'MANUAL-001',
            'customer_id' => $customer->id,
            'sale_date' => '2026-05-13',
            'bill_type' => 'gst',
            'subtotal' => 100,
            'gst_amount' => 0,
            'total_amount' => 100,
            'paid_amount' => 0,
            'balance_amount' => 100,
            'payment_status' => 'pending',
            'payment_mode' => 'credit',
        ]);
        $customer->forceFill(['current_balance' => 100])->save();

        $this->actingAs($admin)->post(route('receipts.store'), [
            'customer_id' => $customer->id,
            'payment_date' => '2026-05-13',
            'reference_type' => 'sale',
            'reference_id' => $sale->id,
            'amount' => 25,
            'payment_mode' => 'cash',
        ]);

        $date = now()->format('Ymd');
        $this->assertStringStartsWith('LQT-'.$date.'-', Quotation::firstOrFail()->quotation_no);
        $this->assertStringStartsWith('LPR-'.$date.'-', Purchase::firstOrFail()->purchase_no);
        $this->assertStringStartsWith('LRC-'.$date.'-', Payment::firstOrFail()->payment_no);
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

    private function customerAndProduct(): array
    {
        $customer = Customer::create([
            'name' => 'Settings Customer',
            'phone' => '9000000000',
            'status' => 'active',
        ]);
        $category = ProductCategory::create([
            'name' => 'Steel',
            'status' => 'active',
        ]);
        $product = Product::create([
            'product_category_id' => $category->id,
            'name' => 'MS Rod',
            'code' => 'MS-ROD',
            'unit' => 'Kg',
            'purchase_price' => 50,
            'selling_price' => 100,
            'gst_percentage' => 18,
            'current_stock' => 20,
            'status' => 'active',
        ]);

        return [$customer, $product];
    }

    private function salePayload(Customer $customer, Product $product, string $billType): array
    {
        return [
            'customer_id' => $customer->id,
            'sale_date' => '2026-05-13',
            'bill_type' => $billType,
            'paid_amount' => 0,
            'payment_mode' => 'credit',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 1,
                    'unit' => 'Kg',
                    'rate' => 100,
                    'gst_percentage' => 18,
                ],
            ],
        ];
    }
}
