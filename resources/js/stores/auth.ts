import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/services/api'
import type { User } from '@/types/user'

export const useAuthStore = defineStore('auth', () => {
    const router = useRouter()

    // State with default values
    const user = ref<User | null>(null)
    const token = ref<string | null>(null)
    const isLoading = ref(false)
    const error = ref<string | null>(null)

    // Initialize from localStorage
    const initFromStorage = () => {
        const storedToken = localStorage.getItem('token')
        const storedUser = localStorage.getItem('user')

        if (storedToken) {
            token.value = storedToken
            api.defaults.headers.common['Authorization'] = `Bearer ${storedToken}`
        }

        if (storedUser) {
            try {
                user.value = JSON.parse(storedUser)
            } catch (e) {
                console.error('Failed to parse stored user:', e)
                localStorage.removeItem('user')
            }
        }
    }

    // Call initialization
    initFromStorage()

    // Computed
    const isAuthenticated = computed(() => {
        return !!token.value && !!user.value
    })

    const userInitials = computed(() => {
        if (!user.value?.name) return 'U'
        return user.value.name
            .split(' ')
            .map(n => n[0])
            .join('')
            .toUpperCase()
            .slice(0, 2)
    })

    // Actions
    const setAuth = (userData: User, authToken: string) => {
        user.value = userData
        token.value = authToken

        localStorage.setItem('token', authToken)
        localStorage.setItem('user', JSON.stringify(userData))
        api.defaults.headers.common['Authorization'] = `Bearer ${authToken}`
    }

    const clearAuth = () => {
        user.value = null
        token.value = null

        localStorage.removeItem('token')
        localStorage.removeItem('user')
        delete api.defaults.headers.common['Authorization']
    }

    const login = async (credentials: { email: string; password: string }) => {
        isLoading.value = true
        error.value = null

        try {
            const response = await api.post('/auth/login', credentials)
            setAuth(response.data.user, response.data.token)
            return response.data
        } catch (err: any) {
            error.value = err.response?.data?.message || 'Login failed'
            throw err
        } finally {
            isLoading.value = false
        }
    }

    const logout = async () => {
        isLoading.value = true

        try {
            await api.post('/auth/logout')
        } catch (err) {
            console.error('Logout error:', err)
        } finally {
            clearAuth()
            isLoading.value = false
            router.push({ name: 'home' })
        }
    }

    const fetchUser = async () => {
        if (!token.value) {
            return null
        }

        isLoading.value = true

        try {
            const response = await api.get('/auth/user')
            user.value = response.data
            localStorage.setItem('user', JSON.stringify(response.data))
            return response.data
        } catch (err) {
            clearAuth()
            throw err
        } finally {
            isLoading.value = false
        }
    }

    const checkAuth = async () => {
        if (!token.value) {
            return false
        }

        try {
            await fetchUser()
            return true
        } catch (err) {
            return false
        }
    }

    return {
        // State
        user,
        token,
        isLoading,
        error,

        // Computed
        isAuthenticated,
        userInitials,

        // Actions
        setAuth,
        clearAuth,
        login,
        logout,
        fetchUser,
        checkAuth
    }
})