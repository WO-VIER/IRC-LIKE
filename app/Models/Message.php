<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'conversation_id',
        'user_id',
        'content',
        'type',
        'reply_to',
        'is_edited',
        'edited_at',
    ];

    protected $casts = [
        'is_edited' => 'boolean',
        'edited_at' => 'datetime',
    ];

    /**
     * La conversation à laquelle appartient le message
     */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    /**
     * L'auteur du message
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Le message auquel celui-ci répond
     */
    public function replyTo(): BelongsTo
    {
        return $this->belongsTo(Message::class, 'reply_to');
    }

    /**
     * Les messages qui répondent à celui-ci
     */
    public function replies(): HasMany
    {
        return $this->hasMany(Message::class, 'reply_to');
    }

    /**
     * Scope pour les messages de type texte
     */
    public function scopeText($query)
    {
        return $query->where('type', 'text');
    }

    /**
     * Scope pour les messages système
     */
    public function scopeSystem($query)
    {
        return $query->where('type', 'system');
    }

    /**
     * Vérifier si le message est un message système
     */
    public function isSystem(): bool
    {
        return $this->type === 'system';
    }

    /**
     * Vérifier si le message est une réponse
     */
    public function isReply(): bool
    {
        return !is_null($this->reply_to);
    }
}
