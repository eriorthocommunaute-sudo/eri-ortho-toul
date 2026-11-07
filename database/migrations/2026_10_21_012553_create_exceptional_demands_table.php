<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exceptional_demands', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique(); // EXC-YYYY-NNNN
            $table->string('title');
            $table->text('description');
            $table->string('category')->nullable(); // emergency, maintenance, legal, medical, other
            $table->decimal('amount', 10, 2);
            $table->enum('urgency', ['normal', 'urgent', 'critical'])->default('normal');
            $table->enum('status', ['draft', 'submitted', 'under_review', 'approved', 'rejected', 'paid', 'cancelled'])->default('draft');
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('approval_notes')->nullable();
            $table->string('financial_reference')->nullable(); // RF-YYYY-NNNN when paid
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['status', 'urgency']);
            $table->index('category');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exceptional_demands');
    }
};