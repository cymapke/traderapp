import { createRouter, createWebHistory } from 'vue-router'
import { authGuard } from './guard.ts'

const router = createRouter({
    history: createWebHistory(),
    routes: [
        {
            path: '/',
            name: 'home',
            component: () => import('@/views/HomeView.vue'),
            meta: { requiresGuest: false }
        },
        {
            path: '/login',
            name: 'login',
            component: () => import('@/views/LoginView.vue'),
            meta: { requiresGuest: true }
        },
        {
            path: '/register',
            name: 'register',
            component: () => import('@/views/RegisterView.vue'),
            meta: { requiresGuest: true }
        },
        {
            path: '/dashboard',
            name: 'dashboard',
            component: () => import('@/views/DashboardView.vue'),
            meta: { requiresAuth: true },
            redirect: { name: 'dashboard.overview' },
            children: [
                {
                    path: 'overview',
                    name: 'dashboard.overview',
                    component: () => import('@/views/dashboard/OverviewView.vue')
                },
                {
                    path: 'funds',
                    name: 'dashboard.funds',
                    component: () => import('@/views/dashboard/FundsView.vue')
                },
                {
                    path: 'orders',
                    name: 'dashboard.orders',
                    component: () => import('@/views/dashboard/OrdersView.vue')
                }
            ]
        }
    ]
})

// Apply route guards
router.beforeEach(authGuard)

export default router
