<?php

namespace Tests\Feature;

use App\Models\Cashbook;
use App\Models\Customer;
use App\Models\Ledger;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\Sale;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuotationManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_quotation_can_be_created_without_stock_or_accounting_posting(): void
    {
        [$customer, $product] = $this->seedCustomerAndProduct();

        $response = $this->actingAs(User::factory()->create())->post(route('quotations.store'), [
            'customer_id' => $customer->id,
            'quotation_date' => '2026-05-13',
            'valid_until' => '2026-05-20',
            'status' => 'sent',
            'notes' => 'Steel supply quotation',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 2,
                    'unit' => 'Kg',
                    'rate' => 100,
                    'gst_percentage' => 18,
                ],
            ],
        ]);

        $quotation = Quotation::firstOrFail();
        $response->assertRedirect(route('quotations.show', $quotation));

        $this->assertStringStartsWith('QTN-', $quotation->quotation_no);
        $this->assertSame(200.0, (float) $quotation->subtotal);
        $this->assertSame(36.0, (float) $quotation->gst_amount);
        $this->assertSame(236.0, (float) $quotation->total_amount);
        $this->assertSame('sent', $quotation->status);
        $this->assertSame(1, QuotationItem::count());
        $this->assertSame(10.0, (float) $product->fresh()->current_stock);
        $this->assertSame(0, Ledger::count());
        $this->assertSame(0, Cashbook::count());
        $this->assertSame(0, StockMovement::count());
    }

    public function test_quotation_does_not_enter_gst_reports_before_conversion(): void
    {
        [$customer, $product] = $this->seedCustomerAndProduct();
        $quotation = $this->createQuotation($customer, $product, 'accepted');

        $this->assertSame(36.0, (float) $quotation->gst_amount);

        $response = $this->actingAs(User::factory()->create())->get(route('gst-reports.sales', [
            'from_date' => '2026-05-01',
            'to_date' => '2026-05-31',
        ]));

        $response->assertOk();
        $response->assertSee('No GST sales found.');
        $response->assertDontSee('Rs. 36.00');
        $this->assertSame(0, Sale::count());
    }

    public function test_quotation_can_be_updated_and_marked_accepted(): void
    {
        [$customer, $product] = $this->seedCustomerAndProduct();
        $quotation = $this->createQuotation($customer, $product, 'draft');

        $response = $this->actingAs(User::factory()->create())->put(route('quotations.update', $quotation), [
            'customer_id' => $customer->id,
            'quotation_date' => '2026-05-14',
            'valid_until' => '2026-05-25',
            'status' => 'accepted',
            'notes' => 'Accepted by customer',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 3,
                    'unit' => 'Kg',
                    'rate' => 120,
                    'gst_percentage' => 18,
                ],
            ],
        ]);

        $response->assertRedirect(route('quotations.show', $quotation));

        $quotation->refresh();
        $this->assertSame('accepted', $quotation->status);
        $this->assertSame(360.0, (float) $quotation->subtotal);
        $this->assertSame(64.8, (float) $quotation->gst_amount);
        $this->assertSame(424.8, (float) $quotation->total_amount);
        $this->assertSame(10.0, (float) $product->fresh()->current_stock);
        $this->assertSame(0, Ledger::count());
        $this->assertSame(0, Cashbook::count());
    }

    public function test_accepted_quotation_converts_to_gst_sale_and_posts_sale_effects(): void
    {
        [$customer, $product] = $this->seedCustomerAndProduct();
        $quotation = $this->createQuotation($customer, $product, 'accepted');

        $response = $this->actingAs(User::factory()->create())->post(route('quotations.convert.store', $quotation), [
            'sale_date' => '2026-05-15',
            'bill_type' => 'gst',
        ]);

        $sale = Sale::firstOrFail();
        $response->assertRedirect(route('sales.show', $sale));

        $quotation->refresh();
        $this->assertSame('converted', $quotation->status);
        $this->assertSame('gst', $sale->bill_type);
        $this->assertSame(200.0, (float) $sale->subtotal);
        $this->assertSame(36.0, (float) $sale->gst_amount);
        $this->assertSame(236.0, (float) $sale->total_amount);
        $this->assertSame(0.0, (float) $sale->paid_amount);
        $this->assertSame(236.0, (float) $sale->balance_amount);
        $this->assertSame('pending', $sale->payment_status);
        $this->assertSame('credit', $sale->payment_mode);
        $this->assertStringContainsString($quotation->quotation_no, $sale->notes);
        $this->assertSame(8.0, (float) $product->fresh()->current_stock);
        $this->assertSame(1, StockMovement::count());
        $this->assertSame(0, Cashbook::count());
        $this->assertSame(0, Payment::count());

        $ledger = Ledger::firstOrFail();
        $this->assertSame('customer', $ledger->party_type);
        $this->assertSame($customer->id, $ledger->party_id);
        $this->assertSame('sale', $ledger->reference_type);
        $this->assertSame($sale->id, $ledger->reference_id);
        $this->assertSame(236.0, (float) $ledger->debit);
        $this->assertSame(236.0, (float) $customer->fresh()->current_balance);
    }

    public function test_quotation_conversion_requires_available_stock(): void
    {
        [$customer, $product] = $this->seedCustomerAndProduct();
        $quotation = $this->createQuotation($customer, $product, 'accepted');
        $product->forceFill(['current_stock' => 1])->save();

        $response = $this
            ->actingAs(User::factory()->create())
            ->from(route('quotations.convert', $quotation))
            ->post(route('quotations.convert.store', $quotation), [
                'sale_date' => '2026-05-15',
                'bill_type' => 'gst',
            ]);

        $response->assertRedirect(route('quotations.convert', $quotation));
        $response->assertSessionHasErrors('items');
        $this->assertSame('accepted', $quotation->fresh()->status);
        $this->assertSame(0, Sale::count());
        $this->assertSame(1.0, (float) $product->fresh()->current_stock);
        $this->assertSame(0, StockMovement::count());
        $this->assertSame(0, Ledger::count());
    }

    public function test_accepted_quotation_converts_to_non_gst_sale_with_zero_gst(): void
    {
        [$customer, $product] = $this->seedCustomerAndProduct();
        $quotation = $this->createQuotation($customer, $product, 'accepted');

        $this->actingAs(User::factory()->create())->post(route('quotations.convert.store', $quotation), [
            'sale_date' => '2026-05-15',
            'bill_type' => 'non_gst',
        ]);

        $sale = Sale::firstOrFail();
        $item = $sale->items()->firstOrFail();

        $this->assertSame('non_gst', $sale->bill_type);
        $this->assertSame(200.0, (float) $sale->subtotal);
        $this->assertSame(0.0, (float) $sale->gst_amount);
        $this->assertSame(200.0, (float) $sale->total_amount);
        $this->assertSame(0.0, (float) $item->gst_percentage);
        $this->assertSame(0.0, (float) $item->gst_amount);
    }

    public function test_draft_quotation_cannot_be_converted(): void
    {
        [$customer, $product] = $this->seedCustomerAndProduct();
        $quotation = $this->createQuotation($customer, $product, 'draft');

        $response = $this->actingAs(User::factory()->create())
            ->from(route('quotations.convert', $quotation))
            ->post(route('quotations.convert.store', $quotation), [
                'sale_date' => '2026-05-15',
                'bill_type' => 'gst',
            ]);

        $response->assertRedirect(route('quotations.convert', $quotation));
        $response->assertSessionHasErrors('status');
        $this->assertSame('draft', $quotation->fresh()->status);
        $this->assertSame(0, Sale::count());
        $this->assertSame(10.0, (float) $product->fresh()->current_stock);
    }

    public function test_converted_quotation_cannot_be_edited(): void
    {
        [$customer, $product] = $this->seedCustomerAndProduct();
        $quotation = $this->createQuotation($customer, $product, 'accepted');

        $this->actingAs(User::factory()->create())->post(route('quotations.convert.store', $quotation), [
            'sale_date' => '2026-05-15',
            'bill_type' => 'gst',
        ]);

        $response = $this
            ->actingAs(User::factory()->create())
            ->get(route('quotations.edit', $quotation->fresh()));

        $response->assertRedirect(route('quotations.show', $quotation));

        $this
            ->actingAs(User::factory()->create())
            ->from(route('quotations.show', $quotation))
            ->put(route('quotations.update', $quotation), [
                'customer_id' => $customer->id,
                'quotation_date' => '2026-05-16',
                'valid_until' => '2026-05-20',
                'status' => 'accepted',
                'items' => [
                    [
                        'product_id' => $product->id,
                        'quantity' => 1,
                        'unit' => 'Kg',
                        'rate' => 150,
                        'gst_percentage' => 18,
                    ],
                ],
            ])
            ->assertSessionHasErrors('status');

        $quotation->refresh();
        $this->assertSame('converted', $quotation->status);
        $this->assertSame(200.0, (float) $quotation->subtotal);
    }

    public function test_quotation_pages_render(): void
    {
        [$customer, $product] = $this->seedCustomerAndProduct();
        $quotation = $this->createQuotation($customer, $product, 'accepted');
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('quotations.index'))->assertOk()->assertSee($quotation->quotation_no);
        $this->actingAs($user)->get(route('quotations.show', $quotation))->assertOk()->assertSee('Convert to Sale');
        $this->actingAs($user)->get(route('quotations.edit', $quotation))->assertOk()->assertSee('Save Quotation');
        $this->actingAs($user)->get(route('quotations.print', $quotation))->assertOk()->assertSee('QUOTATION');
        $this->actingAs($user)->get(route('quotations.convert', $quotation))->assertOk()->assertSee('GST Invoice');
    }

    private function seedCustomerAndProduct(): array
    {
        $customer = Customer::create([
            'name' => 'Quotation Customer',
            'phone' => '9000000000',
            'gst_number' => '33QUOTE1234',
            'status' => 'active',
        ]);
        $category = ProductCategory::create([
            'name' => 'Steel',
            'status' => 'active',
        ]);
        $product = Product::create([
            'product_category_id' => $category->id,
            'name' => 'MS Angle',
            'code' => 'MS-ANG',
            'unit' => 'Kg',
            'purchase_price' => 60,
            'selling_price' => 100,
            'gst_percentage' => 18,
            'current_stock' => 10,
            'status' => 'active',
        ]);

        return [$customer, $product];
    }

    private function createQuotation(Customer $customer, Product $product, string $status): Quotation
    {
        $this->actingAs(User::factory()->create())->post(route('quotations.store'), [
            'customer_id' => $customer->id,
            'quotation_date' => '2026-05-13',
            'valid_until' => '2026-05-20',
            'status' => $status,
            'notes' => 'Quotation note',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 2,
                    'unit' => 'Kg',
                    'rate' => 100,
                    'gst_percentage' => 18,
                ],
            ],
        ]);

        return Quotation::latest('id')->firstOrFail();
    }
}
