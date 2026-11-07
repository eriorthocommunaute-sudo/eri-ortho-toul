<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('founding_cotisation_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('founding_cotisation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('motif');
            $table->json('before_values')->nullable();
            $table->json('after_values')->nullable();
            $table->timestamp('occurred_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('founding_cotisation_histories');
    }
};
