<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->string('loan_no')->unique();
            $table->enum('loan_type', ['loan_taken', 'loan_given', 'partner_withdrawal', 'partner_deposit']);
            $table->string('party_name');
            $table->string('party_phone')->nullable();
            $table->unsignedBigInteger('partner_id')->nullable();
            $table->date('loan_date');
            $table->decimal('principal_amount', 15, 2);
            $table->decimal('interest_percentage', 8, 2)->default(0);
            $table->enum('interest_type', ['none', 'monthly', 'yearly', 'fixed'])->default('none');
            $table->decimal('total_interest', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->decimal('balance_amount', 15, 2)->default(0);
            $table->enum('status', ['active', 'closed'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['loan_type', 'status']);
            $table->index(['loan_date', 'status']);
            $table->index('partner_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loans');
    }
};
