<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
            'content' => $request->input('content'), // ✅ Utiliser input() au lieu de ->content
            'reply_to' => $request->input('reply_to'), // ✅ Même chose ici
        ]);

        // Mettre à jour l'activité de la conversation
        $conversation->update(['last_activity_at' => now()]);

        // Charger les relations pour la réponse
        $message->load(['user', 'replyTo.user']);

        // TODO: Broadcast du message en temps réel
        // broadcast(new MessageSent($message));

        return response()->json([
            'message' => [
                'id' => $message->id,
                'content' => $message->content,
                'type' => $message->type,
                'created_at' => $message->created_at,
                'user' => [
                    'id' => $message->user->id,
                    'name' => $message->user->name,
                ],
                'reply_to' => $message->replyTo ? [
                    'id' => $message->replyTo->id,
                    'content' => $message->replyTo->content,
                    'user' => $message->replyTo->user->name,
                ] : null,
            ]
        ]);
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
            'content' => $request->input('content'), // ✅ Correction ici aussi
            'is_edited' => true,
            'edited_at' => now(),
        ]);

        // TODO: Broadcast de la modification en temps réel
        // broadcast(new MessageUpdated($message));

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

        // TODO: Broadcast de la suppression en temps réel
        // broadcast(new MessageDeleted($message));

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
