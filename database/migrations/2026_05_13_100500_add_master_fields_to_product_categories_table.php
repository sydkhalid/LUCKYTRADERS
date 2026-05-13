<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_categories', function (Blueprint $table) {
            if (! Schema::hasColumn('product_categories', 'name')) {
                $table->string('name')->after('id');
            }

            if (! Schema::hasColumn('product_categories', 'description')) {
                $table->text('description')->nullable()->after('name');
            }

            if (! Schema::hasColumn('product_categories', 'status')) {
                $table->string('status')->default('active')->after('description');
            }
        });
    }

    public function down(): void
    {
        Schema::table('product_categories', function (Blueprint $table) {
            if (Schema::hasColumn('product_categories', 'status')) {
                $table->dropColumn('status');
            }

            if (Schema::hasColumn('product_categories', 'description')) {
                $table->dropColumn('description');
            }

            if (Schema::hasColumn('product_categories', 'name')) {
                $table->dropColumn('name');
            }
        });
    }
};
