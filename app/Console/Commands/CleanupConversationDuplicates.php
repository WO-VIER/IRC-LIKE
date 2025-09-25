<?php

namespace App\Console\Commands;

use App\Models\ConversationUser;
use Illuminate\Console\Command;

class CleanupConversationDuplicates extends Command
{
    protected $signature = 'conversations:cleanup-duplicates';
    protected $description = 'Supprimer les utilisateurs en double dans les conversations';

    public function handle()
    {
        $this->info('Nettoyage des doublons en cours...');

        $conversations = \App\Models\Conversation::all();

        foreach ($conversations as $conversation) {
            ConversationUser::removeDuplicates($conversation->id);
        }

        $this->info('Nettoyage terminé !');
    }
}
