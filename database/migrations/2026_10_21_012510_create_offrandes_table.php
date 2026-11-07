<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('offrandes', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique(); // OFF-YYYY-NNNN
            $table->date('offering_date');
            $table->string('donor_name')->nullable(); // anonymous allowed
            $table->foreignId('member_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('amount', 8, 2);
            $table->string('occasion')->nullable(); // e.g., Pâques, Noël, Baptême
            $table->string('financial_reference'); // RF-YYYY-NNNN
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['offering_date', 'member_id']);
            $table->index('financial_reference');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('offrandes');
    }
};