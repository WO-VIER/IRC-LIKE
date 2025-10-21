<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('user.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});

Broadcast::channel('conversation.{conversationId}', function ($user, $conversationId) {
    return \DB::table('conversation_users')
        ->where('conversation_id', $conversationId)
        ->where('user_id', $user->id)
        ->exists();
});
