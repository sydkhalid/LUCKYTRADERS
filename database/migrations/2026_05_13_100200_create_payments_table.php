<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('payment_no')->unique();
            $table->date('payment_date');
            $table->enum('party_type', ['customer', 'supplier', 'partner', 'loan', 'expense', 'owner']);
            $table->unsignedBigInteger('party_id')->nullable();
            $table->enum('transaction_type', ['receipt', 'payment']);
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->decimal('amount', 15, 2);
            $table->enum('payment_mode', ['cash', 'bank', 'upi', 'cheque']);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['party_type', 'party_id']);
            $table->index(['reference_type', 'reference_id']);
            $table->index(['payment_date', 'transaction_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
