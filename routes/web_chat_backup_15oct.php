<?php

use Illuminate\Support\Facades\Route;
//Controller
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\ForgotPasswordController;
// chat controller
use App\Http\Controllers\ChatController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GroupController;
//message
use App\Http\Controllers\MessageController;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
// Middelware
use App\Http\Middleware\CheckRole;
//Models declare
use App\Models\User;
//Insertia.js
use Inertia\Inertia;


// check the user already login or not
Route::get('/', function () {
    if (Auth::check()) {
        if (Auth::user()->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }
        
    }
    return redirect()->route('login');
});


// registration part 
Route::get('/register',[RegisterController::class, 'registration'])->name('register');

Route::post('/registered', [RegisterController::class, 'register'])->name('register.store');

Route::get('/login',[LoginController::class, 'login'])->name('login');
Route::post('/login',[LoginController::class, 'login_page'])->name('login.complete');
//Logout
Route::post('/logout',[LogoutController::class, 'logout'])->name('logout');

// Forgot Password
Route::get('/forgot-password', [ForgotPasswordController::class, 'showForgotPassword'])
    ->name('password.request');

Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLink'])
    ->name('password.email');

Route::get('/reset-password/{token}', [ForgotPasswordController::class, 'showResetPassword'])
    ->name('password.reset');

Route::post('/reset-password', [ForgotPasswordController::class, 'resetPassword'])
    ->name('password.update');
// END FORGOT PASSWORD ROUTE
// Middleware Auth
Route::middleware('auth')->group(function(){
//chat page open route
 // Chat page
Route::get('/chat', [ChatController::class, 'chat_index'])->name('chat.index');
// Private chat - encrypted user ID
    // Route::get('/chat/{user}', [ChatController::class, 'conversation'])->name('chat.conversation');
    Route::get('/chat/{id}', [ChatController::class, 'conversation'])->name('chat.conversation');

    // Messages route
 Route::post('/chat/message', [ChatController::class, 'sendMessage'])
        ->name('chat.message');

    Route::post('/chat/video', [ChatController::class, 'sendVideo'])
    ->name('chat.video');
    
    Route::post('/chat/image', [ChatController::class, 'sendImage'])
    ->name('chat.image');

Route::post('/chat/pdf', [ChatController::class, 'sendFile'])
    ->name('chat.pdf');
    // message send
    Route::post('/messages/send-audio', [MessageController::class, 'sendAudio'])
    ->name('messages.send-audio');
    // group create
    Route::post('/group/create', [ChatController::class, 'createGroup'])
    ->name('group.create');
    
//    groupController
Route::get('/chat/group/{id}', [GroupController::class, 'show'])
    ->name('chat.group');
Route::post('/messages/read', [ChatController::class, 'markAsRead'])->name('messages.read');
});

// Admin
Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/users', [DashboardController::class, 'userList'])->name('users.index');
        Route::post('/users/{user}/block',[DashboardController::class, 'blockUser'])->name('users.block');
       Route::post('/users/{user}/unblock', [DashboardController::class, 'unblockUser'])->name('users.unblock');
    //user  data edit update
    Route::get('/users/{user}/edit', [DashboardController::class, 'editUser'])->name('users.edit');

    Route::put('/users/{user}', [DashboardController::class, 'updateUser'])->name('users.update');
    Route::delete('/users/{user}', [DashboardController::class, 'deleteUser'])->name('users.delete');
    
    //ADMIN GROUP
        Route::get('/groups', [DashboardController::class, 'groupList'])->name('groups.index');
        Route::delete('/groups/{conversation}/delete', [DashboardController::class, 'deleteGroup'])->name('groups.delete');
        // Group member view
        Route::get('/groups/{conversation}/members', [DashboardController::class, 'groupMembers'])->name('groups.members');
        Route::post('/users', [DashboardController::class, 'createUser'])
    ->name('users.create');
    // admin [profile]
        Route::get('/profile', [DashboardController::class, 'admin_profile'])->name('profile');
        
        Route::get('/profile/{user}/edit', [DashboardController::class, 'editAdminProfile'])->name('profile.edit');

        Route::put('/profile/{user}', [DashboardController::class, 'updateAdminProfile'])->name('profile.update');

        Route::delete('/profile/{user}',[DashboardController::class, 'deleteAdminProfile'])->name('profile.delete');
});

// admin registration
// Admin Registration - no auth middleware
Route::get('/admin/register', function () { return Inertia::render('Admin/CreateAdmin');})->name('admin.register');

Route::post('/admin/create_admin', [DashboardController::class, 'storeAdmin'])->name('admin.register.store');