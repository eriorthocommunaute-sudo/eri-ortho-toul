<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meeting_minutes', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique(); // PV-YYYY-NNNN
            $table->string('title');
            $table->date('meeting_date');
            $table->dateTime('starts_at')->nullable();
            $table->dateTime('ends_at')->nullable();
            $table->string('location')->nullable();
            $table->enum('type', ['board', 'general_assembly', 'bureau', 'committee', 'other'])->default('board');
            $table->text('agenda')->nullable();
            $table->longText('content')->nullable();
            $table->json('attendees')->nullable(); // list of member_ids
            $table->json('absentees')->nullable();
            $table->text('decisions')->nullable();
            $table->text('action_items')->nullable();
            $table->enum('status', ['draft', 'approved', 'archived'])->default('draft');
            $table->foreignId('secretary_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('president_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['meeting_date', 'type']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meeting_minutes');
    }
};