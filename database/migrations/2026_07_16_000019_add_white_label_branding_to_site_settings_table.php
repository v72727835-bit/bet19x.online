<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->string('favicon_path')->nullable()->after('logo_path');
            $table->string('app_tagline')->nullable()->after('site_name');
            $table->string('primary_color', 20)->default('#FFB627')->after('app_tagline');   // fx-amber
            $table->string('secondary_color', 20)->default('#FF6B4A')->after('primary_color'); // fx-coral
            $table->string('accent_color', 20)->default('#34D399')->after('secondary_color');  // fx-green
            $table->string('bg_dark_color', 20)->default('#0F1023')->after('accent_color');
            $table->string('surface_dark_color', 20)->default('#14152B')->after('bg_dark_color');
            $table->string('currency_symbol', 5)->default('₹')->after('surface_dark_color');
            $table->string('support_email')->nullable()->after('currency_symbol');
            $table->string('support_phone')->nullable()->after('support_email');
            $table->text('footer_text')->nullable()->after('support_phone');
            $table->string('social_facebook')->nullable()->after('footer_text');
            $table->string('social_instagram')->nullable()->after('social_facebook');
            $table->string('social_twitter')->nullable()->after('social_instagram');
            $table->string('social_telegram')->nullable()->after('social_twitter');

            // License / white-label protection for resold copies
            $table->string('license_key')->nullable()->after('social_telegram');
            $table->string('licensed_to')->nullable()->after('license_key');
            $table->string('licensed_domain')->nullable()->after('licensed_to');
            $table->date('license_expires_at')->nullable()->after('licensed_domain');
        });
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn([
                'favicon_path', 'app_tagline', 'primary_color', 'secondary_color', 'accent_color',
                'bg_dark_color', 'surface_dark_color', 'currency_symbol', 'support_email', 'support_phone',
                'footer_text', 'social_facebook', 'social_instagram', 'social_twitter', 'social_telegram',
                'license_key', 'licensed_to', 'licensed_domain', 'license_expires_at',
            ]);
        });
    }
};
