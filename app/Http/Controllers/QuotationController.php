<?php

namespace App\Http\Controllers;

use App\Http\Requests\Quotations\ConvertQuotationRequest;
use App\Http\Requests\Quotations\StoreQuotationRequest;
use App\Http\Requests\Quotations\UpdateQuotationRequest;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Quotation;
use App\Services\QuotationService;
use Illuminate\Http\Request;

class QuotationController extends Controller
{
    public function index()
    {
        $quotations = Quotation::with('customer')
            ->latest('quotation_date')
            ->latest('id')
            ->paginate(20);

        $draftCount = Quotation::where('status', 'draft')->count();
        $acceptedCount = Quotation::where('status', 'accepted')->count();
        $convertedCount = Quotation::where('status', 'converted')->count();

        return view('quotations.index', compact('quotations', 'draftCount', 'acceptedCount', 'convertedCount'));
    }

    public function create()
    {
        return view('quotations.create', $this->formData());
    }

    public function store(StoreQuotationRequest $request, QuotationService $quotationService)
    {
        $quotation = $quotationService->createQuotation($request->validated());

        return redirect()
            ->route('quotations.show', $quotation)
            ->with('success', 'Quotation '.$quotation->quotation_no.' created successfully.');
    }

    public function show(Quotation $quotation)
    {
        $quotation->load(['customer', 'items.product']);

        return view('quotations.show', compact('quotation'));
    }

    public function edit(Quotation $quotation)
    {
        if ($quotation->status === 'converted') {
            return redirect()
                ->route('quotations.show', $quotation)
                ->with('error', 'Converted quotations cannot be edited.');
        }

        $quotation->load('items');

        return view('quotations.edit', array_merge($this->formData(), compact('quotation')));
    }

    public function update(UpdateQuotationRequest $request, Quotation $quotation, QuotationService $quotationService)
    {
        $quotationService->updateQuotation($quotation, $request->validated());

        return redirect()
            ->route('quotations.show', $quotation)
            ->with('success', 'Quotation '.$quotation->quotation_no.' updated successfully.');
    }

    public function print(Quotation $quotation)
    {
        $quotation->load(['customer', 'items.product']);

        return view('quotations.print', compact('quotation'));
    }

    public function convert(Quotation $quotation, Request $request, QuotationService $quotationService)
    {
        if ($quotation->status !== 'accepted') {
            return redirect()
                ->route('quotations.show', $quotation)
                ->with('error', 'Only accepted quotations can be converted to sale.');
        }

        $quotation->load(['customer', 'items.product']);
        $selectedBillType = $request->query('bill_type', 'gst');
        $selectedBillType = in_array($selectedBillType, ['gst', 'non_gst'], true) ? $selectedBillType : 'gst';
        $gstPreview = $quotationService->conversionPreview($quotation, 'gst');
        $nonGstPreview = $quotationService->conversionPreview($quotation, 'non_gst');

        return view('quotations.convert', compact('quotation', 'selectedBillType', 'gstPreview', 'nonGstPreview'));
    }

    public function storeConversion(ConvertQuotationRequest $request, Quotation $quotation, QuotationService $quotationService)
    {
        $sale = $quotationService->convertToSale($quotation, $request->validated());

        return redirect()
            ->route('sales.show', $sale)
            ->with('success', 'Quotation '.$quotation->quotation_no.' converted to sale '.$sale->sale_no.'.');
    }

    private function formData(): array
    {
        $customers = Customer::where('status', 'active')->orderBy('name')->get();
        $products = Product::where('status', 'active')->orderBy('name')->get();
        $productData = $products->mapWithKeys(fn (Product $product) => [
            $product->id => [
                'unit' => $product->unit,
                'rate' => (float) $product->selling_price,
                'gst_percentage' => (float) $product->gst_percentage,
                'current_stock' => (float) $product->current_stock,
            ],
        ]);
        $statuses = array_filter(
            Quotation::STATUSES,
            fn (string $status) => $status !== 'Converted'
        );

        return compact('customers', 'products', 'productData', 'statuses');
    }
}
