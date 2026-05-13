<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ledgers', function (Blueprint $table) {
            $table->id();
            $table->date('ledger_date');
            $table->enum('party_type', ['customer', 'supplier', 'partner', 'loan', 'expense', 'owner']);
            $table->unsignedBigInteger('party_id')->nullable();
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->decimal('debit', 15, 2)->default(0);
            $table->decimal('credit', 15, 2)->default(0);
            $table->decimal('balance', 15, 2)->default(0);
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->index(['party_type', 'party_id']);
            $table->index(['reference_type', 'reference_id']);
            $table->index('ledger_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ledgers');
    }
};
