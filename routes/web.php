<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\KnowledgeBaseController;
use App\Http\Middleware\CheckActiveUser; // <-- Our Security Guard
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    return view('welcome');
});

// ABSOLUTE SECURITY: Every authenticated route sits inside this fortress
Route::middleware(['auth', CheckActiveUser::class])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Tickets
    Route::resource('tickets', TicketController::class);
    Route::patch('/tickets/{ticket}/claim', [TicketController::class, 'claim'])->name('tickets.claim');
    Route::post('/tickets/{ticket}/comments', [CommentController::class, 'store'])->name('comments.store');

    // <-- ADD THIS LINE FOR CSAT -->
    Route::post('/tickets/{ticket}/rate', [TicketController::class, 'submitRating'])->name('tickets.rate');

    // Users
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::patch('/users/{user}/role', [UserController::class, 'updateRole'])->name('users.updateRole');
    Route::patch('/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggleStatus');

    // Notifications & Audit Logs
    Route::post('/notifications/mark-read', function () {
        Auth::user()->unreadNotifications->markAsRead();
        return back();
    })->name('notifications.read');

    Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');

    // Knowledge Base API
    Route::get('/kb/search', [KnowledgeBaseController::class, 'search'])->name('kb.search');

    // System Settings
    Route::get('/settings', [\App\Http\Controllers\SettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings', [\App\Http\Controllers\SettingsController::class, 'update'])->name('settings.update');
});

require __DIR__ . '/auth.php';
