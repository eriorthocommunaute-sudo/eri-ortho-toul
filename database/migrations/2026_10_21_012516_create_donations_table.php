<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('donations', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique(); // DON-YYYY-NNNN
            $table->date('donation_date');
            $table->string('donor_name'); // association or individual
            $table->string('donor_type')->nullable(); // association, individual, corporate
            $table->decimal('amount', 10, 2);
            $table->string('purpose')->nullable();
            $table->string('financial_reference'); // RF-YYYY-NNNN
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['donation_date', 'donor_name']);
            $table->index('financial_reference');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('donations');
    }
};