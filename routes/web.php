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

    // Bet history (every game round the player played)
    Route::get('/bets', [App\Http\Controllers\BetHistoryController::class, 'index'])->name('bets.index');

    // Live support chat
    Route::get('/support/messages', [App\Http\Controllers\SupportChatController::class, 'messages'])->name('support.messages');
    Route::post('/support/messages', [App\Http\Controllers\SupportChatController::class, 'send'])
        ->middleware('throttle:20,1')->name('support.send');

    // Admin "login as user": return to the admin panel
    Route::post('/impersonation/leave', [App\Http\Controllers\Admin\UserManagementController::class, 'leaveImpersonation'])->name('impersonation.leave');
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

});

// ---- AS Tech games integration ----
// Browsing the catalogue is public; launching a game needs a login.
Route::get('/games', function (\Illuminate\Http\Request $request) {
    $qs = $request->getQueryString();
    return redirect('/dashboard' . ($qs ? '?' . $qs : '') . '#ag-root');
})->name('astech.games');
Route::middleware('throttle:60,1')->group(function () {
    Route::get('/games/list', [App\Http\Controllers\AsTechController::class, 'gameList'])->name('astech.list');
    Route::get('/games/meta', [App\Http\Controllers\AsTechController::class, 'gameMeta'])->name('astech.meta');
});
Route::get('/games/launch/{gameId}', [App\Http\Controllers\AsTechController::class, 'launch'])
    ->middleware('auth')->name('astech.launch');
Route::post('/api/astech/callback', [App\Http\Controllers\AsTechController::class, 'callback'])
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class]);
require __DIR__.'/admin.php';
