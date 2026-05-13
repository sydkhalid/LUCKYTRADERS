<?php

namespace App\Services;

use App\Models\Loan;
use App\Models\Notification;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class NotificationAlertService
{
    public function __construct(private BackupManager $backups)
    {
    }

    public function generateAll(): int
    {
        if (! Schema::hasTable('notifications')) {
            return 0;
        }

        return $this->generateLowStockAlerts()
            + $this->generatePendingCustomerPaymentAlerts()
            + $this->generateSupplierPayableAlerts()
            + $this->generateLoanDueAlerts()
            + $this->generateBackupFailureAlerts()
            + $this->generateGstFilingReminder()
            + $this->generateDailySummaryNotification();
    }

    public function unreadCount(User $user): int
    {
        if (! Schema::hasTable('notifications')) {
            return 0;
        }

        return $user->erpNotifications()->unread()->count();
    }

    private function generateLowStockAlerts(): int
    {
        $threshold = $this->lowStockThreshold();
        $users = $this->usersWithAnyPermission(['manage_products', 'manage_stock_adjustments']);

        if ($users->isEmpty()) {
            return 0;
        }

        return Product::with('category')
            ->where('status', 'active')
            ->where('current_stock', '<=', $threshold)
            ->orderBy('current_stock')
            ->limit(100)
            ->get()
            ->sum(fn (Product $product) => $this->notifyUsers($users, [
                'type' => 'low_stock',
                'module' => 'products',
                'severity' => 'warning',
                'title' => 'Low stock: '.$product->name,
                'message' => 'Current stock is '.number_format((float) $product->current_stock, 3).' '.$product->unit.' against threshold '.number_format($threshold, 3).'.',
                'reference_type' => Product::class,
                'reference_id' => $product->id,
                'action_url' => route('products.show', $product),
                'fingerprint' => 'low-stock-product-'.$product->id,
                'data' => [
                    'current_stock' => (float) $product->current_stock,
                    'threshold' => $threshold,
                    'unit' => $product->unit,
                    'category' => $product->category?->name,
                ],
            ]));
    }

    private function generatePendingCustomerPaymentAlerts(): int
    {
        $users = $this->usersWithAnyPermission(['manage_receipts', 'manage_ledgers']);

        if ($users->isEmpty()) {
            return 0;
        }

        return Sale::with('customer')
            ->where('balance_amount', '>', 0)
            ->oldest('sale_date')
            ->limit(100)
            ->get()
            ->sum(fn (Sale $sale) => $this->notifyUsers($users, [
                'type' => 'pending_customer_payment',
                'module' => 'sales',
                'severity' => 'warning',
                'title' => 'Pending collection: '.$sale->sale_no,
                'message' => ($sale->customer?->name ?? 'Customer').' has pending balance Rs. '.number_format((float) $sale->balance_amount, 2).'.',
                'reference_type' => Sale::class,
                'reference_id' => $sale->id,
                'action_url' => route('receipts.create', ['customer_id' => $sale->customer_id, 'reference_id' => $sale->id]),
                'fingerprint' => 'pending-customer-payment-sale-'.$sale->id,
                'data' => [
                    'sale_no' => $sale->sale_no,
                    'customer_id' => $sale->customer_id,
                    'balance_amount' => (float) $sale->balance_amount,
                ],
            ]));
    }

    private function generateSupplierPayableAlerts(): int
    {
        $users = $this->usersWithAnyPermission(['manage_payments', 'manage_ledgers']);

        if ($users->isEmpty()) {
            return 0;
        }

        return Purchase::with('supplier')
            ->where('balance_amount', '>', 0)
            ->oldest('purchase_date')
            ->limit(100)
            ->get()
            ->sum(fn (Purchase $purchase) => $this->notifyUsers($users, [
                'type' => 'supplier_payable_due',
                'module' => 'purchases',
                'severity' => 'warning',
                'title' => 'Supplier payable: '.$purchase->purchase_no,
                'message' => ($purchase->supplier?->name ?? 'Supplier').' payable is Rs. '.number_format((float) $purchase->balance_amount, 2).'.',
                'reference_type' => Purchase::class,
                'reference_id' => $purchase->id,
                'action_url' => route('supplier-payments.create', ['supplier_id' => $purchase->supplier_id, 'reference_id' => $purchase->id]),
                'fingerprint' => 'supplier-payable-purchase-'.$purchase->id,
                'data' => [
                    'purchase_no' => $purchase->purchase_no,
                    'supplier_id' => $purchase->supplier_id,
                    'balance_amount' => (float) $purchase->balance_amount,
                ],
            ]));
    }

    private function generateLoanDueAlerts(): int
    {
        $users = $this->usersWithAnyPermission(['manage_loans']);

        if ($users->isEmpty()) {
            return 0;
        }

        return Loan::where('status', 'active')
            ->where('balance_amount', '>', 0)
            ->oldest('loan_date')
            ->limit(100)
            ->get()
            ->sum(fn (Loan $loan) => $this->notifyUsers($users, [
                'type' => 'loan_due',
                'module' => 'loans',
                'severity' => 'warning',
                'title' => 'Loan due: '.$loan->loan_no,
                'message' => $loan->party_name.' has pending loan balance Rs. '.number_format((float) $loan->balance_amount, 2).'.',
                'reference_type' => Loan::class,
                'reference_id' => $loan->id,
                'action_url' => route('loans.show', $loan),
                'fingerprint' => 'loan-due-'.$loan->id,
                'data' => [
                    'loan_no' => $loan->loan_no,
                    'loan_type' => $loan->loan_type,
                    'balance_amount' => (float) $loan->balance_amount,
                ],
            ]));
    }

    private function generateBackupFailureAlerts(): int
    {
        $status = $this->backups->status();

        if (($status['status'] ?? null) !== 'failed') {
            return 0;
        }

        return $this->notifyUsers($this->superAdmins(), [
            'type' => 'backup_failure',
            'module' => 'backups',
            'severity' => 'danger',
            'title' => 'Backup failed',
            'message' => $status['message'] ?? 'Backup failed. Review backup logs.',
            'reference_type' => null,
            'reference_id' => null,
            'action_url' => route('settings.backups.index'),
            'fingerprint' => 'backup-failure-'.($status['type'] ?? 'backup').'-'.md5((string) ($status['ran_at'] ?? $status['message'] ?? now()->toDateString())),
            'data' => $status,
        ]);
    }

    private function generateGstFilingReminder(): int
    {
        $period = now()->subMonthNoOverflow();

        return $this->notifyUsers($this->usersWithAnyPermission(['view_gst_reports']), [
            'type' => 'gst_filing_reminder',
            'module' => 'gst_reports',
            'severity' => 'info',
            'title' => 'GST filing reminder',
            'message' => 'Review GST summary and file returns for '.$period->format('F Y').'.',
            'reference_type' => null,
            'reference_id' => null,
            'action_url' => route('gst-reports.index', [
                'from_date' => $period->copy()->startOfMonth()->toDateString(),
                'to_date' => $period->copy()->endOfMonth()->toDateString(),
            ]),
            'fingerprint' => 'gst-filing-'.$period->format('Y-m'),
            'data' => ['period' => $period->format('Y-m')],
        ]);
    }

    private function generateDailySummaryNotification(): int
    {
        $today = now()->toDateString();
        $sales = (float) Sale::whereDate('sale_date', $today)->sum('total_amount');
        $purchases = (float) Purchase::whereDate('purchase_date', $today)->sum('total_amount');
        $collections = (float) DB::table('payments')
            ->whereNull('deleted_at')
            ->where('transaction_type', 'receipt')
            ->whereDate('payment_date', $today)
            ->sum('amount');
        $payments = (float) DB::table('payments')
            ->whereNull('deleted_at')
            ->where('transaction_type', 'payment')
            ->whereDate('payment_date', $today)
            ->sum('amount');
        $cashIn = (float) DB::table('cashbooks')->where('transaction_type', 'cash_in')->whereDate('entry_date', $today)->sum('amount');
        $cashOut = (float) DB::table('cashbooks')->where('transaction_type', 'cash_out')->whereDate('entry_date', $today)->sum('amount');
        $bankIn = (float) DB::table('cashbooks')->where('transaction_type', 'bank_in')->whereDate('entry_date', $today)->sum('amount');
        $bankOut = (float) DB::table('cashbooks')->where('transaction_type', 'bank_out')->whereDate('entry_date', $today)->sum('amount');
        $cashFlow = ($cashIn + $bankIn) - ($cashOut + $bankOut);

        return $this->notifyUsers($this->usersWithAnyPermission(['view_dashboard']), [
            'type' => 'daily_summary',
            'module' => 'dashboard',
            'severity' => 'info',
            'title' => 'Daily business summary',
            'message' => 'Sales Rs. '.number_format($sales, 2).', purchases Rs. '.number_format($purchases, 2).', cash flow Rs. '.number_format($cashFlow, 2).'.',
            'reference_type' => null,
            'reference_id' => null,
            'action_url' => route('dashboard', ['period' => 'today']),
            'fingerprint' => 'daily-summary-'.$today,
            'data' => compact('sales', 'purchases', 'collections', 'payments', 'cashFlow'),
        ]);
    }

    private function notifyUsers(Collection $users, array $payload): int
    {
        return $users
            ->unique('id')
            ->sum(function (User $user) use ($payload): int {
                Notification::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'fingerprint' => $payload['fingerprint'],
                    ],
                    [
                        'type' => $payload['type'],
                        'module' => $payload['module'] ?? null,
                        'severity' => $payload['severity'] ?? 'info',
                        'title' => $payload['title'],
                        'message' => $payload['message'],
                        'reference_type' => $this->referenceType($payload['reference_type'] ?? null),
                        'reference_id' => $payload['reference_id'] ?? null,
                        'action_url' => $payload['action_url'] ?? null,
                        'data' => $payload['data'] ?? [],
                    ]
                );

                return 1;
            });
    }

    private function usersWithAnyPermission(array $permissions): Collection
    {
        return User::with('roles', 'permissions')->get()
            ->filter(fn (User $user): bool => collect($permissions)->contains(fn (string $permission): bool => $user->can($permission)))
            ->values();
    }

    private function superAdmins(): Collection
    {
        return User::with('roles')->get()
            ->filter(fn (User $user): bool => $user->hasRole('Super Admin'))
            ->values();
    }

    private function lowStockThreshold(): float
    {
        if (! Schema::hasTable('system_settings') || ! Schema::hasColumn('system_settings', 'low_stock_threshold')) {
            return 10;
        }

        return (float) (SystemSetting::current()->low_stock_threshold ?? 10);
    }

    private function referenceType(null|string|Model $referenceType): ?string
    {
        if ($referenceType instanceof Model) {
            return $referenceType::class;
        }

        return $referenceType;
    }
}
