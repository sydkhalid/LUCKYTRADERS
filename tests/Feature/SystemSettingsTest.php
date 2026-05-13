<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\CompanySetting;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\InvoiceSetting;
use App\Models\Purchase;
use App\Models\Quotation;
use App\Models\Sale;
use App\Models\Supplier;
use App\Models\SystemSetting;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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
                'state' => 'Tamil Nadu',
                'city' => 'Krishnagiri',
                'pincode' => '635001',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('company_settings', [
            'company_name' => 'Lucky Steel Traders',
            'city' => 'Krishnagiri',
            'pincode' => '635001',
        ]);

        $this->actingAs($admin)
            ->get(route('settings.invoice'))
            ->assertOk()
            ->assertSee('Invoice Numbering');

        $this->actingAs($admin)
            ->patch(route('settings.invoice.update'), [
                'gst_invoice_prefix' => 'GSTLT',
                'normal_bill_prefix' => 'BILLLT',
                'quotation_prefix' => 'LQT',
                'purchase_prefix' => 'LPR',
                'receipt_prefix' => 'LRC',
                'next_gst_invoice_no' => 25,
                'next_normal_bill_no' => 40,
                'next_quotation_no' => 7,
                'next_purchase_no' => 8,
                'next_receipt_no' => 9,
                'terms_and_conditions' => 'Payment due on delivery.',
                'bank_details' => 'Bank: Test Bank',
            ])
            ->assertRedirect();

        $settings = InvoiceSetting::current()->refresh();
        $this->assertSame('GSTLT', $settings->gst_invoice_prefix);
        $this->assertSame(25, $settings->next_gst_invoice_no);
        $this->assertSame(7, $settings->next_quotation_no);
        $this->assertSame('Payment due on delivery.', $settings->terms_and_conditions);

        $this->assertSame('Lucky Steel Traders', SystemSetting::current()->refresh()->company_name);
        $this->assertSame('GSTLT', SystemSetting::current()->refresh()->gst_invoice_prefix);
    }

    public function test_bank_terms_and_media_settings_pages_update_split_settings(): void
    {
        Storage::fake('public');
        $admin = $this->userWithRole('Admin');

        $this->actingAs($admin)
            ->get(route('settings.bank'))
            ->assertOk()
            ->assertSee('Bank Details Settings');

        $this->actingAs($admin)
            ->patch(route('settings.bank.update'), [
                'bank_details' => 'Bank: Lucky Bank',
            ])
            ->assertRedirect();

        $this->actingAs($admin)
            ->get(route('settings.terms'))
            ->assertOk()
            ->assertSee('Terms and Conditions Settings');

        $this->actingAs($admin)
            ->patch(route('settings.terms.update'), [
                'terms_and_conditions' => 'Payment due within 7 days.',
            ])
            ->assertRedirect();

        $this->actingAs($admin)
            ->get(route('settings.media'))
            ->assertOk()
            ->assertSee('Logo and Signature Upload');

        $this->actingAs($admin)
            ->patch(route('settings.media.update'), [
                'logo' => UploadedFile::fake()->image('logo.png', 200, 80),
                'signature_image' => UploadedFile::fake()->image('signature.png', 300, 120),
            ])
            ->assertRedirect();

        $company = CompanySetting::current()->refresh();
        $invoice = InvoiceSetting::current()->refresh();
        $this->assertSame('Bank: Lucky Bank', $invoice->bank_details);
        $this->assertSame('Payment due within 7 days.', $invoice->terms_and_conditions);
        $this->assertNotNull($company->logo);
        $this->assertNotNull($invoice->signature_image);
        Storage::disk('public')->assertExists($company->logo);
        Storage::disk('public')->assertExists($invoice->signature_image);
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

        InvoiceSetting::current()->update([
            'gst_invoice_prefix' => 'GSTLT',
            'normal_bill_prefix' => 'BILLLT',
            'next_gst_invoice_no' => 10,
            'next_normal_bill_no' => 20,
        ]);

        $this->actingAs($admin)->post(route('sales.store'), $this->salePayload($customer, $product, 'gst'));
        $this->actingAs($admin)->post(route('sales.store'), $this->salePayload($customer, $product, 'non_gst'));

        $this->assertSame('GSTLT-00010', Sale::where('bill_type', 'gst')->firstOrFail()->sale_no);
        $this->assertSame('BILLLT-00020', Sale::where('bill_type', 'non_gst')->firstOrFail()->sale_no);

        $settings = InvoiceSetting::current()->refresh();
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

        InvoiceSetting::current()->update([
            'quotation_prefix' => 'LQT',
            'purchase_prefix' => 'LPR',
            'receipt_prefix' => 'LRC',
            'next_quotation_no' => 3,
            'next_purchase_no' => 4,
            'next_receipt_no' => 5,
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

        $this->assertSame('LQT-00003', Quotation::firstOrFail()->quotation_no);
        $this->assertSame('LPR-00004', Purchase::firstOrFail()->purchase_no);
        $this->assertSame('LRC-00005', Payment::firstOrFail()->payment_no);

        $settings = InvoiceSetting::current()->refresh();
        $this->assertSame(4, $settings->next_quotation_no);
        $this->assertSame(5, $settings->next_purchase_no);
        $this->assertSame(6, $settings->next_receipt_no);
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
