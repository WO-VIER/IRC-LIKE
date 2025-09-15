<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Récupérer toutes les notifications de l'utilisateur
     */
    public function index()
    {
        $notifications = Auth::user()->notifications()
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'notifications' => $notifications->items(),
            'unread_count' => Auth::user()->unreadNotifications()->count(),
        ]);
    }

    /**
     * Marquer une notification comme lue
     */
    public function markAsRead(Notification $notification)
    {
        // Vérifier que la notification appartient à l'utilisateur connecté
        if ($notification->user_id !== Auth::id()) {
            abort(403, 'Cette notification ne vous appartient pas.');
        }

        $notification->markAsRead();

        return response()->json(['message' => 'Notification marquée comme lue.']);
    }

    /**
     * Marquer toutes les notifications comme lues
     */
    public function markAllAsRead()
    {
        Auth::user()->unreadNotifications()->update(['read_at' => now()]);

        return response()->json(['message' => 'Toutes les notifications ont été marquées comme lues.']);
    }

    /**
     * Supprimer une notification
     */
    public function destroy(Notification $notification)
    {
        // Vérifier que la notification appartient à l'utilisateur connecté
        if ($notification->user_id !== Auth::id()) {
            abort(403, 'Cette notification ne vous appartient pas.');
        }

        $notification->delete();

        return response()->json(['message' => 'Notification supprimée.']);
    }

    /**
     * Récupérer le nombre de notifications non lues
     */
    public function unreadCount()
    {
        return response()->json([
            'unread_count' => Auth::user()->unreadNotifications()->count(),
        ]);
    }
}
