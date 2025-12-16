<template>
    <div class="dashboard-layout">
        <!-- Loading state while auth store initializes -->
        <div v-if="!authStore" class="loading-container">
            <div class="loading-spinner"></div>
            <p>Initializing...</p>
        </div>
        
        <!-- Loading authentication -->
        <div v-else-if="authStore.isLoading" class="loading-container">
            <div class="loading-spinner"></div>
            <p>Loading dashboard...</p>
        </div>
        
        <!-- Authenticated content -->
        <div v-else-if="authStore.isAuthenticated" class="authenticated-layout">
            <!-- Header -->
            <header class="dashboard-header">
                <div class="header-content">
                    <router-link :to="{ name: 'dashboard.overview' }" class="logo">
                        TraderApp
                    </router-link>

                    <div class="user-profile">
                        <div class="user-info">
                            <span class="user-name">{{ authStore.user?.name }}</span>
                            <span class="user-email">{{ authStore.user?.email }}</span>
                        </div>
                        <div class="user-avatar">
                            {{ authStore.userInitials }}
                        </div>
                    </div>
                </div>
            </header>

            <!-- Main Content Area -->
            <div class="dashboard-content">
                <!-- Sidebar -->
                <aside class="sidebar">
                    <DashboardMenu v-if="authStore.isAuthenticated" />
                </aside>

                <!-- Main Content Widget -->
                <main class="main-widget scroll-container">
                    <router-view v-slot="{ Component }">
                        <transition name="fade" mode="out-in">
                            <component :is="Component" />
                        </transition>
                    </router-view>
                </main>
            </div>

            <!-- Footer -->
            <footer class="dashboard-footer">
                <div class="footer-content">
                    <p class="copyright">
                        © {{ currentYear }} TraderApp. All rights reserved.
                    </p>
                </div>
            </footer>
        </div>
        
        <!-- Unauthorized -->
        <div v-else class="unauthorized-container">
            <h2>Access Denied</h2>
            <p>You need to be logged in to access this page.</p>
            <router-link to="/login" class="login-link">
                Go to Login
            </router-link>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { storeToRefs } from 'pinia'
import { useAuthStore } from '@/stores/auth'
import DashboardMenu from '@/components/Dashboard/DashboardMenu.vue'

const router = useRouter()
const route = useRoute()

// Initialize auth store - use computed to ensure reactivity
const authStore = computed(() => {
    try {
        return useAuthStore()
    } catch (error) {
        console.error('Failed to initialize auth store:', error)
        return null
    }
})

const currentYear = ref(new Date().getFullYear())

onMounted(() => {
    console.log('📊 DashboardLayout mounted')
    
    if (authStore.value) {
        // Check if user is authenticated
        if (!authStore.value.isAuthenticated) {
            console.log('📊 Not authenticated, redirecting to login')
            router.push({ name: 'login' })
        }
    }
})
</script>

<style scoped>
/* Add transition for route changes */
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

.dashboard-layout {
    height: 100vh;
    width: 100vw;
    overflow: hidden;
}

.authenticated-layout {
    height: 100%;
    width: 100%;
    display: flex;
    flex-direction: column;
    background-color: white;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    overflow: hidden;
}

/* Header - Fixed height */
.dashboard-header {
    height: 64px;
    min-height: 64px;
    padding: 0 32px;
    border-bottom: 1px solid #e5e7eb;
    background: white;
    display: flex;
    align-items: center;
    flex-shrink: 0;
    z-index: 10;
}

.header-content {
    width: 100%;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.logo {
    font-size: 22px;
    font-weight: 800;
    color: #482e92;
    letter-spacing: -0.5px;
    font-family: 'Inter', system-ui, sans-serif;
    text-decoration: none;
}

.logo:hover {
    opacity: 0.9;
}

/* User Profile */
.user-profile {
    display: flex;
    align-items: center;
    gap: 12px;
}

.user-info {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
}

.user-name {
    font-size: 14px;
    font-weight: 600;
    color: #111827;
}

.user-email {
    font-size: 12px;
    color: #6b7280;
}

.user-avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: linear-gradient(135deg, #482e92 0%, #5b3ea8 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    font-weight: 600;
}

/* Dashboard Content Area */
.dashboard-content {
    display: flex;
    flex: 1;
    min-height: 0; /* Important for flex children */
    overflow: hidden;
}

/* Sidebar Styles */
.sidebar {
    width: 240px;
    min-width: 240px;
    background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
    border-right: 1px solid #e5e7eb;
    display: flex;
    flex-direction: column;
    flex-shrink: 0;
    overflow: hidden;
}

/* Main Widget Styles */
.main-widget {
    flex: 1;
    background: white;
    padding: 32px;
    overflow-y: auto;
    overflow-x: hidden;
    min-width: 0; /* Important for flex children */
}

/* Footer Styles */
.dashboard-footer {
    height: 60px;
    min-height: 60px;
    background: #f9fafb;
    border-top: 1px solid #e5e7eb;
    display: flex;
    align-items: center;
    flex-shrink: 0;
    z-index: 10;
}

.footer-content {
    width: 100%;
    text-align: center;
}

.copyright {
    color: #6b7280;
    font-size: 13px;
    font-weight: 500;
}

/* Loading and Unauthorized containers */
.loading-container,
.unauthorized-container {
    height: 100vh;
    width: 100vw;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
    overflow: hidden;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
}

.loading-spinner {
    width: 50px;
    height: 50px;
    border: 3px solid #e5e7eb;
    border-top-color: #482e92;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin-bottom: 16px;
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

.unauthorized-container h2 {
    color: #dc2626;
    margin-bottom: 12px;
}

.unauthorized-container p {
    color: #6b7280;
    margin-bottom: 20px;
}

.login-link {
    padding: 10px 24px;
    background: #482e92;
    color: white;
    text-decoration: none;
    border-radius: 6px;
    font-weight: 500;
}

.login-link:hover {
    background: #5b3ea8;
}

/* Responsive Design */
@media (max-width: 1024px) {
    .sidebar {
        width: 200px;
        min-width: 200px;
    }
    
    .main-widget {
        padding: 24px;
    }
}

@media (max-width: 768px) {
    .dashboard-header {
        padding: 0 20px;
        height: 56px;
        min-height: 56px;
    }
    
    .user-info {
        display: none;
    }
    
    .sidebar {
        width: 180px;
        min-width: 180px;
    }
    
    .main-widget {
        padding: 20px;
    }
    
    .dashboard-footer {
        height: 48px;
        min-height: 48px;
    }
}

@media (max-width: 640px) {
    .dashboard-content {
        flex-direction: column;
    }
    
    .sidebar {
        width: 100%;
        min-width: 100%;
        height: auto;
        border-right: none;
        border-bottom: 1px solid #e5e7eb;
    }
    
    .main-widget {
        height: calc(100vh - 124px); /* Adjust for header + footer + sidebar */
    }
}
</style>
