<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ConversationController extends Controller
{
    /**
     * Afficher la liste des conversations
     */
    public function index()
    {
        $userId = Auth::id();

        $conversations = Auth::user()->conversations()
            ->with(['users'])
            ->get()
            ->map(function ($conversation) use ($userId) {
                $lastMessage = \DB::table('messages')
                    ->where('conversation_id', $conversation->id)
                    ->orderBy('created_at', 'desc')
                    ->first();

                $lastMessageData = null;
                if ($lastMessage) {
                    $user = \App\Models\User::find($lastMessage->user_id);
                    $lastMessageData = [
                        'id' => $lastMessage->id,
                        'content' => $lastMessage->content,
                        'user' => $user->name,
                        'user_id' => $lastMessage->user_id,
                        'created_at' => $lastMessage->created_at,
                    ];
                }

                $currentUserPivot = $conversation->users->where('id', $userId)->first();
                $lastReadAt = $currentUserPivot ? $currentUserPivot->pivot->last_read_at : null;

                $unreadCount = 0;
                if ($lastMessage && $lastMessage->user_id !== $userId) {
                    if (!$lastReadAt) {
                        $unreadCount = \DB::table('messages')
                            ->where('conversation_id', $conversation->id)
                            ->where('user_id', '!=', $userId)
                            ->count();
                    } else {
                        $unreadCount = \DB::table('messages')
                            ->where('conversation_id', $conversation->id)
                            ->where('user_id', '!=', $userId)
                            ->where('created_at', '>', $lastReadAt)
                            ->count();
                    }
                }

                return [
                    'id' => $conversation->id,
                    'name' => $conversation->name,
                    'type' => $conversation->type,
                    'description' => $conversation->description,
                    'last_activity_at' => $conversation->last_activity_at,
                    'users' => $conversation->users,
                    'last_message' => $lastMessageData,
                    'unread_count' => $unreadCount,
                ];
            })
            ->sortByDesc('last_activity_at')
            ->values();

        return Inertia::render('Conversations/Index', [
            'conversations' => $conversations
        ]);
    }

    /**
     * Afficher le formulaire de création
     */
    public function create()
    {
        $users = User::where('id', '!=', Auth::id())->get();

        return Inertia::render('Conversations/Create', [
            'users' => $users
        ]);
    }

    /**
     * Créer une nouvelle conversation
     */
    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:private,group',
            'name' => 'required_if:type,group|nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'exists:users,id',
        ]);

        // Vérifier que l'utilisateur ne s'ajoute pas lui-même
        $userIds = array_filter($request->user_ids, fn($id) => $id != Auth::id());

        if (empty($userIds)) {
            return back()->withErrors(['user_ids' => 'Vous devez sélectionner au moins un utilisateur.']);
        }

        // Pour les conversations privées, vérifier qu'une conversation n'existe pas déjà
        if ($request->type === 'private') {
            if (count($userIds) > 1) {
                return back()->withErrors(['user_ids' => 'Une conversation privée ne peut avoir qu\'un seul destinataire.']);
            }

            $existingConversation = Conversation::where('type', 'private')
                ->whereHas('users', function ($query) use ($userIds) {
                    $query->where('user_id', $userIds[0]);
                })
                ->whereHas('users', function ($query) {
                    $query->where('user_id', Auth::id());
                })
                ->first();

            if ($existingConversation) {
                return redirect()->route('conversations.show', $existingConversation);
            }
        }

        // Créer la conversation
        $conversation = Conversation::create([
            'type' => $request->type,
            'name' => $request->name,
            'description' => $request->description,
            'created_by' => Auth::id(),
            'last_activity_at' => now(),
        ]);

        // Ajouter les utilisateurs
        $conversation->users()->attach(Auth::id(), [
            'role' => 'admin',
            'joined_at' => now(),
        ]);

        foreach ($userIds as $userId) {
            $conversation->users()->attach($userId, [
                'role' => 'member',
                'joined_at' => now(),
            ]);
        }

        return redirect()->route('conversations.show', $conversation);
    }

    /**
     * Afficher une conversation
     */
    public function show(Conversation $conversation)
    {
        // Vérifier que l'utilisateur fait partie de la conversation
        if (!$conversation->users->contains(Auth::id())) {
            abort(403, 'Vous n\'avez pas accès à cette conversation.');
        }

        // Mettre à jour last_read_at pour marquer les messages comme lus
        $conversation->users()->updateExistingPivot(Auth::id(), [
            'last_read_at' => now(),
        ]);

        $conversation->load([
            'users',
            'messages.user'
        ]);

        // Récupérer toutes les conversations de l'utilisateur pour la sidebar
        $userId = Auth::id();
        $conversations = Auth::user()->conversations()
            ->with(['users'])
            ->get()
            ->map(function ($conv) use ($userId) {
                $lastMessage = \DB::table('messages')
                    ->where('conversation_id', $conv->id)
                    ->orderBy('created_at', 'desc')
                    ->first();

                $lastMessageData = null;
                if ($lastMessage) {
                    $user = \App\Models\User::find($lastMessage->user_id);
                    $lastMessageData = [
                        'id' => $lastMessage->id,
                        'content' => $lastMessage->content,
                        'user' => $user->name,
                        'user_id' => $lastMessage->user_id,
                        'created_at' => $lastMessage->created_at,
                    ];
                }

                $currentUserPivot = $conv->users->where('id', $userId)->first();
                $lastReadAt = $currentUserPivot ? $currentUserPivot->pivot->last_read_at : null;

                $unreadCount = 0;
                if ($lastMessage && $lastMessage->user_id !== $userId) {
                    if (!$lastReadAt) {
                        $unreadCount = \DB::table('messages')
                            ->where('conversation_id', $conv->id)
                            ->where('user_id', '!=', $userId)
                            ->count();
                    } else {
                        $unreadCount = \DB::table('messages')
                            ->where('conversation_id', $conv->id)
                            ->where('user_id', '!=', $userId)
                            ->where('created_at', '>', $lastReadAt)
                            ->count();
                    }
                }

                return [
                    'id' => $conv->id,
                    'name' => $conv->name,
                    'type' => $conv->type,
                    'description' => $conv->description,
                    'last_activity_at' => $conv->last_activity_at,
                    'users' => $conv->users,
                    'last_message' => $lastMessageData,
                    'unread_count' => $unreadCount,
                ];
            })
            ->sortByDesc('last_activity_at')
            ->values();

        return Inertia::render('Conversations/Messages', [
            'conversation' => $conversation,
            'conversations' => $conversations
        ]);
    }

    /**
     * Mettre à jour une conversation (nom, description)
     */
    public function update(Request $request, Conversation $conversation)
    {
        // Vérifier que l'utilisateur est admin de la conversation
        $userRole = $conversation->users()->where('user_id', Auth::id())->first();

        if (!$userRole || $userRole->pivot->role !== 'admin') {
            abort(403, 'Seuls les administrateurs peuvent modifier la conversation.');
        }

        $request->validate([
            'name' => 'required_if:type,group|nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        $conversation->update([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return back()->with('success', 'Conversation mise à jour avec succès.');
    }

    /**
     * Ajouter un utilisateur à une conversation de groupe
     */
    public function addUser(Request $request, Conversation $conversation)
    {
        // Vérifier que c'est un groupe
        if ($conversation->type !== 'group') {
            abort(403, 'Vous ne pouvez ajouter des utilisateurs qu\'aux groupes.');
        }

        // Vérifier que l'utilisateur est admin
        $userRole = $conversation->users()->where('user_id', Auth::id())->first();

        if (!$userRole || $userRole->pivot->role !== 'admin') {
            abort(403, 'Seuls les administrateurs peuvent ajouter des membres.');
        }

        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        // Vérifier que l'utilisateur n'est pas déjà dans la conversation
        if ($conversation->users->contains($request->user_id)) {
            return back()->withErrors(['user_id' => 'Cet utilisateur fait déjà partie de la conversation.']);
        }

        $conversation->users()->attach($request->user_id, [
            'role' => 'member',
            'joined_at' => now(),
        ]);

        return back()->with('success', 'Utilisateur ajouté avec succès.');
    }

    /**
     * Quitter une conversation
     * LOGIQUE IMPORTANTE : Ne supprime PAS les groupes, seulement les conversations privées
     */
    public function leave(Conversation $conversation)
    {
        // Vérifier que l'utilisateur fait partie de la conversation
        if (!$conversation->users->contains(Auth::id())) {
            abort(403, 'Vous ne faites pas partie de cette conversation.');
        }

        // Retirer l'utilisateur de la conversation
        $conversation->users()->detach(Auth::id());

        // LOGIQUE DIFFÉRENTE selon le type de conversation
        if ($conversation->type === 'private') {
            // Pour les conversations privées : supprimer si plus qu'un seul utilisateur
            $remainingUsers = $conversation->users()->count();

            if ($remainingUsers <= 1) {
                $conversation->delete();
            }
        } else if ($conversation->type === 'group') {
            // Pour les groupes : supprimer SEULEMENT si plus aucun membre
            $remainingUsers = $conversation->users()->count();

            if ($remainingUsers === 0) {
                $conversation->delete();
            }
        }

        return redirect()->route('conversations.index')
            ->with('success', 'Vous avez quitté la conversation.');
    }

    /**
     * Supprimer complètement une conversation (admin seulement)
     */
    public function destroy(Conversation $conversation)
    {
        // Vérifier que l'utilisateur est admin
        $userRole = $conversation->users()->where('user_id', Auth::id())->first();

        if (!$userRole || $userRole->pivot->role !== 'admin') {
            abort(403, 'Seuls les administrateurs peuvent supprimer la conversation.');
        }

        $conversation->delete();

        return redirect()->route('conversations.index')
            ->with('success', 'Conversation supprimée avec succès.');
    }
}
