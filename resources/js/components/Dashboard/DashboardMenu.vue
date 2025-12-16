<template>
    <nav class="dashboard-menu">
        <!-- Menu Header -->
        <div class="menu-header">
            <h3 class="menu-title">Dashboard</h3>
        </div>

        <!-- Menu Items -->
        <ul class="menu-items scroll-container">
            <!-- Overview Menu Item -->
            <li class="menu-item">
                <router-link 
                    :to="{ name: 'dashboard.overview' }" 
                    class="menu-link"
                    :class="{ active: $route.name === 'dashboard.overview' }"
                >
                    <span class="menu-icon">
                        <i class="menu-icon-fas fas fa-chart-line"></i>
                    </span>
                    <span class="menu-text">Overview</span>
                </router-link>
            </li>

            <!-- Funds Menu Item -->
            <li class="menu-item">
                <router-link 
                    :to="{ name: 'dashboard.funds' }" 
                    class="menu-link"
                    :class="{ active: $route.name === 'dashboard.funds' }"
                >
                    <span class="menu-icon">
                        <i class="menu-icon-fas fas fa-money"></i>
                    </span>
                    <span class="menu-text">Funds</span>
                </router-link>
            </li>       

            <!-- Orders Menu Item -->
            <li class="menu-item">
                <router-link 
                    :to="{ name: 'dashboard.orders' }" 
                    class="menu-link"
                    :class="{ active: $route.name === 'dashboard.orders' }"
                >
                    <span class="menu-icon">
                        <i class="menu-icon-fas fas fa-clipboard-list"></i>
                    </span>
                    <span class="menu-text">Orders</span>
                </router-link>
            </li>

            <!-- Divider -->
            <li class="menu-divider"></li>

            <!-- Log Out Menu Item -->
            <li class="menu-item">
                <a href="#" class="menu-link menu-link-logout" @click.prevent="onLogoutClick">
                    <span class="menu-icon">
                        <i class="menu-icon-fas fas fa-sign-out-alt"></i>
                    </span>
                    <span class="menu-text">Log Out</span>
                </a>
            </li>
        </ul>

        <!-- Menu Footer -->
        <div class="menu-footer">
            <div class="app-version">v1.0.0</div>
        </div>
    </nav>
</template>

<script setup>
import { useSafeAuthStore } from '@/utils/store'
import { useRouter } from 'vue-router'

const authStore = useSafeAuthStore()
const router = useRouter()

const onLogoutClick = async () => {
    if (!authStore) return
    
    console.log('Log out clicked')
    try {
        await authStore.logout()
    } catch (error) {
        console.error('Logout error:', error)
    }
}
</script>

<style scoped>
/* Import Font Awesome via CDN - Add this to your main layout */
@import url('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css');

.dashboard-menu {
    height: 100%;
    display: flex;
    flex-direction: column;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

/* Menu Header */
.menu-header {
    padding: 0 24px 20px 24px;
    border-bottom: 1px solid #e5e7eb;
    margin-bottom: 16px;
}

.menu-title {
    font-size: 14px;
    font-weight: 600;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Menu Items */
.menu-items {
    list-style: none;
    padding: 0;
    margin: 0;
    flex: 1;
}

.menu-item {
    margin: 4px 16px;
}

.menu-link {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 16px;
    color: #374151;
    text-decoration: none;
    border-radius: 8px;
    transition: all 0.2s ease;
    font-size: 15px;
    font-weight: 500;
    cursor: pointer;
}

.menu-link:hover {
    background-color: #f3f4f6;
    color: #111827;
}

/* Add active state for router links */
.menu-link.active {
    background: #482e92;
    color: white;
}

.menu-link.active .menu-icon-fas {
    color: white;
}

.menu-link.active:hover {
    background: #5b3ea8;
}

/* Font Awesome Icons */
.menu-icon-fas {
    width: 20px;
    height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    color: currentColor;
}

.menu-text {
    flex: 1;
}

/* Active State (for future implementation) */
.menu-link.active {
    background-color: #482e92;
    color: white;
}

.menu-link.active .menu-icon-fas {
    color: white;
}

.menu-link.active:hover {
    background-color: #5b3ea8;
}

/* Log Out Specific Styles */
.menu-link-logout {
    color: #dc2626;
}

.menu-link-logout:hover {
    background-color: #fee2e2;
    color: #b91c1c;
}

.menu-link-logout:active {
    background-color: #fecaca;
}

/* Menu Divider */
.menu-divider {
    height: 1px;
    background-color: #e5e7eb;
    margin: 12px 24px;
}

/* Menu Footer */
.menu-footer {
    padding: 20px 24px 0 24px;
    border-top: 1px solid #e5e7eb;
    margin-top: auto;
}

.app-version {
    font-size: 12px;
    color: #9ca3af;
    text-align: center;
}

/* Responsive Design */
@media (max-width: 1024px) {
    .menu-header {
        padding: 0 20px 16px 20px;
    }
    
    .menu-item {
        margin: 4px 12px;
    }
    
    .menu-link {
        padding: 10px 14px;
        font-size: 14px;
    }
    
    .menu-icon-fas {
        font-size: 15px;
    }
}

@media (max-width: 768px) {
    .menu-header {
        padding: 0 16px 12px 16px;
    }
    
    .menu-item {
        margin: 3px 10px;
    }
    
    .menu-link {
        padding: 10px 12px;
        font-size: 13px;
        gap: 10px;
    }
    
    .menu-icon-fas {
        font-size: 14px;
    }
    
    .menu-footer {
        padding: 16px 16px 0 16px;
    }
}

@media (max-width: 640px) {
    .dashboard-menu {
        flex-direction: row;
        overflow-x: auto;
        padding: 0;
    }
    
    .menu-header,
    .menu-divider,
    .menu-footer {
        display: none;
    }
    
    .menu-items {
        display: flex;
        flex: 1;
        justify-content: space-around;
        padding: 8px 0;
    }
    
    .menu-item {
        margin: 0;
        flex: 1;
    }
    
    .menu-link {
        flex-direction: column;
        gap: 6px;
        padding: 10px 8px;
        border-radius: 6px;
        text-align: center;
    }
    
    .menu-text {
        font-size: 11px;
        line-height: 1.2;
    }
    
    .menu-icon-fas {
        font-size: 16px;
        margin-bottom: 2px;
    }
}
</style>
