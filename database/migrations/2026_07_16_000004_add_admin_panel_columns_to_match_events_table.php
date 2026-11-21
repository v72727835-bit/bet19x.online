<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('match_events', function (Blueprint $table) {
            $table->string('winner_team')->nullable()->after('status');
            $table->text('result_summary')->nullable()->after('winner_team');
        });
    }

    public function down(): void
    {
        Schema::table('match_events', function (Blueprint $table) {
            $table->dropColumn(['winner_team', 'result_summary']);
        });
    }
};
