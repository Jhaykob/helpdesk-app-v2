<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\KnowledgeBaseController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // This single line automatically creates URLs for index, create, store, show, edit, update, and destroy!
    Route::resource('tickets', TicketController::class);

    // <-- ADD THIS CLAIM ROUTE -->
    Route::patch('/tickets/{ticket}/claim', [TicketController::class, 'claim'])->name('tickets.claim');

    Route::post('/tickets/{ticket}/comments', [CommentController::class, 'store'])->name('comments.store');

    // <-- ADD THESE USER MANAGEMENT ROUTES -->
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::patch('/users/{user}/role', [UserController::class, 'updateRole'])->name('users.updateRole');

    // <-- NEW: Route to mark notifications as read -->
    Route::post('/notifications/mark-read', function () {
        Auth::user()->unreadNotifications->markAsRead();
        return back();
    })->name('notifications.read');

    // <-- NEW: Global Audit Logs Route -->
    Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');

    Route::get('/kb/search', [KnowledgeBaseController::class, 'search'])->name('kb.search');
});

require __DIR__ . '/auth.php';
