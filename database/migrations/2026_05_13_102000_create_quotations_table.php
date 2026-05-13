<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quotations', function (Blueprint $table) {
            $table->id();
            $table->string('quotation_no')->unique();
            $table->foreignId('customer_id')->constrained()->restrictOnDelete();
            $table->date('quotation_date');
            $table->date('valid_until')->nullable();
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('gst_amount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->enum('status', ['draft', 'sent', 'accepted', 'rejected', 'converted'])->default('draft');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['customer_id', 'quotation_date']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quotations');
    }
};
