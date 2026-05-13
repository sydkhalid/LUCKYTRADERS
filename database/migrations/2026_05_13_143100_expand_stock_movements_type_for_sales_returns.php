<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE stock_movements MODIFY movement_type ENUM('purchase_in', 'sale_out', 'sales_return_in', 'adjustment') NOT NULL");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("UPDATE stock_movements SET movement_type = 'adjustment' WHERE movement_type = 'sales_return_in'");
            DB::statement("ALTER TABLE stock_movements MODIFY movement_type ENUM('purchase_in', 'sale_out', 'adjustment') NOT NULL");
        }
    }
};
