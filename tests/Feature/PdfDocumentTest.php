<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Cashbook;
use App\Models\Ledger;
use App\Models\Loan;
use App\Models\LoanTransaction;
use App\Models\Partner;
use App\Models\PartnerTransaction;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\User;
use App\Support\AmountInWords;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PdfDocumentTest extends TestCase
{
    use RefreshDatabase;

    public function test_pdf_routes_stream_professional_documents(): void
    {
        $records = $this->createPdfRecords();
        $user = User::factory()->create();

        $routes = [
            route('sales.pdf', $records['sale']),
            route('quotations.pdf', $records['quotation']),
            route('purchases.pdf', $records['purchase']),
            route('payments.pdf', $records['receipt']),
            route('payments.pdf', $records['supplierPayment']),
            route('loans.pdf', $records['loan']),
            route('loans.transactions.pdf', ['loan' => $records['loan'], 'transaction' => $records['loanTransaction']]),
            route('partners.transactions.pdf', ['partner' => $records['partner'], 'transaction' => $records['partnerTransaction']]),
            route('expenses.pdf', $records['expense']),
            route('gst-reports.pdf'),
        ];

        foreach ($routes as $route) {
            $response = $this->actingAs($user)->get($route);

            $response->assertOk();
            $this->assertStringContainsString('application/pdf', (string) $response->headers->get('content-type'));
        }

        $this->assertStringContainsString(
            'PdfController',
            app('router')->getRoutes()->getByName('sales.pdf')->getActionName()
        );
    }

    public function test_normal_bill_pdf_template_does_not_show_gst_columns(): void
    {
        $records = $this->createPdfRecords();
        $normalSale = $records['normalSale']->load(['customer', 'items.product']);

        $html = view('pdf.sale', [
            'title' => 'Normal Bill',
            'company' => [
                'name' => 'LUCKY TRADERS',
                'address' => '2/164/14 Line Kollai, Venkatapuram, Krishnagiri, Tamil Nadu, India',
                'gst_number' => null,
            ],
            'generatedAt' => now(),
            'sale' => $normalSale,
            'amountWords' => AmountInWords::rupees($normalSale->total_amount),
        ])->render();

        $this->assertStringContainsString('Normal Bill', $html);
        $this->assertStringContainsString('Normal Bill No', $html);
        $this->assertStringNotContainsString('HSN', $html);
        $this->assertStringNotContainsString('GST Amount', $html);
    }

    public function test_gst_invoice_pdf_template_shows_tax_fields(): void
    {
        $records = $this->createPdfRecords();
        $gstSale = $records['sale']->load(['customer', 'items.product']);

        $html = view('pdf.sale', [
            'title' => 'GST Invoice',
            'company' => [
                'name' => 'LUCKY TRADERS',
                'address' => '2/164/14 Line Kollai, Venkatapuram, Krishnagiri, Tamil Nadu, India',
                'gst_number' => '33GSTLUCKY001',
            ],
            'generatedAt' => now(),
            'termsAndConditions' => '',
            'bankDetails' => null,
            'signatureImagePath' => null,
            'sale' => $gstSale,
            'amountWords' => AmountInWords::rupees($gstSale->total_amount),
        ])->render();

        $this->assertStringContainsString('GST Invoice No', $html);
        $this->assertStringContainsString('Customer GSTIN', $html);
        $this->assertStringContainsString('HSN', $html);
        $this->assertStringContainsString('Taxable', $html);
        $this->assertStringContainsString('GST %', $html);
        $this->assertStringContainsString('GST Amount', $html);
    }

    public function test_pdf_generation_does_not_change_business_records(): void
    {
        $records = $this->createPdfRecords();
        $user = User::factory()->create();

        $before = [
            'products' => Product::pluck('current_stock', 'id')->all(),
            'payments' => Payment::count(),
            'ledgers' => Ledger::count(),
            'cashbooks' => Cashbook::count(),
            'stock_movements' => StockMovement::count(),
            'sale_paid' => (float) $records['sale']->fresh()->paid_amount,
            'purchase_paid' => (float) $records['purchase']->fresh()->paid_amount,
            'expense_amount' => (float) $records['expense']->fresh()->amount,
        ];

        foreach ([
            route('sales.pdf', $records['sale']),
            route('quotations.pdf', $records['quotation']),
            route('purchases.pdf', $records['purchase']),
            route('payments.pdf', $records['receipt']),
            route('payments.pdf', $records['supplierPayment']),
            route('loans.pdf', $records['loan']),
            route('partners.transactions.pdf', ['partner' => $records['partner'], 'transaction' => $records['partnerTransaction']]),
            route('expenses.pdf', $records['expense']),
            route('gst-reports.pdf'),
        ] as $route) {
            $this->actingAs($user)->get($route)->assertOk();
        }

        $this->assertSame($before['products'], Product::pluck('current_stock', 'id')->all());
        $this->assertSame($before['payments'], Payment::count());
        $this->assertSame($before['ledgers'], Ledger::count());
        $this->assertSame($before['cashbooks'], Cashbook::count());
        $this->assertSame($before['stock_movements'], StockMovement::count());
        $this->assertSame($before['sale_paid'], (float) $records['sale']->fresh()->paid_amount);
        $this->assertSame($before['purchase_paid'], (float) $records['purchase']->fresh()->paid_amount);
        $this->assertSame($before['expense_amount'], (float) $records['expense']->fresh()->amount);
    }

    private function createPdfRecords(): array
    {
        $customer = Customer::create([
            'name' => 'GST Customer',
            'phone' => '9999999999',
            'gst_number' => '33GSTCUST001',
            'address' => 'Krishnagiri',
        ]);
        $supplier = Supplier::create([
            'name' => 'GST Supplier',
            'phone' => '8888888888',
            'gst_number' => '33GSTSUP001',
            'address' => 'Hosur',
        ]);
        $category = ProductCategory::create([
            'name' => 'Steel Pipes',
            'status' => 'active',
        ]);
        $product = Product::create([
            'product_category_id' => $category->id,
            'name' => 'MS Square Pipe',
            'code' => 'MSP-001',
            'unit' => 'kg',
            'hsn_code' => '7306',
            'gst_percentage' => 18,
            'purchase_price' => 50,
            'selling_price' => 70,
            'opening_stock' => 100,
            'current_stock' => 100,
            'status' => 'active',
        ]);

        $sale = Sale::create([
            'sale_no' => 'GST-SALE-PDF',
            'customer_id' => $customer->id,
            'sale_date' => '2026-05-13',
            'bill_type' => 'gst',
            'subtotal' => 1000,
            'gst_amount' => 180,
            'total_amount' => 1180,
            'paid_amount' => 500,
            'balance_amount' => 680,
            'payment_status' => 'partial',
            'payment_mode' => 'cash',
        ]);
        SaleItem::create([
            'sale_id' => $sale->id,
            'product_id' => $product->id,
            'quantity' => 10,
            'unit' => 'kg',
            'rate' => 100,
            'subtotal' => 1000,
            'gst_percentage' => 18,
            'gst_amount' => 180,
            'total' => 1180,
            'purchase_cost' => 500,
            'profit_amount' => 500,
        ]);

        $normalSale = Sale::create([
            'sale_no' => 'NORMAL-SALE-PDF',
            'customer_id' => $customer->id,
            'sale_date' => '2026-05-13',
            'bill_type' => 'non_gst',
            'subtotal' => 700,
            'gst_amount' => 0,
            'total_amount' => 700,
            'paid_amount' => 0,
            'balance_amount' => 700,
            'payment_status' => 'pending',
            'payment_mode' => 'credit',
        ]);
        SaleItem::create([
            'sale_id' => $normalSale->id,
            'product_id' => $product->id,
            'quantity' => 10,
            'unit' => 'kg',
            'rate' => 70,
            'subtotal' => 700,
            'gst_percentage' => 0,
            'gst_amount' => 0,
            'total' => 700,
            'purchase_cost' => 500,
            'profit_amount' => 200,
        ]);

        $quotation = Quotation::create([
            'quotation_no' => 'QT-PDF',
            'customer_id' => $customer->id,
            'quotation_date' => '2026-05-13',
            'valid_until' => '2026-05-30',
            'subtotal' => 1000,
            'gst_amount' => 180,
            'total_amount' => 1180,
            'status' => 'sent',
        ]);
        QuotationItem::create([
            'quotation_id' => $quotation->id,
            'product_id' => $product->id,
            'quantity' => 10,
            'unit' => 'kg',
            'rate' => 100,
            'subtotal' => 1000,
            'gst_percentage' => 18,
            'gst_amount' => 180,
            'total' => 1180,
        ]);

        $purchase = Purchase::create([
            'purchase_no' => 'PUR-PDF',
            'supplier_id' => $supplier->id,
            'purchase_date' => '2026-05-13',
            'bill_type' => 'gst',
            'supplier_invoice_no' => 'SUP-PDF',
            'subtotal' => 500,
            'gst_amount' => 90,
            'total_amount' => 590,
            'paid_amount' => 100,
            'balance_amount' => 490,
            'payment_status' => 'partial',
            'payment_mode' => 'bank',
        ]);
        PurchaseItem::create([
            'purchase_id' => $purchase->id,
            'product_id' => $product->id,
            'quantity' => 10,
            'unit' => 'kg',
            'rate' => 50,
            'subtotal' => 500,
            'gst_percentage' => 18,
            'gst_amount' => 90,
            'total' => 590,
        ]);

        $receipt = Payment::create([
            'payment_no' => 'RCPT-PDF',
            'payment_date' => '2026-05-13',
            'party_type' => 'customer',
            'party_id' => $customer->id,
            'transaction_type' => 'receipt',
            'reference_type' => 'sale',
            'reference_id' => $sale->id,
            'amount' => 500,
            'payment_mode' => 'cash',
            'notes' => 'Customer receipt',
        ]);
        $supplierPayment = Payment::create([
            'payment_no' => 'PAY-PDF',
            'payment_date' => '2026-05-13',
            'party_type' => 'supplier',
            'party_id' => $supplier->id,
            'transaction_type' => 'payment',
            'reference_type' => 'purchase',
            'reference_id' => $purchase->id,
            'amount' => 100,
            'payment_mode' => 'bank',
            'notes' => 'Supplier payment',
        ]);

        $loan = Loan::create([
            'loan_no' => 'LN-PDF',
            'loan_type' => 'loan_taken',
            'party_name' => 'Finance Person',
            'party_phone' => '7777777777',
            'loan_date' => '2026-05-13',
            'principal_amount' => 10000,
            'interest_percentage' => 0,
            'interest_type' => 'none',
            'total_interest' => 0,
            'total_amount' => 10000,
            'paid_amount' => 0,
            'balance_amount' => 10000,
            'status' => 'active',
        ]);
        $loanTransaction = LoanTransaction::create([
            'loan_id' => $loan->id,
            'transaction_date' => '2026-05-13',
            'transaction_type' => 'received',
            'amount' => 10000,
            'payment_mode' => 'cash',
            'notes' => 'Loan received',
        ]);

        $partner = Partner::create([
            'name' => 'Partner One',
            'phone' => '6666666666',
            'share_percentage' => 50,
            'opening_investment' => 0,
            'current_investment' => 5000,
            'status' => 'active',
        ]);
        $partnerTransaction = PartnerTransaction::create([
            'partner_id' => $partner->id,
            'transaction_date' => '2026-05-13',
            'transaction_type' => 'investment',
            'amount' => 5000,
            'payment_mode' => 'upi',
            'notes' => 'Capital investment',
        ]);

        $category = ExpenseCategory::create(['name' => 'Rent', 'status' => 'active']);
        $expense = Expense::create([
            'expense_no' => 'EXP-PDF',
            'expense_date' => '2026-05-13',
            'expense_category_id' => $category->id,
            'amount' => 1500,
            'payment_mode' => 'cash',
            'paid_to' => 'Building Owner',
            'notes' => 'Monthly rent',
        ]);

        return compact(
            'sale',
            'normalSale',
            'quotation',
            'purchase',
            'receipt',
            'supplierPayment',
            'loan',
            'loanTransaction',
            'partner',
            'partnerTransaction',
            'expense'
        );
    }
}
