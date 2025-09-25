<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConversationUser extends Model
{
    use HasFactory;

    protected $fillable = [
        'conversation_id',
        'user_id',
        'role',
        'joined_at',
        'last_read_at',
        'is_muted',
    ];

    protected $casts = [
        'joined_at' => 'datetime',
        'last_read_at' => 'datetime',
        'is_muted' => 'boolean',
    ];

    /**
     * La conversation
     */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    /**
     * L'utilisateur
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Vérifier si l'utilisateur est admin de la conversation
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Vérifier si l'utilisateur est modérateur
     */
    public function isModerator(): bool
    {
        return $this->role === 'moderator';
    }

    /**
     * Nettoyer les doublons pour une conversation donnée
     */
    public static function removeDuplicates($conversationId)
    {
        $duplicates = self::select('user_id', 'conversation_id')
            ->where('conversation_id', $conversationId)
            ->groupBy('user_id', 'conversation_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicates as $duplicate) {
            // Garder seulement le premier enregistrement et supprimer les autres
            $records = self::where('user_id', $duplicate->user_id)
                ->where('conversation_id', $duplicate->conversation_id)
                ->orderBy('id')
                ->get();

            // Supprimer tous sauf le premier
            $records->skip(1)->each(function ($record) {
                $record->delete();
            });
        }
    }
}
