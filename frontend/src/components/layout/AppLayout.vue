<script setup lang="ts">
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { computed, ref } from 'vue'

const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()
const isMobileMenuOpen = ref(false)

const navigation = computed(() => [
  { name: 'Dashboard', href: '/', icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6', active: route.path === '/' },
  { name: 'Events', href: '/events', icon: 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z', active: route.path === '/events' },
  { name: 'Helpdesk', href: '/helpdesk', icon: 'M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z', active: route.path === '/helpdesk' },
  { 
    name: 'Agent Panel', 
    href: '/helpdesk-agent', 
    icon: 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z', 
    active: route.path === '/helpdesk-agent',
    show: authStore.isHelpdeskAgent 
  },
  { name: 'Settings', href: '/settings', icon: 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.756 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z', active: route.path === '/settings' },
])

async function handleLogout() {
  await authStore.logout()
  router.push('/login')
}
</script>

<template>
  <div class="min-h-screen bg-[#fdfbf7] flex font-sans">
    <!-- Sidebar Desktop -->
    <aside class="hidden lg:flex flex-col w-72 bg-[#1a1a1a] text-[#fdfbf7] fixed h-full z-20 transition-all duration-300 border-r-4 border-[#c9171e]">
      <div class="p-8">
        <div class="flex items-center space-x-3 group cursor-pointer">
          <div class="bg-[#c9171e] w-8 h-8 rounded-full flex items-center justify-center group-hover:scale-110 transition-transform">
            <span class="w-2 h-2 bg-white rounded-full"></span>
          </div>
          <span class="text-xl font-black tracking-[0.2em] uppercase">UCC <span class="text-slate-500">System</span></span>
        </div>
      </div>

      <nav class="flex-1 px-6 space-y-4 mt-8">
        <template v-for="item in navigation" :key="item.name">
          <router-link
            v-if="item.show !== false"
            :to="item.href"
            :class="[
              item.active
                ? 'text-[#c9171e] border-l-2 border-[#c9171e] pl-4 translate-x-2'
                : 'text-slate-400 hover:text-[#fdfbf7] hover:pl-2',
              'flex items-center py-2 text-xs font-black uppercase tracking-[0.2em] transition-all duration-300 group'
            ]"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-4 transition-colors" :class="item.active ? 'text-[#c9171e]' : 'text-slate-500 group-hover:text-white'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="item.icon" />
            </svg>
            {{ item.name }}
          </router-link>
        </template>
      </nav>

      <div class="p-6 mt-auto">
        <div class="bg-[#111111] p-4 border border-slate-800 relative overflow-hidden group">
          <!-- Decorative Manga Background Element -->
          <div class="absolute -right-4 -bottom-4 text-slate-800/20 text-4xl font-black rotate-12 pointer-events-none uppercase">UCC</div>
          
          <div class="flex items-center mb-6 relative z-10">
            <div class="h-12 w-12 bg-[#fdfbf7] p-0.5 border-2 border-[#c9171e] overflow-hidden">
              <img 
                :src="'https://api.dicebear.com/9.x/adventurer/svg?seed=' + (authStore.user?.name || 'User')" 
                class="w-full h-full object-cover"
                alt="Avatar"
              />
            </div>
            <div class="ml-4 overflow-hidden">
              <p class="text-sm font-bold truncate text-[#fdfbf7]">{{ authStore.user?.name }}</p>
              <p class="text-[10px] text-[#c9171e] font-black truncate uppercase tracking-widest">{{ authStore.user?.role || 'User' }}</p>
            </div>
          </div>
          <button
            @click="handleLogout"
            class="w-full flex items-center justify-center space-x-2 py-3 border border-slate-700 hover:border-[#c9171e] hover:bg-[#c9171e] hover:text-white transition-all text-xs font-black tracking-widest uppercase"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
            </svg>
            <span>Logout (退出)</span>
          </button>
        </div>
      </div>
    </aside>

    <!-- Content Area -->
    <div class="flex-1 lg:ml-72 flex flex-col min-h-screen">
      <!-- Mobile Top Bar -->
      <header class="lg:hidden bg-white border-b border-slate-200 p-4 flex justify-between items-center sticky top-0 z-30">
        <div class="flex items-center space-x-2">
          <div class="bg-indigo-600 p-1.5 rounded-lg">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
            </svg>
          </div>
          <span class="font-black text-slate-900 tracking-tight italic">UCC</span>
        </div>
        <button @click="isMobileMenuOpen = !isMobileMenuOpen" class="p-2 text-slate-600">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" />
          </svg>
        </button>
      </header>

      <!-- Mobile Menu Overlay -->
      <div v-if="isMobileMenuOpen" class="lg:hidden fixed inset-0 z-40">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="isMobileMenuOpen = false"></div>
        <nav class="absolute top-0 left-0 bottom-0 w-64 bg-slate-900 p-6 space-y-4">
          <template v-for="item in navigation" :key="item.name">
            <router-link
              v-if="item.show !== false"
              :to="item.href"
              @click="isMobileMenuOpen = false"
              :class="[
                item.active ? 'bg-indigo-600 text-white' : 'text-slate-400',
                'flex items-center p-3 rounded-xl font-bold'
              ]"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="item.icon" />
              </svg>
              {{ item.name }}
            </router-link>
          </template>
        </nav>
      </div>

      <!-- Main Header (Desktop) -->
      <header class="hidden lg:flex items-center justify-between px-10 py-6 bg-white/50 backdrop-blur-md sticky top-0 z-10 border-b border-slate-200/50">
        <div>
          <h2 class="text-sm font-bold text-slate-400 uppercase tracking-widest">{{ route.name?.toString().replace('-', ' ') }}</h2>
        </div>
        <div class="flex items-center space-x-6">
          <div class="relative group">
            <button class="p-2 text-slate-400 hover:text-indigo-600 transition-colors">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
              </svg>
              <span class="absolute top-2 right-2 h-2 w-2 bg-rose-500 rounded-full border-2 border-white"></span>
            </button>
          </div>
          <div class="h-8 w-px bg-slate-200"></div>
          <div class="flex items-center space-x-3 group cursor-pointer">
            <div class="text-right">
              <p class="text-xs font-black text-slate-400 uppercase tracking-widest leading-none mb-1">User</p>
              <p class="text-sm font-bold text-slate-700 leading-none">{{ authStore.user?.name }}</p>
            </div>
            <div class="h-10 w-10 bg-white border border-slate-200 overflow-hidden shadow-sm group-hover:border-[#c9171e] transition-colors">
              <img 
                :src="'https://api.dicebear.com/9.x/adventurer/svg?seed=' + (authStore.user?.name || 'User')" 
                class="w-full h-full object-cover"
                alt="Profile"
              />
            </div>
          </div>
        </div>
      </header>

      <main class="flex-1 p-6 lg:p-10 max-w-(--breakpoint-2xl) w-full mx-auto">
        <slot></slot>
      </main>

      <!-- Next Gen Footer -->
      <footer class="px-10 py-8 border-t border-slate-200/50 bg-white/30 backdrop-blur-sm">
        <div class="max-w-(--breakpoint-2xl) mx-auto flex flex-col md:flex-row justify-between items-center gap-4">
          <div class="flex items-center space-x-2 opacity-50 grayscale hover:opacity-100 hover:grayscale-0 transition-all duration-500">
            <div class="bg-slate-900 p-1 rounded-lg">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
              </svg>
            </div>
            <span class="text-xs font-black tracking-tight uppercase">UCC System</span>
          </div>
          
          <div class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em]">
            &copy; 2026 Crafted with Passion
          </div>

          <div class="flex items-center space-x-1 text-xs font-bold text-slate-500">
            <span>Developed by</span>
            <a href="https://salamonszilard.hu" target="_blank" class="text-indigo-600 hover:text-indigo-500 transition-colors px-2 py-1 bg-indigo-50 rounded-lg border border-indigo-100 hover:border-indigo-300">
              salamonszilard.hu
            </a>
          </div>
        </div>
      </footer>
    </div>
  </div>
</template>
