<script setup lang="ts">
import { ref, computed } from 'vue'
import { Head, router, usePage } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Avatar, AvatarFallback } from "@/components/ui/avatar";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import {
    Hash,
    Users,
    Plus,
    Send,
    Settings,
    MessageCircle,
    Search
} from 'lucide-vue-next'

interface User {
    id: number
    name: string
    email: string
    role?: string
    is_muted?: boolean
    last_read_at?: string
}

interface Message {
    id: number
    content: string
    user: string
    created_at: string
}

interface Conversation {
    id: number
    name: string | null
    type: string
    description?: string
    users: User[]
    last_message?: Message
    last_activity_at: string
}

const props = defineProps<{
    conversations: Conversation[]
}>()

const { auth } = usePage().props as any
const searchTerm = ref('')

// Computed
const filteredConversations = computed(() => {
    if (!searchTerm.value) return sortedConversations.value

    return sortedConversations.value.filter(conversation => {
        const name = getConversationName(conversation).toLowerCase()
        return name.includes(searchTerm.value.toLowerCase())
    })
})

const sortedConversations = computed(() => {
    return props.conversations.sort((a, b) =>
        new Date(b.last_activity_at).getTime() - new Date(a.last_activity_at).getTime()
    )
})

const privateConversations = computed(() =>
    filteredConversations.value.filter(c => c.type === 'private')
)

const groupConversations = computed(() =>
    filteredConversations.value.filter(c => c.type === 'group')
)

const currentUser = computed(() => auth.user)

// Methods
const getConversationName = (conversation: Conversation) => {
    if (conversation.type === 'group') {
        return conversation.name || 'Groupe sans nom'
    }

    const otherUser = conversation.users.find(u => u.id !== currentUser.value?.id)
    return otherUser?.name || 'Utilisateur inconnu'
}

const getAvatarFallback = (name: string) => {
    return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2)
}

const formatMessageTime = (dateString: string) => {
    const date = new Date(dateString)
    const now = new Date()
    const isToday = date.toDateString() === now.toDateString()

    if (isToday) {
        return date.toLocaleTimeString('fr-FR', {
            hour: '2-digit',
            minute: '2-digit'
        })
    }

    return date.toLocaleDateString('fr-FR', {
        day: 'numeric',
        month: 'short'
    })
}

const selectConversation = (conversationId: number) => {
    router.get(`/conversations/${conversationId}`)
}

const createNewConversation = () => {
    router.get('/conversations/create')
}
</script>

