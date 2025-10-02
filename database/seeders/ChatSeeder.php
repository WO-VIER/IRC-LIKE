<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Notification;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ChatSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 🔄 Nettoyer les données existantes
        $this->command->info('🧹 Nettoyage des données existantes...');

        Notification::truncate();
        Message::truncate();
        \DB::table('conversation_users')->truncate();
        Conversation::truncate();
        User::where('email', '!=', 'admin@test.com')->delete();

        // 👥 Créer des utilisateurs de test
        $this->command->info('👥 Création des utilisateurs...');

        $admin = User::firstOrCreate([
            'email' => 'admin@test.com'
        ], [
            'name' => 'Admin Test',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        $users = collect([
            ['name' => 'Alice Martin', 'email' => 'alice@test.com'],
            ['name' => 'Bob Dupont', 'email' => 'bob@test.com'],
            ['name' => 'Charlie Bernard', 'email' => 'charlie@test.com'],
            ['name' => 'Diana Lopez', 'email' => 'diana@test.com'],
            ['name' => 'Eva Johnson', 'email' => 'eva@test.com'],
        ])->map(function ($userData) {
            return User::firstOrCreate([
                'email' => $userData['email']
            ], [
                'name' => $userData['name'],
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]);
        });

        // Ajouter l'admin à la collection
        $allUsers = $users->prepend($admin);

        $this->command->info("✅ {$allUsers->count()} utilisateurs créés");

        // 💬 Créer des conversations privées
        $this->command->info('💬 Création des conversations privées...');

        $privateConversations = collect([
            [$admin, $users[0]], // Admin & Alice
            [$admin, $users[1]], // Admin & Bob
            [$users[0], $users[1]], // Alice & Bob
            [$users[2], $users[3]], // Charlie & Diana
        ])->map(function ($pair) {
            // ✅ Vérifier si une conversation privée existe déjà
            $existingConversation = Conversation::where('type', 'private')
                ->whereHas('users', fn($q) => $q->where('user_id', $pair[0]->id))
                ->whereHas('users', fn($q) => $q->where('user_id', $pair[1]->id))
                ->whereDoesntHave('users', function ($q) use ($pair) {
                    $q->whereNotIn('user_id', [$pair[0]->id, $pair[1]->id]);
                })
                ->first();

            if ($existingConversation) {
                return $existingConversation;
            }

            $conversation = Conversation::create([
                'type' => 'private',
                'created_by' => $pair[0]->id,
                'last_activity_at' => now()->subMinutes(rand(5, 60)),
            ]);

            // Ajouter les participants
            $conversation->users()->attach([
                $pair[0]->id => [
                    'joined_at' => now()->subHours(rand(1, 24)),
                    'role' => 'member',
                    'last_read_at' => now()->subMinutes(rand(1, 30)),
                ],
                $pair[1]->id => [
                    'joined_at' => now()->subHours(rand(1, 24)),
                    'role' => 'member',
                    'last_read_at' => now()->subMinutes(rand(1, 30)),
                ],
            ]);

            return $conversation;
        });

        // 👥 Créer des conversations de groupe
        $this->command->info('👥 Création des conversations de groupe...');

        $groupConversations = collect([
            [
                'name' => '🚀 Équipe Dev',
                'description' => 'Discussions techniques et développement',
                'users' => [$admin, $users[0], $users[1], $users[2]]
            ],
            [
                'name' => '📊 Marketing Team',
                'description' => 'Stratégies marketing et communication',
                'users' => [$users[1], $users[2], $users[3], $users[4]]
            ],
            [
                'name' => '☕ Pause Café',
                'description' => 'Discussions générales et détente',
                'users' => $allUsers->take(4)->values()
            ],
        ])->map(function ($groupData) {
            $conversation = Conversation::create([
                'name' => $groupData['name'],
                'description' => $groupData['description'],
                'type' => 'group',
                'created_by' => $groupData['users'][0]->id,
                'last_activity_at' => now()->subMinutes(rand(5, 120)),
            ]);

            // Ajouter les participants avec des rôles (éviter les doublons)
            $userIds = collect($groupData['users'])->pluck('id')->unique();

            foreach ($userIds as $index => $userId) {
                $conversation->users()->attach($userId, [
                    'joined_at' => now()->subHours(rand(1, 48)),
                    'role' => $index === 0 ? 'admin' : 'member',
                    'last_read_at' => now()->subMinutes(rand(1, 60)),
                    'is_muted' => $index > 2 ? (rand(0, 1) ? true : false) : false,
                ]);
            }

            return $conversation;
        });

        $allConversations = $privateConversations->concat($groupConversations);
        $this->command->info("✅ {$allConversations->count()} conversations créées");

        // 💌 Créer des messages réalistes
        $this->command->info('💌 Création des messages...');

        $messageTemplates = [
            // Messages génériques
            "Salut ! Comment ça va ?",
            "J'ai terminé la tâche, tout est prêt 👍",
            "On se fait un point demain matin ?",
            "Parfait, merci pour l'info !",
            "Je regarde ça et je reviens vers toi",
            "Super boulot sur le dernier projet 🎉",

            // Messages techniques (pour équipe dev)
            "Le build est OK, on peut déployer",
            "J'ai corrigé le bug sur la page de connexion",
            "Besoin d'aide sur l'API utilisateurs ?",
            "La nouvelle feature est en test",
            "Code review terminée ✅",

            // Messages marketing
            "Les stats de la campagne sont excellentes !",
            "On lance la promo demain ?",
            "J'ai préparé les visuels pour les réseaux",
            "Le taux de conversion a augmenté de 15%",

            // Messages pause café
            "Quelqu'un pour un café ? ☕",
            "Bonne journée à tous ! 😊",
            "Weekend bien mérité 🎉",
            "Il fait beau aujourd'hui !",
        ];

        $totalMessages = 0;

        foreach ($allConversations as $conversation) {
            $messageCount = rand(3, 8);
            $conversationUsers = $conversation->users;

            for ($i = 0; $i < $messageCount; $i++) {
                $user = $conversationUsers->random();
                $createdAt = now()->subMinutes(rand(5, 1440)); // Dans les dernières 24h

                // Choisir un message approprié selon le type de conversation
                if ($conversation->name === '🚀 Équipe Dev') {
                    $messages = array_slice($messageTemplates, 6, 5);
                } elseif ($conversation->name === '📊 Marketing Team') {
                    $messages = array_slice($messageTemplates, 11, 4);
                } elseif ($conversation->name === '☕ Pause Café') {
                    $messages = array_slice($messageTemplates, 15, 4);
                } else {
                    $messages = array_slice($messageTemplates, 0, 6);
                }

                Message::create([
                    'conversation_id' => $conversation->id,
                    'user_id' => $user->id,
                    'content' => $messages[array_rand($messages)],
                    'type' => 'text',
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);

                $totalMessages++;
            }

            // Mettre à jour l'activité de la conversation
            $conversation->updateActivity();
        }

        $this->command->info("✅ {$totalMessages} messages créés");

        // 🔔 Créer des notifications
        $this->command->info('🔔 Création des notifications...');

        $notificationCount = 0;
        foreach ($allUsers->take(3) as $user) { // Notifications pour les 3 premiers utilisateurs
            // Notifications de nouveaux messages
            for ($i = 0; $i < rand(2, 4); $i++) {
                $randomConversation = $user->conversations()->inRandomOrder()->first();
                if ($randomConversation) {
                    Notification::create([
                        'user_id' => $user->id,
                        'type' => 'message',
                        'title' => 'Nouveau message',
                        'content' => "Vous avez reçu un nouveau message dans " . ($randomConversation->name ?: 'une conversation'),
                        'data' => [
                            'conversation_id' => $randomConversation->id,
                            'url' => "/conversations/{$randomConversation->id}",
                        ],
                        'read_at' => rand(0, 1) ? now()->subMinutes(rand(1, 120)) : null,
                        'created_at' => now()->subMinutes(rand(5, 1440)),
                    ]);
                    $notificationCount++;
                }
            }
        }

        $this->command->info("✅ {$notificationCount} notifications créées");

        // 📊 Résumé final
        $this->command->info('');
        $this->command->info('🎉 SEEDING TERMINÉ !');
        $this->command->info('==================');
        $this->command->info("👥 Utilisateurs: {$allUsers->count()}");
        $this->command->info("💬 Conversations: {$allConversations->count()}");
        $this->command->info("💌 Messages: {$totalMessages}");
        $this->command->info("🔔 Notifications: {$notificationCount}");
        $this->command->info('');
        $this->command->info('🔐 Connexions de test:');
        $this->command->info('Email: admin@test.com | Mot de passe: password');
        $this->command->info('Email: alice@test.com | Mot de passe: password');
        $this->command->info('Email: bob@test.com | Mot de passe: password');
        $this->command->info('');
        $this->command->info('🚀 Prêt à développer !');
    }
}
