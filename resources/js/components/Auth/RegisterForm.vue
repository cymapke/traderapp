<template>
    <div class="auth-form">
        <!-- Form Header -->
        <div class="form-header">
            <h1 class="form-title">Create Account</h1>
            <p class="form-subtitle">Start your trading journey with TraderApp</p>
        </div>

        <!-- Register Form -->
        <form @submit.prevent="handleSubmit" class="form-content">
            <!-- Full Name Field -->
            <div class="form-group">
                <label for="name" class="form-label">Full Name</label>
                <input
                    type="text"
                    id="name"
                    v-model="form.name"
                    class="form-input"
                    placeholder="John Doe"
                    required
                />
            </div>

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
                    placeholder="Create a strong password"
                    required
                />
                <div class="password-hint">
                    Use at least 8 characters with a mix of letters, numbers & symbols
                </div>
            </div>

            <!-- Confirm Password Field -->
            <div class="form-group">
                <label for="confirmPassword" class="form-label">Confirm Password</label>
                <input
                    type="password"
                    id="confirmPassword"
                    v-model="form.confirmPassword"
                    class="form-input"
                    placeholder="Confirm your password"
                    required
                />
            </div>

            <!-- Terms Agreement -->
            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" v-model="form.agreeTerms" class="checkbox" required />
                    <span class="checkbox-text">
                        I agree to the 
                        <router-link to="/terms" class="terms-link">Terms of Service</router-link>
                        and 
                        <router-link to="/privacy" class="terms-link">Privacy Policy</router-link>
                    </span>
                </label>
            </div>

            <!-- Newsletter Opt-in -->
            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" v-model="form.newsletter" class="checkbox" />
                    <span class="checkbox-text">
                        Send me trading insights, market updates, and platform news
                    </span>
                </label>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="submit-button" :disabled="loading">
                <span v-if="loading">Creating account...</span>
                <span v-else>Create Account</span>
            </button>

            <!-- Divider -->
            <div class="divider">
                <span>or</span>
            </div>

            <!-- Alternative Sign Up Options -->
            <div class="alternative-options">
                <button type="button" class="alternative-button">
                    <svg class="alternative-icon" viewBox="0 0 24 24">
                        <path fill="currentColor" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                        <path fill="currentColor" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                        <path fill="currentColor" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                        <path fill="currentColor" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                    </svg>
                    Sign up with Google
                </button>
            </div>

            <!-- Sign In Link -->
            <div class="auth-link">
                Already have an account?
                <router-link to="/login" class="auth-link-button">
                    Sign in here
                </router-link>
            </div>
        </form>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';

const router = useRouter();
const loading = ref(false);

const form = ref({
    name: '',
    email: '',
    password: '',
    confirmPassword: '',
    agreeTerms: false,
    newsletter: true
});

const handleSubmit = async () => {
    // Validation
    if (form.value.password !== form.value.confirmPassword) {
        alert('Passwords do not match');
        return;
    }
    
    if (!form.value.agreeTerms) {
        alert('You must agree to the terms and conditions');
        return;
    }

    loading.value = true;
    
    // Simulate API call
    await new Promise(resolve => setTimeout(resolve, 1500));
    
    // For demo purposes, just redirect to home
    router.push({ name: 'dashboard' });
    
    loading.value = false;
};
</script>

<style scoped>
/* Register form specific styles */
.password-hint {
    font-size: 12px;
    color: #6b7280;
    margin-top: 4px;
    line-height: 1.4;
}

.terms-link {
    color: #482e92;
    text-decoration: none;
    font-weight: 500;
}

.terms-link:hover {
    text-decoration: underline;
}

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
/* Reuse styles from LoginForm - no need to duplicate */
</style>
