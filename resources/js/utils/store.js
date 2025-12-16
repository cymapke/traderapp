import { useAuthStore } from '@/stores/auth'

export const useSafeAuthStore = () => {
    try {
        return useAuthStore()
    } catch (error) {
        console.error('Auth store access error:', error)
        return null
    }
}
