<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { eventService } from '@/services/eventService'
import { chatService } from '@/services/chatService'
import AppLayout from '@/components/layout/AppLayout.vue'

const authStore = useAuthStore()
const eventCount = ref(0)
const chatCount = ref(0)
const loading = ref(true)

onMounted(async () => {
  try {
    const [events, chats] = await Promise.all([
      eventService.getAll(),
      chatService.getAll()
    ])
    eventCount.value = events.length
    chatCount.value = chats.length
  } catch (err) {
    console.error('Failed to fetch dashboard stats:', err)
  } finally {
    loading.value = false
  }
})

const stats = computed(() => [
  { 
    name: 'Active Events', 
    value: loading.value ? '...' : eventCount.value.toString(), 
    icon: 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z', 
    color: 'text-indigo-600', 
    bg: 'bg-indigo-50' 
  },
  { 
    name: 'Support Tickets', 
    value: loading.value ? '...' : chatCount.value.toString(), 
    icon: 'M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z', 
    color: 'text-emerald-600', 
    bg: 'bg-emerald-50' 
  },
  { 
    name: 'Security Score', 
    value: authStore.user?.mfa_enabled ? '100%' : '65%', 
    icon: 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z', 
    color: 'text-amber-600', 
    bg: 'bg-amber-50' 
  },
])

const recentActivity = computed(() => {
  const activities = []
  if (eventCount.value > 0) {
    activities.push({ id: 1, type: 'event', text: `You have ${eventCount.value} total events scheduled`, time: 'Updated now', icon: 'calendar' })
  }
  if (authStore.user?.mfa_enabled) {
    activities.push({ id: 2, type: 'security', text: 'MFA protection is active', time: 'Ongoing', icon: 'shield' })
  } else {
    activities.push({ id: 2, type: 'security', text: 'Improve security: Enable MFA', time: 'Recommended', icon: 'alert' })
  }
  if (chatCount.value > 0) {
    activities.push({ id: 3, type: 'helpdesk', text: `${chatCount.value} helpdesk conversations`, time: 'History', icon: 'chat' })
  }
  return activities
})
</script>

