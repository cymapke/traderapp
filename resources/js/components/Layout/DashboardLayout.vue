
<template>
    <div class="dashboard-layout">
        <!-- Header (Common for dashboard) -->
        <header class="dashboard-header">
            <div class="header-content">
                <!-- Logo -->
                <div class="logo">
                    TraderApp
                </div>

                <!-- User Profile -->
                <div class="user-profile">
                    <div class="user-info">
                        <span class="user-name">{{ user.name }}</span>
                        <span class="user-email">{{ user.email }}</span>
                    </div>
                    <div class="user-avatar">
                        {{ user.initials }}
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content Area with Sidebar -->
        <div class="dashboard-content">
            <!-- Left Sidebar Menu -->
            <aside class="sidebar">
                <DashboardMenu />
            </aside>

            <!-- Main Content Widget -->
            <main class="main-widget">
                <slot></slot>
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
</template>

<script setup>
import { ref, computed } from 'vue';
import { useRouter } from 'vue-router';
import DashboardMenu from '@/components/Dashboard/DashboardMenu.vue';

const router = useRouter();
const currentYear = ref(new Date().getFullYear());

// Mock user data - in real app, this would come from auth store
const user = ref({
    name: 'John Trader',
    email: 'john@traderapp.com',
    initials: 'JT'
});

// Check if user is authenticated (mock for now)
const isAuthenticated = ref(true);

// If not authenticated, redirect to login
if (!isAuthenticated.value) {
    router.push({ name: 'login' });
}
</script>

<style scoped>
/* Dashboard Layout Container */
.dashboard-layout {
    min-height: 100vh;
    background-color: white;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    display: flex;
    flex-direction: column;
}

/* Header Styles */
.dashboard-header {
    padding: 16px 32px;
    border-bottom: 1px solid #e5e7eb;
    background: white;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    height: 64px;
    display: flex;
    align-items: center;
}

.header-content {
    width: 100%;
    max-width: 100%;
    margin: 0 auto;
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
    min-height: calc(100vh - 124px); /* Subtract header and footer height */
}

/* Sidebar Styles */
.sidebar {
    width: 240px;
    background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
    border-right: 1px solid #e5e7eb;
    padding: 24px 0;
}

/* Main Widget Styles */
.main-widget {
    flex: 1;
    padding: 24px;
    overflow-y: auto;
    overflow-x: hidden;
}

/* Footer Styles */
.dashboard-footer {
    background: #f9fafb;
    padding: 16px 32px;
    border-top: 1px solid #e5e7eb;
    height: 60px;
    display: flex;
    align-items: center;
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

.widget-container {
    height: 100%;
    width: 100%;
}

/* Responsive Design */
@media (max-width: 1024px) {
    .sidebar {
        width: 200px;
    }
    
    .main-widget {
        padding: 24px;
    }
}

@media (max-width: 768px) {
    .dashboard-header {
        padding: 16px 20px;
    }
    
    .user-info {
        display: none;
    }
    
    .sidebar {
        width: 180px;
        padding: 20px 0;
    }
    
    .main-widget {
        padding: 20px;
    }
}

@media (max-width: 640px) {
    .dashboard-content {
        flex-direction: column;
    }
    
    .sidebar {
        width: 100%;
        border-right: none;
        border-bottom: 1px solid #e5e7eb;
        padding: 16px 0;
    }
    
    .main-widget {
        min-height: auto;
    }
}
</style>
