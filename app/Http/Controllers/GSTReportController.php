<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\Sale;
use App\Models\SalesReturn;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class GSTReportController extends Controller
{
    public function index(Request $request)
    {
        $filters = $this->filters($request);
        $salesTotals = $this->salesQuery($filters, 'gst')->selectRaw(
            'COALESCE(SUM(subtotal), 0) as taxable, COALESCE(SUM(gst_amount), 0) as gst, COALESCE(SUM(total_amount), 0) as total'
        )->first();
        $salesReturnTotals = $this->salesReturnQuery($filters, 'gst')->selectRaw(
            'COALESCE(SUM(sales_returns.subtotal), 0) as taxable, COALESCE(SUM(sales_returns.gst_amount), 0) as gst, COALESCE(SUM(sales_returns.total_amount), 0) as total'
        )->first();
        $purchaseTotals = $this->purchasesQuery($filters, 'gst')->selectRaw(
            'COALESCE(SUM(subtotal), 0) as taxable, COALESCE(SUM(gst_amount), 0) as gst, COALESCE(SUM(total_amount), 0) as total'
        )->first();
        $purchaseReturnTotals = $this->purchaseReturnQuery($filters, 'gst')->selectRaw(
            'COALESCE(SUM(purchase_returns.subtotal), 0) as taxable, COALESCE(SUM(purchase_returns.gst_amount), 0) as gst, COALESCE(SUM(purchase_returns.total_amount), 0) as total'
        )->first();
        $nonGstSalesTotal = $this->salesQuery($filters, 'non_gst')->sum('total_amount');
        $outputGst = (float) $salesTotals->gst - (float) $salesReturnTotals->gst;
        $inputGst = (float) $purchaseTotals->gst - (float) $purchaseReturnTotals->gst;

        $summary = [
            'taxable_sales' => (float) $salesTotals->taxable - (float) $salesReturnTotals->taxable,
            'output_gst' => $outputGst,
            'total_sales' => (float) $salesTotals->total - (float) $salesReturnTotals->total,
            'taxable_purchases' => (float) $purchaseTotals->taxable - (float) $purchaseReturnTotals->taxable,
            'input_gst' => $inputGst,
            'total_purchases' => (float) $purchaseTotals->total - (float) $purchaseReturnTotals->total,
            'net_gst_payable' => $outputGst - $inputGst,
            'non_gst_sales' => (float) $nonGstSalesTotal,
            'sales_returns' => (float) $salesReturnTotals->total,
            'purchase_returns' => (float) $purchaseReturnTotals->total,
        ];

        return view('gst-reports.index', compact('filters', 'summary'));
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

        return view('gst-reports.sales', compact('filters', 'sales', 'totals'));
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

        return view('gst-reports.purchases', compact('filters', 'purchases', 'totals'));
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

        return view('gst-reports.non-gst-sales', compact('filters', 'sales', 'totals'));
    }

    public function export(Request $request): StreamedResponse
    {
        $filters = $this->filters($request);
        $type = $request->validate([
            'type' => ['nullable', 'in:sales,purchases,all'],
        ])['type'] ?? 'all';
        $fileName = 'gst-auditor-export-'.$type.'-'.now()->format('YmdHis').'.csv';

        return response()->streamDownload(function () use ($filters, $type) {
            $handle = fopen('php://output', 'w');

            if ($type === 'sales') {
                $this->writeSalesExport($handle, $filters);
            } elseif ($type === 'purchases') {
                $this->writePurchaseExport($handle, $filters);
            } else {
                $this->writeSalesExport($handle, $filters, 'GST Sales');
                fputcsv($handle, []);
                $this->writePurchaseExport($handle, $filters, 'GST Purchases');
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
        ]);

        return [
            'from_date' => $validated['from_date'] ?? null,
            'to_date' => $validated['to_date'] ?? null,
        ];
    }

    private function salesQuery(array $filters, string $billType)
    {
        return Sale::with('customer')
            ->where('bill_type', $billType)
            ->when($filters['from_date'] ?? null, fn ($query, $date) => $query->whereDate('sale_date', '>=', $date))
            ->when($filters['to_date'] ?? null, fn ($query, $date) => $query->whereDate('sale_date', '<=', $date));
    }

    private function purchasesQuery(array $filters, string $billType)
    {
        return Purchase::with('supplier')
            ->where('bill_type', $billType)
            ->when($filters['from_date'] ?? null, fn ($query, $date) => $query->whereDate('purchase_date', '>=', $date))
            ->when($filters['to_date'] ?? null, fn ($query, $date) => $query->whereDate('purchase_date', '<=', $date));
    }

    private function salesReturnQuery(array $filters, string $billType)
    {
        return SalesReturn::query()
            ->join('sales', 'sales_returns.sale_id', '=', 'sales.id')
            ->where('sales.bill_type', $billType)
            ->when($filters['from_date'] ?? null, fn ($query, $date) => $query->whereDate('sales_returns.return_date', '>=', $date))
            ->when($filters['to_date'] ?? null, fn ($query, $date) => $query->whereDate('sales_returns.return_date', '<=', $date));
    }

    private function purchaseReturnQuery(array $filters, string $billType)
    {
        return PurchaseReturn::query()
            ->join('purchases', 'purchase_returns.purchase_id', '=', 'purchases.id')
            ->where('purchases.bill_type', $billType)
            ->when($filters['from_date'] ?? null, fn ($query, $date) => $query->whereDate('purchase_returns.return_date', '>=', $date))
            ->when($filters['to_date'] ?? null, fn ($query, $date) => $query->whereDate('purchase_returns.return_date', '<=', $date));
    }

    private function salesTotals(array $filters, string $billType): array
    {
        $query = $this->salesQuery($filters, $billType);

        $returns = $this->salesReturnQuery($filters, $billType);

        return [
            'taxable' => (float) (clone $query)->sum('subtotal') - (float) (clone $returns)->sum('sales_returns.subtotal'),
            'gst' => (float) (clone $query)->sum('gst_amount') - (float) (clone $returns)->sum('sales_returns.gst_amount'),
            'total' => (float) (clone $query)->sum('total_amount') - (float) (clone $returns)->sum('sales_returns.total_amount'),
            'paid' => (float) (clone $query)->sum('paid_amount'),
            'balance' => (float) (clone $query)->sum('balance_amount'),
            'returns' => (float) (clone $returns)->sum('sales_returns.total_amount'),
        ];
    }

    private function purchaseTotals(array $filters, string $billType): array
    {
        $query = $this->purchasesQuery($filters, $billType);
        $returns = $this->purchaseReturnQuery($filters, $billType);

        return [
            'taxable' => (float) (clone $query)->sum('subtotal') - (float) (clone $returns)->sum('purchase_returns.subtotal'),
            'gst' => (float) (clone $query)->sum('gst_amount') - (float) (clone $returns)->sum('purchase_returns.gst_amount'),
            'total' => (float) (clone $query)->sum('total_amount') - (float) (clone $returns)->sum('purchase_returns.total_amount'),
            'paid' => (float) (clone $query)->sum('paid_amount'),
            'balance' => (float) (clone $query)->sum('balance_amount'),
            'returns' => (float) (clone $returns)->sum('purchase_returns.total_amount'),
        ];
    }

    private function writeSalesExport($handle, array $filters, ?string $section = null): void
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
                    ]);
                }
            });

        SalesReturn::with(['sale.customer'])
            ->whereHas('sale', fn ($query) => $query->where('bill_type', 'gst'))
            ->when($filters['from_date'] ?? null, fn ($query, $date) => $query->whereDate('return_date', '>=', $date))
            ->when($filters['to_date'] ?? null, fn ($query, $date) => $query->whereDate('return_date', '<=', $date))
            ->orderBy('return_date')
            ->orderBy('id')
            ->chunk(500, function ($returns) use ($handle) {
                foreach ($returns as $return) {
                    fputcsv($handle, [
                        'RETURN '.$return->return_no.' / '.$return->sale?->sale_no,
                        optional($return->return_date)->format('Y-m-d'),
                        $return->customer?->name,
                        $return->customer?->gst_number,
                        number_format(-1 * (float) $return->subtotal, 2, '.', ''),
                        number_format(-1 * (float) $return->gst_amount, 2, '.', ''),
                        number_format(-1 * (float) $return->total_amount, 2, '.', ''),
                        number_format(0, 2, '.', ''),
                        number_format(-1 * (float) $return->adjustment_amount, 2, '.', ''),
                    ]);
                }
            });
    }

    private function writePurchaseExport($handle, array $filters, ?string $section = null): void
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
                    ]);
                }
            });

        PurchaseReturn::with(['purchase.supplier'])
            ->whereHas('purchase', fn ($query) => $query->where('bill_type', 'gst'))
            ->when($filters['from_date'] ?? null, fn ($query, $date) => $query->whereDate('return_date', '>=', $date))
            ->when($filters['to_date'] ?? null, fn ($query, $date) => $query->whereDate('return_date', '<=', $date))
            ->orderBy('return_date')
            ->orderBy('id')
            ->chunk(500, function ($returns) use ($handle) {
                foreach ($returns as $return) {
                    fputcsv($handle, [
                        'RETURN '.$return->return_no.' / '.$return->purchase?->purchase_no,
                        $return->purchase?->supplier_invoice_no,
                        optional($return->return_date)->format('Y-m-d'),
                        $return->supplier?->name,
                        $return->supplier?->gst_number,
                        number_format(-1 * (float) $return->subtotal, 2, '.', ''),
                        number_format(-1 * (float) $return->gst_amount, 2, '.', ''),
                        number_format(-1 * (float) $return->total_amount, 2, '.', ''),
                        number_format(0, 2, '.', ''),
                        number_format(-1 * (float) $return->adjustment_amount, 2, '.', ''),
                    ]);
                }
            });
    }
}
