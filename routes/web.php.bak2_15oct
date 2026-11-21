
text/x-generic web.php ( PHP script, ASCII text, with CRLF line terminators )
<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('dashboard');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Wallet routes
    Route::get('/wallet', [App\Http\Controllers\WalletController::class, 'index'])->name('wallet.index');
    Route::get('/wallet/deposit', [App\Http\Controllers\WalletController::class, 'deposit'])->name('wallet.deposit');
    Route::post('/wallet/deposit', [App\Http\Controllers\WalletController::class, 'storeDeposit'])->name('wallet.deposit.store');
    Route::get('/wallet/withdraw', [App\Http\Controllers\WalletController::class, 'withdraw'])->name('wallet.withdraw');
    Route::post('/wallet/withdraw', [App\Http\Controllers\WalletController::class, 'storeWithdraw'])->name('wallet.withdraw.store');
});

require __DIR__.'/auth.php';

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest:admin')->group(function () {
        Route::get('/login', [App\Http\Controllers\Admin\Auth\AdminLoginController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [App\Http\Controllers\Admin\Auth\AdminLoginController::class, 'login']);

        Route::get('/forgot-password', [App\Http\Controllers\Admin\Auth\AdminForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
        Route::post('/forgot-password', [App\Http\Controllers\Admin\Auth\AdminForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');

        Route::get('/reset-password/{token}', [App\Http\Controllers\Admin\Auth\AdminResetPasswordController::class, 'showResetForm'])->name('password.reset');
        Route::post('/reset-password', [App\Http\Controllers\Admin\Auth\AdminResetPasswordController::class, 'reset'])->name('password.update');
    });

    Route::post('/logout', [App\Http\Controllers\Admin\Auth\AdminLoginController::class, 'logout'])->name('logout');

    Route::middleware('auth:admin')->group(function () {
        Route::get('/dashboard', function () {
            return view('admin.dashboard');
        })->name('dashboard');

        Route::get('/settings', [App\Http\Controllers\Admin\SettingsController::class, 'edit'])->name('settings.edit');
        Route::post('/settings', [App\Http\Controllers\Admin\SettingsController::class, 'update'])->name('settings.update');

        Route::get('/menu', [App\Http\Controllers\Admin\MenuController::class, 'index'])->name('menu.index');
        Route::post('/menu', [App\Http\Controllers\Admin\MenuController::class, 'store'])->name('menu.store');
        Route::post('/menu/{menuItem}/toggle', [App\Http\Controllers\Admin\MenuController::class, 'toggle'])->name('menu.toggle');
        Route::delete('/menu/{menuItem}', [App\Http\Controllers\Admin\MenuController::class, 'destroy'])->name('menu.destroy');

        Route::get('/hero-banners', [App\Http\Controllers\Admin\HeroBannerController::class, 'index'])->name('hero-banners.index');
        Route::post('/hero-banners', [App\Http\Controllers\Admin\HeroBannerController::class, 'store'])->name('hero-banners.store');
        Route::post('/hero-banners/{heroBanner}/toggle', [App\Http\Controllers\Admin\HeroBannerController::class, 'toggle'])->name('hero-banners.toggle');
        Route::delete('/hero-banners/{heroBanner}', [App\Http\Controllers\Admin\HeroBannerController::class, 'destroy'])->name('hero-banners.destroy');

        Route::get('/sport-categories', [App\Http\Controllers\Admin\SportCategoryController::class, 'index'])->name('sport-categories.index');
        Route::post('/sport-categories', [App\Http\Controllers\Admin\SportCategoryController::class, 'store'])->name('sport-categories.store');
        Route::post('/sport-categories/{sportCategory}/toggle', [App\Http\Controllers\Admin\SportCategoryController::class, 'toggle'])->name('sport-categories.toggle');
        Route::delete('/sport-categories/{sportCategory}', [App\Http\Controllers\Admin\SportCategoryController::class, 'destroy'])->name('sport-categories.destroy');

        Route::get('/featured-contests', [App\Http\Controllers\Admin\FeaturedContestController::class, 'index'])->name('featured-contests.index');
        Route::post('/featured-contests', [App\Http\Controllers\Admin\FeaturedContestController::class, 'store'])->name('featured-contests.store');
        Route::post('/featured-contests/{featuredContest}/toggle', [App\Http\Controllers\Admin\FeaturedContestController::class, 'toggle'])->name('featured-contests.toggle');
        Route::delete('/featured-contests/{featuredContest}', [App\Http\Controllers\Admin\FeaturedContestController::class, 'destroy'])->name('featured-contests.destroy');

        Route::get('/top-winners', [App\Http\Controllers\Admin\TopWinnerController::class, 'index'])->name('top-winners.index');
        Route::post('/top-winners', [App\Http\Controllers\Admin\TopWinnerController::class, 'store'])->name('top-winners.store');
        Route::post('/top-winners/{topWinner}/toggle', [App\Http\Controllers\Admin\TopWinnerController::class, 'toggle'])->name('top-winners.toggle');
        Route::delete('/top-winners/{topWinner}', [App\Http\Controllers\Admin\TopWinnerController::class, 'destroy'])->name('top-winners.destroy');

        Route::get('/site-stats', [App\Http\Controllers\Admin\SiteStatController::class, 'index'])->name('site-stats.index');
        Route::post('/site-stats', [App\Http\Controllers\Admin\SiteStatController::class, 'store'])->name('site-stats.store');
        Route::post('/site-stats/{siteStat}/toggle', [App\Http\Controllers\Admin\SiteStatController::class, 'toggle'])->name('site-stats.toggle');
        Route::delete('/site-stats/{siteStat}', [App\Http\Controllers\Admin\SiteStatController::class, 'destroy'])->name('site-stats.destroy');
    });
});

// In Play route (auto-added)
Route::get('/in-play', [App\Http\Controllers\InPlayController::class, 'index'])
    ->middleware('auth')
    ->name('in-play.index');