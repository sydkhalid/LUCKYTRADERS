<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Loan;
use App\Models\Partner;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Quotation;
use App\Models\Sale;
use App\Models\Supplier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class GlobalSearchController extends Controller
{
    public function search(Request $request): JsonResponse
    {
        $data = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        $term = trim((string) ($data['q'] ?? ''));

        if (mb_strlen($term) < 2) {
            return response()->json([
                'query' => $term,
                'groups' => [],
            ]);
        }

        $user = $request->user();
        $groups = collect();

        if ($user?->can('manage_sales')) {
            $groups->push($this->sales($term));
        }

        if ($user?->can('manage_purchases')) {
            $groups->push($this->purchases($term));
        }

        if ($user?->can('manage_customers')) {
            $groups->push($this->customers($term));
        }

        if ($user?->can('manage_suppliers')) {
            $groups->push($this->suppliers($term));
        }

        if ($user?->can('manage_products')) {
            $groups->push($this->products($term));
        }

        if ($user?->can('manage_quotations')) {
            $groups->push($this->quotations($term));
        }

        if ($user?->can('manage_loans')) {
            $groups->push($this->loans($term));
        }

        if ($user?->can('manage_partners')) {
            $groups->push($this->partners($term));
        }

        if ($user?->can('manage_receipts')) {
            $groups->push($this->receipts($term));
        }

        if ($user?->can('manage_payments')) {
            $groups->push($this->payments($term));
        }

        return response()->json([
            'query' => $term,
            'groups' => $groups
                ->filter(fn (array $group) => count($group['items']) > 0)
                ->values(),
        ]);
    }

    private function sales(string $term): array
    {
        $items = Sale::with('customer')
            ->where('sale_no', 'like', $this->like($term))
            ->latest('sale_date')
            ->limit(5)
            ->get()
            ->map(fn (Sale $sale) => [
                'title' => $sale->sale_no,
                'subtitle' => trim(($sale->customer?->name ?? 'Customer').' | '.strtoupper($sale->bill_type).' | ₹ '.number_format((float) $sale->total_amount, 2)),
                'url' => route('sales.show', $sale),
            ]);

        return $this->group('Sales Invoices', $items);
    }

    private function purchases(string $term): array
    {
        $items = Purchase::with('supplier')
            ->where(function ($query) use ($term): void {
                $query->where('purchase_no', 'like', $this->like($term))
                    ->orWhere('supplier_invoice_no', 'like', $this->like($term));
            })
            ->latest('purchase_date')
            ->limit(5)
            ->get()
            ->map(fn (Purchase $purchase) => [
                'title' => $purchase->purchase_no,
                'subtitle' => trim(($purchase->supplier?->name ?? 'Supplier').' | Supplier invoice: '.($purchase->supplier_invoice_no ?: '-')),
                'url' => route('purchases.show', $purchase),
            ]);

        return $this->group('Purchase Invoices', $items);
    }

    private function customers(string $term): array
    {
        $items = Customer::query()
            ->where(function ($query) use ($term): void {
                $query->where('name', 'like', $this->like($term))
                    ->orWhere('phone', 'like', $this->like($term))
                    ->orWhere('gst_number', 'like', $this->like($term));
            })
            ->orderBy('name')
            ->limit(5)
            ->get()
            ->map(fn (Customer $customer) => [
                'title' => $customer->name,
                'subtitle' => trim(($customer->phone ?: 'No phone').' | Balance ₹ '.number_format((float) $customer->current_balance, 2)),
                'url' => route('customers.show', $customer),
            ]);

        return $this->group('Customers', $items);
    }

    private function suppliers(string $term): array
    {
        $items = Supplier::query()
            ->where(function ($query) use ($term): void {
                $query->where('name', 'like', $this->like($term))
                    ->orWhere('phone', 'like', $this->like($term))
                    ->orWhere('gst_number', 'like', $this->like($term));
            })
            ->orderBy('name')
            ->limit(5)
            ->get()
            ->map(fn (Supplier $supplier) => [
                'title' => $supplier->name,
                'subtitle' => trim(($supplier->phone ?: 'No phone').' | Balance ₹ '.number_format((float) $supplier->current_balance, 2)),
                'url' => route('suppliers.show', $supplier),
            ]);

        return $this->group('Suppliers', $items);
    }

    private function products(string $term): array
    {
        $items = Product::with('category')
            ->where(function ($query) use ($term): void {
                $query->where('name', 'like', $this->like($term))
                    ->orWhere('code', 'like', $this->like($term));
            })
            ->orderBy('name')
            ->limit(5)
            ->get()
            ->map(fn (Product $product) => [
                'title' => $product->name.' ('.$product->code.')',
                'subtitle' => trim(($product->category?->name ?? 'Product').' | Stock '.number_format((float) $product->current_stock, 3).' '.$product->unit),
                'url' => route('products.show', $product),
            ]);

        return $this->group('Products', $items);
    }

    private function quotations(string $term): array
    {
        $items = Quotation::with('customer')
            ->where('quotation_no', 'like', $this->like($term))
            ->latest('quotation_date')
            ->limit(5)
            ->get()
            ->map(fn (Quotation $quotation) => [
                'title' => $quotation->quotation_no,
                'subtitle' => trim(($quotation->customer?->name ?? 'Customer').' | '.ucfirst($quotation->status).' | ₹ '.number_format((float) $quotation->total_amount, 2)),
                'url' => route('quotations.show', $quotation),
            ]);

        return $this->group('Quotations', $items);
    }

    private function loans(string $term): array
    {
        $items = Loan::query()
            ->where(function ($query) use ($term): void {
                $query->where('loan_no', 'like', $this->like($term))
                    ->orWhere('party_name', 'like', $this->like($term));
            })
            ->latest('loan_date')
            ->limit(5)
            ->get()
            ->map(fn (Loan $loan) => [
                'title' => $loan->loan_no,
                'subtitle' => trim($loan->party_name.' | '.$loan->typeLabel().' | Balance ₹ '.number_format((float) $loan->balance_amount, 2)),
                'url' => route('loans.show', $loan),
            ]);

        return $this->group('Loans', $items);
    }

    private function partners(string $term): array
    {
        $items = Partner::query()
            ->where(function ($query) use ($term): void {
                $query->where('name', 'like', $this->like($term))
                    ->orWhere('phone', 'like', $this->like($term));
            })
            ->orderBy('name')
            ->limit(5)
            ->get()
            ->map(fn (Partner $partner) => [
                'title' => $partner->name,
                'subtitle' => 'Share '.number_format((float) $partner->share_percentage, 2).'% | Investment ₹ '.number_format((float) $partner->current_investment, 2),
                'url' => route('partners.show', $partner),
            ]);

        return $this->group('Partners', $items);
    }

    private function receipts(string $term): array
    {
        $receipts = Payment::query()
            ->where('transaction_type', 'receipt')
            ->where('payment_no', 'like', $this->like($term))
            ->latest('payment_date')
            ->limit(5)
            ->get();
        $customerNames = Customer::whereIn('id', $receipts->where('party_type', 'customer')->pluck('party_id'))->pluck('name', 'id');

        $items = $receipts->map(fn (Payment $payment) => [
            'title' => $payment->payment_no,
            'subtitle' => trim(($customerNames[$payment->party_id] ?? ucfirst($payment->party_type)).' | ₹ '.number_format((float) $payment->amount, 2).' | '.ucfirst($payment->payment_mode)),
            'url' => route('receipts.show', $payment),
        ]);

        return $this->group('Receipts', $items);
    }

    private function payments(string $term): array
    {
        $payments = Payment::query()
            ->where('transaction_type', 'payment')
            ->where('payment_no', 'like', $this->like($term))
            ->latest('payment_date')
            ->limit(5)
            ->get();
        $supplierNames = Supplier::whereIn('id', $payments->where('party_type', 'supplier')->pluck('party_id'))->pluck('name', 'id');

        $items = $payments->map(fn (Payment $payment) => [
            'title' => $payment->payment_no,
            'subtitle' => trim(($supplierNames[$payment->party_id] ?? ucfirst($payment->party_type)).' | ₹ '.number_format((float) $payment->amount, 2).' | '.ucfirst($payment->payment_mode)),
            'url' => route('payments.show', $payment),
        ]);

        return $this->group('Payments', $items);
    }

    private function group(string $module, Collection $items): array
    {
        return [
            'module' => $module,
            'items' => $items->values()->all(),
        ];
    }

    private function like(string $term): string
    {
        return '%'.str_replace(['%', '_'], ['\\%', '\\_'], $term).'%';
    }
}
