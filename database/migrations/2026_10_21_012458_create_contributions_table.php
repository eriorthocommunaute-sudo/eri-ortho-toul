<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contributions', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique(); // COT-YYYY-MM
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('month');
            $table->decimal('amount_per_member', 8, 2)->default(config('finance.default_monthly_contribution', 10.00));
            $table->unsignedInteger('expected_count')->default(0);
            $table->unsignedInteger('received_count')->default(0);
            $table->decimal('total_expected', 12, 2)->default(0);
            $table->decimal('total_received', 12, 2)->default(0);
            $table->enum('status', ['open', 'closed'])->default('open');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['year', 'month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contributions');
    }
};