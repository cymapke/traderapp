<template>
    <div class="auth-layout">
        <!-- Header Section (Common for all auth pages) -->
        <header class="header">
            <div class="header-content">
                <!-- Logo -->
                <div class="logo">
                    TraderApp
                </div>

                <!-- Dynamic Navigation Button -->
                <div class="nav-buttons">
                    <router-link 
                        v-if="showAlternateButton"
                        :to="alternateRoute"
                        class="alternate-button"
                    >
                        {{ alternateButtonText }}
                    </router-link>
                </div>
            </div>
        </header>

        <!-- Main Content Area -->
        <main class="main-content">
            <!-- Form Container -->
            <div class="form-container">
                <!-- Dynamic Form Content -->
                <slot></slot>
            </div>
        </main>

        <!-- Footer -->
        <footer class="footer">
            <div class="footer-content">
                <p class="copyright">
                    © {{ currentYear }} TraderApp. All rights reserved.
                </p>
            </div>
        </footer>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useRoute } from 'vue-router';

const currentYear = ref(new Date().getFullYear());
const route = useRoute();

// Determine which button to show based on current route
const showAlternateButton = computed(() => {
    return route.name === 'login' || route.name === 'register';
});

const alternateButtonText = computed(() => {
    if (route.name === 'login') return 'Sign up';
    if (route.name === 'register') return 'Log in';
    return '';
});

const alternateRoute = computed(() => {
    if (route.name === 'login') return { name: 'register' };
    if (route.name === 'register') return { name: 'login' };
    return { name: 'home' };
});
</script>

<style scoped>
/* Auth Layout Container */
.auth-layout {
    min-height: 100vh;
    background-color: white;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    display: flex;
    flex-direction: column;
}

/* Header Styles */
.header {
    padding: 24px 40px;
    border-bottom: 1px solid #e5e7eb;
    background: white;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

.header-content {
    max-width: 1200px;
    margin: 0 auto;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.logo {
    font-size: 24px;
    font-weight: 800;
    color: #482e92; /* Fiorentina purple */
    letter-spacing: -0.5px;
    font-family: 'Inter', system-ui, sans-serif;
    text-shadow: 0 1px 2px rgba(72, 46, 146, 0.1);
}

.nav-buttons {
    display: flex;
    gap: 16px;
}

.alternate-button {
    padding: 10px 24px;
    background: linear-gradient(135deg, #482e92 0%, #5b3ea8 100%);
    color: white;
    text-decoration: none;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    border-radius: 8px;
    transition: all 0.3s ease;
    box-shadow: 0 2px 4px rgba(72, 46, 146, 0.2);
}

.alternate-button:hover {
    background: linear-gradient(135deg, #5b3ea8 0%, #6d4bc4 100%);
    box-shadow: 0 4px 8px rgba(72, 46, 146, 0.3);
    transform: translateY(-1px);
}

/* Main Content Styles */
.main-content {
    background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
    padding: 60px 20px;
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
}

.form-container {
    width: 100%;
    max-width: 480px;
    margin: 0 auto;
}

/* Footer Styles */
.footer {
    background: #f9fafb;
    padding: 24px 40px;
    border-top: 1px solid #e5e7eb;
}

.footer-content {
    max-width: 1200px;
    margin: 0 auto;
    text-align: center;
}

.copyright {
    color: #6b7280;
    font-size: 14px;
    font-weight: 500;
}

/* Responsive Design */
@media (max-width: 768px) {
    .header {
        padding: 20px 24px;
    }
    
    .main-content {
        padding: 40px 20px;
    }
    
    .logo {
        font-size: 20px;
    }
    
    .alternate-button {
        padding: 8px 20px;
        font-size: 13px;
    }
}

@media (max-width: 480px) {
    .header {
        padding: 16px 20px;
    }
    
    .main-content {
        padding: 30px 16px;
    }
    
    .logo {
        font-size: 18px;
    }
}
</style>
