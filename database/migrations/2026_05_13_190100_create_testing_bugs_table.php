<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('testing_bugs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('testing_checklist_id')->nullable()->constrained('testing_checklists')->nullOnDelete();
            $table->string('module');
            $table->string('title');
            $table->string('severity')->default('medium');
            $table->string('status')->default('open');
            $table->text('description')->nullable();
            $table->text('resolution_notes')->nullable();
            $table->foreignId('reported_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'severity']);
            $table->index('module');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('testing_bugs');
    }
};
