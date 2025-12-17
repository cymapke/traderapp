import axios from 'axios'

// Create axios instance with base URL
const api = axios.create({
    baseURL: '/api',
    headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json'
    }
})

// Request interceptor - adds token to every request
api.interceptors.request.use(
    (config) => {
        const token = localStorage.getItem('token')
        if (token) {
            config.headers.Authorization = `Bearer ${token}`
        } else {
            console.warn('No token found in localStorage for API request')
        }
        return config
    },
    (error) => {
        return Promise.reject(error)
    }
)

// Response interceptor - handles auth errors
api.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.response?.status === 401) {
            console.error('Authentication failed, clearing token')

            // Clear stored data
            localStorage.removeItem('token')
            localStorage.removeItem('user')
            localStorage.removeItem('user_id')

            // Show notification
            if (typeof window.showToast === 'function') {
                window.showToast('error', 'Session Expired', 'Please login again')
            }

            // Redirect to login page
            if (window.location.pathname !== '/login') {
                setTimeout(() => {
                    window.location.href = '/login'
                }, 2000)
            }
        }
        return Promise.reject(error)
    }
)

export default api
