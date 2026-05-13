<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cashbooks', function (Blueprint $table) {
            $table->id();
            $table->date('entry_date');
            $table->enum('transaction_type', ['cash_in', 'cash_out', 'bank_in', 'bank_out']);
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->decimal('amount', 15, 2);
            $table->enum('payment_mode', ['cash', 'bank', 'upi', 'cheque']);
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->index(['entry_date', 'transaction_type']);
            $table->index(['reference_type', 'reference_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cashbooks');
    }
};
