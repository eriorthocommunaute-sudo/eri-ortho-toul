<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contribution_notices', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique(); // AVI-YYYY-NNNN
            $table->foreignId('member_id')->constrained()->restrictOnDelete();
            $table->foreignId('contribution_id')->constrained()->restrictOnDelete();
            $table->date('notice_date');
            $table->date('due_date');
            $table->decimal('amount', 8, 2);
            $table->enum('status', ['generated', 'sent', 'paid', 'overdue', 'cancelled'])->default('generated');
            $table->date('sent_date')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['member_id', 'contribution_id']);
            $table->index(['status', 'due_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contribution_notices');
    }
};