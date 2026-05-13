<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('purchases') && ! Schema::hasColumn('purchases', 'payment_mode')) {
            Schema::table('purchases', function (Blueprint $table) {
                $table->enum('payment_mode', ['cash', 'bank', 'upi', 'cheque', 'credit'])
                    ->default('credit')
                    ->after('payment_status');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('purchases') && Schema::hasColumn('purchases', 'payment_mode')) {
            Schema::table('purchases', function (Blueprint $table) {
                $table->dropColumn('payment_mode');
            });
        }
    }
};
