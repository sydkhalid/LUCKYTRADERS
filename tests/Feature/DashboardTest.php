<?php

namespace Tests\Feature;

use App\Models\Cashbook;
use App\Models\Customer;
use App\Models\Loan;
use App\Models\Partner;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Supplier;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2026-05-13 10:00:00');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_dashboard_reads_real_business_summary_data(): void
    {
        $this->seedDashboardData();

        $response = $this->actingAs(User::factory()->create())->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Today Sales');
        $response->assertSee('Rs. 1,180.00');
        $response->assertSee('Rs. 1,680.00');
        $response->assertSee('Rs. 590.00');
        $response->assertSee('Rs. 1,000.00');
        $response->assertSee('Rs. 750.00');
        $response->assertSee('Rs. 1,200.00');
        $response->assertSee('Rs. 1,180.00');
        $response->assertSee('Rs. 750.00');
        $response->assertSee('Rs. 800.00');
        $response->assertSee('Rs. 3,000.00');
        $response->assertSee('Rs. 7,000.00');
        $response->assertSee('Rs. 300.00');
        $response->assertSee('Recent Sales');
        $response->assertSee('DASH-GST-001');
        $response->assertSee('Low Stock Products');
        $response->assertSee('MS Sheet');
        $response->assertSee('Active Loans');
        $response->assertSee('DASH-LOAN-001');
    }

    public function test_dashboard_custom_date_range_limits_period_tables(): void
    {
        $this->seedDashboardData();

        $response = $this->actingAs(User::factory()->create())->get(route('dashboard', [
            'period' => 'custom',
            'from_date' => '2026-05-13',
            'to_date' => '2026-05-13',
        ]));

        $response->assertOk();
        $response->assertSee('DASH-GST-001');
        $response->assertDontSee('DASH-MONTH-001');
        $response->assertSee('DASH-PUR-001');
        $response->assertDontSee('DASH-PUR-MONTH');
    }

    private function seedDashboardData(): void
    {
        $customer = Customer::create([
            'name' => 'Dashboard Customer',
            'gst_number' => '33DASHCUST1',
        ]);
        $supplier = Supplier::create([
            'name' => 'Dashboard Supplier',
            'gst_number' => '33DASHSUPP1',
        ]);
        $category = ProductCategory::create([
            'name' => 'Sheets',
            'status' => 'active',
        ]);
        $product = Product::create([
            'product_category_id' => $category->id,
            'name' => 'MS Sheet',
            'code' => 'DASH-MS-001',
            'unit' => 'Kg',
            'purchase_price' => 100,
            'selling_price' => 150,
            'current_stock' => 8,
            'status' => 'active',
        ]);

        $todaySale = Sale::create([
            'sale_no' => 'DASH-GST-001',
            'customer_id' => $customer->id,
            'sale_date' => '2026-05-13',
            'bill_type' => 'gst',
            'subtotal' => 1000,
            'gst_amount' => 180,
            'total_amount' => 1180,
            'paid_amount' => 500,
            'balance_amount' => 680,
            'payment_status' => 'partial',
            'payment_mode' => 'credit',
        ]);
        SaleItem::create([
            'sale_id' => $todaySale->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'unit' => 'Kg',
            'rate' => 500,
            'subtotal' => 1000,
            'gst_percentage' => 18,
            'gst_amount' => 180,
            'total' => 1180,
            'purchase_cost' => 700,
            'profit_amount' => 300,
        ]);

        Sale::create([
            'sale_no' => 'DASH-MONTH-001',
            'customer_id' => $customer->id,
            'sale_date' => '2026-05-05',
            'bill_type' => 'non_gst',
            'subtotal' => 500,
            'gst_amount' => 0,
            'total_amount' => 500,
            'paid_amount' => 0,
            'balance_amount' => 500,
            'payment_status' => 'pending',
            'payment_mode' => 'credit',
        ]);

        Purchase::create([
            'purchase_no' => 'DASH-PUR-001',
            'supplier_id' => $supplier->id,
            'purchase_date' => '2026-05-13',
            'bill_type' => 'gst',
            'supplier_invoice_no' => 'SUP-DASH-001',
            'subtotal' => 500,
            'gst_amount' => 90,
            'total_amount' => 590,
            'paid_amount' => 250,
            'balance_amount' => 340,
            'payment_status' => 'partial',
            'payment_mode' => 'credit',
        ]);
        Purchase::create([
            'purchase_no' => 'DASH-PUR-MONTH',
            'supplier_id' => $supplier->id,
            'purchase_date' => '2026-05-01',
            'bill_type' => 'non_gst',
            'supplier_invoice_no' => 'SUP-DASH-002',
            'subtotal' => 410,
            'gst_amount' => 0,
            'total_amount' => 410,
            'paid_amount' => 0,
            'balance_amount' => 410,
            'payment_status' => 'pending',
            'payment_mode' => 'credit',
        ]);

        Cashbook::create([
            'entry_date' => '2026-05-13',
            'transaction_type' => 'cash_in',
            'reference_type' => 'test',
            'reference_id' => 1,
            'amount' => 1000,
            'payment_mode' => 'cash',
        ]);
        Cashbook::create([
            'entry_date' => '2026-05-13',
            'transaction_type' => 'cash_out',
            'reference_type' => 'test',
            'reference_id' => 2,
            'amount' => 250,
            'payment_mode' => 'cash',
        ]);
        Cashbook::create([
            'entry_date' => '2026-05-13',
            'transaction_type' => 'bank_in',
            'reference_type' => 'test',
            'reference_id' => 3,
            'amount' => 2000,
            'payment_mode' => 'bank',
        ]);
        Cashbook::create([
            'entry_date' => '2026-05-13',
            'transaction_type' => 'bank_out',
            'reference_type' => 'test',
            'reference_id' => 4,
            'amount' => 800,
            'payment_mode' => 'upi',
        ]);

        Loan::create([
            'loan_no' => 'DASH-LOAN-001',
            'loan_type' => 'loan_taken',
            'party_name' => 'Dashboard Lender',
            'loan_date' => '2026-05-13',
            'principal_amount' => 3000,
            'interest_percentage' => 0,
            'interest_type' => 'none',
            'total_interest' => 0,
            'total_amount' => 3000,
            'paid_amount' => 0,
            'balance_amount' => 3000,
            'status' => 'active',
        ]);

        Partner::create([
            'name' => 'Dashboard Partner',
            'share_percentage' => 50,
            'opening_investment' => 7000,
            'current_investment' => 7000,
            'status' => 'active',
        ]);
    }
}
