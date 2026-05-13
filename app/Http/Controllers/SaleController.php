<?php

namespace App\Http\Controllers;

use App\Models\Cashbook;
use App\Models\Customer;
use App\Models\Ledger;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Sale;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SaleController extends Controller
{
    public function index()
    {
        $sales = Sale::with('customer')
            ->latest('sale_date')
            ->latest('id')
            ->paginate(15);

        return view('sales.index', compact('sales'));
    }

    public function create()
    {
        return view('sales.create', $this->formData());
    }

    public function store(Request $request)
    {
        $data = $this->validatedData($request);
        $totals = $this->calculateTotals($data);

        $sale = DB::transaction(function () use ($data, $totals) {
            $this->assertStockAvailable($totals['items']);

            $sale = Sale::create([
                'sale_no' => $this->nextSaleNo(),
                'customer_id' => $data['customer_id'],
                'sale_date' => $data['sale_date'],
                'bill_type' => $data['bill_type'],
                'subtotal' => $totals['subtotal'],
                'gst_amount' => $totals['gst_amount'],
                'total_amount' => $totals['total_amount'],
                'paid_amount' => $totals['paid_amount'],
                'balance_amount' => $totals['balance_amount'],
                'payment_status' => $this->paymentStatus($totals['paid_amount'], $totals['total_amount']),
                'payment_mode' => $data['payment_mode'],
                'notes' => $data['notes'] ?? null,
            ]);

            $this->createItemsAndStock($sale, $totals['items']);
            $this->postCustomerAccounting($sale);

            return $sale;
        });

        return redirect()
            ->route('sales.show', $sale)
            ->with('success', 'Sale '.$sale->sale_no.' created successfully.');
    }

    public function show(Sale $sale)
    {
        $sale->load(['customer', 'items.product']);

        return view('sales.show', compact('sale'));
    }

    public function edit(Request $request, Sale $sale)
    {
        $this->authorizeAdmin($request);
        $sale->load('items');

        return view('sales.edit', array_merge($this->formData(), compact('sale')));
    }

    public function update(Request $request, Sale $sale)
    {
        $this->authorizeAdmin($request);
        $this->ensureNoExternalReceipts($sale);

        $data = $this->validatedData($request);
        $totals = $this->calculateTotals($data);

        DB::transaction(function () use ($sale, $data, $totals) {
            $sale->load('items');
            $this->reverseSalePosting($sale);
            $this->assertStockAvailable($totals['items']);

            $sale->update([
                'customer_id' => $data['customer_id'],
                'sale_date' => $data['sale_date'],
                'bill_type' => $data['bill_type'],
                'subtotal' => $totals['subtotal'],
                'gst_amount' => $totals['gst_amount'],
                'total_amount' => $totals['total_amount'],
                'paid_amount' => $totals['paid_amount'],
                'balance_amount' => $totals['balance_amount'],
                'payment_status' => $this->paymentStatus($totals['paid_amount'], $totals['total_amount']),
                'payment_mode' => $data['payment_mode'],
                'notes' => $data['notes'] ?? null,
            ]);

            $sale->items()->delete();
            $this->deleteSaleReferences($sale);
            $this->createItemsAndStock($sale, $totals['items']);
            $this->postCustomerAccounting($sale);
        });

        return redirect()
            ->route('sales.show', $sale)
            ->with('success', 'Sale '.$sale->sale_no.' updated successfully.');
    }

    public function destroy(Request $request, Sale $sale)
    {
        $this->authorizeAdmin($request);
        $this->ensureNoExternalReceipts($sale);

        DB::transaction(function () use ($sale) {
            $sale->load('items');
            $this->reverseSalePosting($sale);
            $this->deleteSaleReferences($sale);
            $sale->delete();
        });

        return redirect()
            ->route('sales.index')
            ->with('success', 'Sale deleted and stock reversed successfully.');
    }

    public function printInvoice(Sale $sale)
    {
        $sale->load(['customer', 'items.product']);

        return view('sales.print', compact('sale'));
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
                'purchase_price' => (float) $product->purchase_price,
                'current_stock' => (float) $product->current_stock,
            ],
        ]);

        return compact('customers', 'products', 'productData');
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'sale_date' => ['required', 'date'],
            'bill_type' => ['required', Rule::in(['gst', 'non_gst'])],
            'paid_amount' => ['required', 'numeric', 'min:0'],
            'payment_mode' => ['required', Rule::in(['cash', 'bank', 'upi', 'credit'])],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.001'],
            'items.*.unit' => ['required', 'string', 'max:50'],
            'items.*.rate' => ['required', 'numeric', 'min:0'],
            'items.*.gst_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);
    }

    private function calculateTotals(array $data): array
    {
        $productIds = collect($data['items'])->pluck('product_id')->all();
        $products = Product::whereIn('id', $productIds)->get()->keyBy('id');
        $items = [];
        $subtotal = 0;
        $gstAmount = 0;

        foreach ($data['items'] as $item) {
            $product = $products[(int) $item['product_id']];
            $quantity = round((float) $item['quantity'], 3);
            $rate = round((float) $item['rate'], 2);
            $itemSubtotal = round($quantity * $rate, 2);
            $gstPercentage = $data['bill_type'] === 'gst' ? round((float) $item['gst_percentage'], 2) : 0;
            $itemGst = round($itemSubtotal * $gstPercentage / 100, 2);
            $itemTotal = round($itemSubtotal + $itemGst, 2);
            $purchaseCost = round($quantity * (float) $product->purchase_price, 2);

            $items[] = [
                'product_id' => $product->id,
                'quantity' => $quantity,
                'unit' => $item['unit'],
                'rate' => $rate,
                'subtotal' => $itemSubtotal,
                'gst_percentage' => $gstPercentage,
                'gst_amount' => $itemGst,
                'total' => $itemTotal,
                'purchase_cost' => $purchaseCost,
                'profit_amount' => round($itemSubtotal - $purchaseCost, 2),
            ];

            $subtotal += $itemSubtotal;
            $gstAmount += $itemGst;
        }

        $subtotal = round($subtotal, 2);
        $gstAmount = round($gstAmount, 2);
        $totalAmount = round($subtotal + $gstAmount, 2);
        $paidAmount = round((float) $data['paid_amount'], 2);

        if ($paidAmount > $totalAmount) {
            throw ValidationException::withMessages([
                'paid_amount' => 'Paid amount cannot be greater than sale total.',
            ]);
        }

        if ($paidAmount > 0 && $data['payment_mode'] === 'credit') {
            throw ValidationException::withMessages([
                'payment_mode' => 'Use cash, bank, or UPI when paid amount is entered.',
            ]);
        }

        return [
            'items' => $items,
            'subtotal' => $subtotal,
            'gst_amount' => $gstAmount,
            'total_amount' => $totalAmount,
            'paid_amount' => $paidAmount,
            'balance_amount' => round($totalAmount - $paidAmount, 2),
        ];
    }

    private function assertStockAvailable(array $items): void
    {
        $quantitiesByProduct = collect($items)
            ->groupBy('product_id')
            ->map(fn ($rows) => $rows->sum('quantity'));

        foreach ($quantitiesByProduct as $productId => $quantity) {
            $product = Product::whereKey($productId)->lockForUpdate()->firstOrFail();

            if ((float) $product->current_stock < (float) $quantity) {
                throw ValidationException::withMessages([
                    'items' => 'Insufficient stock for '.$product->name.'. Available: '.$product->current_stock.' '.$product->unit,
                ]);
            }
        }
    }

    private function createItemsAndStock(Sale $sale, array $items): void
    {
        foreach ($items as $item) {
            $sale->items()->create($item);

            Product::whereKey($item['product_id'])->decrement('current_stock', $item['quantity']);

            StockMovement::create([
                'product_id' => $item['product_id'],
                'movement_type' => 'sale_out',
                'reference_type' => 'sale',
                'reference_id' => $sale->id,
                'quantity' => $item['quantity'],
                'rate' => $item['rate'],
                'total_value' => $item['subtotal'],
                'movement_date' => $sale->sale_date,
                'remarks' => 'Sale '.$sale->sale_no,
            ]);
        }
    }

    private function postCustomerAccounting(Sale $sale): void
    {
        $customer = Customer::lockForUpdate()->findOrFail($sale->customer_id);
        $balanceAfterSale = round((float) $customer->current_balance + (float) $sale->total_amount, 2);
        $finalBalance = round($balanceAfterSale - (float) $sale->paid_amount, 2);

        Ledger::create([
            'ledger_date' => $sale->sale_date,
            'party_type' => 'customer',
            'party_id' => $customer->id,
            'reference_type' => 'sale',
            'reference_id' => $sale->id,
            'debit' => $sale->total_amount,
            'credit' => 0,
            'balance' => $balanceAfterSale,
            'remarks' => 'Sale '.$sale->sale_no,
        ]);

        if ((float) $sale->paid_amount > 0) {
            $payment = Payment::create([
                'payment_no' => $this->nextPaymentNo('RCPT'),
                'payment_date' => $sale->sale_date,
                'party_type' => 'customer',
                'party_id' => $customer->id,
                'transaction_type' => 'receipt',
                'reference_type' => 'sale_direct_payment',
                'reference_id' => $sale->id,
                'amount' => $sale->paid_amount,
                'payment_mode' => $sale->payment_mode,
                'notes' => 'Direct payment for '.$sale->sale_no,
            ]);

            Ledger::create([
                'ledger_date' => $sale->sale_date,
                'party_type' => 'customer',
                'party_id' => $customer->id,
                'reference_type' => 'sale_direct_payment',
                'reference_id' => $sale->id,
                'debit' => 0,
                'credit' => $sale->paid_amount,
                'balance' => $finalBalance,
                'remarks' => 'Sale payment '.$payment->payment_no,
            ]);

            Cashbook::create([
                'entry_date' => $sale->sale_date,
                'transaction_type' => $sale->payment_mode === 'cash' ? 'cash_in' : 'bank_in',
                'reference_type' => 'sale_direct_payment',
                'reference_id' => $sale->id,
                'amount' => $sale->paid_amount,
                'payment_mode' => $sale->payment_mode,
                'remarks' => $sale->sale_no.' - '.$customer->name,
            ]);
        }

        $customer->forceFill(['current_balance' => $finalBalance])->save();
    }

    private function reverseSalePosting(Sale $sale): void
    {
        foreach ($sale->items as $item) {
            Product::whereKey($item->product_id)->increment('current_stock', $item->quantity);
        }

        $customer = Customer::lockForUpdate()->findOrFail($sale->customer_id);
        $customer->forceFill([
            'current_balance' => round((float) $customer->current_balance - (float) $sale->balance_amount, 2),
        ])->save();
    }

    private function deleteSaleReferences(Sale $sale): void
    {
        StockMovement::where('reference_type', 'sale')->where('reference_id', $sale->id)->delete();
        Ledger::whereIn('reference_type', ['sale', 'sale_direct_payment'])->where('reference_id', $sale->id)->delete();
        Cashbook::where('reference_type', 'sale_direct_payment')->where('reference_id', $sale->id)->delete();
        Payment::where('reference_type', 'sale_direct_payment')->where('reference_id', $sale->id)->delete();
    }

    private function ensureNoExternalReceipts(Sale $sale): void
    {
        if (Payment::where('reference_type', 'sale')->where('reference_id', $sale->id)->exists()) {
            abort(409, 'This sale has customer receipts linked to it and cannot be edited or deleted.');
        }
    }

    private function paymentStatus(float $paidAmount, float $totalAmount): string
    {
        if ($paidAmount <= 0) {
            return 'pending';
        }

        return $paidAmount >= $totalAmount ? 'paid' : 'partial';
    }

    private function nextSaleNo(): string
    {
        $date = now()->format('Ymd');
        $sequence = Sale::where('sale_no', 'like', 'SAL-'.$date.'-%')->count() + 1;

        do {
            $saleNo = sprintf('SAL-%s-%05d', $date, $sequence++);
        } while (Sale::where('sale_no', $saleNo)->exists());

        return $saleNo;
    }

    private function nextPaymentNo(string $prefix): string
    {
        $date = now()->format('Ymd');
        $sequence = Payment::where('payment_no', 'like', $prefix.'-'.$date.'-%')->count() + 1;

        do {
            $paymentNo = sprintf('%s-%s-%05d', $prefix, $date, $sequence++);
        } while (Payment::where('payment_no', $paymentNo)->exists());

        return $paymentNo;
    }

    private function authorizeAdmin(Request $request): void
    {
        abort_unless((bool) $request->user()?->is_admin, 403);
    }
}
