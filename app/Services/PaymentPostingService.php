<?php

namespace App\Services;

use App\Models\Cashbook;
use App\Models\Customer;
use App\Models\Ledger;
use App\Models\Payment;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Supplier;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class PaymentPostingService
{
    private const CUSTOMER_SALE_REFERENCE_TYPES = ['sale', 'gst_invoice', 'normal_bill'];

    public function __construct(private SystemSettingService $settings)
    {
    }

    public function recordCustomerReceipt(Customer $customer, array $data): Payment
    {
        return DB::transaction(function () use ($customer, $data) {
            $customer = Customer::whereKey($customer->id)->lockForUpdate()->firstOrFail();
            $referenceType = $data['reference_type'] ?? null;
            $referenceId = $data['reference_id'] ?? null;
            $amount = round((float) $data['amount'], 2);

            $this->validateCustomerReference($customer, $referenceType, $referenceId, $amount);

            $newBalance = round((float) $customer->current_balance - $amount, 2);

            $payment = Payment::create([
                'payment_no' => $this->nextPaymentNo('RCPT'),
                'payment_date' => $data['payment_date'],
                'party_type' => 'customer',
                'party_id' => $customer->id,
                'transaction_type' => 'receipt',
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'amount' => $amount,
                'payment_mode' => $data['payment_mode'],
                'notes' => $data['notes'] ?? null,
            ]);

            $customer->forceFill(['current_balance' => $newBalance])->save();

            Ledger::create([
                'ledger_date' => $data['payment_date'],
                'party_type' => 'customer',
                'party_id' => $customer->id,
                'reference_type' => 'payment',
                'reference_id' => $payment->id,
                'debit' => 0,
                'credit' => $amount,
                'balance' => $newBalance,
                'remarks' => 'Customer receipt '.$payment->payment_no,
            ]);

            $this->recordCashbook($payment, $customer->name);
            $this->updateReferencePayment($referenceType, $referenceId, $amount);

            return $payment;
        });
    }

    public function recordSupplierPayment(Supplier $supplier, array $data): Payment
    {
        return DB::transaction(function () use ($supplier, $data) {
            $supplier = Supplier::whereKey($supplier->id)->lockForUpdate()->firstOrFail();
            $referenceType = $data['reference_type'] ?? null;
            $referenceId = $data['reference_id'] ?? null;
            $amount = round((float) $data['amount'], 2);

            $this->validateSupplierReference($supplier, $referenceType, $referenceId, $amount);

            $newBalance = round((float) $supplier->current_balance - $amount, 2);

            $payment = Payment::create([
                'payment_no' => $this->nextPaymentNo('PAY'),
                'payment_date' => $data['payment_date'],
                'party_type' => 'supplier',
                'party_id' => $supplier->id,
                'transaction_type' => 'payment',
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'amount' => $amount,
                'payment_mode' => $data['payment_mode'],
                'notes' => $data['notes'] ?? null,
            ]);

            $supplier->forceFill(['current_balance' => $newBalance])->save();

            Ledger::create([
                'ledger_date' => $data['payment_date'],
                'party_type' => 'supplier',
                'party_id' => $supplier->id,
                'reference_type' => 'payment',
                'reference_id' => $payment->id,
                'debit' => $amount,
                'credit' => 0,
                'balance' => $newBalance,
                'remarks' => 'Supplier payment '.$payment->payment_no,
            ]);

            $this->recordCashbook($payment, $supplier->name);
            $this->updateReferencePayment($referenceType, $referenceId, $amount);

            return $payment;
        });
    }

    private function validateCustomerReference(Customer $customer, ?string $referenceType, ?int $referenceId, float $amount): void
    {
        if (! in_array($referenceType, self::CUSTOMER_SALE_REFERENCE_TYPES, true)) {
            return;
        }

        if (! $referenceId) {
            throw ValidationException::withMessages([
                'reference_id' => 'Select the referenced customer bill.',
            ]);
        }

        $sale = Sale::whereKey($referenceId)
            ->where('customer_id', $customer->id)
            ->lockForUpdate()
            ->first();

        if (! $sale) {
            throw ValidationException::withMessages([
                'reference_id' => 'The selected sale does not belong to this customer.',
            ]);
        }

        if ($referenceType === 'gst_invoice' && $sale->bill_type !== 'gst') {
            throw ValidationException::withMessages([
                'reference_id' => 'The selected sale is not a GST invoice.',
            ]);
        }

        if ($referenceType === 'normal_bill' && $sale->bill_type !== 'non_gst') {
            throw ValidationException::withMessages([
                'reference_id' => 'The selected sale is not a normal bill.',
            ]);
        }

        if ($amount > round((float) $sale->balance_amount, 2)) {
            throw ValidationException::withMessages([
                'amount' => 'Receipt amount cannot be greater than the selected bill balance.',
            ]);
        }
    }

    private function validateSupplierReference(Supplier $supplier, ?string $referenceType, ?int $referenceId, float $amount): void
    {
        if ($referenceType !== 'purchase') {
            return;
        }

        if (! $referenceId) {
            throw ValidationException::withMessages([
                'reference_id' => 'Select the referenced supplier purchase.',
            ]);
        }

        $purchase = Purchase::whereKey($referenceId)
            ->where('supplier_id', $supplier->id)
            ->lockForUpdate()
            ->first();

        if (! $purchase) {
            throw ValidationException::withMessages([
                'reference_id' => 'The selected purchase does not belong to this supplier.',
            ]);
        }

        if ($amount > round((float) $purchase->balance_amount, 2)) {
            throw ValidationException::withMessages([
                'amount' => 'Payment amount cannot be greater than the selected purchase balance.',
            ]);
        }
    }

    private function recordCashbook(Payment $payment, string $partyName): void
    {
        Cashbook::create([
            'entry_date' => $payment->payment_date,
            'transaction_type' => $this->cashbookTransactionType($payment),
            'reference_type' => 'payment',
            'reference_id' => $payment->id,
            'amount' => $payment->amount,
            'payment_mode' => $payment->payment_mode,
            'remarks' => $payment->payment_no.' - '.$partyName,
        ]);
    }

    private function cashbookTransactionType(Payment $payment): string
    {
        $book = $payment->payment_mode === 'cash' ? 'cash' : 'bank';
        $direction = $payment->transaction_type === 'receipt' ? 'in' : 'out';

        return $book.'_'.$direction;
    }

    private function nextPaymentNo(string $prefix): string
    {
        return $this->settings->nextPaymentNumber($prefix);
    }

    private function updateReferencePayment(?string $referenceType, ?int $referenceId, float $amount): void
    {
        if (! $referenceType || ! $referenceId) {
            return;
        }

        $table = match ($referenceType) {
            'sale', 'gst_invoice', 'normal_bill' => 'sales',
            'purchase' => 'purchases',
            default => null,
        };

        if (! $table || ! Schema::hasTable($table)) {
            return;
        }

        $record = DB::table($table)->where('id', $referenceId)->lockForUpdate()->first();

        if (! $record) {
            return;
        }

        $updates = [];
        $paidAmount = round((float) ($record->paid_amount ?? 0) + $amount, 2);

        if (Schema::hasColumn($table, 'paid_amount')) {
            $updates['paid_amount'] = $paidAmount;
        }

        $totalAmount = $this->referenceTotalAmount($table, $record, $paidAmount);
        $balanceAmount = max(round($totalAmount - $paidAmount, 2), 0);

        if (Schema::hasColumn($table, 'balance_amount')) {
            $updates['balance_amount'] = $balanceAmount;
        }

        if (Schema::hasColumn($table, 'payment_status')) {
            $updates['payment_status'] = $paidAmount <= 0
                ? 'pending'
                : ($balanceAmount <= 0 ? 'paid' : 'partial');
        }

        if ($updates !== []) {
            DB::table($table)->where('id', $referenceId)->update($updates);
        }
    }

    private function referenceTotalAmount(string $table, object $record, float $paidAmount): float
    {
        foreach (['total_amount', 'grand_total', 'net_total', 'final_amount'] as $column) {
            if (Schema::hasColumn($table, $column) && isset($record->{$column})) {
                return (float) $record->{$column};
            }
        }

        if (Schema::hasColumn($table, 'balance_amount') && isset($record->balance_amount)) {
            return (float) ($record->paid_amount ?? 0) + (float) $record->balance_amount;
        }

        return $paidAmount;
    }
}
