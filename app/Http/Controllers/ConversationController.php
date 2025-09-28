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
     * Afficher toutes les conversations de l'utilisateur
     */
    public function index()
    {
        $conversations = Auth::user()->conversations()
            ->with(['users', 'lastMessage.user'])
            ->get()
            ->map(function ($conversation) {
                return [
                    'id' => $conversation->id,
                    'name' => $conversation->name,
                    'type' => $conversation->type,
                    'description' => $conversation->description,
                    'last_activity_at' => $conversation->last_activity_at,
                    'users' => $conversation->users
                        ->unique('id') // Éliminer les doublons par ID utilisateur
                        ->map(function ($user) {
                            return [
                                'id' => $user->id,
                                'name' => $user->name,
                                'email' => $user->email,
                                'role' => $user->pivot->role,
                                'is_muted' => $user->pivot->is_muted,
                                'last_read_at' => $user->pivot->last_read_at,
                            ];
                        })->values(), // Réindexer après unique()
                    'last_message' => $conversation->lastMessage->first() ? [
                        'id' => $conversation->lastMessage->first()->id,
                        'content' => $conversation->lastMessage->first()->content,
                        'user' => $conversation->lastMessage->first()->user ?
                            $conversation->lastMessage->first()->user->name :
                            'Utilisateur inconnu',
                        'created_at' => $conversation->lastMessage->first()->created_at,
                    ] : null,
                ];
            });

        return Inertia::render('Conversations/Index', [
            'conversations' => $conversations,
        ]);
    }

    public function index2()
    {
        $conversations = auth()->user()
            ->conversations()
            ->with(['users', 'messages' => fn($q) => $q->latest()->take(1)])
            ->get();
        return Inertia::render('Conversations/Index', [
            'conversations' => $conversations
        ]);
    }

    public function show2(Conversation $conversation)
    {
        if (!$conversation->users->contains(Auth::id())) {
            abort(403, 'Vous n\'avez pas accès à cette conversation.');
        }

        $messages = $conversation->messages()
            ->with('user')
            ->orderBy('created_at')
            ->get();

        return Inertia::render('Conversations/Show', [
            'conversations' => $conversation->load('users'),
            'messages' => $messages
        ]);
    }

    /**
     * Afficher une conversation spécifique
     */
    public function show(Conversation $conversation)
    {
        // Vérifier que l'utilisateur fait partie de la conversation
        if (!$conversation->users->contains(Auth::id())) {
            abort(403, 'Vous n\'avez pas accès à cette conversation.');
        }

        $conversation->load(['users', 'messages.user', 'messages.replyTo.user']);

        return Inertia::render('Conversations/Messages', [
            'conversation' => [
                'id' => $conversation->id,
                'name' => $conversation->name,
                'type' => $conversation->type,
                'description' => $conversation->description,
                'users' => $conversation->users->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'role' => $user->pivot->role,
                    ];
                }),
                'messages' => $conversation->messages->map(function ($message) {
                    return [
                        'id' => $message->id,
                        'content' => $message->content,
                        'type' => $message->type,
                        'is_edited' => $message->is_edited,
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
                    ];
                }),
            ],
        ]);
    }

    /**
     * Créer une nouvelle conversation
     */
    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:private,group',
            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'exists:users,id',
        ]);

        // Pour les conversations privées, vérifier qu'il n'y a qu'un seul autre utilisateur
        if ($request->input('type') === 'private' && count($request->input('user_ids')) !== 1) {
            return response()->json(['message' => 'Une conversation privée doit avoir exactement 2 participants.'], 422);
        }

        // Vérifier si une conversation privée existe déjà entre ces utilisateurs
        if ($request->input('type') === 'private') {
            $existingConversation = Conversation::where('type', 'private')
                ->whereHas('users', function ($query) use ($request) {
                    $query->where('user_id', Auth::id());
                })
                ->whereHas('users', function ($query) use ($request) {
                    $query->where('user_id', $request->input('user_ids')[0]);
                })
                ->first();

            if ($existingConversation) {
                // Rediriger vers la page Messages au lieu de Show
                return Inertia::location("/conversations/{$existingConversation->id}");
            }
        }

        $conversation = Conversation::create([
            'name' => $request->input('name'),
            'type' => $request->input('type'),
            'description' => $request->input('description'),
            'created_by' => Auth::id(),
            'last_activity_at' => now(),
        ]);

        // Ajouter le créateur à la conversation comme admin
        $conversation->users()->attach(Auth::id(), [
            'role' => 'admin',
            'joined_at' => now(),
        ]);

        // Ajouter les autres utilisateurs
        foreach ($request->input('user_ids') as $userId) {
            $conversation->users()->attach($userId, [
                'role' => 'member',
                'joined_at' => now(),
            ]);
        }

        // Redirection correcte
        return redirect()->route('conversations.show', $conversation);
    }

    /**
     * Ajouter un utilisateur à une conversation
     */
    public function addUser(Request $request, Conversation $conversation)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        // Vérifier que l'utilisateur connecté est admin de la conversation
        $userRole = $conversation->users()->where('user_id', Auth::id())->first();
        if (!$userRole || $userRole->pivot->role !== 'admin') {
            abort(403, 'Seuls les administrateurs peuvent ajouter des utilisateurs.');
        }

        // Vérifier que l'utilisateur n'est pas déjà dans la conversation
        if ($conversation->users()->where('user_id', $request->input('user_id'))->exists()) {
            return response()->json(['message' => 'L\'utilisateur fait déjà partie de cette conversation.'], 422);
        }

        $conversation->users()->attach($request->input('user_id'), [
            'role' => 'member',
            'joined_at' => now(),
        ]);

        $conversation->update(['last_activity_at' => now()]);

        return response()->json(['message' => 'Utilisateur ajouté avec succès.']);
    }

    /**
     * Quitter une conversation
     */
    public function leave(Conversation $conversation)
    {
        $conversation->users()->detach(Auth::id());

        $conversation->update(['last_activity_at' => now()]);

        return redirect()->route('conversations.index');
    }

    public function create()
    {
        // Récupérer tous les utilisateurs sauf l'utilisateur connecté
        $users = User::where('id', '!=', Auth::id())
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        return Inertia::render('Conversations/Create', [
            'users' => $users,
        ]);
    }
}
