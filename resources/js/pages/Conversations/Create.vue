<script setup lang="ts">
import { ref, computed } from 'vue'
import { Head, router, useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Avatar, AvatarFallback } from "@/components/ui/avatar"
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import { Checkbox } from "@/components/ui/checkbox"
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card"
import {
    ArrowLeft,
    Hash,
    MessageCircle,
    Plus,
    Search,
    Users,
    X
} from 'lucide-vue-next'

interface User {
    id: number
    name: string
    email: string
}

const props = defineProps<{
    users: User[]
}>()

const searchTerm = ref('')
const selectedUsers = ref<number[]>([])

const form = useForm({
    type: 'private',
    name: '',
    description: '',
    user_ids: [] as number[]
})

// Computed
const filteredUsers = computed(() => {
    if (!searchTerm.value) return props.users

    return props.users.filter(user =>
        user.name.toLowerCase().includes(searchTerm.value.toLowerCase()) ||
        user.email.toLowerCase().includes(searchTerm.value.toLowerCase())
    )
})

const selectedUsersList = computed(() => {
    return props.users.filter(user => selectedUsers.value.includes(user.id))
})

const canCreateConversation = computed(() => {
    if (form.type === 'private') {
        return selectedUsers.value.length === 1
    }
    return selectedUsers.value.length >= 1 && form.name.trim() !== ''
})

// Methods
const getAvatarFallback = (name: string) => {
    return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2)
}

const toggleUserSelection = (userId: number) => {
    const index = selectedUsers.value.indexOf(userId)
    if (index === -1) {
        if (form.type === 'private') {
            selectedUsers.value = [userId]
        } else {
            selectedUsers.value.push(userId)
        }
    } else {
        selectedUsers.value.splice(index, 1)
    }
}

const removeSelectedUser = (userId: number) => {
    const index = selectedUsers.value.indexOf(userId)
    if (index !== -1) {
        selectedUsers.value.splice(index, 1)
    }
}

const goBack = () => {
    console.log(' goBack called')
    router.get('/conversations')
}

const createConversation = () => {
    console.log(' createConversation called')
    form.user_ids = selectedUsers.value
    form.post('/conversations')
}

const onTypeChange = (event: Event) => {
    const target = event.target as HTMLSelectElement
    const newType = target.value
    form.type = newType
    if (newType === 'private') {
        // Garder seulement le premier utilisateur sélectionné pour les conversations privées
        selectedUsers.value = selectedUsers.value.slice(0, 1)
        form.name = ''
        form.description = ''
    }
}
</script>

