<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_adjustments', function (Blueprint $table) {
            $table->id();
            $table->string('adjustment_no')->unique();
            $table->date('adjustment_date');
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->enum('adjustment_type', ['increase', 'decrease']);
            $table->enum('reason', ['damage', 'shortage', 'excess', 'return', 'wastage', 'correction', 'other']);
            $table->decimal('quantity', 15, 3);
            $table->decimal('old_stock', 15, 3);
            $table->decimal('new_stock', 15, 3);
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->index(['product_id', 'adjustment_date']);
            $table->index(['adjustment_type', 'reason']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_adjustments');
    }
};
