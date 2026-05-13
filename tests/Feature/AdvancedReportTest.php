<?php

namespace Tests\Feature;

use App\Models\Cashbook;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Loan;
use App\Models\Partner;
use App\Models\PartnerTransaction;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Supplier;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdvancedReportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_advanced_reports_render_real_financial_and_inventory_values(): void
    {
        $admin = $this->userWithRole('Admin');
        $data = $this->seedReportData();
        $filters = [
            'from_date' => now()->startOfMonth()->toDateString(),
            'to_date' => now()->toDateString(),
        ];

        $this->actingAs($admin)
            ->get(route('reports.profit-loss', $filters))
            ->assertOk()
            ->assertSee('Profit &amp; Loss Report', false)
            ->assertSee('Rs. 500.00')
            ->assertSee('Rs. 300.00')
            ->assertSee('Rs. 150.00');

        $this->actingAs($admin)
            ->get(route('reports.product-profit', array_merge($filters, ['product_id' => $data['product']->id])))
            ->assertOk()
            ->assertSee('MS Rod')
            ->assertSee('5.000')
            ->assertSee('Rs. 200.00')
            ->assertSee('40.00%');

        $this->actingAs($admin)
            ->get(route('reports.customer-outstanding', array_merge($filters, ['customer_id' => $data['customer']->id])))
            ->assertOk()
            ->assertSee('Arun Steel')
            ->assertSee('Rs. 300.00');

        $this->actingAs($admin)
            ->get(route('reports.supplier-outstanding', array_merge($filters, ['supplier_id' => $data['supplier']->id])))
            ->assertOk()
            ->assertSee('Lucky Supplier')
            ->assertSee('Rs. 200.00');

        $this->actingAs($admin)
            ->get(route('reports.stock-valuation', array_merge($filters, ['product_id' => $data['product']->id])))
            ->assertOk()
            ->assertSee('Stock Valuation Report')
            ->assertSee('Rs. 600.00');

        $this->actingAs($admin)
            ->get(route('reports.expense-summary', array_merge($filters, ['category_id' => $data['expenseCategory']->id])))
            ->assertOk()
            ->assertSee('Rent')
            ->assertSee('Rs. 50.00');
    }

    public function test_partner_loan_gst_and_daily_reports_calculate_summary_values(): void
    {
        $admin = $this->userWithRole('Admin');
        $this->seedReportData();
        $filters = [
            'from_date' => now()->startOfMonth()->toDateString(),
            'to_date' => now()->toDateString(),
        ];

        $this->actingAs($admin)
            ->get(route('reports.partner-balance', $filters))
            ->assertOk()
            ->assertSee('Main Partner')
            ->assertSee('Rs. 780.00');

        $this->actingAs($admin)
            ->get(route('reports.loan-summary', $filters))
            ->assertOk()
            ->assertSee('LN-001')
            ->assertSee('Rs. 600.00')
            ->assertSee('Rs. 100.00');

        $this->actingAs($admin)
            ->get(route('reports.gst-summary', $filters))
            ->assertOk()
            ->assertSee('Output GST')
            ->assertSee('Input GST')
            ->assertSee('Rs. 36.00');

        $this->actingAs($admin)
            ->get(route('reports.daily-business-summary', $filters))
            ->assertOk()
            ->assertSee('Daily Business Summary')
            ->assertSee('Rs. 150.00');
    }

    public function test_report_exports_support_csv_excel_and_pdf_and_require_export_permission(): void
    {
        $admin = $this->userWithRole('Admin');
        $viewer = $this->userWithRole('Viewer');
        $this->seedReportData();
        $filters = [
            'from_date' => now()->startOfMonth()->toDateString(),
            'to_date' => now()->toDateString(),
        ];

        $this->actingAs($admin)
            ->get(route('reports.export', array_merge(['report' => 'product-profit', 'format' => 'csv'], $filters)))
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');

        $this->actingAs($admin)
            ->get(route('reports.export', array_merge(['report' => 'product-profit', 'format' => 'excel'], $filters)))
            ->assertOk()
            ->assertHeader('content-type', 'application/vnd.ms-excel; charset=UTF-8');

        $this->actingAs($admin)
            ->get(route('reports.export', array_merge(['report' => 'profit-loss', 'format' => 'pdf'], $filters)))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');

        $this->actingAs($viewer)
            ->get(route('reports.export', array_merge(['report' => 'profit-loss', 'format' => 'csv'], $filters)))
            ->assertForbidden();
    }

    private function seedReportData(): array
    {
        $date = now()->toDateString();
        $category = ProductCategory::create(['name' => 'Steel', 'status' => 'active']);
        $product = Product::create([
            'product_category_id' => $category->id,
            'name' => 'MS Rod',
            'code' => 'MS-001',
            'unit' => 'Kg',
            'purchase_price' => 60,
            'selling_price' => 100,
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
            'sale_no' => 'GST-001',
            'customer_id' => $customer->id,
            'sale_date' => $date,
            'bill_type' => 'gst',
            'subtotal' => 500,
            'gst_amount' => 90,
            'total_amount' => 500,
            'paid_amount' => 200,
            'balance_amount' => 300,
            'payment_status' => 'partial',
            'payment_mode' => 'cash',
        ]);
        SaleItem::create([
            'sale_id' => $sale->id,
            'product_id' => $product->id,
            'quantity' => 5,
            'unit' => 'Kg',
            'rate' => 100,
            'subtotal' => 500,
            'gst_percentage' => 18,
            'gst_amount' => 90,
            'total' => 590,
            'purchase_cost' => 300,
            'profit_amount' => 200,
        ]);
        Purchase::create([
            'purchase_no' => 'PUR-001',
            'supplier_id' => $supplier->id,
            'purchase_date' => $date,
            'bill_type' => 'gst',
            'supplier_invoice_no' => 'SIN-001',
            'subtotal' => 300,
            'gst_amount' => 54,
            'total_amount' => 300,
            'paid_amount' => 100,
            'balance_amount' => 200,
            'payment_status' => 'partial',
            'payment_mode' => 'bank',
        ]);
        Payment::create([
            'payment_no' => 'RCPT-001',
            'payment_date' => $date,
            'party_type' => 'customer',
            'party_id' => $customer->id,
            'transaction_type' => 'receipt',
            'reference_type' => 'sale',
            'reference_id' => $sale->id,
            'amount' => 200,
            'payment_mode' => 'cash',
        ]);
        Payment::create([
            'payment_no' => 'PAY-001',
            'payment_date' => $date,
            'party_type' => 'supplier',
            'party_id' => $supplier->id,
            'transaction_type' => 'payment',
            'reference_type' => 'purchase',
            'reference_id' => 1,
            'amount' => 100,
            'payment_mode' => 'bank',
        ]);
        Cashbook::create([
            'entry_date' => $date,
            'transaction_type' => 'cash_in',
            'reference_type' => 'sale',
            'reference_id' => $sale->id,
            'amount' => 200,
            'payment_mode' => 'cash',
        ]);
        Cashbook::create([
            'entry_date' => $date,
            'transaction_type' => 'cash_out',
            'reference_type' => 'expense',
            'reference_id' => 1,
            'amount' => 50,
            'payment_mode' => 'cash',
        ]);
        $expenseCategory = ExpenseCategory::create(['name' => 'Rent', 'status' => 'active']);
        Expense::create([
            'expense_no' => 'EXP-001',
            'expense_date' => $date,
            'expense_category_id' => $expenseCategory->id,
            'amount' => 50,
            'payment_mode' => 'cash',
            'paid_to' => 'Owner',
        ]);
        $partner = Partner::create([
            'name' => 'Main Partner',
            'share_percentage' => 50,
            'opening_investment' => 1000,
            'current_investment' => 700,
            'status' => 'active',
        ]);
        foreach ([
            ['investment', 1000],
            ['withdrawal', 300],
            ['profit_share', 100],
            ['return', 20],
        ] as [$type, $amount]) {
            PartnerTransaction::create([
                'partner_id' => $partner->id,
                'transaction_date' => $date,
                'transaction_type' => $type,
                'amount' => $amount,
                'payment_mode' => 'cash',
            ]);
        }
        Loan::create([
            'loan_no' => 'LN-001',
            'loan_type' => 'loan_taken',
            'party_name' => 'Finance Party',
            'loan_date' => $date,
            'principal_amount' => 1000,
            'interest_percentage' => 10,
            'interest_type' => 'fixed',
            'total_interest' => 100,
            'total_amount' => 1100,
            'paid_amount' => 500,
            'balance_amount' => 600,
            'status' => 'active',
        ]);

        return compact('product', 'customer', 'supplier', 'expenseCategory');
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
