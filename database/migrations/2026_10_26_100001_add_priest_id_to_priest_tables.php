<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('priest_transports', function (Blueprint $table) {
            $table->foreignId('priest_id')->nullable()->after('reference')->constrained('priests')->nullOnDelete();
        });

        Schema::table('priest_indemnities', function (Blueprint $table) {
            $table->foreignId('priest_id')->nullable()->after('reference')->constrained('priests')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('priest_transports', function (Blueprint $table) {
            $table->dropForeign(['priest_id']);
            $table->dropColumn('priest_id');
        });

        Schema::table('priest_indemnities', function (Blueprint $table) {
            $table->dropForeign(['priest_id']);
            $table->dropColumn('priest_id');
        });
    }
};
