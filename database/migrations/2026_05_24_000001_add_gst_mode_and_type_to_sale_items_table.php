<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('sale_items', 'gst_calculation')) {
            Schema::table('sale_items', function (Blueprint $table) {
                $table->string('gst_calculation', 20)->default('exclusive');
            });
        }

        if (! Schema::hasColumn('sale_items', 'gst_type')) {
            Schema::table('sale_items', function (Blueprint $table) {
                $table->string('gst_type', 20)->default('cgst_sgst');
            });
        }
    }

    public function down(): void
    {
        $columns = array_values(array_filter([
            Schema::hasColumn('sale_items', 'gst_type') ? 'gst_type' : null,
            Schema::hasColumn('sale_items', 'gst_calculation') ? 'gst_calculation' : null,
        ]));

        if ($columns !== []) {
            Schema::table('sale_items', function (Blueprint $table) use ($columns) {
                $table->dropColumn($columns);
            });
        }
    }
};
