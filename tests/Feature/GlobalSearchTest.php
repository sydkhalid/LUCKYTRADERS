<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Loan;
use App\Models\Partner;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Purchase;
use App\Models\Quotation;
use App\Models\Sale;
use App\Models\Supplier;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GlobalSearchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_global_search_requires_two_characters(): void
    {
        $admin = $this->userWithRole('Admin');

        $this->actingAs($admin)
            ->getJson(route('global-search.search', ['q' => 'A']))
            ->assertOk()
            ->assertJsonPath('groups', []);
    }

    public function test_global_search_returns_grouped_permission_allowed_results(): void
    {
        $admin = $this->userWithRole('Admin');
        $records = $this->seedSearchRecords();

        $this->actingAs($admin)
            ->getJson(route('global-search.search', ['q' => 'MS']))
            ->assertOk()
            ->assertJsonFragment(['module' => 'Products'])
            ->assertJsonFragment(['title' => 'MS Rod (MS-001)'])
            ->assertJsonFragment(['url' => route('products.show', $records['product'])]);

        $this->actingAs($admin)
            ->getJson(route('global-search.search', ['q' => 'INV']))
            ->assertOk()
            ->assertJsonFragment(['module' => 'Sales Invoices'])
            ->assertJsonFragment(['title' => 'INV-001'])
            ->assertJsonFragment(['url' => route('sales.show', $records['sale'])]);

        $this->actingAs($admin)
            ->getJson(route('global-search.search', ['q' => 'Arun']))
            ->assertOk()
            ->assertJsonFragment(['module' => 'Customers'])
            ->assertJsonFragment(['title' => 'Arun Steel']);
    }

    public function test_global_search_hides_modules_without_permission(): void
    {
        $billingStaff = $this->userWithRole('Billing Staff');
        $this->seedSearchRecords();

        $this->actingAs($billingStaff)
            ->getJson(route('global-search.search', ['q' => 'MS']))
            ->assertOk()
            ->assertJsonMissing(['module' => 'Products']);

        $this->actingAs($billingStaff)
            ->getJson(route('global-search.search', ['q' => 'QUO']))
            ->assertOk()
            ->assertJsonFragment(['module' => 'Quotations'])
            ->assertJsonFragment(['title' => 'QUO-001']);

        $this->actingAs($billingStaff)
            ->getJson(route('global-search.search', ['q' => 'RCPT']))
            ->assertOk()
            ->assertJsonFragment(['module' => 'Receipts'])
            ->assertJsonFragment(['title' => 'RCPT-001']);

        $this->actingAs($billingStaff)
            ->getJson(route('global-search.search', ['q' => 'PAY']))
            ->assertOk()
            ->assertJsonMissing(['module' => 'Payments']);
    }

    public function test_erp_layout_renders_search_component_for_authenticated_users(): void
    {
        $admin = $this->userWithRole('Admin');

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('data-global-search', false)
            ->assertSee(route('global-search.search'), false);
    }

    private function seedSearchRecords(): array
    {
        $category = ProductCategory::create(['name' => 'Steel', 'status' => 'active']);
        $product = Product::create([
            'product_category_id' => $category->id,
            'name' => 'MS Rod',
            'code' => 'MS-001',
            'unit' => 'Kg',
            'purchase_price' => 50,
            'selling_price' => 70,
            'opening_stock' => 10,
            'current_stock' => 10,
            'status' => 'active',
        ]);
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
        $sale = Sale::create([
            'sale_no' => 'INV-001',
            'customer_id' => $customer->id,
            'sale_date' => now()->toDateString(),
            'bill_type' => 'gst',
            'subtotal' => 100,
            'gst_amount' => 18,
            'total_amount' => 118,
            'paid_amount' => 0,
            'balance_amount' => 118,
            'payment_status' => 'pending',
            'payment_mode' => 'credit',
        ]);
        Purchase::create([
            'purchase_no' => 'PUR-001',
            'supplier_id' => $supplier->id,
            'purchase_date' => now()->toDateString(),
            'bill_type' => 'gst',
            'supplier_invoice_no' => 'SUP-INV-001',
            'subtotal' => 100,
            'gst_amount' => 18,
            'total_amount' => 118,
            'paid_amount' => 0,
            'balance_amount' => 118,
            'payment_status' => 'pending',
            'payment_mode' => 'credit',
        ]);
        Quotation::create([
            'quotation_no' => 'QUO-001',
            'customer_id' => $customer->id,
            'quotation_date' => now()->toDateString(),
            'subtotal' => 100,
            'gst_amount' => 18,
            'total_amount' => 118,
            'status' => 'draft',
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
            'paid_amount' => 0,
            'balance_amount' => 1000,
            'status' => 'active',
        ]);
        Partner::create([
            'name' => 'Main Partner',
            'share_percentage' => 50,
            'opening_investment' => 1000,
            'current_investment' => 1000,
            'status' => 'active',
        ]);
        Payment::create([
            'payment_no' => 'RCPT-001',
            'payment_date' => now()->toDateString(),
            'party_type' => 'customer',
            'party_id' => $customer->id,
            'transaction_type' => 'receipt',
            'reference_type' => 'sale',
            'reference_id' => $sale->id,
            'amount' => 50,
            'payment_mode' => 'cash',
        ]);
        Payment::create([
            'payment_no' => 'PAY-001',
            'payment_date' => now()->toDateString(),
            'party_type' => 'supplier',
            'party_id' => $supplier->id,
            'transaction_type' => 'payment',
            'reference_type' => 'purchase',
            'reference_id' => 1,
            'amount' => 50,
            'payment_mode' => 'bank',
        ]);

        return compact('product', 'customer', 'supplier', 'sale');
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
