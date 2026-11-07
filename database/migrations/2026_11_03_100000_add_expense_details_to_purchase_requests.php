<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('purchase_requests', 'expense_details')) {
            Schema::table('purchase_requests', function (Blueprint $table) {
                $table->json('expense_details')->nullable()->after('items');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('purchase_requests', 'expense_details')) {
            Schema::table('purchase_requests', function (Blueprint $table) {
                $table->dropColumn('expense_details');
            });
        }
    }
};
