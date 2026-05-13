<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quotation_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quotation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->decimal('quantity', 15, 3);
            $table->string('unit', 50);
            $table->decimal('rate', 15, 2);
            $table->decimal('subtotal', 15, 2);
            $table->decimal('gst_percentage', 5, 2)->default(0);
            $table->decimal('gst_amount', 15, 2)->default(0);
            $table->decimal('total', 15, 2);
            $table->timestamps();

            $table->index(['quotation_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quotation_items');
    }
};
