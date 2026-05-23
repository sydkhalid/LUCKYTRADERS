<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('sales', 'eway_bill_no')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->string('eway_bill_no')->nullable()->after('payment_mode');
            });
        }

        if (! Schema::hasColumn('sales', 'eway_date')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->date('eway_date')->nullable()->after('eway_bill_no');
            });
        }

        if (! Schema::hasColumn('sales', 'eway_driver_name')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->string('eway_driver_name')->nullable()->after('eway_date');
            });
        }

        if (! Schema::hasColumn('sales', 'eway_mobile_no')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->string('eway_mobile_no', 30)->nullable()->after('eway_driver_name');
            });
        }

        if (! Schema::hasColumn('sales', 'eway_vehicle_no')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->string('eway_vehicle_no', 50)->nullable()->after('eway_mobile_no');
            });
        }

        if (! Schema::hasColumn('sales', 'eway_valid_upto')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->date('eway_valid_upto')->nullable()->after('eway_vehicle_no');
            });
        }
    }

    public function down(): void
    {
        $columns = array_values(array_filter([
            Schema::hasColumn('sales', 'eway_valid_upto') ? 'eway_valid_upto' : null,
            Schema::hasColumn('sales', 'eway_vehicle_no') ? 'eway_vehicle_no' : null,
            Schema::hasColumn('sales', 'eway_mobile_no') ? 'eway_mobile_no' : null,
            Schema::hasColumn('sales', 'eway_driver_name') ? 'eway_driver_name' : null,
            Schema::hasColumn('sales', 'eway_date') ? 'eway_date' : null,
            Schema::hasColumn('sales', 'eway_bill_no') ? 'eway_bill_no' : null,
        ]));

        if ($columns !== []) {
            Schema::table('sales', function (Blueprint $table) use ($columns) {
                $table->dropColumn($columns);
            });
        }
    }
};
