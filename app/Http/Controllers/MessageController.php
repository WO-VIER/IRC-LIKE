<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class MessageController extends Controller
{
    /**
     * Envoyer un nouveau message
     */
    public function store(Request $request, Conversation $conversation)
    {
        // Vérifier que l'utilisateur fait partie de la conversation
        if (!$conversation->users->contains(Auth::id())) {
            abort(403, 'Vous n\'avez pas accès à cette conversation.');
        }

        $request->validate([
            'content' => 'required|string|max:5000',
            'reply_to' => 'nullable|exists:messages,id',
        ]);

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'user_id' => Auth::id(),
            'content' => $request->input('content'),
            'reply_to' => $request->input('reply_to'),
        ]);

        // Mettre à jour l'activité de la conversation
        $conversation->update(['last_activity_at' => now()]);

        // Rediriger vers la route GET au lieu de retourner Inertia directement
        return redirect()->route('conversations.show', $conversation);
    }

    /**
     * Modifier un message
     */
    public function update(Request $request, Message $message)
    {
        // Vérifier que l'utilisateur est l'auteur du message
        if ($message->user_id !== Auth::id()) {
            abort(403, 'Vous ne pouvez modifier que vos propres messages.');
        }

        $request->validate([
            'content' => 'required|string|max:5000',
        ]);

        $message->update([
            'content' => $request->input('content'),
            'is_edited' => true,
            'edited_at' => now(),
        ]);


        return response()->json(['message' => 'Message modifié avec succès.']);
    }

    /**
     * Supprimer un message
     */
    public function destroy(Message $message)
    {
        // Vérifier que l'utilisateur est l'auteur du message ou admin de la conversation
        $userRole = $message->conversation->users()->where('user_id', Auth::id())->first();

        if ($message->user_id !== Auth::id() && (!$userRole || $userRole->pivot->role !== 'admin')) {
            abort(403, 'Vous ne pouvez supprimer que vos propres messages ou être administrateur.');
        }

        $message->delete();


        return response()->json(['message' => 'Message supprimé avec succès.']);
    }

    /**
     * Marquer les messages comme lus
     */
    public function markAsRead(Request $request, Conversation $conversation)
    {
        // Mettre à jour le last_read_at pour l'utilisateur dans cette conversation
        $conversation->users()->updateExistingPivot(Auth::id(), [
            'last_read_at' => now(),
        ]);

        return response()->json(['message' => 'Messages marqués comme lus.']);
    }
}
