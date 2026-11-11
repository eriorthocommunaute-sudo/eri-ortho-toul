<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // SQLite cannot drop foreign keys by name; their inline constraints are
        // removed together with the columns below.
        $dropForeign = Schema::getConnection()->getDriverName() !== 'sqlite';

        Schema::table('payments', function (Blueprint $table) use ($dropForeign) {
            if ($dropForeign) {
                $table->dropForeign('payments_member_foreign');
            }
            $table->dropColumn('member');
        });

        Schema::table('payment_allocations', function (Blueprint $table) use ($dropForeign) {
            if ($dropForeign) {
                $table->dropForeign('payment_allocations_payment_foreign');
            }
            $table->dropColumn('payment');
        });

        Schema::table('payment_allocations', function (Blueprint $table) use ($dropForeign) {
            if ($dropForeign) {
                $table->dropForeign('payment_allocations_member_contribution_foreign');
            }
            $table->dropColumn('member_contribution');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->unsignedBigInteger('member')->nullable()->after('member_id');
            $table->foreign('member')->references('id')->on('members')->onDelete('cascade');
        });

        Schema::table('payment_allocations', function (Blueprint $table) {
            $table->unsignedBigInteger('payment')->nullable()->after('payment_id');
            $table->foreign('payment')->references('id')->on('payments')->onDelete('cascade');
            $table->unsignedBigInteger('member_contribution')->nullable()->after('member_contribution_id');
            $table->foreign('member_contribution')->references('id')->on('member_contributions')->onDelete('cascade');
        });
    }
};
