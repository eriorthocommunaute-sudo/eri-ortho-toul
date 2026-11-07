<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reference_sequences', function (Blueprint $table) {
            $table->id();
            $table->string('prefix', 12);
            $table->unsignedSmallInteger('sequence_year');
            $table->unsignedInteger('last_number')->default(0);
            $table->timestamps();
            $table->unique(['prefix', 'sequence_year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reference_sequences');
    }
};