<template>
  <AppLayout>
    <!-- Welcome Header -->
    <div class="relative overflow-hidden rounded-3xl bg-slate-900 p-8 lg:p-12 mb-10 shadow-2xl shadow-slate-200">
      <div class="relative z-10">
        <h1 class="text-3xl lg:text-4xl font-black text-white tracking-tight">
          Welcome back, <span class="text-indigo-400">{{ authStore.user?.name.split(' ')[0] }}</span>!
        </h1>
        <p class="mt-4 text-slate-400 max-w-xl text-lg font-medium leading-relaxed">
          Everything is running smoothly. You have <span class="text-white font-bold">{{ chatCount }} tickets</span> and <span class="text-white font-bold">{{ eventCount }} events</span> in your system.
        </p>
        <div class="mt-8 flex flex-wrap gap-4">
          <router-link to="/events" class="px-6 py-3 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl font-bold transition-all shadow-lg shadow-indigo-600/30 active:scale-95">
            Manage Events
          </router-link>
          <router-link to="/helpdesk" class="px-6 py-3 bg-white/10 hover:bg-white/20 text-white border border-white/10 rounded-xl font-bold transition-all backdrop-blur-md active:scale-95">
            Support Chat
          </router-link>
        </div>
      </div>
      <!-- Decorative Background elements -->
      <div class="absolute -right-20 -top-20 w-96 h-96 bg-indigo-600/20 rounded-full blur-3xl"></div>
      <div class="absolute -left-20 -bottom-20 w-96 h-96 bg-purple-600/10 rounded-full blur-3xl"></div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
      <div v-for="stat in stats" :key="stat.name" class="card group p-6 hover:translate-y-[-4px] transition-all duration-300">
        <div class="flex items-center justify-between">
          <div :class="[stat.bg, stat.color, 'p-3 rounded-2xl transition-transform group-hover:scale-110']">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="stat.icon" />
            </svg>
          </div>
          <span class="text-3xl font-black text-slate-900 tracking-tighter">{{ stat.value }}</span>
        </div>
        <div class="mt-4">
          <h3 class="text-sm font-bold text-slate-400 uppercase tracking-widest">{{ stat.name }}</h3>
          <div v-if="!loading" class="mt-1 flex items-center text-xs font-bold text-emerald-500">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M12 7a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0V8.414l-4.293 4.293a1 1 0 01-1.414 0L8 10.414l-4.293 4.293a1 1 0 01-1.414-1.414l5-5a1 1 0 011.414 0L11 10.586 14.586 7H12z" clip-rule="evenodd" />
            </svg>
            Live Data
          </div>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      <!-- Activity Feed -->
      <div class="lg:col-span-2 card p-8">
        <div class="flex items-center justify-between mb-8">
          <h3 class="text-xl font-black text-slate-900 tracking-tight">System Status</h3>
          <button class="text-xs font-bold text-indigo-600 hover:text-indigo-800 uppercase tracking-widest">Refresh</button>
        </div>
        <div class="space-y-8">
          <div v-if="recentActivity.length === 0" class="text-center py-10 text-slate-400 font-medium italic">
            No activity recorded yet.
          </div>
          <div v-for="item in recentActivity" :key="item.id" class="relative flex items-start group">
            <div class="absolute left-4 top-10 bottom-[-32px] w-0.5 bg-slate-100 last:hidden"></div>
            <div class="flex-shrink-0 h-9 w-9 rounded-full bg-slate-50 border-2 border-white shadow-sm flex items-center justify-center z-10 group-hover:bg-indigo-50 group-hover:border-indigo-100 transition-colors">
              <div class="h-2 w-2 rounded-full bg-slate-300 group-hover:bg-indigo-500 transition-colors"></div>
            </div>
            <div class="ml-6">
              <p class="text-sm font-bold text-slate-800 group-hover:text-indigo-600 transition-colors">{{ item.text }}</p>
              <p class="text-xs font-medium text-slate-400 mt-1 uppercase tracking-wider">{{ item.time }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Quick Actions / Status -->
      <div class="space-y-6">
        <div class="card p-8 bg-indigo-600 text-white border-none shadow-xl shadow-indigo-200">
          <h3 class="text-lg font-bold mb-4">Security Overview</h3>
          <div class="space-y-4">
            <div class="flex justify-between items-center py-2 border-b border-white/10">
              <span class="text-indigo-100 text-sm font-medium">MFA Protection</span>
              <span class="px-2 py-1 rounded-lg bg-white/20 text-xs font-black uppercase">{{ authStore.user?.mfa_enabled ? 'Active' : 'Missing' }}</span>
            </div>
            <div class="flex justify-between items-center py-2 border-b border-white/10">
              <span class="text-indigo-100 text-sm font-medium">Access Level</span>
              <span class="px-2 py-1 rounded-lg bg-white/20 text-xs font-black uppercase">{{ authStore.user?.role || 'User' }}</span>
            </div>
          </div>
          <router-link to="/settings" class="mt-6 w-full flex items-center justify-center py-3 bg-white text-indigo-600 rounded-xl text-sm font-black uppercase tracking-widest hover:bg-indigo-50 transition-colors shadow-lg">
            Security Settings
          </router-link>
        </div>

        <div class="card p-8 border-dashed border-2 border-slate-200 bg-transparent shadow-none">
          <div class="text-center">
            <div class="h-12 w-12 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
              </svg>
            </div>
            <h4 class="text-sm font-bold text-slate-900 uppercase tracking-widest">New Integration</h4>
            <p class="text-xs text-slate-400 mt-2 font-medium">Connect your calendar to sync events automatically.</p>
            <button class="mt-4 text-xs font-black text-indigo-600 uppercase tracking-widest hover:text-indigo-800">Coming Soon</button>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
