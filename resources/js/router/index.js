import { createRouter, createWebHistory } from 'vue-router'
import { authGuard } from './guard.ts'

const router = createRouter({
    history: createWebHistory(),
    routes: [
        {
            path: '/',
            name: 'home',
            component: () => import('@/views/HomeView.vue'),
            meta: { requiresGuest: false } // Public route
        },
        {
            path: '/login',
            name: 'login',
            component: () => import('@/views/LoginView.vue'),
            meta: { requiresGuest: true } // Only for non-logged in users
        },
        {
            path: '/register',
            name: 'register',
            component: () => import('@/views/RegisterView.vue'),
            meta: { requiresGuest: true } // Only for non-logged in users
        },
        {
            path: '/dashboard',
            name: 'dashboard',
            component: () => import('@/views/DashboardView.vue'),
            meta: { requiresAuth: true } // Requires authentication
        }
    ]
})

// Apply the guard to all routes
router.beforeEach(authGuard)

export default router
