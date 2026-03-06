import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/login',
      name: 'login',
      component: () => import('../features/auth/LoginView.vue'),
      meta: { requiresGuest: true }
    },
    {
      path: '/forgot-password',
      name: 'forgot-password',
      component: () => import('../features/auth/ForgotPasswordView.vue'),
      meta: { requiresGuest: true }
    },
    {
      path: '/reset-password',
      name: 'reset-password',
      component: () => import('../features/auth/ResetPasswordView.vue'),
      meta: { requiresGuest: true }
    },
    {
      path: '/',
      name: 'dashboard',
      component: () => import('../views/DashboardView.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/events',
      name: 'events',
      component: () => import('../features/events/EventsView.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/helpdesk',
      name: 'helpdesk',
      component: () => import('../features/helpdesk/HelpdeskView.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/helpdesk-agent',
      name: 'helpdesk-agent',
      component: () => import('../features/helpdesk-agent/HelpdeskAgentView.vue'),
      meta: { requiresAuth: true, requiresAgent: true }
    }
  ]
})

router.beforeEach((to, from, next) => {
  const authStore = useAuthStore()

  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    next('/login')
  } else if (to.meta.requiresGuest && authStore.isAuthenticated) {
    next('/')
  } else if (to.meta.requiresAgent && !authStore.isHelpdeskAgent) {
    next('/')
  } else {
    next()
  }
})

export default router
