<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('payment_reference')->unique(); // PAY-YYYY-NNNNNN
            $table->string('receipt_reference')->unique(); // REC-YYYY-NNNNNN
            $table->unsignedBigInteger('member_id');
            // SQLite cannot drop an FK-constrained column in the later fix
            // migration, so the constraint itself is MySQL-only.
            if (Schema::getConnection()->getDriverName() === 'sqlite') {
                $table->unsignedBigInteger('member');
            } else {
                $table->foreignId('member')->constrained()->onDelete('cascade');
            }
            $table->date('payment_date');
            $table->decimal('amount', 10, 2);
            $table->enum('payment_method', ['cash', 'bank_transfer'])->default('cash');
            $table->enum('status', ['pending', 'confirmed', 'cancelled'])->default('pending');
            $table->text('notes')->nullable();
            $table->string('evidence_path')->nullable(); // path to receipt document
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by');
            $table->timestamps();

            $table->index(['member_id', 'payment_date']);
            $table->index('payment_method');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
