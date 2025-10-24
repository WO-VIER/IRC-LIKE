<script setup lang="ts">
import { ref, computed, onMounted, onBeforeUnmount, nextTick } from 'vue'
import { Head, router, useForm, usePage } from '@inertiajs/vue3'
import { toast } from 'vue-sonner'
import AppLayout from '@/layouts/AppLayout.vue'
import ConversationSidebar from '@/components/ConversationSidebar.vue'
import { useUnreadMessages } from '@/composables/useUnreadMessages'
import { Avatar, AvatarFallback } from "@/components/ui/avatar"
import { Button } from "@/components/ui/button"
import { Badge } from "@/components/ui/badge"
import {
    Hash,
    MessageCircle,
    Send,
    Users
} from 'lucide-vue-next'

interface User {
    id: number
    name: string
    email: string
}

interface Message {
    id: number
    content: string
    user: User
    created_at: string
    updated_at: string
}

interface ConversationSidebarType {
    id: number
    name: string | null
    type: string
    users: User[]
    last_message?: any
    last_activity_at: string
    unread_count: number
}

interface Conversation {
    id: number
    name: string | null
    type: string
    description?: string
    users: User[]
    messages: Message[]
}

const props = defineProps<{
    conversation: Conversation
    conversations: ConversationSidebarType[]
}>()

const { auth } = usePage().props as any
const messageContent = ref('')
const messagesContainer = ref<HTMLElement>()
const showMembersList = ref(true)
const localMessages = ref<Message[]>([...props.conversation.messages])
const { markConversationAsRead } = useUnreadMessages()

let echoChannel: any = null

const form = useForm({
    content: ''
})

const currentUser = computed(() => auth.user)

const conversationName = computed(() => {
    if (props.conversation.type === 'group') {
        return props.conversation.name || 'Groupe sans nom'
    }
    const otherUser = props.conversation.users.find(user => user.id !== currentUser.value?.id)
    return otherUser?.name || 'Utilisateur inconnu'
})

const conversationDescription = computed(() => {
    if (props.conversation.type === 'group') {
        return props.conversation.description || `${props.conversation.users.length} membres`
    }
    const otherUser = props.conversation.users.find(user => user.id !== currentUser.value?.id)
    return otherUser?.email || ''
})

const getAvatarFallback = (name: string) => {
    return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2)
}

const formatMessageTime = (dateString: string) => {
    const date = new Date(dateString)
    const now = new Date()
    const isToday = date.toDateString() === now.toDateString()
    const isThisYear = date.getFullYear() === now.getFullYear()

    if (isToday) {
        return date.toLocaleTimeString('fr-FR', {
            hour: '2-digit',
            minute: '2-digit'
        })
    }

    if (isThisYear) {
        return date.toLocaleDateString('fr-FR', {
            day: 'numeric',
            month: 'short'
        }) + ' ' + date.toLocaleTimeString('fr-FR', {
            hour: '2-digit',
            minute: '2-digit'
        })
    }

    return date.toLocaleDateString('fr-FR', {
        day: 'numeric',
        month: 'short',
        year: 'numeric'
    }) + ' ' + date.toLocaleTimeString('fr-FR', {
        hour: '2-digit',
        minute: '2-digit'
    })
}

const isMyMessage = (message: Message) => {
    return message.user.id === currentUser.value?.id
}

const sendMessage = () => {
    if (!messageContent.value.trim()) return

    form.content = messageContent.value
    form.post(`/conversations/${props.conversation.id}/messages`, {
        preserveState: true,
        preserveScroll: true,
        only: ['conversation'],
        onSuccess: () => {
            messageContent.value = ''
            form.content = ''
            scrollToBottom()

            // ✅ FIX : Recharger la sidebar pour mettre à jour last_message et l'ordre
            router.reload({
                only: ['conversations']
            })
        },
        onError: (errors) => {
            console.error('Erreur envoi message:', errors)
        }
    })
}

const scrollToBottom = () => {
    nextTick(() => {
        if (messagesContainer.value) {
            messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight
        }
    })
}

