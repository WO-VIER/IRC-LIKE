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
    // Dashboard reste inchangé - retour à l'original
    Route::get('dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    // Routes pour les conversations
    Route::get('/conversations', [ConversationController::class, 'index2'])->name('conversations.index');
    Route::get('conversations/create', [ConversationController::class, 'create'])->name('conversations.create');
    Route::post('conversations', [ConversationController::class, 'store'])->name('conversations.store');
    Route::get('conversations/{conversation}', [ConversationController::class, 'show'])->name('conversations.show'); // Cette route doit pointer vers la méthode show qui rend Messages.vue
    Route::put('conversations/{conversation}', [ConversationController::class, 'update'])->name('conversations.update');
    Route::delete('conversations/{conversation}', [ConversationController::class, 'destroy'])->name('conversations.destroy');

    Route::post('conversations/{conversation}/add-user', [ConversationController::class, 'addUser'])->name('conversations.add-user');
    Route::delete('conversations/{conversation}/leave', [ConversationController::class, 'leave'])->name('conversations.leave');

    // Routes pour les messages
    Route::post('conversations/{conversation}/messages', [MessageController::class, 'store'])->name('messages.store');
    Route::put('messages/{message}', [MessageController::class, 'update'])->name('messages.update');
    Route::delete('messages/{message}', [MessageController::class, 'destroy'])->name('messages.destroy');
    Route::post('conversations/{conversation}/mark-as-read', [MessageController::class, 'markAsRead'])->name('messages.mark-as-read');

    // Routes pour les notifications
    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
    Route::delete('notifications/{notification}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
    Route::get('notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('notifications.unread-count');
});

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
