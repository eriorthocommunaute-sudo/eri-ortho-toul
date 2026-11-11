<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reference_sequences', function (Blueprint $table) {
            $table->unsignedTinyInteger('sequence_month')->default(0)->after('sequence_year');
        });

        Schema::table('reference_sequences', function (Blueprint $table) {
            $table->dropUnique(['prefix', 'sequence_year']);
            $table->unique(['prefix', 'sequence_year', 'sequence_month']);
        });
    }

    public function down(): void
    {
        Schema::table('reference_sequences', function (Blueprint $table) {
            $table->dropUnique(['prefix', 'sequence_year', 'sequence_month']);
            $table->unique(['prefix', 'sequence_year']);
        });

        Schema::table('reference_sequences', function (Blueprint $table) {
            $table->dropColumn('sequence_month');
        });
    }
};
