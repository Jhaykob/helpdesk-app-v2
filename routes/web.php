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

    // Email Resolution Action Links
    Route::get('/tickets/{ticket}/confirm-resolution', [TicketController::class, 'confirmResolution'])->name('tickets.confirmResolution');
    Route::get('/tickets/{ticket}/reject-resolution', [TicketController::class, 'rejectResolution'])->name('tickets.rejectResolution');

    // User Management Routes
    Route::get('/users', [\App\Http\Controllers\UserController::class, 'index'])->name('users.index');
    Route::post('/users', [\App\Http\Controllers\UserController::class, 'store'])->name('users.store');
    Route::patch('/users/{user}', [\App\Http\Controllers\UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [\App\Http\Controllers\UserController::class, 'destroy'])->name('users.destroy');

    // Notifications & Audit Logs
    Route::post('/notifications/mark-read', function () {
        Auth::user()->unreadNotifications->markAsRead();
        return back();
    })->name('notifications.read');

    Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');

    // Knowledge Base CMS
    Route::resource('articles', \App\Http\Controllers\ArticleController::class)->except(['show']);

    // Public Knowledge Base Portal
    Route::get('/kb', [KnowledgeBaseController::class, 'index'])->name('kb.index');
    Route::get('/kb/{article}', [KnowledgeBaseController::class, 'show'])->name('kb.show');

    // System Settings
    Route::get('/settings', [\App\Http\Controllers\SettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings', [\App\Http\Controllers\SettingsController::class, 'update'])->name('settings.update');

    // Macro / Canned Responses Management
    Route::resource('macros', \App\Http\Controllers\MacroController::class)->except(['create', 'show', 'edit']);
});

require __DIR__ . '/auth.php';