const handleKeyPress = (event: KeyboardEvent) => {
    if (event.key === 'Enter' && !event.shiftKey) {
        event.preventDefault()
        sendMessage()
    }
}

const toggleMembersList = () => {
    showMembersList.value = !showMembersList.value
}

onMounted(() => {
    scrollToBottom()
    markConversationAsRead(props.conversation.id)

    const channelName = `conversation.${props.conversation.id}`

    echoChannel = window.Echo.private(channelName)
        .listen('.MessageSent', (event: any) => {
            console.log('Message reçu via Echo:', event)

            if (!event.message || !event.message.user) {
                console.error('Structure de message invalide:', event)
                return
            }

            const messageExists = localMessages.value.some(m => m.id === event.message.id)

            if (!messageExists) {
                localMessages.value.push(event.message)
                scrollToBottom()

                if (event.message.user.id !== currentUser.value?.id) {
                    // ✅ Afficher la notification IMMÉDIATEMENT
                    toast.success(`Nouveau message de ${event.message.user.name}`, {
                        description: event.message.content.substring(0, 100) + (event.message.content.length > 100 ? '...' : ''),
                        duration: 4000,
                    })

                    fetch(`/conversations/${props.conversation.id}/mark-as-read`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                        }
                    }).catch(error => {
                        console.error('Erreur lors du marquage comme lu:', error)
                    })
                }

                // ✅ Recharger la sidebar après un délai pour laisser le toast s'afficher
                nextTick(() => {
                    setTimeout(() => {
                        router.reload({
                            only: ['conversations']
                        })
                    }, 500)
                })
            }
        })
        .error((error: any) => {
            console.error('Erreur Echo:', error)
        })
})

onBeforeUnmount(() => {
    if (echoChannel) {
        window.Echo.leave(`conversation.${props.conversation.id}`)
        echoChannel = null
    }
})
</script>

