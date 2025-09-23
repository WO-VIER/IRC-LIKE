<!-- filepath: c:\laragon\www\IRC-LIKE-GIT\resources\js\Pages\Conversations\Index.vue -->
<template>
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
            <h2 class="text-2xl font-bold mb-6">Mes Conversations</h2>

            <!-- Bouton créer conversation -->
            <button @click="showCreateForm = true"
                class="mb-4 bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                Nouvelle Conversation
            </button>

            <!-- Liste des conversations -->
            <div class="space-y-4">
                <div v-for="conversation in conversations" :key="conversation.id"
                    class="border rounded-lg p-4 hover:bg-gray-50 cursor-pointer"
                    @click="$inertia.visit(route('conversations.show', conversation.id))">
                    <h3 class="font-semibold">
                        {{ conversation.name || 'Conversation privée' }}
                    </h3>
                    <p class="text-sm text-gray-600">
                        {{ conversation.users.length }} participant(s)
                    </p>
                    <p v-if="conversation.messages[0]" class="text-sm text-gray-500 mt-2">
                        Dernier message : {{ conversation.messages[0].content }}
                    </p>
                </div>
            </div>

            <!-- Formulaire création (simple pour commencer) -->
            <div v-if="showCreateForm" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center">
                <div class="bg-white p-6 rounded-lg">
                    <h3 class="text-lg font-semibold mb-4">Nouvelle Conversation</h3>
                    <form @submit.prevent="createConversation">
                        <input v-model="newConversation.name" placeholder="Nom de la conversation (optionnel)"
                            class="w-full p-2 border rounded mb-4">
                        <div class="flex gap-2">
                            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">
                                Créer
                            </button>
                            <button type="button" @click="showCreateForm = false"
                                class="bg-gray-500 text-white px-4 py-2 rounded">
                                Annuler
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'

defineProps<{
    conversations: Array<{
        id: number
        name: string | null
        type: string
        users: Array<any>
        messages: Array<any>
    }>
}>()

const showCreateForm = ref(false)
const newConversation = ref({
    name: '',
    type: 'group',
    user_ids: []
})

const createConversation = () => {
    router.post(route('conversations.store'), {
        ...newConversation.value,
        user_ids: [1] // Pour l'instant, juste l'utilisateur courant
    })
    showCreateForm.value = false
}
</script>
