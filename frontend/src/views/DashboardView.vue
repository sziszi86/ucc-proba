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
const showSecurityTip = ref(true)

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
    name: 'Active Events (イベント)', 
    value: loading.value ? '...' : eventCount.value.toString(), 
    icon: 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z', 
  },
  { 
    name: 'Tickets (チケット)', 
    value: loading.value ? '...' : chatCount.value.toString(), 
    icon: 'M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z', 
  },
  { 
    name: 'Security (セキュリティ)', 
    value: authStore.user?.mfa_enabled ? '100%' : '65%', 
    icon: 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z', 
  },
])

const recentActivity = computed(() => {
  const activities = []
  if (eventCount.value > 0) {
    activities.push({ id: 1, type: 'event', text: `You have ${eventCount.value} total events scheduled`, time: 'Updated now' })
  }
  if (authStore.user?.mfa_enabled) {
    activities.push({ id: 2, type: 'security', text: 'MFA protection is active', time: 'Ongoing' })
  } else {
    activities.push({ id: 2, type: 'security', text: 'Improve security: Enable MFA', time: 'Recommended' })
  }
  if (chatCount.value > 0) {
    activities.push({ id: 3, type: 'helpdesk', text: `${chatCount.value} helpdesk conversations`, time: 'History' })
  }
  return activities
})
</script>

