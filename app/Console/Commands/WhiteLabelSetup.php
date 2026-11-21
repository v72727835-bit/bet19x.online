<?php

namespace App\Console\Commands;

use App\Models\SiteSetting;
use Illuminate\Console\Command;

class WhiteLabelSetup extends Command
{
    protected $signature = 'whitelabel:setup
        {--name= : Brand / site name}
        {--tagline= : Short tagline}
        {--primary= : Primary brand color hex, e.g. #FFB627}
        {--secondary= : Secondary brand color hex}
        {--currency=₹ : Currency symbol}
        {--support-email= : Support email}
        {--support-phone= : Support phone}
        {--licensed-to= : Client name this copy is licensed to}
        {--licensed-domain= : Domain this copy is licensed to run on}
        {--license-key= : License key string}
        {--license-months=12 : Months until license expiry from today}';

    protected $description = 'Quickly rebrand a freshly deployed white-label copy of the platform from the terminal';

    public function handle(): int
    {
        $setting = SiteSetting::first() ?? new SiteSetting();

        if ($name = $this->option('name')) {
            $setting->site_name = $name;
        }
        if ($tagline = $this->option('tagline')) {
            $setting->app_tagline = $tagline;
        }
        if ($primary = $this->option('primary')) {
            $setting->primary_color = $primary;
        }
        if ($secondary = $this->option('secondary')) {
            $setting->secondary_color = $secondary;
        }
        $setting->currency_symbol = $this->option('currency') ?: ($setting->currency_symbol ?? '₹');

        if ($email = $this->option('support-email')) {
            $setting->support_email = $email;
        }
        if ($phone = $this->option('support-phone')) {
            $setting->support_phone = $phone;
        }

        if ($licensedTo = $this->option('licensed-to')) {
            $setting->licensed_to = $licensedTo;
        }
        if ($domain = $this->option('licensed-domain')) {
            $setting->licensed_domain = $domain;
        }
        if ($key = $this->option('license-key')) {
            $setting->license_key = $key;
        }
        if ($this->option('licensed-to') || $this->option('license-key')) {
            $months = (int) $this->option('license-months');
            $setting->license_expires_at = now()->addMonths($months > 0 ? $months : 12);
        }

        $setting->save();

        $this->info('White-label branding update ho gayi.');
        $this->table(['Field', 'Value'], [
            ['Site Name', $setting->site_name],
            ['Tagline', $setting->app_tagline],
            ['Primary Color', $setting->primary_color],
            ['Secondary Color', $setting->secondary_color],
            ['Currency', $setting->currency_symbol],
            ['Licensed To', $setting->licensed_to],
            ['Licensed Domain', $setting->licensed_domain],
            ['License Expires', optional($setting->license_expires_at)->format('Y-m-d')],
        ]);

        $this->comment('Logo/favicon Admin Panel > Settings se upload karo (file upload CLI se nahi hoti).');

        return self::SUCCESS;
    }
}
