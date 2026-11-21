<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vip_tiers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedInteger('min_points')->default(0);
            $table->decimal('cashback_percent', 5, 2)->default(0);
            $table->decimal('deposit_bonus_percent', 5, 2)->default(0);
            $table->string('badge_color', 20)->default('#FFB627');
            $table->text('perks')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::table('site_settings', function (Blueprint $table) {
            $table->decimal('loyalty_points_per_100_played', 8, 2)->default(1)->after('ticker_text');
            $table->decimal('loyalty_point_value', 8, 4)->default(0.1)->after('loyalty_points_per_100_played');
        });
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn(['loyalty_points_per_100_played', 'loyalty_point_value']);
        });
        Schema::dropIfExists('vip_tiers');
    }
};
