<template>
    <div class="auth-form">
        <div class="form-header">
            <h1 class="form-title">Welcome Back</h1>
            <p class="form-subtitle">Sign in to your TraderApp account</p>
        </div>

        <!-- Error Message Display -->
        <div v-if="errorMessage" class="error-message">
            {{ errorMessage }}
        </div>

        <!-- Auth Store Error -->
        <div v-if="authStore.error" class="error-message">
            {{ authStore.error }}
        </div>

        <form @submit.prevent="handleSubmit" class="form-content">
            <!-- Email Field -->
            <div class="form-group">
                <label for="email" class="form-label">Email Address</label>
                <input
                    type="email"
                    id="email"
                    v-model="form.email"
                    class="form-input"
                    placeholder="satoshi@email.com"
                    required
                />
            </div>

            <!-- Password Field -->
            <div class="form-group">
                <label for="password" class="form-label">Password</label>
                <input
                    type="password"
                    id="password"
                    v-model="form.password"
                    class="form-input"
                    placeholder="Enter your password"
                    required
                />
                <div class="password-options">
                    <router-link to="/forgot-password" class="forgot-password">
                        Forgot password?
                    </router-link>
                </div>
            </div>

            <!-- Submit Button -->
            <button 
                type="submit" 
                class="submit-button" 
                :disabled="loading || authStore.isLoading"
            >
                <span v-if="loading || authStore.isLoading">Signing in...</span>
                <span v-else>Sign In</span>
            </button>

            <!-- Sign Up Link -->
            <div class="auth-link">
                Don't have an account?
                <router-link to="/register" class="auth-link-button">
                    Create account
                </router-link>
            </div>
        </form>
    </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const authStore = useAuthStore()
const loading = ref(false)
const errorMessage = ref('')

const form = reactive({
    email: '',
    password: ''
})

const validateForm = () => {
    if (!form.email.trim()) {
        errorMessage.value = 'Email is required'
        return false
    }
    
    if (!form.password) {
        errorMessage.value = 'Password is required'
        return false
    }
    
    // Basic email validation
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
    if (!emailRegex.test(form.email)) {
        errorMessage.value = 'Please enter a valid email address'
        return false
    }
    
    return true
}

const handleSubmit = async () => {
    // Reset error
    errorMessage.value = ''
    
    // Validate form
    if (!validateForm()) {
        return
    }
    
    loading.value = true
    
    try {
        // REAL API CALL - Remove the mock/simulation
        const result = await authStore.login({
            email: form.email,
            password: form.password
        })
        
        console.log('Login successful:', result)
        
        // Only redirect if login was successful
        if (result.token) {
            router.push({ name: 'dashboard' })
        } else {
            errorMessage.value = 'Login failed: No token received'
        }
        
    } catch (error) {
        console.error('Login error:', error)
        errorMessage.value = error.response?.data?.message || error.message || 'Login failed. Please try again.'
    } finally {
        loading.value = false
    }
}
</script>

<style scoped>
.auth-form {
    background: white;
    border-radius: 16px;
    padding: 40px;
    box-shadow: 
        0 10px 25px -5px rgba(0, 0, 0, 0.05),
        0 20px 40px -10px rgba(0, 0, 0, 0.1);
}

.form-header {
    text-align: center;
    margin-bottom: 32px;
}

.form-title {
    font-size: 28px;
    font-weight: 700;
    color: #111827;
    margin-bottom: 8px;
}

.form-subtitle {
    font-size: 16px;
    color: #6b7280;
}

.form-content {
    display: flex;
    flex-direction: column;
    gap: 24px;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.form-label {
    font-size: 14px;
    font-weight: 500;
    color: #374151;
}

.form-input {
    padding: 12px 16px;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    font-size: 16px;
    transition: all 0.2s;
    outline: none;
}

.form-input:focus {
    border-color: #482e92;
    box-shadow: 0 0 0 3px rgba(72, 46, 146, 0.1);
}

.form-input::placeholder {
    color: #9ca3af;
}

.password-options {
    display: flex;
    justify-content: flex-end;
    margin-top: 4px;
}

.forgot-password {
    font-size: 14px;
    color: #482e92;
    text-decoration: none;
    font-weight: 500;
}

.forgot-password:hover {
    text-decoration: underline;
}

.checkbox-label {
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
}

.checkbox {
    width: 16px;
    height: 16px;
    border-radius: 4px;
    border: 1px solid #d1d5db;
    cursor: pointer;
}

.checkbox-text {
    font-size: 14px;
    color: #374151;
}

.submit-button {
    padding: 14px;
    background: linear-gradient(135deg, #482e92 0%, #5b3ea8 100%);
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    margin-top: 8px;
}

.submit-button:hover:not(:disabled) {
    background: linear-gradient(135deg, #5b3ea8 0%, #6d4bc4 100%);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(72, 46, 146, 0.3);
}

.submit-button:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}

.divider {
    display: flex;
    align-items: center;
    text-align: center;
    color: #9ca3af;
    font-size: 14px;
    margin: 8px 0;
}

.divider::before,
.divider::after {
    content: '';
    flex: 1;
    border-bottom: 1px solid #e5e7eb;
}

.divider span {
    padding: 0 16px;
}

.alternative-options {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.alternative-button {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
    padding: 12px;
    border: 1px solid #d1d5db;
    background: white;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 500;
    color: #374151;
    cursor: pointer;
    transition: all 0.2s;
}

.alternative-button:hover {
    background: #f9fafb;
    border-color: #9ca3af;
}

.alternative-icon {
    width: 18px;
    height: 18px;
}

.auth-link {
    text-align: center;
    font-size: 14px;
    color: #6b7280;
    margin-top: 16px;
}

.auth-link-button {
    color: #482e92;
    font-weight: 600;
    text-decoration: none;
    margin-left: 4px;
}

.auth-link-button:hover {
    text-decoration: underline;
}

/* Responsive Design */
@media (max-width: 640px) {
    .auth-form {
        padding: 32px 24px;
    }
    
    .form-title {
        font-size: 24px;
    }
    
    .form-subtitle {
        font-size: 14px;
    }
}
</style>
