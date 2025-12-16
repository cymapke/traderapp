import type { NavigationGuard } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

export const authGuard: NavigationGuard = (to, from, next) => {
    const authStore = useAuthStore()

    // Check authentication status
    const isAuthenticated = authStore.isAuthenticated

    console.log('🔐 Route Guard:', {
        to: to.name,
        requiresAuth: to.meta.requiresAuth,
        requiresGuest: to.meta.requiresGuest,
        isAuthenticated: isAuthenticated,
        hasToken: !!localStorage.getItem('token')
    })

    // If route requires authentication and user is not authenticated
    if (to.meta.requiresAuth && !isAuthenticated) {
        console.log('🛑 Redirecting to login (requires auth)')
        next({ name: 'login' })
        return
    }

    // If route is for guests only (login/register) and user is authenticated
    if (to.meta.requiresGuest && isAuthenticated) {
        console.log('🛑 Redirecting to dashboard (already logged in)')
        next({ name: 'dashboard' })
        return
    }

    // Otherwise, proceed
    next()
}
