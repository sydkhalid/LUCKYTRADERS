<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\Sale;
use App\Models\SalesReturn;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GSTReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_gst_sales_report_only_shows_gst_sales(): void
    {
        [$gstSale, $normalSale] = $this->createSales();

        $response = $this->actingAs(User::factory()->create())->get(route('gst-reports.sales'));

        $response->assertOk();
        $response->assertSee($gstSale->sale_no);
        $response->assertDontSee($normalSale->sale_no);
    }

    public function test_non_gst_sales_report_only_shows_normal_sales(): void
    {
        [$gstSale, $normalSale] = $this->createSales();

        $response = $this->actingAs(User::factory()->create())->get(route('gst-reports.non-gst-sales'));

        $response->assertOk();
        $response->assertSee($normalSale->sale_no);
        $response->assertDontSee($gstSale->sale_no);
    }

    public function test_gst_purchase_report_only_shows_gst_purchases(): void
    {
        [$gstPurchase, $normalPurchase] = $this->createPurchases();

        $response = $this->actingAs(User::factory()->create())->get(route('gst-reports.purchases'));

        $response->assertOk();
        $response->assertSee($gstPurchase->purchase_no);
        $response->assertDontSee($normalPurchase->purchase_no);
    }

    public function test_gst_summary_uses_only_gst_sales_and_purchases_for_gst_totals(): void
    {
        $this->createSales();
        $this->createPurchases();

        $response = $this->actingAs(User::factory()->create())->get(route('gst-reports.index'));

        $response->assertOk();
        $response->assertSee('Rs. 1,000.00');
        $response->assertSee('Rs. 180.00');
        $response->assertSee('Rs. 500.00');
        $response->assertSee('Rs. 90.00');
        $response->assertSee('Rs. 90.00');
        $response->assertSee('Rs. 800.00');
    }

    public function test_gst_return_reports_show_only_gst_credit_and_debit_notes(): void
    {
        [$gstSale, $normalSale] = $this->createSales();
        [$gstPurchase, $normalPurchase] = $this->createPurchases();

        $gstSalesReturn = $this->createSalesReturn($gstSale, 'GST-SRET-001', 100, 18, 118);
        $normalSalesReturn = $this->createSalesReturn($normalSale, 'NORMAL-SRET-001', 100, 0, 100);
        $gstPurchaseReturn = $this->createPurchaseReturn($gstPurchase, 'GST-PRET-001', 100, 18, 118);
        $normalPurchaseReturn = $this->createPurchaseReturn($normalPurchase, 'NORMAL-PRET-001', 100, 0, 100);

        $salesResponse = $this->actingAs(User::factory()->create())->get(route('gst-reports.sales-returns'));
        $salesResponse->assertOk();
        $salesResponse->assertSee($gstSalesReturn->return_no);
        $salesResponse->assertDontSee($normalSalesReturn->return_no);

        $purchaseResponse = $this->actingAs(User::factory()->create())->get(route('gst-reports.purchase-returns'));
        $purchaseResponse->assertOk();
        $purchaseResponse->assertSee($gstPurchaseReturn->return_no);
        $purchaseResponse->assertDontSee($normalPurchaseReturn->return_no);
    }

    public function test_gst_summary_subtracts_gst_return_adjustments(): void
    {
        [$gstSale] = $this->createSales();
        [$gstPurchase] = $this->createPurchases();
        $this->createSalesReturn($gstSale, 'GST-SRET-001', 100, 18, 118);
        $this->createPurchaseReturn($gstPurchase, 'GST-PRET-001', 50, 9, 59);

        $response = $this->actingAs(User::factory()->create())->get(route('gst-reports.index'));

        $response->assertOk();
        $response->assertSee('Rs. 162.00');
        $response->assertSee('Rs. 81.00');
        $response->assertSee('Rs. 81.00');
    }

    public function test_customer_supplier_and_payment_status_filters_apply_to_gst_reports(): void
    {
        [$gstSale] = $this->createSales();
        [$gstPurchase] = $this->createPurchases();

        $otherCustomer = Customer::create(['name' => 'Other GST Customer', 'gst_number' => '33OTHERGST']);
        $otherSale = Sale::create([
            'sale_no' => 'GST-SALE-OTHER',
            'customer_id' => $otherCustomer->id,
            'sale_date' => '2026-05-13',
            'bill_type' => 'gst',
            'subtotal' => 100,
            'gst_amount' => 18,
            'total_amount' => 118,
            'paid_amount' => 118,
            'balance_amount' => 0,
            'payment_status' => 'paid',
            'payment_mode' => 'cash',
        ]);

        $otherSupplier = Supplier::create(['name' => 'Other GST Supplier', 'gst_number' => '33OTHERGSTSUP']);
        $otherPurchase = Purchase::create([
            'purchase_no' => 'GST-PUR-OTHER',
            'supplier_id' => $otherSupplier->id,
            'purchase_date' => '2026-05-13',
            'bill_type' => 'gst',
            'supplier_invoice_no' => 'SUP-OTHER',
            'subtotal' => 100,
            'gst_amount' => 18,
            'total_amount' => 118,
            'paid_amount' => 118,
            'balance_amount' => 0,
            'payment_status' => 'paid',
            'payment_mode' => 'cash',
        ]);

        $salesResponse = $this->actingAs(User::factory()->create())->get(route('gst-reports.sales', [
            'customer_id' => $gstSale->customer_id,
            'payment_status' => 'partial',
        ]));
        $salesResponse->assertOk();
        $salesResponse->assertSee($gstSale->sale_no);
        $salesResponse->assertDontSee($otherSale->sale_no);

        $purchaseResponse = $this->actingAs(User::factory()->create())->get(route('gst-reports.purchases', [
            'supplier_id' => $gstPurchase->supplier_id,
            'payment_status' => 'partial',
        ]));
        $purchaseResponse->assertOk();
        $purchaseResponse->assertSee($gstPurchase->purchase_no);
        $purchaseResponse->assertDontSee($otherPurchase->purchase_no);
    }

    public function test_conflicting_bill_type_filter_does_not_leak_normal_bills_into_gst_reports(): void
    {
        [$gstSale, $normalSale] = $this->createSales();

        $response = $this->actingAs(User::factory()->create())->get(route('gst-reports.sales', [
            'bill_type' => 'non_gst',
        ]));

        $response->assertOk();
        $response->assertDontSee($gstSale->sale_no);
        $response->assertDontSee($normalSale->sale_no);
        $response->assertSee('No GST sales found.');
    }

    public function test_auditor_export_includes_only_gst_sales_and_purchases(): void
    {
        [$gstSale, $normalSale] = $this->createSales();
        [$gstPurchase, $normalPurchase] = $this->createPurchases();
        $gstSalesReturn = $this->createSalesReturn($gstSale, 'GST-SRET-001', 100, 18, 118);
        $normalSalesReturn = $this->createSalesReturn($normalSale, 'NORMAL-SRET-001', 100, 0, 100);

        $response = $this->actingAs(User::factory()->create())->get(route('gst-reports.export', [
            'type' => 'all',
        ]));

        $response->assertOk();
        $content = $response->streamedContent();

        $this->assertStringContainsString($gstSale->sale_no, $content);
        $this->assertStringContainsString($gstPurchase->purchase_no, $content);
        $this->assertStringContainsString('GST Summary', $content);
        $this->assertStringContainsString($gstSalesReturn->return_no, $content);
        $this->assertStringNotContainsString($normalSale->sale_no, $content);
        $this->assertStringNotContainsString($normalPurchase->purchase_no, $content);
        $this->assertStringNotContainsString($normalSalesReturn->return_no, $content);
    }

    public function test_summary_export_contains_auditor_gst_formula_rows(): void
    {
        [$gstSale] = $this->createSales();
        [$gstPurchase] = $this->createPurchases();
        $this->createSalesReturn($gstSale, 'GST-SRET-001', 100, 18, 118);
        $this->createPurchaseReturn($gstPurchase, 'GST-PRET-001', 50, 9, 59);

        $response = $this->actingAs(User::factory()->create())->get(route('gst-reports.export', [
            'type' => 'summary',
        ]));

        $response->assertOk();
        $content = $response->streamedContent();

        $this->assertStringContainsString('Output GST', $content);
        $this->assertStringContainsString('Input GST', $content);
        $this->assertStringContainsString('Net GST Payable', $content);
        $this->assertStringContainsString('162.00', $content);
        $this->assertStringContainsString('81.00', $content);
    }

    public function test_date_filter_limits_gst_sales_report(): void
    {
        [$gstSale] = $this->createSales();
        $outsideCustomer = Customer::create([
            'name' => 'Outside Customer',
            'gst_number' => '33OUTSIDE001',
        ]);
        $outsideSale = Sale::create([
            'sale_no' => 'GST-OUTSIDE',
            'customer_id' => $outsideCustomer->id,
            'sale_date' => '2026-05-01',
            'bill_type' => 'gst',
            'subtotal' => 200,
            'gst_amount' => 36,
            'total_amount' => 236,
            'paid_amount' => 0,
            'balance_amount' => 236,
            'payment_status' => 'pending',
            'payment_mode' => 'credit',
        ]);

        $response = $this->actingAs(User::factory()->create())->get(route('gst-reports.sales', [
            'from_date' => '2026-05-10',
            'to_date' => '2026-05-20',
        ]));

        $response->assertOk();
        $response->assertSee($gstSale->sale_no);
        $response->assertDontSee($outsideSale->sale_no);
    }

    private function createSales(): array
    {
        $customer = Customer::create([
            'name' => 'GST Customer',
            'gst_number' => '33GSTCUST001',
        ]);

        $gstSale = Sale::create([
            'sale_no' => 'GST-SALE-001',
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

        $normalSale = Sale::create([
            'sale_no' => 'NORMAL-SALE-001',
            'customer_id' => $customer->id,
            'sale_date' => '2026-05-13',
            'bill_type' => 'non_gst',
            'subtotal' => 800,
            'gst_amount' => 0,
            'total_amount' => 800,
            'paid_amount' => 300,
            'balance_amount' => 500,
            'payment_status' => 'partial',
            'payment_mode' => 'credit',
        ]);

        return [$gstSale, $normalSale];
    }

    private function createPurchases(): array
    {
        $supplier = Supplier::create([
            'name' => 'GST Supplier',
            'gst_number' => '33GSTSUPP001',
        ]);

        $gstPurchase = Purchase::create([
            'purchase_no' => 'GST-PUR-001',
            'supplier_id' => $supplier->id,
            'purchase_date' => '2026-05-13',
            'bill_type' => 'gst',
            'supplier_invoice_no' => 'SUP-GST-001',
            'subtotal' => 500,
            'gst_amount' => 90,
            'total_amount' => 590,
            'paid_amount' => 250,
            'balance_amount' => 340,
            'payment_status' => 'partial',
            'payment_mode' => 'credit',
        ]);

        $normalPurchase = Purchase::create([
            'purchase_no' => 'NORMAL-PUR-001',
            'supplier_id' => $supplier->id,
            'purchase_date' => '2026-05-13',
            'bill_type' => 'non_gst',
            'supplier_invoice_no' => 'SUP-NORMAL-001',
            'subtotal' => 400,
            'gst_amount' => 0,
            'total_amount' => 400,
            'paid_amount' => 0,
            'balance_amount' => 400,
            'payment_status' => 'pending',
            'payment_mode' => 'credit',
        ]);

        return [$gstPurchase, $normalPurchase];
    }

    private function createSalesReturn(Sale $sale, string $returnNo, float $subtotal, float $gst, float $total): SalesReturn
    {
        return SalesReturn::create([
            'return_no' => $returnNo,
            'sale_id' => $sale->id,
            'customer_id' => $sale->customer_id,
            'return_date' => '2026-05-14',
            'subtotal' => $subtotal,
            'gst_amount' => $gst,
            'total_amount' => $total,
            'refund_amount' => 0,
            'adjustment_amount' => $total,
        ]);
    }

    private function createPurchaseReturn(Purchase $purchase, string $returnNo, float $subtotal, float $gst, float $total): PurchaseReturn
    {
        return PurchaseReturn::create([
            'return_no' => $returnNo,
            'purchase_id' => $purchase->id,
            'supplier_id' => $purchase->supplier_id,
            'return_date' => '2026-05-14',
            'subtotal' => $subtotal,
            'gst_amount' => $gst,
            'total_amount' => $total,
            'refund_amount' => 0,
            'adjustment_amount' => $total,
        ]);
    }
}
