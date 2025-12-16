import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/services/api'
import type { User } from '@/types/user'

export const useAuthStore = defineStore('auth', () => {
    const router = useRouter()

    // State
    const user = ref<User | null>(null)
    const token = ref<string | null>(localStorage.getItem('token'))
    const isLoading = ref(false)
    const error = ref<string | null>(null)

    // Computed
    const isAuthenticated = computed(() => !!token.value)
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
        api.defaults.headers.common['Authorization'] = `Bearer ${authToken}`
    }

    const clearAuth = () => {
        user.value = null
        token.value = null
        localStorage.removeItem('token')
        delete api.defaults.headers.common['Authorization']
    }

    const register = async (credentials: {
        name: string
        email: string
        password: string
        password_confirmation: string
    }) => {
        isLoading.value = true
        error.value = null

        try {
            const response = await api.post('/auth/register', credentials)
            setAuth(response.data.user, response.data.token)
            return response.data
        } catch (err: any) {
            error.value = err.response?.data?.message || 'Registration failed'
            throw err
        } finally {
            isLoading.value = false
        }
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
        try {
            await api.post('/auth/logout')
        } catch (err) {
            console.error('Logout error:', err)
        } finally {
            clearAuth()
            router.push({ name: 'home' })
        }
    }

    const fetchUser = async () => {
        if (!token.value) return

        try {
            const response = await api.get('/auth/user')
            user.value = response.data
            return response.data
        } catch (err) {
            clearAuth()
            throw err
        }
    }

    const checkAuth = async () => {
        if (token.value) {
            api.defaults.headers.common['Authorization'] = `Bearer ${token.value}`
            try {
                await fetchUser()
            } catch (err) {
                console.error('Auth check failed:', err)
            }
        }
    }

    // Initialize
    if (token.value) {
        api.defaults.headers.common['Authorization'] = `Bearer ${token.value}`
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
        register,
        login,
        logout,
        fetchUser,
        checkAuth
    }
})