<template>

    <Head title="Conversations" />

    <AppLayout>
        <div class="flex h-[calc(100vh-4rem)] bg-gray-50 dark:bg-gray-900">
            <!-- Sidebar principale -->
            <div class="w-80 bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 flex flex-col">
                <!-- Header -->
                <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between mb-4">
                        <h1 class="text-xl font-bold text-gray-900 dark:text-white">
                            Messages
                        </h1>
                        <Button size="sm" variant="ghost" @click="createNewConversation">
                            <Plus class="h-4 w-4" />
                        </Button>
                    </div>

                    <!-- Barre de recherche -->
                    <div class="relative">
                        <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400" />
                        <Input v-model="searchTerm" placeholder="Rechercher une conversation..."
                               class="pl-10 bg-gray-100 dark:bg-gray-700 border-0" />
                    </div>
                </div>

                <!-- Liste des conversations -->
                <div class="flex-1 overflow-y-auto px-3 py-4">
                    <div class="space-y-6">
                        <!-- Messages directs -->
                        <div v-if="privateConversations.length > 0">
                            <div class="px-2 mb-2">
                                <h2
                                    class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide flex items-center">
                                    <MessageCircle class="h-3 w-3 mr-2" />
                                    Messages directs
                                    <Badge variant="secondary" class="ml-2 text-xs">
                                        {{ privateConversations.length }}
                                    </Badge>
                                </h2>
                            </div>

                            <div class="space-y-1">
                                <div v-for="conversation in privateConversations" :key="conversation.id"
                                     class="flex items-center p-3 mx-2 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer transition-all duration-200 group"
                                     @click="selectConversation(conversation.id)">
                                    <div class="relative">
                                        <Avatar class="h-10 w-10">
                                            <AvatarFallback
                                                class="bg-gradient-to-r from-blue-500 to-purple-600 text-white text-sm font-medium">
                                                {{ getAvatarFallback(getConversationName(conversation)) }}
                                            </AvatarFallback>
                                        </Avatar>
                                        <!-- Indicateur en ligne -->
                                        <div
                                            class="absolute -bottom-0.5 -right-0.5 w-3 h-3 bg-green-400 border-2 border-white dark:border-gray-800 rounded-full">
                                        </div>
                                    </div>

                                    <div class="ml-3 flex-1 min-w-0">
                                        <div class="flex items-center justify-between">
                                            <h3
                                                class="font-semibold text-gray-900 dark:text-white truncate group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                                                {{ getConversationName(conversation) }}
                                            </h3>
                                            <span v-if="conversation.last_message"
                                                  class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ formatMessageTime(conversation.last_message.created_at) }}
                                            </span>
                                        </div>

                                        <p v-if="conversation.last_message"
                                           class="text-sm text-gray-600 dark:text-gray-400 truncate">
                                            {{ conversation.last_message.content }}
                                        </p>
                                        <p v-else class="text-sm text-gray-500 dark:text-gray-500 italic">
                                            Aucun message
                                        </p>

                                        <!-- Badge non lu -->
                                        <div class="flex items-center mt-1">
                                            <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Conversations de groupe -->
                        <div v-if="groupConversations.length > 0">
                            <div class="px-2 mb-2">
                                <h2
                                    class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide flex items-center">
                                    <Hash class="h-3 w-3 mr-2" />
                                    Groupes
                                    <Badge variant="secondary" class="ml-2 text-xs">
                                        {{ groupConversations.length }}
                                    </Badge>
                                </h2>
                            </div>

                            <div class="space-y-1">
                                <div v-for="conversation in groupConversations" :key="conversation.id"
                                     class="flex items-center p-3 mx-2 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer transition-all duration-200 group"
                                     @click="selectConversation(conversation.id)">
                                    <div
                                        class="flex items-center justify-center w-10 h-10 bg-gradient-to-r from-green-400 to-blue-500 rounded-xl mr-3">
                                        <Hash class="h-5 w-5 text-white" />
                                    </div>

                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center justify-between">
                                            <h3
                                                class="font-semibold text-gray-900 dark:text-white truncate group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                                                {{ conversation.name }}
                                            </h3>
                                            <span v-if="conversation.last_message"
                                                  class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ formatMessageTime(conversation.last_message.created_at) }}
                                            </span>
                                        </div>

                                        <div class="flex items-center justify-between">
                                            <p v-if="conversation.last_message"
                                               class="text-sm text-gray-600 dark:text-gray-400 truncate">
                                                <span class="font-medium">{{ conversation.last_message.user }}:</span>
                                                {{ conversation.last_message.content }}
                                            </p>
                                            <p v-else class="text-sm text-gray-500 dark:text-gray-500 italic">
                                                Aucun message
                                            </p>

                                            <div class="flex items-center">
                                                <Users class="h-3 w-3 text-gray-400 mr-1" />
                                                <span class="text-xs text-gray-500">{{ conversation.users.length
                                                    }}</span>
                                            </div>
                                        </div>

                                        <!-- Description du groupe -->
                                        <p v-if="conversation.description"
                                           class="text-xs text-gray-500 dark:text-gray-400 truncate mt-1">
                                            {{ conversation.description }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- État vide -->
                        <div v-if="filteredConversations.length === 0" class="text-center py-8">
                            <div
                                class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4">
                                <MessageCircle class="h-8 w-8 text-gray-400" />
                            </div>

                            <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-2">
                                {{ searchTerm ? 'Aucun résultat' : 'Aucune conversation' }}
                            </h3>

                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                                {{ searchTerm ? 'Essayez avec d\'autres mots-clés'
                                : 'Commencez une nouvelle conversation' }}
                            </p>

                            <Button v-if="!searchTerm" size="sm" @click="createNewConversation">
                                <Plus class="h-4 w-4 mr-2" />
                                Nouvelle conversation
                            </Button>
                        </div>
                    </div>
                </div>

                <!-- Footer utilisateur -->
                <div class="p-3 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                    <div class="flex items-center">
                        <div class="relative">
                            <Avatar class="h-10 w-10">
                                <AvatarFallback
                                    class="bg-gradient-to-r from-purple-500 to-pink-500 text-white font-medium">
                                    {{ getAvatarFallback(currentUser?.name || '') }}
                                </AvatarFallback>
                            </Avatar>
                            <div
                                class="absolute -bottom-0.5 -right-0.5 w-3 h-3 bg-green-400 border-2 border-white dark:border-gray-800 rounded-full">
                            </div>
                        </div>

                        <div class="ml-3 flex-1 min-w-0">
                            <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">
                                {{ currentUser?.name }}
                            </p>
                            <div class="flex items-center">
                                <div class="w-2 h-2 bg-green-400 rounded-full mr-2"></div>
                                <span class="text-xs text-gray-500 dark:text-gray-400">En ligne</span>
                            </div>
                        </div>

                        <Button size="sm" variant="ghost" class="text-gray-500 hover:text-gray-700">
                            <Settings class="h-4 w-4" />
                        </Button>
                    </div>
                </div>
            </div>

            <!-- Zone principale (vide pour l'instant) -->
            <div class="flex-1 flex items-center justify-center bg-gray-50 dark:bg-gray-900">
                <div class="text-center">
                    <div
                        class="w-20 h-20 bg-gradient-to-r from-blue-500 to-purple-600 rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg">
                        <MessageCircle class="h-10 w-10 text-white" />
                    </div>

                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">
                        Bienvenue dans IRC-LIKE
                    </h2>

                    <p class="text-gray-600 dark:text-gray-400 max-w-md mb-6">
                        Sélectionnez une conversation dans la sidebar pour commencer à discuter avec votre équipe.
                    </p>

                    <div class="flex justify-center space-x-4">
                        <Button @click="createNewConversation">
                            <Plus class="h-4 w-4 mr-2" />
                            Nouvelle conversation
                        </Button>
                        <Button variant="outline">
                            <Users class="h-4 w-4 mr-2" />
                            Inviter des membres
                        </Button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
