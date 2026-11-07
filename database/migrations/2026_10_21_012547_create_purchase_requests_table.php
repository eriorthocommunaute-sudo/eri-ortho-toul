<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_requests', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique(); // DA-YYYY-NNNN
            $table->string('title');
            $table->text('description');
            $table->string('category')->nullable(); // equipment, supplies, maintenance, transport, other
            $table->decimal('estimated_amount', 10, 2);
            $table->string('supplier')->nullable();
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            $table->enum('status', ['draft', 'submitted', 'approved', 'rejected', 'ordered', 'received', 'cancelled'])->default('draft');
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('approval_notes')->nullable();
            $table->string('equipment_reference')->nullable(); // EQP-YYYY-NNNN when created
            $table->string('financial_reference')->nullable(); // RF-YYYY-NNNN when paid
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['status', 'requested_by']);
            $table->index('category');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_requests');
    }
};