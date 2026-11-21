<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('agent_id')->nullable()->after('id')->constrained('agents')->nullOnDelete();
            $table->unsignedInteger('loyalty_points')->default(0)->after('max_contest_entry_limit');
            $table->foreignId('vip_tier_id')->nullable()->after('loyalty_points')->constrained('vip_tiers')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('agent_id');
            $table->dropConstrainedForeignId('vip_tier_id');
            $table->dropColumn('loyalty_points');
        });
    }
};
