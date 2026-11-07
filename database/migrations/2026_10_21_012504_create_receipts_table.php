<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('receipts', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique(); // REC-YYYY-NNNN
            $table->foreignId('member_id')->constrained()->restrictOnDelete();
            $table->foreignId('contribution_id')->nullable()->constrained()->nullOnDelete();
            $table->date('payment_date');
            $table->decimal('amount', 8, 2);
            $table->enum('payment_method', ['cash', 'transfer', 'check', 'other'])->default('cash');
            $table->string('financial_reference'); // RF-YYYY-NNNN
            $table->text('notes')->nullable();
            $table->enum('status', ['draft', 'confirmed', 'cancelled'])->default('draft');
            $table->foreignId('confirmed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('confirmed_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['member_id', 'payment_date']);
            $table->index('financial_reference');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('receipts');
    }
};