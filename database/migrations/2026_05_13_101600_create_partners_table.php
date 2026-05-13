<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('partners', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->decimal('share_percentage', 8, 2)->default(0);
            $table->decimal('opening_investment', 15, 2)->default(0);
            $table->decimal('current_investment', 15, 2)->default(0);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();

            $table->index(['status', 'name']);
            $table->index('share_percentage');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partners');
    }
};
