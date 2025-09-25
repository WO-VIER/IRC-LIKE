<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Testing\Fluent\Concerns\Has;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Les conversations auxquelles l'utilisateur participe
     */
    public function conversations(): BelongsToMany
    {
        return $this->belongsToMany(Conversation::class, 'conversation_users')
            ->withPivot(['joined_at', 'last_read_at', 'role', 'is_muted'])
            ->withTimestamps();
    }

    /**
     * Les conversations créées par l'utilisateur
     */
    public function createdConversations(): HasMany
    {
        return $this->hasMany(Conversation::class, 'created_by');
    }

    /**
     * Les messages envoyés par l'utilisateur
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    /**
     * Les notifications de l'utilisateur
     */
    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class)->orderBy('created_at', 'desc');
    }

    /**
     * Les notifications non lues
     */
    public function unreadNotifications(): HasMany
    {
        return $this->hasMany(Notification::class)->whereNull('read_at');
    }

    public function getUnreadNotificationsCount(): int
    {
        return $this->notifications()->where('read_at', null)->count();
    }

    public function getUnreadMessagesInConversation(Conversation $conversation): int
    {
        $lastRead = $this->conversations()
            ->where('conversation_id', $conversation->id)
            ->first()?->pivot?->last_read_at;

        if (!$lastRead) {
            return $conversation->messages()->count();
        }

        return $conversation->messages()
            ->where('created_at', '>', $lastRead)
            ->where('user_id', '!=', $this->id)
            ->count();
    }
}
