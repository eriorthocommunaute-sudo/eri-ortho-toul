<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique(); // EVT-YYYY-NNNN
            $table->string('title');
            $table->text('description')->nullable();
            $table->dateTime('starts_at');
            $table->dateTime('ends_at')->nullable();
            $table->string('location')->nullable();
            $table->enum('type', ['liturgy', 'meeting', 'celebration', 'formation', 'charity', 'other'])->default('liturgy');
            $table->unsignedInteger('expected_attendees')->nullable();
            $table->unsignedInteger('actual_attendees')->nullable();
            $table->decimal('budget', 10, 2)->nullable();
            $table->decimal('actual_cost', 10, 2)->nullable();
            $table->string('financial_reference')->nullable(); // RF-YYYY-NNNN for expenses
            $table->enum('status', ['planned', 'confirmed', 'completed', 'cancelled'])->default('planned');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['starts_at', 'status']);
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};