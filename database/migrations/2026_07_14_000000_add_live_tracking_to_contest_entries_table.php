<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contest_entries', function (Blueprint $table) {
            if (!Schema::hasColumn('contest_entries', 'rank')) {
                $table->unsignedInteger('rank')->nullable()->after('user_team_id');
            }
            if (!Schema::hasColumn('contest_entries', 'points')) {
                $table->decimal('points', 8, 2)->nullable()->default(0)->after('rank');
            }
        });
    }

    public function down(): void
    {
        Schema::table('contest_entries', function (Blueprint $table) {
            if (Schema::hasColumn('contest_entries', 'rank')) {
                $table->dropColumn('rank');
            }
            if (Schema::hasColumn('contest_entries', 'points')) {
                $table->dropColumn('points');
            }
        });
    }
};
