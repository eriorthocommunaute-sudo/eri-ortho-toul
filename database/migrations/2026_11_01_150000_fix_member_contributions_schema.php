<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // SQLite cannot drop foreign keys by name; its inline constraint is
        // removed together with the column below.
        if (Schema::getConnection()->getDriverName() !== 'sqlite') {
            Schema::table('member_contributions', function (Blueprint $table) {
                $table->dropForeign(['member']);
            });
        }

        Schema::table('member_contributions', function (Blueprint $table) {
            $table->dropColumn('member');
        });

        Schema::table('member_contributions', function (Blueprint $table) {
            $table->dropUnique(['member_id', 'period_month']);
        });

        Schema::table('member_contributions', function (Blueprint $table) {
            $table->unique(['member_id', 'period_year', 'period_month']);
        });
    }

    public function down(): void
    {
        $yearMonthIndex = 'member_contributions_member_id_period_year_period_month_unique';
        $monthIndex = 'member_contributions_member_id_period_month_unique';

        if (Schema::hasIndex('member_contributions', $yearMonthIndex)) {
            Schema::table('member_contributions', function (Blueprint $table) {
                $table->dropUnique(['member_id', 'period_year', 'period_month']);
            });
        }

        if (! Schema::hasIndex('member_contributions', $monthIndex)) {
            Schema::table('member_contributions', function (Blueprint $table) {
                $table->unique(['member_id', 'period_month']);
            });
        }

        if (! Schema::hasColumn('member_contributions', 'member')) {
            Schema::table('member_contributions', function (Blueprint $table) {
                $table->unsignedBigInteger('member')->nullable();
            });
        }

        DB::table('member_contributions')->update([
            'member' => DB::raw('member_id'),
        ]);

        Schema::table('member_contributions', function (Blueprint $table) {
            $table->unsignedBigInteger('member')->nullable(false)->change();
        });

        $hasMemberForeignKey = collect(Schema::getForeignKeys('member_contributions'))
            ->contains(fn (array $foreignKey) => $foreignKey['name'] === 'member_contributions_member_foreign');

        if (! $hasMemberForeignKey) {
            Schema::table('member_contributions', function (Blueprint $table) {
                $table->foreign('member')->references('id')->on('members')->onDelete('cascade');
            });
        }
    }
};
