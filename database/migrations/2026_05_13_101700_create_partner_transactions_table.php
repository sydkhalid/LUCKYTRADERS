<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('partner_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('partner_id')->constrained()->cascadeOnDelete();
            $table->date('transaction_date');
            $table->enum('transaction_type', ['investment', 'withdrawal', 'profit_share', 'return']);
            $table->decimal('amount', 15, 2);
            $table->enum('payment_mode', ['cash', 'bank', 'upi', 'cheque']);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['partner_id', 'transaction_date']);
            $table->index(['transaction_type', 'payment_mode']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partner_transactions');
    }
};
