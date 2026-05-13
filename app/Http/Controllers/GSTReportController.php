<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\Sale;
use App\Models\SalesReturn;
use App\Models\Supplier;
use App\Services\ActivityLogger;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class GSTReportController extends Controller
{
    public function index(Request $request)
    {
        $filters = $this->filters($request);
        $salesTotals = $this->documentTotals($this->salesQuery($filters, 'gst'));
        $salesReturnTotals = $this->returnTotals($this->salesReturnQuery($filters, 'gst'));
        $purchaseTotals = $this->documentTotals($this->purchasesQuery($filters, 'gst'));
        $purchaseReturnTotals = $this->returnTotals($this->purchaseReturnQuery($filters, 'gst'));
        $nonGstSalesTotal = (float) $this->salesQuery($filters, 'non_gst')->sum('total_amount');
        $outputGst = $salesTotals['gst'] - $salesReturnTotals['gst'];
        $inputGst = $purchaseTotals['gst'] - $purchaseReturnTotals['gst'];

        $summary = [
            'gross_taxable_sales' => $salesTotals['taxable'],
            'gross_output_gst' => $salesTotals['gst'],
            'sales_returns_taxable' => $salesReturnTotals['taxable'],
            'sales_returns_gst' => $salesReturnTotals['gst'],
            'taxable_sales' => $salesTotals['taxable'] - $salesReturnTotals['taxable'],
            'output_gst' => $outputGst,
            'total_sales' => $salesTotals['total'] - $salesReturnTotals['total'],
            'gross_taxable_purchases' => $purchaseTotals['taxable'],
            'gross_input_gst' => $purchaseTotals['gst'],
            'purchase_returns_taxable' => $purchaseReturnTotals['taxable'],
            'purchase_returns_gst' => $purchaseReturnTotals['gst'],
            'taxable_purchases' => $purchaseTotals['taxable'] - $purchaseReturnTotals['taxable'],
            'input_gst' => $inputGst,
            'total_purchases' => $purchaseTotals['total'] - $purchaseReturnTotals['total'],
            'net_gst_payable' => $outputGst - $inputGst,
            'non_gst_sales' => $nonGstSalesTotal,
            'sales_returns' => $salesReturnTotals['total'],
            'purchase_returns' => $purchaseReturnTotals['total'],
        ];

        return view('gst-reports.index', array_merge($this->filterOptions(), compact('filters', 'summary')));
    }

    public function sales(Request $request)
    {
        $filters = $this->filters($request);
        $sales = $this->salesQuery($filters, 'gst')
            ->latest('sale_date')
            ->latest('id')
            ->paginate(20)
            ->withQueryString();
        $totals = $this->salesTotals($filters, 'gst');

        return view('gst-reports.sales', array_merge($this->filterOptions(), compact('filters', 'sales', 'totals')));
    }

    public function purchases(Request $request)
    {
        $filters = $this->filters($request);
        $purchases = $this->purchasesQuery($filters, 'gst')
            ->latest('purchase_date')
            ->latest('id')
            ->paginate(20)
            ->withQueryString();
        $totals = $this->purchaseTotals($filters, 'gst');

        return view('gst-reports.purchases', array_merge($this->filterOptions(), compact('filters', 'purchases', 'totals')));
    }

    public function salesReturns(Request $request)
    {
        $filters = $this->filters($request);
        $returns = $this->salesReturnQuery($filters, 'gst')
            ->latest('return_date')
            ->latest('id')
            ->paginate(20)
            ->withQueryString();
        $totals = $this->returnTotals($this->salesReturnQuery($filters, 'gst'));

        return view('gst-reports.sales-returns', array_merge($this->filterOptions(), compact('filters', 'returns', 'totals')));
    }

    public function purchaseReturns(Request $request)
    {
        $filters = $this->filters($request);
        $returns = $this->purchaseReturnQuery($filters, 'gst')
            ->latest('return_date')
            ->latest('id')
            ->paginate(20)
            ->withQueryString();
        $totals = $this->returnTotals($this->purchaseReturnQuery($filters, 'gst'));

        return view('gst-reports.purchase-returns', array_merge($this->filterOptions(), compact('filters', 'returns', 'totals')));
    }

    public function nonGstSales(Request $request)
    {
        $filters = $this->filters($request);
        $sales = $this->salesQuery($filters, 'non_gst')
            ->latest('sale_date')
            ->latest('id')
            ->paginate(20)
            ->withQueryString();
        $totals = $this->salesTotals($filters, 'non_gst');

        return view('gst-reports.non-gst-sales', array_merge($this->filterOptions(), compact('filters', 'sales', 'totals')));
    }

    public function export(Request $request): StreamedResponse
    {
        $filters = $this->filters($request);
        $type = $request->validate([
            'type' => ['nullable', 'in:sales,purchases,sales_returns,purchase_returns,summary,all'],
        ])['type'] ?? 'all';
        $fileName = 'gst-auditor-export-'.$type.'-'.now()->format('YmdHis').'.csv';

        app(ActivityLogger::class)->log(
            'export_report',
            'gst_reports',
            'GST auditor export generated',
            null,
            [],
            ['format' => 'csv', 'type' => $type, 'filters' => $filters, 'file_name' => $fileName]
        );

        return response()->streamDownload(function () use ($filters, $type) {
            $handle = fopen('php://output', 'w');

            if ($type === 'sales') {
                $this->writeSalesExport($handle, $filters);
            } elseif ($type === 'purchases') {
                $this->writePurchaseExport($handle, $filters);
            } elseif ($type === 'sales_returns') {
                $this->writeSalesReturnExport($handle, $filters);
            } elseif ($type === 'purchase_returns') {
                $this->writePurchaseReturnExport($handle, $filters);
            } elseif ($type === 'summary') {
                $this->writeSummaryExport($handle, $filters);
            } else {
                $this->writeFullAuditorExport($handle, $filters);
            }

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function filters(Request $request): array
    {
        $validated = $request->validate([
            'from_date' => ['nullable', 'date'],
            'to_date' => ['nullable', 'date', 'after_or_equal:from_date'],
            'customer_id' => ['nullable', 'exists:customers,id'],
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'bill_type' => ['nullable', 'in:all,gst,non_gst'],
            'payment_status' => ['nullable', 'in:pending,partial,paid'],
        ]);

        return [
            'from_date' => $validated['from_date'] ?? null,
            'to_date' => $validated['to_date'] ?? null,
            'customer_id' => $validated['customer_id'] ?? null,
            'supplier_id' => $validated['supplier_id'] ?? null,
            'bill_type' => $validated['bill_type'] ?? null,
            'payment_status' => $validated['payment_status'] ?? null,
        ];
    }

    private function filterOptions(): array
    {
        return [
            'customers' => Customer::orderBy('name')->get(['id', 'name', 'gst_number']),
            'suppliers' => Supplier::orderBy('name')->get(['id', 'name', 'gst_number']),
            'paymentStatuses' => [
                'pending' => 'Pending',
                'partial' => 'Partial',
                'paid' => 'Paid',
            ],
            'billTypes' => [
                'all' => 'All Bill Types',
                'gst' => 'GST',
                'non_gst' => 'Non-GST',
            ],
        ];
    }

    private function salesQuery(array $filters, string $billType): Builder
    {
        return Sale::with('customer')
            ->where('bill_type', $billType)
            ->when($this->billTypeConflicts($filters, $billType), fn ($query) => $query->whereRaw('1 = 0'))
            ->when($filters['from_date'] ?? null, fn ($query, $date) => $query->whereDate('sale_date', '>=', $date))
            ->when($filters['to_date'] ?? null, fn ($query, $date) => $query->whereDate('sale_date', '<=', $date))
            ->when($filters['customer_id'] ?? null, fn ($query, $customerId) => $query->where('customer_id', $customerId))
            ->when($filters['payment_status'] ?? null, fn ($query, $status) => $query->where('payment_status', $status));
    }

    private function purchasesQuery(array $filters, string $billType): Builder
    {
        return Purchase::with('supplier')
            ->where('bill_type', $billType)
            ->when($this->billTypeConflicts($filters, $billType), fn ($query) => $query->whereRaw('1 = 0'))
            ->when($filters['from_date'] ?? null, fn ($query, $date) => $query->whereDate('purchase_date', '>=', $date))
            ->when($filters['to_date'] ?? null, fn ($query, $date) => $query->whereDate('purchase_date', '<=', $date))
            ->when($filters['supplier_id'] ?? null, fn ($query, $supplierId) => $query->where('supplier_id', $supplierId))
            ->when($filters['payment_status'] ?? null, fn ($query, $status) => $query->where('payment_status', $status));
    }

    private function salesReturnQuery(array $filters, string $billType): Builder
    {
        return SalesReturn::with(['sale.customer', 'customer'])
            ->when($this->billTypeConflicts($filters, $billType), fn ($query) => $query->whereRaw('1 = 0'))
            ->whereHas('sale', function ($query) use ($filters, $billType): void {
                $query->where('bill_type', $billType)
                    ->when($filters['payment_status'] ?? null, fn ($query, $status) => $query->where('payment_status', $status));
            })
            ->when($filters['from_date'] ?? null, fn ($query, $date) => $query->whereDate('return_date', '>=', $date))
            ->when($filters['to_date'] ?? null, fn ($query, $date) => $query->whereDate('return_date', '<=', $date))
            ->when($filters['customer_id'] ?? null, fn ($query, $customerId) => $query->where('customer_id', $customerId));
    }

    private function purchaseReturnQuery(array $filters, string $billType): Builder
    {
        return PurchaseReturn::with(['purchase.supplier', 'supplier'])
            ->when($this->billTypeConflicts($filters, $billType), fn ($query) => $query->whereRaw('1 = 0'))
            ->whereHas('purchase', function ($query) use ($filters, $billType): void {
                $query->where('bill_type', $billType)
                    ->when($filters['payment_status'] ?? null, fn ($query, $status) => $query->where('payment_status', $status));
            })
            ->when($filters['from_date'] ?? null, fn ($query, $date) => $query->whereDate('return_date', '>=', $date))
            ->when($filters['to_date'] ?? null, fn ($query, $date) => $query->whereDate('return_date', '<=', $date))
            ->when($filters['supplier_id'] ?? null, fn ($query, $supplierId) => $query->where('supplier_id', $supplierId));
    }

    private function billTypeConflicts(array $filters, string $requiredBillType): bool
    {
        return ! empty($filters['bill_type'])
            && $filters['bill_type'] !== 'all'
            && $filters['bill_type'] !== $requiredBillType;
    }

    private function salesTotals(array $filters, string $billType): array
    {
        $totals = $this->documentTotals($this->salesQuery($filters, $billType));
        $returns = $this->returnTotals($this->salesReturnQuery($filters, $billType));

        return array_merge($totals, [
            'returns' => $returns['total'],
        ]);
    }

    private function purchaseTotals(array $filters, string $billType): array
    {
        $totals = $this->documentTotals($this->purchasesQuery($filters, $billType));
        $returns = $this->returnTotals($this->purchaseReturnQuery($filters, $billType));

        return array_merge($totals, [
            'returns' => $returns['total'],
        ]);
    }

    private function documentTotals(Builder $query): array
    {
        return [
            'taxable' => (float) (clone $query)->sum('subtotal'),
            'gst' => (float) (clone $query)->sum('gst_amount'),
            'total' => (float) (clone $query)->sum('total_amount'),
            'paid' => (float) (clone $query)->sum('paid_amount'),
            'balance' => (float) (clone $query)->sum('balance_amount'),
        ];
    }

    private function returnTotals(Builder $query): array
    {
        return [
            'taxable' => (float) (clone $query)->sum('subtotal'),
            'gst' => (float) (clone $query)->sum('gst_amount'),
            'total' => (float) (clone $query)->sum('total_amount'),
            'refund' => (float) (clone $query)->sum('refund_amount'),
            'adjustment' => (float) (clone $query)->sum('adjustment_amount'),
        ];
    }

    private function writeFullAuditorExport($handle, array $filters): void
    {
        $this->writeSummaryExport($handle, $filters, 'GST Summary');
        fputcsv($handle, []);
        $this->writeSalesExport($handle, $filters, 'GST Sales', false);
        fputcsv($handle, []);
        $this->writeSalesReturnExport($handle, $filters, 'GST Sales Returns / Credit Notes');
        fputcsv($handle, []);
        $this->writePurchaseExport($handle, $filters, 'GST Purchases', false);
        fputcsv($handle, []);
        $this->writePurchaseReturnExport($handle, $filters, 'GST Purchase Returns / Debit Notes');
    }

    private function writeSummaryExport($handle, array $filters, ?string $section = null): void
    {
        if ($section) {
            fputcsv($handle, [$section]);
        }

        $salesTotals = $this->documentTotals($this->salesQuery($filters, 'gst'));
        $salesReturnTotals = $this->returnTotals($this->salesReturnQuery($filters, 'gst'));
        $purchaseTotals = $this->documentTotals($this->purchasesQuery($filters, 'gst'));
        $purchaseReturnTotals = $this->returnTotals($this->purchaseReturnQuery($filters, 'gst'));
        $outputGst = $salesTotals['gst'] - $salesReturnTotals['gst'];
        $inputGst = $purchaseTotals['gst'] - $purchaseReturnTotals['gst'];

        fputcsv($handle, ['Metric', 'Amount']);
        fputcsv($handle, ['GST Sales Taxable', number_format($salesTotals['taxable'], 2, '.', '')]);
        fputcsv($handle, ['GST Sales Output GST', number_format($salesTotals['gst'], 2, '.', '')]);
        fputcsv($handle, ['GST Sales Return GST', number_format($salesReturnTotals['gst'], 2, '.', '')]);
        fputcsv($handle, ['Output GST', number_format($outputGst, 2, '.', '')]);
        fputcsv($handle, ['GST Purchase Taxable', number_format($purchaseTotals['taxable'], 2, '.', '')]);
        fputcsv($handle, ['GST Purchase Input GST', number_format($purchaseTotals['gst'], 2, '.', '')]);
        fputcsv($handle, ['GST Purchase Return GST', number_format($purchaseReturnTotals['gst'], 2, '.', '')]);
        fputcsv($handle, ['Input GST', number_format($inputGst, 2, '.', '')]);
        fputcsv($handle, ['Net GST Payable', number_format($outputGst - $inputGst, 2, '.', '')]);
    }

    private function writeSalesExport($handle, array $filters, ?string $section = null, bool $includeReturns = true): void
    {
        if ($section) {
            fputcsv($handle, [$section]);
        }

        fputcsv($handle, [
            'Invoice No',
            'Invoice Date',
            'Customer Name',
            'Customer GST Number',
            'Taxable Amount',
            'GST Amount',
            'Total Amount',
            'Paid Amount',
            'Balance Amount',
            'Payment Status',
        ]);

        $this->salesQuery($filters, 'gst')
            ->orderBy('sale_date')
            ->orderBy('id')
            ->chunk(500, function ($sales) use ($handle) {
                foreach ($sales as $sale) {
                    fputcsv($handle, [
                        $sale->sale_no,
                        optional($sale->sale_date)->format('Y-m-d'),
                        $sale->customer?->name,
                        $sale->customer?->gst_number,
                        number_format((float) $sale->subtotal, 2, '.', ''),
                        number_format((float) $sale->gst_amount, 2, '.', ''),
                        number_format((float) $sale->total_amount, 2, '.', ''),
                        number_format((float) $sale->paid_amount, 2, '.', ''),
                        number_format((float) $sale->balance_amount, 2, '.', ''),
                        ucfirst($sale->payment_status),
                    ]);
                }
            });

        if ($includeReturns) {
            $this->writeSalesReturnExport($handle, $filters, 'GST Sales Returns / Credit Notes');
        }
    }

    private function writeSalesReturnExport($handle, array $filters, ?string $section = null): void
    {
        if ($section) {
            fputcsv($handle, [$section]);
        }

        fputcsv($handle, [
            'Credit Note No',
            'Original Invoice No',
            'Return Date',
            'Customer Name',
            'Customer GST Number',
            'Taxable Amount',
            'GST Amount',
            'Total Amount',
            'Refund Amount',
            'Adjustment Amount',
        ]);

        $this->salesReturnQuery($filters, 'gst')
            ->orderBy('return_date')
            ->orderBy('id')
            ->chunk(500, function ($returns) use ($handle) {
                foreach ($returns as $return) {
                    fputcsv($handle, [
                        $return->return_no,
                        $return->sale?->sale_no,
                        optional($return->return_date)->format('Y-m-d'),
                        $return->customer?->name,
                        $return->customer?->gst_number,
                        number_format((float) $return->subtotal, 2, '.', ''),
                        number_format((float) $return->gst_amount, 2, '.', ''),
                        number_format((float) $return->total_amount, 2, '.', ''),
                        number_format((float) $return->refund_amount, 2, '.', ''),
                        number_format((float) $return->adjustment_amount, 2, '.', ''),
                    ]);
                }
            });
    }

    private function writePurchaseExport($handle, array $filters, ?string $section = null, bool $includeReturns = true): void
    {
        if ($section) {
            fputcsv($handle, [$section]);
        }

        fputcsv($handle, [
            'Purchase No',
            'Supplier Invoice No',
            'Purchase Date',
            'Supplier Name',
            'Supplier GST Number',
            'Taxable Amount',
            'Input GST',
            'Total Amount',
            'Paid Amount',
            'Balance Amount',
            'Payment Status',
        ]);

        $this->purchasesQuery($filters, 'gst')
            ->orderBy('purchase_date')
            ->orderBy('id')
            ->chunk(500, function ($purchases) use ($handle) {
                foreach ($purchases as $purchase) {
                    fputcsv($handle, [
                        $purchase->purchase_no,
                        $purchase->supplier_invoice_no,
                        optional($purchase->purchase_date)->format('Y-m-d'),
                        $purchase->supplier?->name,
                        $purchase->supplier?->gst_number,
                        number_format((float) $purchase->subtotal, 2, '.', ''),
                        number_format((float) $purchase->gst_amount, 2, '.', ''),
                        number_format((float) $purchase->total_amount, 2, '.', ''),
                        number_format((float) $purchase->paid_amount, 2, '.', ''),
                        number_format((float) $purchase->balance_amount, 2, '.', ''),
                        ucfirst($purchase->payment_status),
                    ]);
                }
            });

        if ($includeReturns) {
            $this->writePurchaseReturnExport($handle, $filters, 'GST Purchase Returns / Debit Notes');
        }
    }

    private function writePurchaseReturnExport($handle, array $filters, ?string $section = null): void
    {
        if ($section) {
            fputcsv($handle, [$section]);
        }

        fputcsv($handle, [
            'Debit Note No',
            'Purchase No',
            'Supplier Invoice No',
            'Return Date',
            'Supplier Name',
            'Supplier GST Number',
            'Taxable Amount',
            'Input GST',
            'Total Amount',
            'Refund Amount',
            'Adjustment Amount',
        ]);

        $this->purchaseReturnQuery($filters, 'gst')
            ->orderBy('return_date')
            ->orderBy('id')
            ->chunk(500, function ($returns) use ($handle) {
                foreach ($returns as $return) {
                    fputcsv($handle, [
                        $return->return_no,
                        $return->purchase?->purchase_no,
                        $return->purchase?->supplier_invoice_no,
                        optional($return->return_date)->format('Y-m-d'),
                        $return->supplier?->name,
                        $return->supplier?->gst_number,
                        number_format((float) $return->subtotal, 2, '.', ''),
                        number_format((float) $return->gst_amount, 2, '.', ''),
                        number_format((float) $return->total_amount, 2, '.', ''),
                        number_format((float) $return->refund_amount, 2, '.', ''),
                        number_format((float) $return->adjustment_amount, 2, '.', ''),
                    ]);
                }
            });
    }
}
