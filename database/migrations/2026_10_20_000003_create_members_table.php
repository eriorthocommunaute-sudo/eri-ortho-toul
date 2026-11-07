<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->string('member_code')->unique();
            $table->string('title', 30)->nullable();
            $table->string('given_name');
            $table->string('family_name');
            $table->string('display_name')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->date('joined_at')->nullable();
            $table->string('status', 20)->default('active')->index();
            $table->text('restricted_notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['family_name', 'given_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};
