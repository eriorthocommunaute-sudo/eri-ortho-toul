<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_requests', function (Blueprint $table) {
            $table->string('financial_manager')->nullable()->after('supplier');
            $table->string('signature')->nullable()->after('financial_manager');
        });
    }

    public function down(): void
    {
        Schema::table('purchase_requests', function (Blueprint $table) {
            $table->dropColumn(['financial_manager', 'signature']);
        });
    }
};
