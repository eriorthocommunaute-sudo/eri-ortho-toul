<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('member_contributions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('member_id');
            $table->foreignId('member')->constrained()->onDelete('cascade');
            $table->integer('period_year');
            $table->integer('period_month');
            $table->decimal('expected_amount', 8, 2)->default(10.00);
            $table->decimal('allocated_amount', 8, 2)->default(0.00);
            $table->enum('status', ['unpaid', 'partially_paid', 'paid', 'covered_in_advance'])->default('unpaid');
            $table->unsignedInteger('paid_count')->default(0);
            $table->decimal('total_paid', 12, 2)->default(0.00);
            $table->timestamps();

            $table->unique(['member_id', 'period_month']);
            $table->index(['member_id', 'period_year', 'period_month']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('member_contributions');
    }
};