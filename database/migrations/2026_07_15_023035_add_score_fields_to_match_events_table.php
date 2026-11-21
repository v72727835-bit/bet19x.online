<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('match_events', function (Blueprint $table) {
            $table->string('team_a_score')->nullable()->after('team_b_name');
            $table->string('team_b_score')->nullable()->after('team_a_score');
            $table->string('current_over')->nullable()->after('team_b_score');
            $table->string('live_summary')->nullable()->after('current_over');
        });
    }

    public function down(): void
    {
        Schema::table('match_events', function (Blueprint $table) {
            $table->dropColumn(['team_a_score', 'team_b_score', 'current_over', 'live_summary']);
        });
    }
};
