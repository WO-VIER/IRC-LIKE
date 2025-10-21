import { ref, computed } from 'vue'
import { usePage } from '@inertiajs/vue3'

interface UnreadMessage {
    conversationId: number
    messageId: number
    senderName: string
    content: string
    timestamp: string
}

const unreadMessages = ref<Map<number, UnreadMessage[]>>(new Map())

export function useUnreadMessages() {
    const { auth } = usePage().props as any
    const userId = auth.user?.id

    const addUnreadMessage = (conversationId: number, message: UnreadMessage) => {
        const messages = unreadMessages.value.get(conversationId) || []
        messages.push(message)
        unreadMessages.value.set(conversationId, messages)
        saveToLocalStorage()
    }

    const markConversationAsRead = (conversationId: number) => {
        unreadMessages.value.delete(conversationId)
        saveToLocalStorage()
    }

    const getUnreadCount = (conversationId: number) => {
        return unreadMessages.value.get(conversationId)?.length || 0
    }

    const totalUnreadCount = computed(() => {
        let total = 0
        unreadMessages.value.forEach(messages => {
            total += messages.length
        })
        return total
    })

    const saveToLocalStorage = () => {
        if (!userId) return

        const data = Array.from(unreadMessages.value.entries())
        localStorage.setItem(`unread_messages_${userId}`, JSON.stringify(data))
    }

    const loadFromLocalStorage = () => {
        if (!userId) return

        const saved = localStorage.getItem(`unread_messages_${userId}`)
        if (saved) {
            try {
                const data = JSON.parse(saved)
                unreadMessages.value = new Map(data)
            } catch (e) {
                console.error('Erreur lors du chargement des messages non lus:', e)
            }
        }
    }

    loadFromLocalStorage()

    return {
        unreadMessages,
        addUnreadMessage,
        markConversationAsRead,
        getUnreadCount,
        totalUnreadCount,
        loadFromLocalStorage
    }
}
