<script setup lang="ts">
import { onMounted, onUnmounted } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { toast } from 'vue-sonner'
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue'
import type { BreadcrumbItemType } from '@/types'
import { Toaster } from 'vue-sonner'

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
})

const { auth } = usePage().props as any

let echoChannel: any = null

onMounted(() => {
    const userId = auth.user?.id

    if (!userId) return

    echoChannel = window.Echo.private(`user.${userId}`)
        .listen('.MessageSent', (event: any) => {
            if (!event.message || !event.message.user) return

            if (event.message.user.id === userId) return

            const currentUrl = window.location.pathname
            const isOnConversation = currentUrl.includes(`/conversations/${event.message.conversation_id}`)

            if (!isOnConversation) {
                toast.info(`Nouveau message de ${event.message.user.name}`, {
                    description: event.message.content.substring(0, 100) + (event.message.content.length > 100 ? '...' : ''),
                    duration: 5000,
                })
            }
        })
        .error((error: any) => {
            console.error('Erreur Echo notifications:', error)
        })
})

onUnmounted(() => {
    const userId = auth.user?.id
    if (userId && echoChannel) {
        window.Echo.leave(`user.${userId}`)
    }
})
</script>

<template>
    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <Toaster position="top-right" richColors />
        <slot />
    </AppSidebarLayout>
</template>
