<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->string('expense_no')->unique();
            $table->date('expense_date');
            $table->foreignId('expense_category_id')->constrained()->restrictOnDelete();
            $table->decimal('amount', 15, 2);
            $table->enum('payment_mode', ['cash', 'bank', 'upi', 'cheque']);
            $table->string('paid_to')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['expense_date', 'expense_category_id']);
            $table->index('payment_mode');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
