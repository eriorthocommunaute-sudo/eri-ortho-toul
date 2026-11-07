<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('receipts', function (Blueprint $table) {
            $table->foreignId('member_id')->nullable()->change();
            $table->string('receipt_type', 30)->default('cotisation')->after('reference');
            $table->string('payer_name')->nullable()->after('member_id');
            $table->text('description')->nullable()->after('payer_name');
            $table->string('source_type')->nullable()->after('contribution_id');
            $table->unsignedBigInteger('source_id')->nullable()->after('source_type');
            $table->unique(['source_type', 'source_id'], 'receipts_source_unique');
        });
    }

    public function down(): void
    {
        Schema::table('receipts', function (Blueprint $table) {
            $table->dropUnique('receipts_source_unique');
            $table->dropColumn(['receipt_type', 'payer_name', 'description', 'source_type', 'source_id']);
            // Keep member_id nullable on rollback so generic receipt history remains valid.
        });
    }
};
