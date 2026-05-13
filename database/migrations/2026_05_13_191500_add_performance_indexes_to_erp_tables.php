<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * @return array<string, array<string, list<string>>>
     */
    private function indexes(): array
    {
        return [
            'sales' => [
                'sales_perf_date_bill_status_deleted_idx' => ['sale_date', 'bill_type', 'payment_status', 'deleted_at'],
                'sales_perf_balance_date_deleted_idx' => ['balance_amount', 'sale_date', 'deleted_at'],
            ],
            'purchases' => [
                'purchases_perf_date_bill_status_deleted_idx' => ['purchase_date', 'bill_type', 'payment_status', 'deleted_at'],
                'purchases_perf_balance_date_deleted_idx' => ['balance_amount', 'purchase_date', 'deleted_at'],
            ],
            'sale_items' => [
                'sale_items_product_sale_idx' => ['product_id', 'sale_id'],
            ],
            'purchase_items' => [
                'purchase_items_product_purchase_idx' => ['product_id', 'purchase_id'],
            ],
            'payments' => [
                'payments_party_txn_party_date_deleted_idx' => ['party_type', 'transaction_type', 'party_id', 'payment_date', 'deleted_at'],
            ],
            'cashbooks' => [
                'cashbooks_type_date_idx' => ['transaction_type', 'entry_date'],
            ],
            'expenses' => [
                'expenses_category_date_deleted_idx' => ['expense_category_id', 'expense_date', 'deleted_at'],
            ],
            'products' => [
                'products_status_stock_deleted_idx' => ['status', 'current_stock', 'deleted_at'],
                'products_deleted_category_name_idx' => ['deleted_at', 'product_category_id', 'name'],
            ],
            'sales_returns' => [
                'sales_returns_date_customer_deleted_idx' => ['return_date', 'customer_id', 'deleted_at'],
            ],
            'purchase_returns' => [
                'purchase_returns_date_supplier_deleted_idx' => ['return_date', 'supplier_id', 'deleted_at'],
            ],
            'stock_adjustments' => [
                'stock_adj_date_product_type_reason_deleted_idx' => ['adjustment_date', 'product_id', 'adjustment_type', 'reason', 'deleted_at'],
            ],
            'stock_movements' => [
                'stock_movements_type_date_product_idx' => ['movement_type', 'movement_date', 'product_id'],
            ],
            'ledgers' => [
                'ledgers_party_date_idx' => ['party_type', 'party_id', 'ledger_date'],
            ],
            'loans' => [
                'loans_status_date_deleted_idx' => ['status', 'loan_date', 'deleted_at'],
            ],
            'partners' => [
                'partners_deleted_status_name_idx' => ['deleted_at', 'status', 'name'],
            ],
            'partner_transactions' => [
                'partner_txn_date_partner_type_idx' => ['transaction_date', 'partner_id', 'transaction_type'],
            ],
            'loan_transactions' => [
                'loan_txn_date_loan_type_idx' => ['transaction_date', 'loan_id', 'transaction_type'],
            ],
        ];
    }

    public function up(): void
    {
        foreach ($this->indexes() as $tableName => $indexes) {
            if (! Schema::hasTable($tableName)) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($indexes): void {
                foreach ($indexes as $name => $columns) {
                    $table->index($columns, $name);
                }
            });
        }
    }

    public function down(): void
    {
        foreach (array_reverse($this->indexes()) as $tableName => $indexes) {
            if (! Schema::hasTable($tableName)) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($indexes): void {
                foreach (array_keys($indexes) as $name) {
                    $table->dropIndex($name);
                }
            });
        }
    }
};
