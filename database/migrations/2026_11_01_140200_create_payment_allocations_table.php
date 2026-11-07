<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_allocations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('payment_id');
            $table->foreignId('payment')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('member_contribution_id');
            $table->foreignId('member_contribution')->constrained('member_contributions')->onDelete('cascade');
            $table->decimal('allocated_amount', 8, 2)->default(0.00);
            $table->timestamps();

            $table->index(['payment_id', 'member_contribution_id']);
            $table->unique(['payment_id', 'member_contribution_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_allocations');
    }
};