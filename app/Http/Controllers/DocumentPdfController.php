<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Expense;
use App\Models\Loan;
use App\Models\LoanTransaction;
use App\Models\Partner;
use App\Models\PartnerTransaction;
use App\Models\Payment;
use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\Quotation;
use App\Models\Sale;
use App\Models\SalesReturn;
use App\Models\Supplier;
use App\Services\SystemSettingService;
use App\Support\AmountInWords;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DocumentPdfController extends Controller
{
    public function sale(Request $request, Sale $sale)
    {
        $sale->load(['customer', 'items.product']);
        $title = $sale->bill_type === 'gst' ? 'GST Invoice' : 'Normal Bill';

        return $this->render($request, 'pdf.sale', [
            'title' => $title,
            'sale' => $sale,
            'amountWords' => AmountInWords::rupees($sale->total_amount),
        ], $this->fileName($title, $sale->sale_no));
    }

    public function quotation(Request $request, Quotation $quotation)
    {
        $quotation->load(['customer', 'items.product']);

        return $this->render($request, 'pdf.quotation', [
            'title' => 'Quotation',
            'quotation' => $quotation,
            'amountWords' => AmountInWords::rupees($quotation->total_amount),
        ], $this->fileName('Quotation', $quotation->quotation_no));
    }

    public function purchase(Request $request, Purchase $purchase)
    {
        $purchase->load(['supplier', 'items.product']);

        return $this->render($request, 'pdf.purchase', [
            'title' => 'Purchase Invoice',
            'purchase' => $purchase,
            'amountWords' => AmountInWords::rupees($purchase->total_amount),
        ], $this->fileName('Purchase Invoice', $purchase->purchase_no));
    }

    public function payment(Request $request, Payment $payment)
    {
        $party = $this->paymentParty($payment);
        $reference = $this->paymentReference($payment);
        $title = $payment->transaction_type === 'receipt'
            ? 'Customer Receipt'
            : ($payment->party_type === 'supplier' ? 'Supplier Payment Voucher' : 'Payment Voucher');

        return $this->render($request, 'pdf.payment', [
            'title' => $title,
            'payment' => $payment,
            'party' => $party,
            'reference' => $reference,
            'amountWords' => AmountInWords::rupees($payment->amount),
        ], $this->fileName($title, $payment->payment_no));
    }

    public function loan(Request $request, Loan $loan)
    {
        $loan->load(['transactions' => fn ($query) => $query->orderBy('transaction_date')->orderBy('id')]);

        return $this->render($request, 'pdf.loan', [
            'title' => 'Loan Voucher',
            'loan' => $loan,
            'amountWords' => AmountInWords::rupees($loan->total_amount),
        ], $this->fileName('Loan Voucher', $loan->loan_no));
    }

    public function loanTransaction(Request $request, Loan $loan, LoanTransaction $transaction)
    {
        abort_unless((int) $transaction->loan_id === (int) $loan->id, 404);
        $transaction->load('loan');

        return $this->render($request, 'pdf.loan-transaction', [
            'title' => 'Loan Transaction Voucher',
            'loan' => $loan,
            'transaction' => $transaction,
            'amountWords' => AmountInWords::rupees($transaction->amount),
        ], $this->fileName('Loan Transaction', $loan->loan_no.'-'.$transaction->id));
    }

    public function partnerTransaction(Request $request, Partner $partner, PartnerTransaction $transaction)
    {
        abort_unless((int) $transaction->partner_id === (int) $partner->id, 404);
        $transaction->load('partner');

        return $this->render($request, 'pdf.partner-transaction', [
            'title' => 'Partner Transaction Voucher',
            'partner' => $partner,
            'transaction' => $transaction,
            'amountWords' => AmountInWords::rupees($transaction->amount),
        ], $this->fileName('Partner Transaction', $partner->name.'-'.$transaction->id));
    }

    public function expense(Request $request, Expense $expense)
    {
        $expense->load('category');

        return $this->render($request, 'pdf.expense', [
            'title' => 'Expense Voucher',
            'expense' => $expense,
            'amountWords' => AmountInWords::rupees($expense->amount),
        ], $this->fileName('Expense Voucher', $expense->expense_no));
    }

    public function gstReport(Request $request)
    {
        $filters = $this->filters($request);
        $summary = $this->gstSummary($filters);
        $sales = $this->salesQuery($filters)->orderBy('sale_date')->orderBy('id')->get();
        $purchases = $this->purchasesQuery($filters)->orderBy('purchase_date')->orderBy('id')->get();
        $salesReturns = $this->salesReturnRows($filters);
        $purchaseReturns = $this->purchaseReturnRows($filters);

        return $this->render($request, 'pdf.gst-report', [
            'title' => 'GST Report',
            'filters' => $filters,
            'summary' => $summary,
            'sales' => $sales,
            'purchases' => $purchases,
            'salesReturns' => $salesReturns,
            'purchaseReturns' => $purchaseReturns,
            'amountWords' => AmountInWords::rupees($summary['net_gst_payable']),
        ], 'gst-report-'.now()->format('YmdHis').'.pdf');
    }

    private function render(Request $request, string $view, array $data, string $fileName)
    {
        $pdf = Pdf::loadView($view, array_merge([
            'company' => $this->company(),
            'generatedAt' => now(),
            'termsAndConditions' => app(SystemSettingService::class)->termsAndConditions(),
            'bankDetails' => app(SystemSettingService::class)->bankDetails(),
            'signatureImagePath' => app(SystemSettingService::class)->signatureImagePath(),
        ], $data))->setPaper('a4');

        return $request->boolean('download')
            ? $pdf->download($fileName)
            : $pdf->stream($fileName);
    }

    private function company(): array
    {
        return app(SystemSettingService::class)->company();
    }

    private function fileName(string $type, string $number): string
    {
        return Str::slug($type.'-'.$number).'.pdf';
    }

    private function paymentParty(Payment $payment): Customer|Supplier|Partner|Loan|Expense|null
    {
        return match ($payment->party_type) {
            'customer' => Customer::find($payment->party_id),
            'supplier' => Supplier::find($payment->party_id),
            'partner' => Partner::find($payment->party_id),
            'loan' => Loan::find($payment->party_id),
            'expense' => Expense::find($payment->party_id),
            default => null,
        };
    }

    private function paymentReference(Payment $payment): Sale|Purchase|LoanTransaction|PartnerTransaction|Expense|null
    {
        return match ($payment->reference_type) {
            'sale', 'gst_invoice', 'normal_bill', 'sale_direct_payment' => Sale::find($payment->reference_id),
            'purchase', 'purchase_direct_payment' => Purchase::find($payment->reference_id),
            'loan_transaction' => LoanTransaction::with('loan')->find($payment->reference_id),
            'partner_transaction' => PartnerTransaction::with('partner')->find($payment->reference_id),
            'expense' => Expense::with('category')->find($payment->reference_id),
            default => null,
        };
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

    private function gstSummary(array $filters): array
    {
        $salesTaxable = (float) (clone $this->salesQuery($filters))->sum('subtotal');
        $salesGst = (float) (clone $this->salesQuery($filters))->sum('gst_amount');
        $salesTotal = (float) (clone $this->salesQuery($filters))->sum('total_amount');
        $purchaseTaxable = (float) (clone $this->purchasesQuery($filters))->sum('subtotal');
        $purchaseGst = (float) (clone $this->purchasesQuery($filters))->sum('gst_amount');
        $purchaseTotal = (float) (clone $this->purchasesQuery($filters))->sum('total_amount');
        $salesReturnTotals = $this->salesReturnTotals($filters);
        $purchaseReturnTotals = $this->purchaseReturnTotals($filters);
        $outputGst = $salesGst - $salesReturnTotals['gst'];
        $inputGst = $purchaseGst - $purchaseReturnTotals['gst'];

        return [
            'taxable_sales' => $salesTaxable - $salesReturnTotals['taxable'],
            'output_gst' => $outputGst,
            'total_sales' => $salesTotal - $salesReturnTotals['total'],
            'taxable_purchases' => $purchaseTaxable - $purchaseReturnTotals['taxable'],
            'input_gst' => $inputGst,
            'total_purchases' => $purchaseTotal - $purchaseReturnTotals['total'],
            'sales_returns' => $salesReturnTotals['total'],
            'purchase_returns' => $purchaseReturnTotals['total'],
            'net_gst_payable' => $outputGst - $inputGst,
        ];
    }

    private function salesQuery(array $filters)
    {
        return Sale::with('customer')
            ->where('bill_type', 'gst')
            ->when($filters['from_date'], fn ($query, $date) => $query->whereDate('sale_date', '>=', $date))
            ->when($filters['to_date'], fn ($query, $date) => $query->whereDate('sale_date', '<=', $date));
    }

    private function purchasesQuery(array $filters)
    {
        return Purchase::with('supplier')
            ->where('bill_type', 'gst')
            ->when($filters['from_date'], fn ($query, $date) => $query->whereDate('purchase_date', '>=', $date))
            ->when($filters['to_date'], fn ($query, $date) => $query->whereDate('purchase_date', '<=', $date));
    }

    private function salesReturnRows(array $filters)
    {
        return SalesReturn::with(['sale.customer', 'customer'])
            ->whereHas('sale', fn ($query) => $query->where('bill_type', 'gst'))
            ->when($filters['from_date'], fn ($query, $date) => $query->whereDate('return_date', '>=', $date))
            ->when($filters['to_date'], fn ($query, $date) => $query->whereDate('return_date', '<=', $date))
            ->orderBy('return_date')
            ->orderBy('id')
            ->get();
    }

    private function purchaseReturnRows(array $filters)
    {
        return PurchaseReturn::with(['purchase.supplier', 'supplier'])
            ->whereHas('purchase', fn ($query) => $query->where('bill_type', 'gst'))
            ->when($filters['from_date'], fn ($query, $date) => $query->whereDate('return_date', '>=', $date))
            ->when($filters['to_date'], fn ($query, $date) => $query->whereDate('return_date', '<=', $date))
            ->orderBy('return_date')
            ->orderBy('id')
            ->get();
    }

    private function salesReturnTotals(array $filters): array
    {
        $rows = $this->salesReturnRows($filters);

        return [
            'taxable' => (float) $rows->sum('subtotal'),
            'gst' => (float) $rows->sum('gst_amount'),
            'total' => (float) $rows->sum('total_amount'),
        ];
    }

    private function purchaseReturnTotals(array $filters): array
    {
        $rows = $this->purchaseReturnRows($filters);

        return [
            'taxable' => (float) $rows->sum('subtotal'),
            'gst' => (float) $rows->sum('gst_amount'),
            'total' => (float) $rows->sum('total_amount'),
        ];
    }
}
