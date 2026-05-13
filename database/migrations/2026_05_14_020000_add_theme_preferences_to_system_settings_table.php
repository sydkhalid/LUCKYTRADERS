<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('system_settings')) {
            return;
        }

        Schema::table('system_settings', function (Blueprint $table): void {
            if (! Schema::hasColumn('system_settings', 'default_tax')) {
                $table->decimal('default_tax', 5, 2)->default(18)->after('low_stock_threshold');
            }

            if (! Schema::hasColumn('system_settings', 'currency')) {
                $table->string('currency', 10)->default('INR')->after('default_tax');
            }

            if (! Schema::hasColumn('system_settings', 'date_format')) {
                $table->string('date_format', 30)->default('d M Y')->after('currency');
            }

            if (! Schema::hasColumn('system_settings', 'theme_mode')) {
                $table->string('theme_mode', 20)->default('light')->after('date_format');
            }

            if (! Schema::hasColumn('system_settings', 'theme_color')) {
                $table->string('theme_color', 30)->default('#2563eb')->after('theme_mode');
            }

            if (! Schema::hasColumn('system_settings', 'sidebar_style')) {
                $table->string('sidebar_style', 30)->default('dark')->after('theme_color');
            }

            if (! Schema::hasColumn('system_settings', 'header_style')) {
                $table->string('header_style', 30)->default('light')->after('sidebar_style');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('system_settings')) {
            return;
        }

        Schema::table('system_settings', function (Blueprint $table): void {
            foreach ([
                'default_tax',
                'currency',
                'date_format',
                'theme_mode',
                'theme_color',
                'sidebar_style',
                'header_style',
            ] as $column) {
                if (Schema::hasColumn('system_settings', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
