import axios from 'axios'

// Determine base URL based on environment
const getBaseURL = () => {
    if (import.meta.env.DEV) {
        return 'http://localhost:8000/api'
    }
    return '/api' // For production
}

const api = axios.create({
    baseURL: '/api', // Vite will proxy to Laravel
    headers: {
        'Content-Type': 'application/json',
    }
})

// Request interceptor
api.interceptors.request.use(
    (config) => {
        const token = localStorage.getItem('token')
        if (token) {
            config.headers.Authorization = `Bearer ${token}`
        }
        return config
    },
    (error) => {
        return Promise.reject(error)
    }
)

// Response interceptor
api.interceptors.response.use(
    (response) => response,
    async (error) => {
        const originalRequest = error.config

        // Handle 401 Unauthorized
        if (error.response?.status === 401 && !originalRequest._retry) {
            // Clear auth
            localStorage.removeItem('token')
            delete api.defaults.headers.common['Authorization']

            // Redirect to login
            if (window.location.pathname !== '/login') {
                window.location.href = '/login'
            }
        }

        return Promise.reject(error)
    }
)

export default api
