<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class ProductionReadinessController extends Controller
{
    public function checklist(): View
    {
        return view('settings.testing-checklist', [
            'testingItems' => $this->testingItems(),
            'securityItems' => $this->securityItems(),
            'deploymentCommands' => $this->deploymentCommands(),
        ]);
    }

    private function testingItems(): array
    {
        return [
            ['module' => 'Product CRUD testing', 'coverage' => 'Create, edit, list, delete permission, validation', 'test' => 'ProductManagementTest / RolePermissionTest'],
            ['module' => 'Purchase entry testing', 'coverage' => 'Supplier purchase, items, totals, payment status', 'test' => 'PaymentLedgerTest / GSTReportTest'],
            ['module' => 'Stock inward testing', 'coverage' => 'Purchase stock movement and product stock increase', 'test' => 'PaymentLedgerTest'],
            ['module' => 'GST invoice testing', 'coverage' => 'GST bill totals, stock out, ledger, GST report inclusion', 'test' => 'GSTReportTest / PdfDocumentTest'],
            ['module' => 'Normal bill testing', 'coverage' => 'Non-GST bill totals and GST report exclusion', 'test' => 'GSTReportTest / PdfDocumentTest'],
            ['module' => 'Stock reduction testing', 'coverage' => 'Sale stock out, insufficient stock prevention, adjustment decrease checks', 'test' => 'StockAdjustmentTest / QuotationManagementTest'],
            ['module' => 'Customer receipt testing', 'coverage' => 'Receipt posting, sale paid amount, customer balance, cashbook', 'test' => 'PaymentLedgerTest'],
            ['module' => 'Supplier payment testing', 'coverage' => 'Supplier payment posting, payable balance, bankbook', 'test' => 'PaymentLedgerTest'],
            ['module' => 'Cashbook testing', 'coverage' => 'Cash in/out and bank in/out from real postings', 'test' => 'DashboardTest / PaymentLedgerTest'],
            ['module' => 'Loan testing', 'coverage' => 'Loan taken/given, repayment, balance close, ledger/cashbook', 'test' => 'LoanManagementTest'],
            ['module' => 'Partner transaction testing', 'coverage' => 'Investment, withdrawal, return, profit share', 'test' => 'PartnerManagementTest'],
            ['module' => 'GST report testing', 'coverage' => 'GST-only sales/purchases, non-GST exclusion, returns, export', 'test' => 'GSTReportTest / ReturnManagementTest'],
            ['module' => 'PDF invoice testing', 'coverage' => 'GST invoice, normal bill, receipt/voucher/report PDFs', 'test' => 'PdfDocumentTest'],
            ['module' => 'Role permission testing', 'coverage' => 'Sidebar visibility, admin/staff/viewer route access', 'test' => 'RolePermissionTest / SystemSettingsTest'],
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