<template>
    <Head title="Nouvelle Conversation" />

    <AppLayout>
        <div class="flex h-[calc(100vh-4rem)] bg-gray-50 dark:bg-gray-900">
            <!-- Sidebar principale (même structure que l'index) -->
            <div class="w-80 bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 flex flex-col">
                <!-- Header -->
                <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center">
                            <Button size="sm" variant="ghost" @click="goBack" class="mr-2">
                                <ArrowLeft class="h-4 w-4" />
                            </Button>
                            <h1 class="text-xl font-bold text-gray-900 dark:text-white">
                                Nouvelle conversation
                            </h1>
                        </div>
                    </div>

                    <!-- Type de conversation -->
                    <div class="mb-4">
                        <Label class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 block">
                            Type de conversation
                        </Label>
                        <select
                            :value="form.type"
                            @change="onTypeChange"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        >
                            <option value="private" class="flex items-center">
                                💬 Message direct
                            </option>
                            <option value="group" class="flex items-center">
                                # Groupe
                            </option>
                        </select>
                    </div>

                    <!-- Barre de recherche -->
                    <div class="relative">
                        <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400" />
                        <Input
                            v-model="searchTerm"
                            placeholder="Rechercher un utilisateur..."
                            class="pl-10 bg-gray-100 dark:bg-gray-700 border-0"
                        />
                    </div>
                </div>

                <!-- Liste des utilisateurs -->
                <div class="flex-1 overflow-y-auto px-3 py-4">
                    <div class="space-y-1">
                        <div
                            v-for="user in filteredUsers"
                            :key="user.id"
                            class="flex items-center p-3 mx-2 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer transition-all duration-200 group"
                            @click="toggleUserSelection(user.id)"
                        >
                            <div class="relative mr-3">
                                <Avatar class="h-10 w-10">
                                    <AvatarFallback
                                        class="bg-gradient-to-r from-blue-500 to-purple-600 text-white text-sm font-medium">
                                        {{ getAvatarFallback(user.name) }}
                                    </AvatarFallback>
                                </Avatar>
                            </div>

                            <div class="flex-1 min-w-0">
                                <h3 class="font-semibold text-gray-900 dark:text-white truncate">
                                    {{ user.name }}
                                </h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400 truncate">
                                    {{ user.email }}
                                </p>
                            </div>

                            <Checkbox
                                :checked="selectedUsers.includes(user.id)"
                                class="ml-2"
                            />
                        </div>
                    </div>

                    <!-- État vide -->
                    <div v-if="filteredUsers.length === 0" class="text-center py-8">
                        <div class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4">
                            <Users class="h-8 w-8 text-gray-400" />
                        </div>
                        <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-2">
                            Aucun utilisateur trouvé
                        </h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Essayez avec d'autres mots-clés
                        </p>
                    </div>
                </div>
            </div>

            <!-- Zone principale pour la configuration -->
            <div class="flex-1 flex flex-col bg-gray-50 dark:bg-gray-900">
                <!-- Header de la zone principale -->
                <div class="p-6 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-gradient-to-r from-blue-500 to-purple-600 rounded-xl flex items-center justify-center mr-4">
                            <Hash v-if="form.type === 'group'" class="h-6 w-6 text-white" />
                            <MessageCircle v-else class="h-6 w-6 text-white" />
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                                {{ form.type === 'group' ? 'Créer un groupe' : 'Nouveau message direct' }}
                            </h2>
                            <p class="text-gray-600 dark:text-gray-400">
                                {{ form.type === 'group' ? 'Configurez votre nouveau groupe' : 'Sélectionnez un utilisateur pour commencer' }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Contenu principal -->
                <div class="flex-1 p-6 overflow-y-auto">
                    <div class="max-w-2xl mx-auto space-y-6">
                        <!-- Utilisateurs sélectionnés -->
                        <Card v-if="selectedUsersList.length > 0">
                            <CardHeader>
                                <CardTitle class="text-lg">
                                    {{ form.type === 'group' ? 'Membres' : 'Destinataire' }}
                                </CardTitle>
                                <CardDescription>
                                    {{ selectedUsersList.length }} utilisateur(s) sélectionné(s)
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <div class="flex flex-wrap gap-2">
                                    <div
                                        v-for="user in selectedUsersList"
                                        :key="user.id"
                                        class="flex items-center bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 px-3 py-1 rounded-full text-sm"
                                    >
                                        <Avatar class="h-6 w-6 mr-2">
                                            <AvatarFallback class="bg-blue-500 text-white text-xs">
                                                {{ getAvatarFallback(user.name) }}
                                            </AvatarFallback>
                                        </Avatar>
                                        {{ user.name }}
                                        <Button
                                            size="sm"
                                            variant="ghost"
                                            class="ml-2 h-4 w-4 p-0 hover:bg-blue-200 dark:hover:bg-blue-800"
                                            @click="removeSelectedUser(user.id)"
                                        >
                                            <X class="h-3 w-3" />
                                        </Button>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        <!-- Configuration du groupe -->
                        <Card v-if="form.type === 'group'">
                            <CardHeader>
                                <CardTitle class="text-lg">Configuration du groupe</CardTitle>
                            </CardHeader>
                            <CardContent class="space-y-4">
                                <div>
                                    <Label for="group-name" class="text-sm font-medium">
                                        Nom du groupe *
                                    </Label>
                                    <Input
                                        id="group-name"
                                        v-model="form.name"
                                        placeholder="Ex: Équipe développement"
                                        class="mt-1"
                                        :class="{ 'border-red-500': form.errors.name }"
                                    />
                                    <p v-if="form.errors.name" class="text-red-500 text-sm mt-1">
                                        {{ form.errors.name }}
                                    </p>
                                </div>
                            </CardContent>
                        </Card>

                        <!-- Actions -->
                        <div class="flex justify-end space-x-3 pt-6">
                            <Button variant="outline" @click="goBack">
                                Annuler
                            </Button>
                            <Button
                                @click="createConversation"
                                :disabled="!canCreateConversation || form.processing"
                                class="min-w-[120px]"
                            >
                                <Plus v-if="!form.processing" class="h-4 w-4 mr-2" />
                                <span v-if="form.processing">Création...</span>
                                <span v-else>{{ form.type === 'group' ? 'Créer le groupe' : 'Commencer la conversation' }}</span>
                            </Button>
                        </div>

                        <!-- Erreurs générales -->
                        <div v-if="form.errors.user_ids" class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
                            <p class="text-red-600 dark:text-red-400 text-sm">
                                {{ form.errors.user_ids }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
