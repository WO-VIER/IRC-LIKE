<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import { Plus, MessageCircle, Home } from 'lucide-vue-next'

interface User {
    id: number
    name: string
    email: string
    role: string
}

interface LastMessage {
    id: number
    content: string
    user: string
    created_at: string
}

interface Conversation {
    id: number
    name: string | null
    type: 'private' | 'group'
    description: string | null
    last_activity_at: string | null
    users: User[]
    last_message: LastMessage | null
}

defineProps<{
    conversations: Conversation[]
}>()

const createConversation = () => {
    router.visit('/conversations/create')
}
</script>

<template>
    <Head title="Conversations" />

    <div class="min-h-screen bg-gray-50">
        <div class="max-w-4xl mx-auto px-4 py-8">

            <!-- Header avec boutons -->
            <div class="flex justify-between items-center mb-8">
                <h1 class="text-3xl font-bold text-gray-900">Conversations</h1>

                <div class="flex items-center space-x-3">
                    <!-- Bouton Dashboard -->
                    <Link
                        href="/dashboard"
                        class="flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition-colors"
                    >
                        <Home class="w-5 h-5 mr-2" />
                        Dashboard
                    </Link>
                </div>
            </div>

            <!-- État vide simple -->
            <div class="text-center py-16">
                <div class="bg-white rounded-lg shadow p-8">
                    <MessageCircle class="w-16 h-16 text-gray-300 mx-auto mb-4" />
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">
                        Aucune conversation
                    </h3>
                    <p class="text-gray-500 mb-6">
                        Commencez une nouvelle conversation avec vos collègues
                    </p>
                    <button
                        @click="createConversation"
                        class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors"
                    >
                        <Plus class="w-5 h-5 mr-2" />
                        Créer une conversation
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
