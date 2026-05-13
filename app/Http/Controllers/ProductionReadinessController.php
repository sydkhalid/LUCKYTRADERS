<?php

namespace App\Http\Controllers;

use App\Models\TestingBug;
use App\Models\TestingChecklist;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class ProductionReadinessController extends Controller
{
    public function checklist(): View
    {
        $this->syncDefaultChecklist();

        $testingItems = TestingChecklist::query()
            ->with(['tester', 'bugs' => fn ($query) => $query->whereNotIn('status', [TestingBug::STATUS_RESOLVED, TestingBug::STATUS_CLOSED])])
            ->orderBy('sort_order')
            ->get();

        $bugs = TestingBug::query()
            ->with(['checklist', 'reporter', 'resolver'])
            ->latest()
            ->paginate(10);

        return view('settings.testing-checklist', [
            'testingItems' => $testingItems,
            'bugs' => $bugs,
            'summary' => $this->summary($testingItems),
            'statuses' => $this->statuses(),
            'bugStatuses' => $this->bugStatuses(),
            'severities' => $this->severities(),
            'securityItems' => $this->securityItems(),
            'deploymentCommands' => $this->deploymentCommands(),
        ]);
    }

    public function updateChecklist(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'items' => ['required', 'array'],
            'items.*.status' => ['required', 'in:pending,pass,fail'],
            'items.*.notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $items = TestingChecklist::query()
            ->whereIn('id', array_keys($validated['items']))
            ->get()
            ->keyBy('id');

        foreach ($validated['items'] as $id => $payload) {
            $item = $items->get((int) $id);

            if (! $item) {
                continue;
            }

            $statusChanged = $item->status !== $payload['status'];

            $item->fill([
                'status' => $payload['status'],
                'notes' => $payload['notes'] ?? null,
            ]);

            if ($statusChanged || $payload['status'] !== TestingChecklist::STATUS_PENDING) {
                $item->tested_by = $request->user()->id;
                $item->tested_at = now();
            }

            if ($payload['status'] === TestingChecklist::STATUS_PENDING && blank($payload['notes'] ?? null)) {
                $item->tested_by = null;
                $item->tested_at = null;
            }

            $item->save();
        }

        return back()->with('success', 'Testing checklist updated.');
    }

    public function storeBug(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'testing_checklist_id' => ['nullable', 'exists:testing_checklists,id'],
            'module' => ['required', 'string', 'max:120'],
            'title' => ['required', 'string', 'max:180'],
            'severity' => ['required', 'in:low,medium,high,critical'],
            'description' => ['nullable', 'string', 'max:5000'],
        ]);

        TestingBug::create([
            ...$validated,
            'status' => TestingBug::STATUS_OPEN,
            'reported_by' => $request->user()->id,
        ]);

        return back()->with('success', 'Bug added to the testing tracker.');
    }

    public function updateBug(Request $request, TestingBug $bug): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:open,in_progress,resolved,closed'],
            'severity' => ['required', 'in:low,medium,high,critical'],
            'resolution_notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $wasResolved = $bug->isResolved();
        $bug->fill($validated);

        if (! $wasResolved && $bug->isResolved()) {
            $bug->resolved_by = $request->user()->id;
            $bug->resolved_at = now();
        }

        if (! $bug->isResolved()) {
            $bug->resolved_by = null;
            $bug->resolved_at = null;
        }

        $bug->save();

        return back()->with('success', 'Bug status updated.');
    }

    private function syncDefaultChecklist(): void
    {
        foreach ($this->defaultTestingItems() as $index => $item) {
            TestingChecklist::updateOrCreate(
                ['key' => $item['key']],
                [
                    'module' => $item['module'],
                    'scenario' => $item['scenario'],
                    'expected_result' => $item['expected_result'],
                    'automated_test' => $item['automated_test'],
                    'sort_order' => $index + 1,
                ],
            );
        }
    }

    private function summary(Collection $items): array
    {
        $total = $items->count();
        $passed = $items->where('status', TestingChecklist::STATUS_PASS)->count();
        $failed = $items->where('status', TestingChecklist::STATUS_FAIL)->count();
        $pending = $items->where('status', TestingChecklist::STATUS_PENDING)->count();
        $openBugs = TestingBug::whereNotIn('status', [TestingBug::STATUS_RESOLVED, TestingBug::STATUS_CLOSED])->count();

        return [
            'total' => $total,
            'passed' => $passed,
            'failed' => $failed,
            'pending' => $pending,
            'open_bugs' => $openBugs,
            'progress' => $total > 0 ? round(($passed / $total) * 100) : 0,
        ];
    }

    private function statuses(): array
    {
        return [
            TestingChecklist::STATUS_PENDING => 'Pending',
            TestingChecklist::STATUS_PASS => 'Pass',
            TestingChecklist::STATUS_FAIL => 'Fail',
        ];
    }

    private function bugStatuses(): array
    {
        return [
            TestingBug::STATUS_OPEN => 'Open',
            TestingBug::STATUS_IN_PROGRESS => 'In Progress',
            TestingBug::STATUS_RESOLVED => 'Resolved',
            TestingBug::STATUS_CLOSED => 'Closed',
        ];
    }

    private function severities(): array
    {
        return [
            'low' => 'Low',
            'medium' => 'Medium',
            'high' => 'High',
            'critical' => 'Critical',
        ];
    }

    private function defaultTestingItems(): array
    {
        return [
            ['key' => 'product_crud', 'module' => 'Product CRUD testing', 'scenario' => 'Create, edit, search, view, deactivate, and soft delete product masters.', 'expected_result' => 'Product category link, duplicate code prevention, status, stock fields, and delete permission work correctly.', 'automated_test' => 'ProductManagementTest / RolePermissionTest'],
            ['key' => 'purchase_flow', 'module' => 'Purchase flow testing', 'scenario' => 'Create GST and non-GST purchases with multiple item rows and paid amount.', 'expected_result' => 'Totals, GST, payment status, supplier balance, and validation rules are correct.', 'automated_test' => 'PurchaseModuleTest'],
            ['key' => 'stock_increase', 'module' => 'Stock increase testing', 'scenario' => 'Post purchase inward entries and verify product stock movement.', 'expected_result' => 'Product current stock increases and purchase_in stock movement is created.', 'automated_test' => 'PurchaseModuleTest'],
            ['key' => 'sales_billing', 'module' => 'Sales billing testing', 'scenario' => 'Create customer bill with multiple products, payment mode, and paid/balance values.', 'expected_result' => 'Sale items, totals, profit, payment status, and ledger/cashbook effects are correct.', 'automated_test' => 'SaleModuleTest / PaymentLedgerTest'],
            ['key' => 'gst_invoice', 'module' => 'GST invoice testing', 'scenario' => 'Create GST invoice and print/download invoice.', 'expected_result' => 'GST invoice shows GST number, HSN, taxable value, GST percentage, GST amount, and appears in GST reports.', 'automated_test' => 'GSTReportTest / PdfDocumentTest'],
            ['key' => 'normal_bill', 'module' => 'Normal bill testing', 'scenario' => 'Create non-GST normal bill and print/download bill.', 'expected_result' => 'GST fields remain hidden/zero and normal bill never appears in GST reports.', 'automated_test' => 'SaleModuleTest / GSTReportTest / PdfDocumentTest'],
            ['key' => 'stock_decrease', 'module' => 'Stock decrease testing', 'scenario' => 'Bill products and attempt insufficient-stock billing.', 'expected_result' => 'Stock decreases on sale, sale_out movement is created, and negative stock is blocked.', 'automated_test' => 'SaleModuleTest'],
            ['key' => 'receipt_entry', 'module' => 'Receipt entry testing', 'scenario' => 'Receive money from customer against pending invoice.', 'expected_result' => 'Sale paid/balance updates, receipt is recorded, customer ledger is credited, and cash/bank entry is posted.', 'automated_test' => 'PaymentLedgerTest'],
            ['key' => 'supplier_payment', 'module' => 'Supplier payment testing', 'scenario' => 'Pay supplier against pending purchase.', 'expected_result' => 'Purchase paid/balance updates, supplier ledger is debited, and cash/bank out entry is posted.', 'automated_test' => 'PaymentLedgerTest'],
            ['key' => 'loan_transaction', 'module' => 'Loan transaction testing', 'scenario' => 'Create loan taken/given and add repayment/received transaction.', 'expected_result' => 'Loan balance updates, closed status applies at zero, and ledger/cashbook entries are correct.', 'automated_test' => 'LoanManagementTest'],
            ['key' => 'partner_transaction', 'module' => 'Partner transaction testing', 'scenario' => 'Post investment, withdrawal, profit share, and return transactions.', 'expected_result' => 'Partner capital/payable balances, ledger, and cash/bank effects follow transaction type.', 'automated_test' => 'PartnerManagementTest'],
            ['key' => 'expense_entry', 'module' => 'Expense entry testing', 'scenario' => 'Create cash/bank expense with category and paid-to details.', 'expected_result' => 'Expense posts cash/bank out, ledger debit, dashboard/report totals, and net profit reduction.', 'automated_test' => 'ExpenseManagementTest / AdvancedReportTest'],
            ['key' => 'stock_adjustment', 'module' => 'Stock adjustment testing', 'scenario' => 'Increase and decrease stock for damage, shortage, return, wastage, correction, and other reasons.', 'expected_result' => 'Old/new stock is captured, insufficient decrease is blocked, and stock movement/history are visible.', 'automated_test' => 'StockAdjustmentTest'],
            ['key' => 'sales_return', 'module' => 'Sales return testing', 'scenario' => 'Return sold items with refund or balance adjustment.', 'expected_result' => 'Return quantity validation, stock increase, credit note, ledger, GST adjustment, and cashbook refund work.', 'automated_test' => 'ReturnManagementTest'],
            ['key' => 'purchase_return', 'module' => 'Purchase return testing', 'scenario' => 'Return purchased items with refund received or supplier payable adjustment.', 'expected_result' => 'Stock decreases, insufficient stock is blocked, debit note, ledger, GST adjustment, and cashbook receipt work.', 'automated_test' => 'ReturnManagementTest'],
            ['key' => 'gst_reports', 'module' => 'GST reports testing', 'scenario' => 'Run GST sales, purchase, returns, summary, and auditor export reports.', 'expected_result' => 'Only GST bills appear, non-GST bills are excluded, returns adjust GST, and exports are auditor-friendly.', 'automated_test' => 'GSTReportTest'],
            ['key' => 'pdf_generation', 'module' => 'PDF generation testing', 'scenario' => 'Generate invoices, quotations, vouchers, loan, partner, expense, and GST report PDFs.', 'expected_result' => 'PDFs are printable/downloadable and do not mutate stock, ledger, or cashbook records.', 'automated_test' => 'PdfDocumentTest'],
            ['key' => 'permissions', 'module' => 'Permissions testing', 'scenario' => 'Login as each role and verify menu visibility and route access.', 'expected_result' => 'Super Admin/Admin/staff/viewer permissions match the role matrix and unauthorized routes are blocked.', 'automated_test' => 'RolePermissionTest'],
            ['key' => 'dashboard_calculations', 'module' => 'Dashboard calculations testing', 'scenario' => 'Verify cards, charts, recent tables, low stock, pending payments, active loans, and filters.', 'expected_result' => 'Dashboard uses real data and formulas for cash, bank, stock value, expenses, payable, receivable, and profit.', 'automated_test' => 'DashboardTest'],
            ['key' => 'backup_system', 'module' => 'Backup system testing', 'scenario' => 'Create, download, delete, and cleanup database/full backups as Super Admin.', 'expected_result' => 'Backups are restricted, stored safely, timestamped, downloadable, and cleanup/status logging works.', 'automated_test' => 'BackupManagementTest'],
        ];
    }

    private function securityItems(): array
    {
        return [
            'All ERP routes are inside auth middleware.',
            'Admin and module routes are protected by permission or role middleware.',
            'Forms use Laravel validation and Blade CSRF tokens.',
            'Negative payment and quantity inputs are blocked by validation.',
            'Negative stock is blocked in sale, return, and adjustment services.',
            'Delete actions require delete_records permission.',
            'Core ERP records use soft deletes.',
            'Create, edit, and delete events are captured in activity logs.',
            'Backups are restricted to Super Admin users.',
            'APP_ENV and APP_DEBUG must be production-safe before live use.',
        ];
    }

    private function deploymentCommands(): array
    {
        return [
            'php artisan migrate --force',
            'php artisan db:seed --class=RolePermissionSeeder',
            'php artisan storage:link',
            'npm run build',
            'php artisan config:cache',
            'php artisan route:cache',
            'php artisan view:cache',
        ];
    }
}
