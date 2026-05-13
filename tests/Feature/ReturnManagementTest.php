<?php

namespace Tests\Feature;

use App\Models\Cashbook;
use App\Models\Customer;
use App\Models\Ledger;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\PurchaseReturn;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SalesReturn;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReturnManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_sales_return_increases_stock_updates_customer_ledger_and_posts_refund(): void
    {
        [$customer, $product, $sale] = $this->seedSale(balance: 236, customerBalance: 236, stock: 8);

        $response = $this->actingAs(User::factory()->create())->post(route('sales-returns.store'), [
            'sale_id' => $sale->id,
            'return_date' => '2026-05-13',
            'adjustment_amount' => 68,
            'refund_amount' => 50,
            'payment_mode' => 'upi',
            'notes' => 'Customer returned one bundle',
            'items' => [[
                'product_id' => $product->id,
                'quantity' => 1,
                'rate' => 100,
                'gst_percentage' => 18,
            ]],
        ]);

        $return = SalesReturn::firstOrFail();
        $response->assertRedirect(route('sales-returns.show', $return));

        $this->assertStringStartsWith('SRET-', $return->return_no);
        $this->assertSame(100.0, (float) $return->subtotal);
        $this->assertSame(18.0, (float) $return->gst_amount);
        $this->assertSame(118.0, (float) $return->total_amount);
        $this->assertSame(9.0, (float) $product->fresh()->current_stock);
        $this->assertSame(168.0, (float) $customer->fresh()->current_balance);
        $this->assertSame(168.0, (float) $sale->fresh()->balance_amount);

        $movement = StockMovement::firstOrFail();
        $this->assertSame('adjustment', $movement->movement_type);
        $this->assertSame('sales_return', $movement->reference_type);
        $this->assertSame($return->id, $movement->reference_id);

        $this->assertDatabaseHas('ledgers', [
            'party_type' => 'customer',
            'reference_type' => 'sales_return',
            'reference_id' => $return->id,
            'credit' => 118,
        ]);
        $this->assertDatabaseHas('ledgers', [
            'party_type' => 'customer',
            'reference_type' => 'sales_return_refund',
            'reference_id' => $return->id,
            'debit' => 50,
        ]);

        $cashbook = Cashbook::firstOrFail();
        $this->assertSame('bank_out', $cashbook->transaction_type);
        $this->assertSame('upi', $cashbook->payment_mode);
        $this->assertSame(50.0, (float) $cashbook->amount);
    }

    public function test_purchase_return_decreases_stock_updates_supplier_ledger_and_posts_refund_received(): void
    {
        [$supplier, $product, $purchase] = $this->seedPurchase(balance: 590, supplierBalance: 590, stock: 10);

        $response = $this->actingAs(User::factory()->create())->post(route('purchase-returns.store'), [
            'purchase_id' => $purchase->id,
            'return_date' => '2026-05-13',
            'adjustment_amount' => 68,
            'refund_amount' => 50,
            'payment_mode' => 'bank',
            'notes' => 'Returned defective goods',
            'items' => [[
                'product_id' => $product->id,
                'quantity' => 1,
                'rate' => 100,
                'gst_percentage' => 18,
            ]],
        ]);

        $return = PurchaseReturn::firstOrFail();
        $response->assertRedirect(route('purchase-returns.show', $return));

        $this->assertStringStartsWith('PRET-', $return->return_no);
        $this->assertSame(9.0, (float) $product->fresh()->current_stock);
        $this->assertSame(522.0, (float) $supplier->fresh()->current_balance);
        $this->assertSame(522.0, (float) $purchase->fresh()->balance_amount);

        $movement = StockMovement::firstOrFail();
        $this->assertSame('purchase_return', $movement->reference_type);
        $this->assertSame($return->id, $movement->reference_id);

        $this->assertDatabaseHas('ledgers', [
            'party_type' => 'supplier',
            'reference_type' => 'purchase_return',
            'reference_id' => $return->id,
            'debit' => 118,
        ]);
        $this->assertDatabaseHas('ledgers', [
            'party_type' => 'supplier',
            'reference_type' => 'purchase_return_refund',
            'reference_id' => $return->id,
            'credit' => 50,
        ]);

        $cashbook = Cashbook::firstOrFail();
        $this->assertSame('bank_in', $cashbook->transaction_type);
        $this->assertSame(50.0, (float) $cashbook->amount);
    }

    public function test_sales_return_cannot_exceed_remaining_sold_quantity(): void
    {
        [, $product, $sale] = $this->seedSale(balance: 236, customerBalance: 236, stock: 8);

        $response = $this->actingAs(User::factory()->create())
            ->from(route('sales-returns.create'))
            ->post(route('sales-returns.store'), [
                'sale_id' => $sale->id,
                'return_date' => '2026-05-13',
                'adjustment_amount' => 354,
                'refund_amount' => 0,
                'items' => [[
                    'product_id' => $product->id,
                    'quantity' => 3,
                    'rate' => 100,
                    'gst_percentage' => 18,
                ]],
            ]);

        $response->assertRedirect(route('sales-returns.create'));
        $response->assertSessionHasErrors('items');
        $this->assertSame(0, SalesReturn::count());
        $this->assertSame(8.0, (float) $product->fresh()->current_stock);
    }

    public function test_purchase_return_cannot_exceed_available_stock(): void
    {
        [, $product, $purchase] = $this->seedPurchase(balance: 590, supplierBalance: 590, stock: 0.5);

        $response = $this->actingAs(User::factory()->create())
            ->from(route('purchase-returns.create'))
            ->post(route('purchase-returns.store'), [
                'purchase_id' => $purchase->id,
                'return_date' => '2026-05-13',
                'adjustment_amount' => 118,
                'refund_amount' => 0,
                'items' => [[
                    'product_id' => $product->id,
                    'quantity' => 1,
                    'rate' => 100,
                    'gst_percentage' => 18,
                ]],
            ]);

        $response->assertRedirect(route('purchase-returns.create'));
        $response->assertSessionHasErrors('items');
        $this->assertSame(0, PurchaseReturn::count());
        $this->assertSame(0.5, (float) $product->fresh()->current_stock);
    }

    public function test_gst_summary_subtracts_gst_returns_and_ignores_non_gst_returns(): void
    {
        [$customer, $product, $gstSale] = $this->seedSale(balance: 1180, customerBalance: 1180, stock: 20, subtotal: 1000, gst: 180, total: 1180, billType: 'gst');
        [, , $nonGstSale] = $this->seedSale(balance: 500, customerBalance: 500, stock: 20, subtotal: 500, gst: 0, total: 500, billType: 'non_gst', saleNo: 'RET-NON-GST');
        [$supplier, $purchaseProduct, $gstPurchase] = $this->seedPurchase(balance: 590, supplierBalance: 590, stock: 20, subtotal: 500, gst: 90, total: 590, billType: 'gst');

        SalesReturn::create([
            'return_no' => 'GST-SRET-001',
            'sale_id' => $gstSale->id,
            'customer_id' => $customer->id,
            'return_date' => '2026-05-13',
            'subtotal' => 100,
            'gst_amount' => 18,
            'total_amount' => 118,
            'refund_amount' => 0,
            'adjustment_amount' => 118,
        ]);
        SalesReturn::create([
            'return_no' => 'NORMAL-SRET-001',
            'sale_id' => $nonGstSale->id,
            'customer_id' => $nonGstSale->customer_id,
            'return_date' => '2026-05-13',
            'subtotal' => 500,
            'gst_amount' => 0,
            'total_amount' => 500,
            'refund_amount' => 0,
            'adjustment_amount' => 500,
        ]);
        PurchaseReturn::create([
            'return_no' => 'GST-PRET-001',
            'purchase_id' => $gstPurchase->id,
            'supplier_id' => $supplier->id,
            'return_date' => '2026-05-13',
            'subtotal' => 100,
            'gst_amount' => 18,
            'total_amount' => 118,
            'refund_amount' => 0,
            'adjustment_amount' => 118,
        ]);

        $response = $this->actingAs(User::factory()->create())->get(route('gst-reports.index'));

        $response->assertOk();
        $response->assertSee('Rs. 900.00');
        $response->assertSee('Rs. 162.00');
        $response->assertSee('Rs. 400.00');
        $response->assertSee('Rs. 72.00');
        $response->assertSee('Rs. 90.00');
        $response->assertSee('Rs. 118.00');

        $export = $this->actingAs(User::factory()->create())->get(route('gst-reports.export', ['type' => 'sales']))->streamedContent();
        $this->assertStringContainsString('GST-SRET-001', $export);
        $this->assertStringNotContainsString('NORMAL-SRET-001', $export);
    }

    public function test_return_pages_and_reports_render(): void
    {
        [, $product, $sale] = $this->seedSale(balance: 236, customerBalance: 236, stock: 8);
        [, $purchaseProduct, $purchase] = $this->seedPurchase(balance: 590, supplierBalance: 590, stock: 10);
        $salesReturn = $this->createSalesReturn($sale, $product);
        $purchaseReturn = $this->createPurchaseReturn($purchase, $purchaseProduct);
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('sales-returns.index'))->assertOk()->assertSee($salesReturn->return_no);
        $this->actingAs($user)->get(route('sales-returns.show', $salesReturn))->assertOk()->assertSee('Sales Return Details');
        $this->actingAs($user)->get(route('sales-returns.create'))->assertOk()->assertSee('Create Sales Return');
        $this->actingAs($user)->get(route('sales-returns.report'))->assertOk()->assertSee($salesReturn->return_no);

        $this->actingAs($user)->get(route('purchase-returns.index'))->assertOk()->assertSee($purchaseReturn->return_no);
        $this->actingAs($user)->get(route('purchase-returns.show', $purchaseReturn))->assertOk()->assertSee('Purchase Return Details');
        $this->actingAs($user)->get(route('purchase-returns.create'))->assertOk()->assertSee('Create Purchase Return');
        $this->actingAs($user)->get(route('purchase-returns.report'))->assertOk()->assertSee($purchaseReturn->return_no);
    }

    private function seedSale(float $balance, float $customerBalance, float $stock, float $subtotal = 200, float $gst = 36, float $total = 236, string $billType = 'gst', string $saleNo = 'RET-SALE-001'): array
    {
        $customer = Customer::create([
            'name' => 'Return Customer '.$saleNo,
            'gst_number' => '33RETCUST',
            'current_balance' => $customerBalance,
        ]);
        $product = $this->product('Sales Return Product '.$saleNo, 'SRP-'.substr(md5($saleNo), 0, 8), $stock);
        $sale = Sale::create([
            'sale_no' => $saleNo,
            'customer_id' => $customer->id,
            'sale_date' => '2026-05-12',
            'bill_type' => $billType,
            'subtotal' => $subtotal,
            'gst_amount' => $gst,
            'total_amount' => $total,
            'paid_amount' => max($total - $balance, 0),
            'balance_amount' => $balance,
            'payment_status' => $balance > 0 ? 'partial' : 'paid',
            'payment_mode' => 'credit',
        ]);
        SaleItem::create([
            'sale_id' => $sale->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'unit' => 'Kg',
            'rate' => $subtotal / 2,
            'subtotal' => $subtotal,
            'gst_percentage' => $billType === 'gst' ? 18 : 0,
            'gst_amount' => $gst,
            'total' => $total,
            'purchase_cost' => 100,
            'profit_amount' => 100,
        ]);

        return [$customer, $product, $sale];
    }

    private function seedPurchase(float $balance, float $supplierBalance, float $stock, float $subtotal = 500, float $gst = 90, float $total = 590, string $billType = 'gst'): array
    {
        $supplier = Supplier::create([
            'name' => 'Return Supplier',
            'gst_number' => '33RETSUPP',
            'current_balance' => $supplierBalance,
        ]);
        $product = $this->product('Purchase Return Product', 'PRP-001', $stock);
        $purchase = Purchase::create([
            'purchase_no' => 'RET-PUR-001',
            'supplier_id' => $supplier->id,
            'purchase_date' => '2026-05-12',
            'bill_type' => $billType,
            'supplier_invoice_no' => 'RET-SUP-001',
            'subtotal' => $subtotal,
            'gst_amount' => $gst,
            'total_amount' => $total,
            'paid_amount' => max($total - $balance, 0),
            'balance_amount' => $balance,
            'payment_status' => $balance > 0 ? 'partial' : 'paid',
            'payment_mode' => 'credit',
        ]);
        PurchaseItem::create([
            'purchase_id' => $purchase->id,
            'product_id' => $product->id,
            'quantity' => 5,
            'unit' => 'Kg',
            'rate' => $subtotal / 5,
            'subtotal' => $subtotal,
            'gst_percentage' => $billType === 'gst' ? 18 : 0,
            'gst_amount' => $gst,
            'total' => $total,
        ]);

        return [$supplier, $product, $purchase];
    }

    private function product(string $name, string $code, float $stock): Product
    {
        $category = ProductCategory::create([
            'name' => $name.' Category',
            'status' => 'active',
        ]);

        return Product::create([
            'product_category_id' => $category->id,
            'name' => $name,
            'code' => $code,
            'unit' => 'Kg',
            'purchase_price' => 50,
            'selling_price' => 100,
            'gst_percentage' => 18,
            'opening_stock' => $stock,
            'current_stock' => $stock,
            'status' => 'active',
        ]);
    }

    private function createSalesReturn(Sale $sale, Product $product): SalesReturn
    {
        $this->actingAs(User::factory()->create())->post(route('sales-returns.store'), [
            'sale_id' => $sale->id,
            'return_date' => '2026-05-13',
            'adjustment_amount' => 118,
            'refund_amount' => 0,
            'items' => [[
                'product_id' => $product->id,
                'quantity' => 1,
                'rate' => 100,
                'gst_percentage' => 18,
            ]],
        ]);

        return SalesReturn::latest('id')->firstOrFail();
    }

    private function createPurchaseReturn(Purchase $purchase, Product $product): PurchaseReturn
    {
        $this->actingAs(User::factory()->create())->post(route('purchase-returns.store'), [
            'purchase_id' => $purchase->id,
            'return_date' => '2026-05-13',
            'adjustment_amount' => 118,
            'refund_amount' => 0,
            'items' => [[
                'product_id' => $product->id,
                'quantity' => 1,
                'rate' => 100,
                'gst_percentage' => 18,
            ]],
        ]);

        return PurchaseReturn::latest('id')->firstOrFail();
    }
}
