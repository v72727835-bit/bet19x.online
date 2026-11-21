<?php

namespace App\Providers;

use App\Models\MenuItem;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Share branding globally so every view (admin + user panel) can white-label itself
        View::composer('*', function ($view) {
            static $cached = null;
            if ($cached === null) {
                try {
                    $cached = SiteSetting::first() ?? new SiteSetting();
                } catch (\Throwable $e) {
                    // No tenant resolved / tenant DB not reachable (e.g. master panel
                    // routes, or a domain that isn't provisioned yet) — don't crash the page.
                    $cached = new SiteSetting();
                }
            }
            $view->with('siteSetting', $cached);
        });


        View::composer('dashboard', function ($view) {
            $view->with([
                'siteSetting' => \App\Models\SiteSetting::first(),
                'siteStats' => \App\Models\SiteStat::where('is_active', true)->orderBy('sort_order')->get(),
                'topWinners' => \App\Models\TopWinner::where('is_active', true)->orderBy('sort_order')->get(),
                'featuredContests' => \App\Models\FeaturedContest::where('is_active', true)->orderBy('sort_order')->get(),
                'sportCategories' => \App\Models\SportCategory::where('is_active', true)->orderBy('sort_order')->get(),
                'heroBanners' => \App\Models\HeroBanner::where('is_active', true)->orderBy('sort_order')->get(),
            ]);
        });
        View::composer('layouts.navigation', function ($view) {
            $view->with([
                'siteSetting' => SiteSetting::first(),
                'menuItems' => MenuItem::where('is_active', true)->orderBy('sort_order')->get(),
            ]);
        });

        
    }
}
