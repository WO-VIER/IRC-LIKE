<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import { toast } from 'vue-sonner'
import { Avatar, AvatarFallback } from "@/components/ui/avatar"
import { Button } from "@/components/ui/button"
import { Badge } from "@/components/ui/badge"
import { Input } from "@/components/ui/input"
import {
    Hash,
    Plus,
    Settings,
    MessageCircle,
    Search,
    ArrowLeft
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
    user_id?: number
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
    unread_count: number
}

const props = defineProps<{
    conversations: Conversation[]
    currentConversationId?: number
}>()

const { auth } = usePage().props as any
const searchTerm = ref('')

const filteredConversations = computed(() => {
    if (!searchTerm.value) return sortedConversations.value

    return sortedConversations.value.filter(conversation => {
        const name = getConversationName(conversation).toLowerCase()
        return name.includes(searchTerm.value.toLowerCase())
    })
})

const sortedConversations = computed(() => {
    return [...props.conversations].sort((a, b) =>
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

const getConversationName = (conversation: Conversation) => {
    if (conversation.type === 'group') {
        return conversation.name || 'Groupe sans nom'
    }

    const uniqueUsers = conversation.users.filter((user, index, self) =>
        user.id !== currentUser.value?.id &&
        self.findIndex(u => u.id === user.id) === index
    )

    const otherUser = uniqueUsers[0]
    return otherUser?.name || 'Utilisateur inconnu'
}

const getAvatarFallback = (name: string) => {
    return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2)
}

const formatMessageTime = (dateString: string) => {
    if (!dateString) return ''

    try {
        const date = new Date(dateString)
        if (isNaN(date.getTime())) return ''

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
    } catch (e) {
        return ''
    }
}

const selectConversation = (conversationId: number) => {
    router.visit(`/conversations/${conversationId}`, {
        preserveState: false
    })
}

const goBackToIndex = () => {
    router.visit('/conversations', {
        preserveState: false
    })
}

// ✅ Écouter les messages sur le canal utilisateur pour les autres conversations
let echoChannel: any = null

onMounted(() => {
    const userId = auth.user?.id
    if (!userId) return

    echoChannel = window.Echo.private(`user.${userId}`)
        .listen('.MessageSent', (event: any) => {
            console.log('📨 Message reçu sur user channel (Sidebar):', event)

            if (!event.message) return

            // Vérifier si on est dans cette conversation
            const currentUrl = window.location.pathname
            const isOnThisConversation = currentUrl === `/conversations/${event.message.conversation_id}`

            // Si on est PAS dans cette conversation et que ce n'est pas notre message
            if (!isOnThisConversation && event.message.user.id !== userId) {
                // Afficher une notification toast
                toast.success(`Nouveau message de ${event.message.user.name}`, {
                    description: event.message.content.substring(0, 100) + (event.message.content.length > 100 ? '...' : ''),
                    duration: 4000,
                })

                // Recharger la liste des conversations pour mettre à jour le badge et last_message
                setTimeout(() => {
                    router.reload({
                        only: ['conversations']
                    })
                }, 500)
            }
        })
        .error((error: any) => {
            console.error('Erreur Echo sur user channel (Sidebar):', error)
        })
})

onUnmounted(() => {
    const userId = auth.user?.id
    if (userId && echoChannel) {
        window.Echo.leave(`user.${userId}`)
        echoChannel = null
    }
})
</script>

<template>
    <div class="w-80 bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 flex flex-col">
        <div class="p-4 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2">
                    <Button v-if="currentConversationId" size="sm" variant="ghost" @click="goBackToIndex" class="mr-2">
                        <ArrowLeft class="h-4 w-4" />
                    </Button>
                    <h1 class="text-xl font-bold text-gray-900 dark:text-white">
                        Messages
                    </h1>
                </div>
                <Button size="sm" variant="ghost" @click="createNewConversation">
                    <Plus class="h-4 w-4" />
                </Button>
            </div>

            <div class="relative">
                <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400" />
                <Input v-model="searchTerm" placeholder="Rechercher une conversation..."
                    class="pl-10 bg-gray-100 dark:bg-gray-700 border-0" />
            </div>
        </div>

        <div class="flex-1 overflow-y-auto px-3 py-4">
            <div class="space-y-6">
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
                            class="flex items-center p-3 mx-2 rounded-xl cursor-pointer transition-all duration-200 group"
                            :class="currentConversationId === conversation.id ? 'bg-blue-50 dark:bg-blue-900/20' : 'hover:bg-gray-100 dark:hover:bg-gray-700'"
                            @click="selectConversation(conversation.id)">

                            <Avatar class="h-10 w-10 relative">
                                <AvatarFallback
                                    class="bg-gradient-to-r from-blue-500 to-purple-600 text-white text-sm font-medium">
                                    {{ getAvatarFallback(getConversationName(conversation)) }}
                                </AvatarFallback>
                            </Avatar>

                            <div class="ml-3 flex-1 min-w-0">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <h3 class="truncate group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors"
                                            :class="(conversation.unread_count || 0) > 0 ? 'font-bold text-gray-900 dark:text-white' : 'font-semibold text-gray-900 dark:text-white'">
                                            {{ getConversationName(conversation) }}
                                        </h3>
                                        <Badge v-if="(conversation.unread_count || 0) > 0" variant="destructive"
                                            class="h-5 px-2 text-xs">
                                            {{ conversation.unread_count }}
                                        </Badge>
                                    </div>
                                    <span v-if="conversation.last_message && conversation.last_message.created_at"
                                        class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ formatMessageTime(conversation.last_message.created_at) }}
                                    </span>
                                </div>

                                <p v-if="conversation.last_message"
                                    class="text-sm text-gray-600 dark:text-gray-400 truncate"
                                    :class="(conversation.unread_count || 0) > 0 ? 'font-semibold' : ''">
                                    {{ conversation.last_message.content }}
                                </p>
                                <p v-else class="text-sm text-gray-500 dark:text-gray-500 italic">
                                    Aucun message
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

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
                            class="flex items-center p-3 mx-2 rounded-xl cursor-pointer transition-all duration-200 group"
                            :class="currentConversationId === conversation.id ? 'bg-blue-50 dark:bg-blue-900/20' : 'hover:bg-gray-100 dark:hover:bg-gray-700'"
                            @click="selectConversation(conversation.id)">

                            <div class="relative">
                                <div
                                    class="flex items-center justify-center w-10 h-10 bg-gradient-to-r from-green-400 to-blue-500 rounded-xl mr-3">
                                    <Hash class="h-5 w-5 text-white" />
                                </div>
                                <Badge v-if="(conversation.unread_count || 0) > 0" variant="destructive"
                                    class="absolute -top-2 -right-1 h-5 w-5 flex items-center justify-center p-0 text-xs rounded-full">
                                    {{ conversation.unread_count }}
                                </Badge>
                            </div>

                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <h3 class="truncate group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors"
                                            :class="(conversation.unread_count || 0) > 0 ? 'font-bold text-gray-900 dark:text-white' : 'font-semibold text-gray-900 dark:text-white'">
                                            {{ conversation.name }}
                                        </h3>
                                        <Badge v-if="(conversation.unread_count || 0) > 0" variant="destructive"
                                            class="h-5 px-2 text-xs">
                                            {{ conversation.unread_count }}
                                        </Badge>
                                    </div>
                                    <span v-if="conversation.last_message && conversation.last_message.created_at"
                                        class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ formatMessageTime(conversation.last_message.created_at) }}
                                    </span>
                                </div>

                                <p v-if="conversation.last_message"
                                    class="text-sm text-gray-600 dark:text-gray-400 truncate"
                                    :class="(conversation.unread_count || 0) > 0 ? 'font-semibold' : ''">
                                    {{ conversation.last_message.content }}
                                </p>
                                <p v-else class="text-sm text-gray-500 dark:text-gray-500 italic">
                                    Aucun message
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="p-3 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
            <div class="flex items-center">
                <Avatar class="h-10 w-10">
                    <AvatarFallback class="bg-gradient-to-r from-purple-500 to-pink-500 text-white font-medium">
                        {{ getAvatarFallback(currentUser?.name || '') }}
                    </AvatarFallback>
                </Avatar>

                <div class="ml-3 flex-1 min-w-0">
                    <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">
                        {{ currentUser?.name }}
                    </p>
                    <span class="text-xs text-gray-500 dark:text-gray-400">En ligne</span>
                </div>

                <Button size="sm" variant="ghost" class="text-gray-500 hover:text-gray-700">
                    <Settings class="h-4 w-4" />
                </Button>
            </div>
        </div>
    </div>
</template>