<template>
  <AppLayout>
    <!-- Minimalist Welcome Header (Ink Black) -->
    <div class="relative bg-[#1a1a1a] p-10 lg:p-16 mb-12 shadow-[8px_8px_0px_0px_rgba(201,23,30,1)] group overflow-hidden">
      <!-- Subtle Japanese pattern (Seigaiha inspired) -->
      <div class="absolute inset-0 opacity-5" style="background-image: radial-gradient(circle at 100% 150%, #ffffff 24%, transparent 25%), radial-gradient(circle at 0% 150%, #ffffff 24%, transparent 25%), radial-gradient(circle at 50% 100%, #ffffff 10%, transparent 11%); background-size: 50px 25px;"></div>
      
      <!-- Red Sun Accent -->
      <div class="absolute -right-20 -top-20 w-64 h-64 bg-[#c9171e] rounded-full opacity-90 transition-transform duration-1000 group-hover:scale-110"></div>
      
      <div class="relative z-10">
        <p class="text-[#c9171e] font-black tracking-[0.3em] uppercase mb-4 text-xs">ようこそ / Welcome</p>
        <h1 class="text-4xl lg:text-5xl font-black text-[#fdfbf7] tracking-tight leading-tight">
          Welcome back,<br />
          <span>{{ authStore.user?.name.split(' ')[0] }}</span>.
        </h1>
        <p class="mt-6 text-slate-400 max-w-xl text-lg font-medium leading-relaxed font-serif">
          Find clarity in your workflow. You have <span class="text-[#fdfbf7] font-bold">{{ chatCount }} active tickets</span> and <span class="text-[#fdfbf7] font-bold">{{ eventCount }} upcoming events</span>.
        </p>
        <div class="mt-10 flex flex-wrap gap-4">
          <router-link to="/events" class="px-8 py-3 bg-[#c9171e] text-white font-bold tracking-widest uppercase text-sm border-2 border-[#c9171e] hover:bg-transparent hover:text-[#c9171e] transition-all duration-300">
            Manage Events
          </router-link>
          <router-link to="/helpdesk" class="px-8 py-3 bg-transparent text-[#fdfbf7] border-2 border-[#fdfbf7] font-bold tracking-widest uppercase text-sm hover:bg-[#fdfbf7] hover:text-[#1a1a1a] transition-all duration-300">
            Support Chat
          </router-link>
        </div>
      </div>
    </div>

    <!-- Security Tip Banner (Zen Style) -->
    <div v-if="!authStore.user?.mfa_enabled && showSecurityTip" class="animate-in mb-12 bg-white border-l-4 border-[#c9171e] p-6 shadow-[4px_4px_0px_0px_rgba(26,26,26,0.1)] relative">
      <button @click="showSecurityTip = false" class="absolute top-4 right-4 text-slate-400 hover:text-[#c9171e] transition-colors">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
      </button>
      <div class="flex flex-col md:flex-row items-start md:items-center justify-between">
        <div class="pr-8 mb-4 md:mb-0">
          <h4 class="text-[#1a1a1a] font-black uppercase tracking-wider text-sm flex items-center">
            <span class="w-2 h-2 bg-[#c9171e] rounded-full mr-3 animate-pulse"></span>
            Security Attention (注意)
          </h4>
          <p class="text-slate-500 text-sm mt-2 font-medium">Your current score is 65%. Protect your data by enabling Multi-Factor Authentication.</p>
        </div>
        <router-link to="/settings" class="text-xs font-bold tracking-widest uppercase text-[#c9171e] hover:text-[#1a1a1a] border-b-2 border-[#c9171e] hover:border-[#1a1a1a] pb-1 transition-colors whitespace-nowrap">
          Enable MFA
        </router-link>
      </div>
    </div>

    <!-- Stats Grid (Minimal Cards) -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-12">
      <div v-for="stat in stats" :key="stat.name" class="bg-white border border-slate-200 p-8 shadow-[4px_4px_0px_0px_rgba(26,26,26,1)] hover:translate-y-[-4px] hover:shadow-[8px_8px_0px_0px_rgba(26,26,26,1)] transition-all duration-300">
        <div class="flex items-start justify-between">
          <div>
            <h3 class="text-xs font-black text-slate-400 uppercase tracking-[0.2em] mb-4">{{ stat.name }}</h3>
            <span class="text-4xl font-black text-[#1a1a1a]">{{ stat.value }}</span>
          </div>
          <div class="text-[#c9171e]">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 opacity-80" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" :d="stat.icon" />
            </svg>
          </div>
        </div>
        <div class="mt-6 pt-4 border-t border-slate-100 flex items-center text-xs font-bold text-slate-500 uppercase tracking-widest">
          <span v-if="!loading" class="flex items-center text-[#1a1a1a]">
            Live Data
          </span>
          <span v-else>Loading...</span>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
      <!-- Activity Feed (Timeline) -->
      <div class="lg:col-span-2">
        <h3 class="text-xl font-black text-[#1a1a1a] tracking-tight uppercase mb-8 flex items-center">
          <span class="w-8 h-0.5 bg-[#c9171e] mr-4"></span>
          Recent Activity (活動)
        </h3>
        
        <div class="bg-white border border-slate-200 p-8 shadow-[4px_4px_0px_0px_rgba(26,26,26,0.05)]">
          <div v-if="recentActivity.length === 0" class="text-center py-10 text-slate-400 font-medium italic">
            No activity recorded yet.
          </div>
          <div class="space-y-0 relative before:absolute before:inset-0 before:ml-2 before:-translate-x-px md:before:mx-auto md:before:translate-x-0 before:h-full before:w-0.5 before:bg-gradient-to-b before:from-transparent before:via-slate-200 before:to-transparent">
            <div v-for="(item, index) in recentActivity" :key="item.id" class="relative flex items-center justify-between md:justify-normal md:odd:flex-row-reverse group is-active py-6">
              <!-- Timeline Dot -->
              <div class="flex items-center justify-center w-5 h-5 rounded-full border-4 border-white bg-[#1a1a1a] group-hover:bg-[#c9171e] shadow shrink-0 md:order-1 md:group-odd:-translate-x-1/2 md:group-even:translate-x-1/2 z-10 transition-colors"></div>
              
              <!-- Content -->
              <div class="w-[calc(100%-2.5rem)] md:w-[calc(50%-1.25rem)] p-4 border border-slate-100 bg-slate-50/50 hover:bg-white hover:border-slate-300 transition-colors">
                <div class="flex items-center justify-between mb-1">
                  <span class="text-xs font-black text-[#c9171e] tracking-widest uppercase">{{ item.time }}</span>
                </div>
                <p class="text-sm font-bold text-[#1a1a1a]">{{ item.text }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Quick Actions / Status -->
      <div class="space-y-8">
        <h3 class="text-xl font-black text-[#1a1a1a] tracking-tight uppercase mb-8 flex items-center">
          <span class="w-8 h-0.5 bg-[#1a1a1a] mr-4"></span>
          Overview
        </h3>
        
        <div class="bg-[#1a1a1a] text-[#fdfbf7] p-8 shadow-[4px_4px_0px_0px_rgba(201,23,30,1)]">
          <h3 class="text-sm font-black uppercase tracking-[0.2em] mb-6 text-slate-400">Security Profile</h3>
          <div class="space-y-6">
            <div class="flex justify-between items-center pb-4 border-b border-white/10">
              <span class="text-sm font-medium">MFA Status</span>
              <span class="px-3 py-1 bg-white/10 text-xs font-black uppercase tracking-widest">{{ authStore.user?.mfa_enabled ? 'Active' : 'Disabled' }}</span>
            </div>
            <div class="flex justify-between items-center pb-4 border-b border-white/10">
              <span class="text-sm font-medium">Clearance</span>
              <span class="px-3 py-1 bg-[#c9171e] text-white text-xs font-black uppercase tracking-widest">{{ authStore.user?.role || 'User' }}</span>
            </div>
          </div>
          <router-link to="/settings" class="mt-8 block w-full text-center py-3 border border-white/20 hover:bg-white hover:text-[#1a1a1a] text-xs font-black uppercase tracking-[0.2em] transition-colors">
            Configure
          </router-link>
        </div>

        <!-- Vertical Text Accent Element -->
        <div class="flex items-center justify-center p-8 border border-slate-200 bg-white">
          <p class="text-4xl font-black text-slate-100" style="writing-mode: vertical-rl; text-orientation: upright;">
            集中
          </p>
          <div class="ml-6 text-center">
            <p class="text-xs font-black text-slate-400 uppercase tracking-[0.2em]">Focus</p>
            <p class="text-sm text-slate-500 mt-2 font-medium">Maintain clarity in your daily tasks.</p>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
