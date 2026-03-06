<script setup lang="ts">
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { computed } from 'vue'

const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()

const navigation = computed(() => [
  { name: 'Dashboard', href: '/', icon: 'dashboard', active: route.path === '/' },
  { name: 'Events', href: '/events', icon: 'calendar', active: route.path === '/events' },
  { name: 'Helpdesk', href: '/helpdesk', icon: 'chat', active: route.path === '/helpdesk' },
  { 
    name: 'Agent Panel', 
    href: '/helpdesk-agent', 
    icon: 'support', 
    active: route.path === '/helpdesk-agent',
    show: authStore.isHelpdeskAgent 
  },
])

async function handleLogout() {
  await authStore.logout()
  router.push('/login')
}
</script>

<template>
  <div class="min-h-screen bg-slate-50 flex flex-col">
    <!-- Navbar -->
    <nav class="bg-white border-b border-slate-200 sticky top-0 z-10">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
          <div class="flex">
            <div class="flex-shrink-0 flex items-center">
              <div class="p-2 bg-indigo-600 rounded-lg shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
              </div>
              <span class="ml-3 text-xl font-bold text-slate-900 tracking-tight">UCC Proba</span>
            </div>
            <div class="hidden sm:-my-px sm:ml-8 sm:flex sm:space-x-8">
              <template v-for="item in navigation" :key="item.name">
                <router-link
                  v-if="item.show !== false"
                  :to="item.href"
                  :class="[
                    item.active
                      ? 'border-indigo-500 text-slate-900'
                      : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300',
                    'inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition'
                  ]"
                >
                  {{ item.name }}
                </router-link>
              </template>
            </div>
          </div>
          <div class="hidden sm:ml-6 sm:flex sm:items-center space-x-4">
            <div class="flex flex-col items-end">
              <span class="text-sm font-semibold text-slate-900">{{ authStore.user?.name }}</span>
              <span class="text-xs text-slate-500">{{ authStore.user?.email }}</span>
            </div>
            <div class="h-8 w-px bg-slate-200"></div>
            <button
              @click="handleLogout"
              class="btn btn-secondary text-sm py-1.5"
            >
              Sign out
            </button>
          </div>
        </div>
      </div>
    </nav>

    <!-- Mobile Navigation -->
    <div class="sm:hidden bg-white border-b border-slate-200 px-4 py-2 flex overflow-x-auto space-x-4 no-scrollbar">
      <template v-for="item in navigation" :key="item.name">
        <router-link
          v-if="item.show !== false"
          :to="item.href"
          :class="[
            item.active ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600',
            'px-3 py-1.5 rounded-md text-sm font-medium whitespace-nowrap'
          ]"
        >
          {{ item.name }}
        </router-link>
      </template>
    </div>

    <!-- Content Area -->
    <main class="flex-1 py-8 px-4 sm:px-6 lg:px-8">
      <div class="max-w-7xl mx-auto">
        <slot></slot>
      </div>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200 py-6">
      <div class="max-w-7xl mx-auto px-4 text-center">
        <p class="text-sm text-slate-500">&copy; 2026 UCC Project Task. Built with Vue 3 & Laravel.</p>
      </div>
    </footer>
  </div>
</template>

<style scoped>
.no-scrollbar::-webkit-scrollbar {
  display: none;
}
.no-scrollbar {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
</style>
