<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\ConversationController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\NotificationController;

// Rediriger la page d'accueil vers les conversations pour les utilisateurs connectés
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('conversations.index');
    }
    return Inertia::render('Welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    // Rediriger dashboard vers conversations aussi
    Route::get('dashboard', function () {
        return redirect()->route('conversations.index');
    })->name('dashboard');

    // Routes pour les conversations
    Route::get('/conversations', [ConversationController::class, 'index'])->name('conversations.index');
    Route::get('/conversations/{conversation}', [ConversationController::class, 'show'])->name('conversations.show');
    Route::post('/conversations', [ConversationController::class, 'store'])->name('conversations.store');
    Route::post('/conversations/{conversation}/users', [ConversationController::class, 'addUser'])->name('conversations.addUser');

    // Routes pour les messages
    Route::post('/conversations/{conversation}/messages', [MessageController::class, 'store'])->name('messages.store');
    Route::put('/messages/{message}', [MessageController::class, 'update'])->name('messages.update');
    Route::delete('/messages/{message}', [MessageController::class, 'destroy'])->name('messages.destroy');

    // Routes pour les notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::put('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.markAsRead');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.markAllAsRead');
    Route::delete('/notifications/{notification}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('notifications.unreadCount');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
