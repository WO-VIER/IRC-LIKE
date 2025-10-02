<script setup lang="ts">
import { ref, computed, onMounted, nextTick } from 'vue'
import { Head, router, useForm, usePage } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Avatar, AvatarFallback } from "@/components/ui/avatar"
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import { Badge } from "@/components/ui/badge"
import {
    ArrowLeft,
    Hash,
    MessageCircle,
    Send,
    Users,
    Settings,
    Phone,
    Video,
    MoreVertical,
    Smile,
    Paperclip
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
}>()

const { auth } = usePage().props as any
const messageContent = ref('')
const messagesContainer = ref<HTMLElement>()

const form = useForm({
    content: ''
})

// Computed
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

// Methods
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
        only: ['conversation'], // Rechargez seulement les données de conversation
        onSuccess: () => {
            messageContent.value = ''
            form.content = ''
            scrollToBottom()
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

const goBack = () => {
    console.log('Retour à la liste des conversations')
    router.visit('/conversations', {
        method: 'get',
        preserveState: false,
        preserveScroll: false,
    })
}

const handleKeyPress = (event: KeyboardEvent) => {
    if (event.key === 'Enter' && !event.shiftKey) {
        event.preventDefault()
        sendMessage()
    }
}

onMounted(() => {
    scrollToBottom()
})
</script>

<template>

    <Head title="Messages" />

    <AppLayout>
        <div class="flex h-[calc(100vh-4rem)] bg-gray-50 dark:bg-gray-900">
            <!-- Sidebar conversations (similaire à l'index mais plus compacte) -->
            <div class="w-80 bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 flex flex-col">
                <!-- Header sidebar -->
                <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <h1 class="text-lg font-bold text-gray-900 dark:text-white">Messages</h1>
                        <Button size="sm" variant="ghost" @click="goBack">
                            <ArrowLeft class="h-4 w-4" />
                        </Button>
                    </div>
                </div>

                <!-- Zone pour autres conversations (placeholder pour l'instant) -->
                <div class="flex-1 p-4">
                    <p class="text-sm text-gray-500 dark:text-gray-400 text-center">
                        Liste des conversations<br />
                        (à implémenter)
                    </p>
                </div>
            </div>

            <!-- Zone principale de chat -->
            <div class="flex-1 flex flex-col">
                <!-- Header de la conversation -->
                <div class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-6 py-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <!-- Avatar/Icône -->
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

                            <!-- Informations -->
                            <div>
                                <h2 class="text-xl font-bold text-gray-900 dark:text-white flex items-center">
                                    {{ conversationName }}
                                    <Badge v-if="conversation.type === 'group'" variant="secondary" class="ml-2">
                                        {{ conversation.users.length }} membres
                                    </Badge>
                                </h2>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    {{ conversationDescription }}
                                </p>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center space-x-2">
                            <Button size="sm" variant="ghost">
                                <Phone class="h-4 w-4" />
                            </Button>
                            <Button size="sm" variant="ghost">
                                <Video class="h-4 w-4" />
                            </Button>
                            <Button size="sm" variant="ghost">
                                <Users class="h-4 w-4" />
                            </Button>
                            <Button size="sm" variant="ghost">
                                <MoreVertical class="h-4 w-4" />
                            </Button>
                        </div>
                    </div>
                </div>

                <!-- Zone des messages -->
                <div ref="messagesContainer" class="flex-1 overflow-y-auto p-6 space-y-4">
                    <!-- Messages -->
                    <div v-for="message in conversation.messages" :key="message.id" class="flex"
                        :class="{ 'justify-end': isMyMessage(message) }">

                        <div class="flex max-w-xs lg:max-w-md" :class="{ 'flex-row-reverse': isMyMessage(message) }">

                            <!-- Avatar -->
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

                            <!-- Bulle de message -->
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

                    <!-- État vide -->
                    <div v-if="conversation.messages.length === 0" class="text-center py-12">
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

                <!-- Zone de saisie -->
                <div class="bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 p-6">
                    <div class="flex items-end space-x-4">
                        <!-- Boutons d'actions -->
                        <div class="flex space-x-2">
                            <Button size="sm" variant="ghost">
                                <Paperclip class="h-4 w-4" />
                            </Button>
                            <Button size="sm" variant="ghost">
                                <Smile class="h-4 w-4" />
                            </Button>
                        </div>

                        <!-- Zone de saisie -->
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

                        <!-- Bouton d'envoi -->
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
        </div>
    </AppLayout>
</template>