<template>

    <Head title="Messages" />

    <AppLayout>
        <div class="flex h-[calc(100vh-4rem)] bg-gray-50 dark:bg-gray-900">
            <ConversationSidebar :conversations="conversations" :current-conversation-id="conversation.id" />

            <div class="flex-1 flex flex-col">
                <div class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-6 py-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="mr-4">
                                <div v-if="conversation.type === 'group'"
                                    class="w-12 h-12 bg-gradient-to-r from-green-400 to-blue-500 rounded-xl flex items-center justify-center">
                                    <Hash class="h-6 w-6 text-white" />
                                </div>
                                <Avatar v-else class="h-12 w-12">
                                    <AvatarFallback class="bg-gradient-to-r from-blue-500 to-purple-600 text-white">
                                        {{ getAvatarFallback(conversationName) }}
                                    </AvatarFallback>
                                </Avatar>
                            </div>

                            <div>
                                <h2 class="text-xl font-bold text-gray-900 dark:text-white flex items-center">
                                    {{ conversationName }}
                                    <Badge v-if="conversation.type === 'group'" variant="secondary" class="ml-2">
                                        {{ conversation.users.length }} membres
                                    </Badge>
                                </h2>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    <span v-if="conversation.type === 'group'">
                                        {{ conversationDescription }}
                                    </span>
                                    <span v-else class="text-green-500 dark:text-green-400">
                                        En ligne
                                    </span>
                                </p>
                            </div>
                        </div>

                        <Button v-if="conversation.type === 'group'" size="sm" variant="ghost"
                            @click="toggleMembersList" class="ml-auto">
                            <Users class="h-4 w-4" />
                        </Button>
                    </div>
                </div>

                <div class="flex flex-1 overflow-hidden">
                    <div class="flex-1 flex flex-col">
                        <div ref="messagesContainer" class="flex-1 overflow-y-auto p-6 space-y-4">
                            <div v-for="message in localMessages" :key="message.id" class="flex"
                                :class="{ 'justify-end': isMyMessage(message) }">

                                <div class="flex max-w-xs lg:max-w-md"
                                    :class="{ 'flex-row-reverse': isMyMessage(message) }">
                                    <div class="flex-shrink-0"
                                        :class="{ 'ml-3': isMyMessage(message), 'mr-3': !isMyMessage(message) }">
                                        <Avatar class="h-8 w-8">
                                            <AvatarFallback :class="isMyMessage(message)
                                                ? 'bg-gradient-to-r from-blue-500 to-purple-600 text-white'
                                                : 'bg-gradient-to-r from-green-400 to-blue-500 text-white'">
                                                {{ getAvatarFallback(message.user.name) }}
                                            </AvatarFallback>
                                        </Avatar>
                                    </div>

                                    <div>
                                        <div class="flex items-center mb-1"
                                            :class="{ 'flex-row-reverse': isMyMessage(message) }">
                                            <span class="text-sm font-medium text-gray-900 dark:text-white">
                                                {{ message.user.name }}
                                            </span>
                                            <span class="text-xs text-gray-500 dark:text-gray-400"
                                                :class="{ 'mr-2': isMyMessage(message), 'ml-2': !isMyMessage(message) }">
                                                {{ formatMessageTime(message.created_at) }}
                                            </span>
                                        </div>

                                        <div class="px-4 py-2 rounded-2xl"
                                            :class="isMyMessage(message)
                                                ? 'bg-blue-600 text-white'
                                                : 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white border border-gray-200 dark:border-gray-600'">
                                            <p class="text-sm whitespace-pre-wrap">{{ message.content }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div v-if="localMessages.length === 0" class="text-center py-12">
                                <div
                                    class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <MessageCircle class="h-8 w-8 text-gray-400" />
                                </div>
                                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
                                    Commencez la conversation
                                </h3>
                                <p class="text-gray-600 dark:text-gray-400">
                                    Soyez le premier à envoyer un message dans cette conversation.
                                </p>
                            </div>
                        </div>

                        <div class="bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 p-6">
                            <div class="flex items-center space-x-4">
                                <div class="flex-1">
                                    <div class="relative">
                                        <textarea v-model="messageContent" @keydown="handleKeyPress"
                                            placeholder="Tapez votre message..." rows="1"
                                            class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-2xl bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none"
                                            :class="{ 'border-red-500': form.errors.content }"></textarea>
                                    </div>

                                    <p v-if="form.errors.content" class="text-red-500 text-xs mt-1">
                                        {{ form.errors.content }}
                                    </p>
                                </div>

                                <Button @click="sendMessage" :disabled="!messageContent.trim() || form.processing"
                                    class="rounded-2xl px-6">
                                    <Send class="h-4 w-4" />
                                </Button>
                            </div>

                            <div class="flex justify-between items-center mt-2 px-4">
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    Appuyez sur Entrée pour envoyer, Shift+Entrée pour un saut de ligne
                                </p>
                                <p v-if="messageContent.length > 0" class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ messageContent.length }} caractères
                                </p>
                            </div>
                        </div>
                    </div>

                    <div v-if="conversation.type === 'group' && showMembersList"
                        class="w-64 bg-white dark:bg-gray-800 border-l border-gray-200 dark:border-gray-700 flex flex-col">
                        <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                            <h3 class="text-sm font-semibold text-gray-900 dark:text-white flex items-center">
                                <Users class="h-4 w-4 mr-2" />
                                Membres ({{ conversation.users.length }})
                            </h3>
                        </div>

                        <div class="flex-1 overflow-y-auto p-3">
                            <div class="space-y-2">
                                <div v-for="user in conversation.users" :key="user.id"
                                    class="flex items-center p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                                    <Avatar class="h-8 w-8">
                                        <AvatarFallback
                                            class="bg-gradient-to-r from-blue-500 to-purple-600 text-white text-xs">
                                            {{ getAvatarFallback(user.name) }}
                                        </AvatarFallback>
                                    </Avatar>
                                    <div class="ml-3 flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                            {{ user.name }}
                                            <Badge v-if="user.id === currentUser?.id" variant="secondary"
                                                class="ml-1 text-xs">
                                                Vous
                                            </Badge>
                                        </p>
                                        <p class="text-xs text-green-500 dark:text-green-400">
                                            En ligne
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
