<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Conversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'description',
        'created_by',
        'last_activity_at',
    ];

    protected $casts = [
        'last_activity_at' => 'datetime',
    ];

    /**
     * Le créateur de la conversation
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Les participants de la conversation
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'conversation_users')
            ->withPivot(['joined_at', 'last_read_at', 'role', 'is_muted'])
            ->withTimestamps();
    }

    /**
     * Les messages de la conversation
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class)->orderBy('created_at', 'asc');
    }

    /**
     * Le dernier message de la conversation
     */
    public function lastMessage(): HasMany
    {
        return $this->hasMany(Message::class)->latest();
    }

    /**
     * Scope pour les conversations privées
     */
    public function scopePrivate($query)
    {
        return $query->where('type', 'private');
    }

    /**
     * Scope pour les conversations de groupe
     */
    public function scopeGroup($query)
    {
        return $query->where('type', 'group');
    }

    public function scopeForUser($query, $userId)
    {
        return $query->whereHas('users', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        });
    }

    public function scopeActive($query)
    {
        return $query->orderBy('last_activity_at', 'desc');
    }

    /**
     * Vérifier si c'est une conversation privée
     */
    public function isPrivate(): bool
    {
        return $this->type === 'private';
    }

    /**
     * Vérifier si c'est une conversation de groupe
     */
    public function isGroup(): bool
    {
        return $this->type === 'group';
    }

    public function updateActivity(): void
    {
        $this->update(['last_activity_at' => now()]);
    }
}
