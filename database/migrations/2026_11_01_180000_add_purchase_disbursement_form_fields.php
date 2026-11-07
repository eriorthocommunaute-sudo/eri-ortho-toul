<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_requests', function (Blueprint $table) {
            $table->string('requester_role')->nullable();
            $table->string('requester_phone', 40)->nullable();
            $table->json('items')->nullable();
            $table->string('decision', 30)->nullable();
            $table->decimal('authorized_amount', 10, 2)->nullable();
            $table->string('authorizing_manager')->nullable();
            $table->string('authorizing_signature')->nullable();
            $table->decimal('paid_amount', 10, 2)->nullable();
            $table->date('payment_date')->nullable();
            $table->string('payment_method', 30)->nullable();
            $table->string('payment_reference')->nullable();
            $table->string('accounting_document_number')->nullable();
            $table->string('supporting_document_type', 30)->nullable();
            $table->decimal('actual_spent_amount', 10, 2)->nullable();
            $table->decimal('remaining_amount', 10, 2)->nullable();
            $table->date('proof_submitted_date')->nullable();
            $table->string('requester_signature')->nullable();
            $table->string('treasurer_signature')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('purchase_requests', function (Blueprint $table) {
            $table->dropColumn([
                'requester_role', 'requester_phone', 'items', 'decision',
                'authorized_amount', 'authorizing_manager', 'authorizing_signature',
                'paid_amount', 'payment_date', 'payment_method', 'payment_reference',
                'accounting_document_number', 'supporting_document_type',
                'actual_spent_amount', 'remaining_amount', 'proof_submitted_date',
                'requester_signature', 'treasurer_signature',
            ]);
        });
    }
};
