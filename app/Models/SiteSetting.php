<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    protected $connection = 'tenant';

    protected $fillable = [
        'site_name', 'app_tagline', 'logo_path', 'favicon_path', 'ticker_text',
        'primary_color', 'secondary_color', 'accent_color', 'bg_dark_color', 'surface_dark_color',
        'currency_symbol', 'support_email', 'support_phone', 'footer_text',
        'social_facebook', 'social_instagram', 'social_twitter', 'social_telegram',
        'license_key', 'licensed_to', 'licensed_domain', 'license_expires_at',
        'loyalty_points_per_100_played', 'loyalty_point_value',
    ];

    protected $casts = [
        'license_expires_at' => 'date',
    ];

    public function isLicenseValid(): bool
    {
        if (empty($this->license_key)) {
            return true; // no license enforced if not configured
        }

        if ($this->license_expires_at && $this->license_expires_at->isPast()) {
            return false;
        }

        return true;
    }
}
