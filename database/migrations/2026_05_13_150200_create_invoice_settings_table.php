<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_settings', function (Blueprint $table): void {
            $table->id();
            $table->string('gst_invoice_prefix', 30)->default('GST');
            $table->string('normal_bill_prefix', 30)->default('BILL');
            $table->string('quotation_prefix', 30)->default('QTN');
            $table->string('purchase_prefix', 30)->default('PUR');
            $table->string('receipt_prefix', 30)->default('RCPT');
            $table->unsignedBigInteger('next_gst_invoice_no')->default(1);
            $table->unsignedBigInteger('next_normal_bill_no')->default(1);
            $table->unsignedBigInteger('next_quotation_no')->default(1);
            $table->unsignedBigInteger('next_purchase_no')->default(1);
            $table->unsignedBigInteger('next_receipt_no')->default(1);
            $table->text('terms_and_conditions')->nullable();
            $table->text('bank_details')->nullable();
            $table->string('signature_image')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_settings');
    }
};
