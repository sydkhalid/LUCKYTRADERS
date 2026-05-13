<?php

namespace Tests\Feature;

use App\Models\Cashbook;
use App\Models\Ledger;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\StockAdjustment;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockAdjustmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_increase_adjustment_updates_product_stock_and_creates_stock_movement(): void
    {
        $product = $this->product(currentStock: 10);

        $response = $this->actingAs(User::factory()->create())->post(route('stock-adjustments.store'), [
            'adjustment_date' => '2026-05-13',
            'product_id' => $product->id,
            'adjustment_type' => 'increase',
            'reason' => 'excess',
            'quantity' => 5,
            'remarks' => 'Physical count excess',
        ]);

        $adjustment = StockAdjustment::firstOrFail();
        $response->assertRedirect(route('stock-adjustments.show', $adjustment));

        $this->assertStringStartsWith('ADJ-', $adjustment->adjustment_no);
        $this->assertSame(10.0, (float) $adjustment->old_stock);
        $this->assertSame(15.0, (float) $adjustment->new_stock);
        $this->assertSame(15.0, (float) $product->fresh()->current_stock);

        $movement = StockMovement::firstOrFail();
        $this->assertSame('adjustment', $movement->movement_type);
        $this->assertSame('stock_adjustment', $movement->reference_type);
        $this->assertSame($adjustment->id, $movement->reference_id);
        $this->assertSame(5.0, (float) $movement->quantity);
        $this->assertSame(250.0, (float) $movement->total_value);

        $this->assertSame(0, Ledger::count());
        $this->assertSame(0, Cashbook::count());
        $this->assertSame(0, Sale::count());
        $this->assertSame(0, Purchase::count());
    }

    public function test_decrease_adjustment_updates_product_stock(): void
    {
        $product = $this->product(currentStock: 10);

        $this->actingAs(User::factory()->create())->post(route('stock-adjustments.store'), [
            'adjustment_date' => '2026-05-13',
            'product_id' => $product->id,
            'adjustment_type' => 'decrease',
            'reason' => 'damage',
            'quantity' => 3,
            'remarks' => 'Damaged material',
        ]);

        $adjustment = StockAdjustment::firstOrFail();
        $this->assertSame('decrease', $adjustment->adjustment_type);
        $this->assertSame(10.0, (float) $adjustment->old_stock);
        $this->assertSame(7.0, (float) $adjustment->new_stock);
        $this->assertSame(7.0, (float) $product->fresh()->current_stock);
        $this->assertSame('adjustment', StockMovement::firstOrFail()->movement_type);
    }

    public function test_decrease_adjustment_cannot_exceed_available_stock(): void
    {
        $product = $this->product(currentStock: 4);

        $response = $this->actingAs(User::factory()->create())
            ->from(route('stock-adjustments.create'))
            ->post(route('stock-adjustments.store'), [
                'adjustment_date' => '2026-05-13',
                'product_id' => $product->id,
                'adjustment_type' => 'decrease',
                'reason' => 'shortage',
                'quantity' => 4.001,
            ]);

        $response->assertRedirect(route('stock-adjustments.create'));
        $response->assertSessionHasErrors('quantity');
        $this->assertSame(4.0, (float) $product->fresh()->current_stock);
        $this->assertSame(0, StockAdjustment::count());
        $this->assertSame(0, StockMovement::count());
    }

    public function test_stock_adjustment_pages_and_stock_history_render(): void
    {
        $product = $this->product(currentStock: 10);
        $adjustment = $this->createAdjustment($product);
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('stock-adjustments.index'))->assertOk()->assertSee($adjustment->adjustment_no);
        $this->actingAs($user)->get(route('stock-adjustments.show', $adjustment))->assertOk()->assertSee('Old Stock');
        $this->actingAs($user)->get(route('stock-adjustments.create'))->assertOk()->assertSee('Create Stock Adjustment');
        $this->actingAs($user)->get(route('stock-adjustments.products.history', $product))->assertOk()->assertSee($adjustment->adjustment_no);
        $this->actingAs($user)->get(route('stock-adjustments.movements'))->assertOk()->assertSee('Stock Movement Report');
        $this->actingAs($user)->get(route('products.index'))->assertOk()->assertSee('History');
    }

    public function test_stock_movement_report_filters_adjustments_by_product_and_date(): void
    {
        $firstProduct = $this->product('MS Flat', 'MS-FLAT', 10);
        $secondProduct = $this->product('MS Pipe', 'MS-PIPE', 10);

        $firstAdjustment = $this->createAdjustment($firstProduct, '2026-05-13', 2);
        $secondAdjustment = $this->createAdjustment($secondProduct, '2026-05-14', 3);
        $this->flushSession();

        $response = $this->actingAs(User::factory()->create())->get(route('stock-adjustments.movements', [
            'from_date' => '2026-05-13',
            'to_date' => '2026-05-13',
            'product_id' => $firstProduct->id,
            'movement_type' => 'adjustment',
        ]));

        $response->assertOk();
        $response->assertSee($firstAdjustment->adjustment_no);
        $response->assertSee('MS Flat');
        $response->assertDontSee($secondAdjustment->adjustment_no);
    }

    public function test_adjustments_do_not_enter_gst_reports(): void
    {
        $product = $this->product(currentStock: 10);
        $adjustment = $this->createAdjustment($product);
        $this->flushSession();

        $response = $this->actingAs(User::factory()->create())->get(route('gst-reports.sales'));

        $response->assertOk();
        $response->assertDontSee($adjustment->adjustment_no);
        $response->assertDontSee('Physical count excess');
    }

    private function product(string $name = 'MS Angle', string $code = 'MS-ANG', float $currentStock = 10): Product
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
            'selling_price' => 80,
            'gst_percentage' => 18,
            'opening_stock' => $currentStock,
            'current_stock' => $currentStock,
            'status' => 'active',
        ]);
    }

    private function createAdjustment(Product $product, string $date = '2026-05-13', float $quantity = 2): StockAdjustment
    {
        $this->actingAs(User::factory()->create())->post(route('stock-adjustments.store'), [
            'adjustment_date' => $date,
            'product_id' => $product->id,
            'adjustment_type' => 'increase',
            'reason' => 'excess',
            'quantity' => $quantity,
            'remarks' => 'Physical count excess',
        ]);

        return StockAdjustment::latest('id')->firstOrFail();
    }
}
