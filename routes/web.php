<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\KnowledgeBaseController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\MacroController;
use App\Http\Controllers\RoleController;
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

    // Ticket Actions (Comments, CSAT, Resolution)
    Route::post('/tickets/{ticket}/comments', [CommentController::class, 'store'])->name('comments.store');
    Route::post('/tickets/{ticket}/rate', [TicketController::class, 'submitRating'])->name('tickets.rate');
    Route::get('/tickets/{ticket}/confirm-resolution', [TicketController::class, 'confirmResolution'])->name('tickets.confirmResolution');
    Route::get('/tickets/{ticket}/reject-resolution', [TicketController::class, 'rejectResolution'])->name('tickets.rejectResolution');

    // User Management
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::patch('/users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

    // Roles & Security Dashboard
    Route::resource('roles', RoleController::class)->except(['create', 'show', 'edit']);

    // Notifications (Cleaned and deduplicated)
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.markAllRead');

    // Audit Logs
    Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');

    // Knowledge Base CMS (Admin/Agent Panel)
    Route::resource('articles', ArticleController::class)->except(['show']);

    // Public Knowledge Base Portal
    Route::get('/kb', [KnowledgeBaseController::class, 'index'])->name('kb.index');
    Route::get('/kb/{article}', [KnowledgeBaseController::class, 'show'])->name('kb.show');

    // System Settings
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');

    // Macro / Canned Responses Management
    Route::resource('macros', MacroController::class)->except(['create', 'show', 'edit']);

    // Ticket Actions
    Route::post('/tickets/{ticket}/collaborators', [TicketController::class, 'addCollaborator'])->name('tickets.collaborators.add');
    Route::get('/tickets/{ticket}/attachments/{attachment}', [TicketController::class, 'downloadAttachment'])->name('tickets.attachment');
});

require __DIR__ . '/auth.php';
