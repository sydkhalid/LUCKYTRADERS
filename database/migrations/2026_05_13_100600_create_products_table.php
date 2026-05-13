<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_category_id')->constrained()->restrictOnDelete();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('size')->nullable();
            $table->string('thickness')->nullable();
            $table->string('unit')->default('Kg');
            $table->decimal('weight_per_unit', 15, 3)->default(0);
            $table->string('hsn_code')->nullable();
            $table->decimal('gst_percentage', 5, 2)->default(0);
            $table->decimal('purchase_price', 15, 2)->default(0);
            $table->decimal('selling_price', 15, 2)->default(0);
            $table->decimal('opening_stock', 15, 3)->default(0);
            $table->decimal('current_stock', 15, 3)->default(0);
            $table->string('status')->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['product_category_id', 'status']);
            $table->index('hsn_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
