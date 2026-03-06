<script setup lang="ts">
import { useAuthStore } from '@/stores/auth'
import AppLayout from '@/components/layout/AppLayout.vue'

const authStore = useAuthStore()
</script>

<template>
  <AppLayout>
    <div class="mb-8">
      <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Dashboard</h1>
      <p class="mt-2 text-slate-600">Welcome back, <span class="font-semibold text-indigo-600">{{ authStore.user?.name }}</span>. Here's what's happening today.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
      <!-- Quick Action: Events -->
      <router-link to="/events" class="card group p-6 hover:ring-2 hover:ring-indigo-500 transition duration-300">
        <div class="flex items-start justify-between">
          <div class="p-3 bg-indigo-50 text-indigo-600 rounded-xl group-hover:bg-indigo-600 group-hover:text-white transition duration-300">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
          </div>
          <span class="text-slate-300 group-hover:text-indigo-200 transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
          </span>
        </div>
        <div class="mt-4">
          <h3 class="text-xl font-bold text-slate-900">Events</h3>
          <p class="mt-1 text-slate-500">Manage your scheduled events and create new ones.</p>
        </div>
      </router-link>

      <!-- Quick Action: Helpdesk -->
      <router-link to="/helpdesk" class="card group p-6 hover:ring-2 hover:ring-indigo-500 transition duration-300">
        <div class="flex items-start justify-between">
          <div class="p-3 bg-emerald-50 text-emerald-600 rounded-xl group-hover:bg-emerald-600 group-hover:text-white transition duration-300">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
            </svg>
          </div>
          <span class="text-slate-300 group-hover:text-emerald-200 transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
          </span>
        </div>
        <div class="mt-4">
          <h3 class="text-xl font-bold text-slate-900">Helpdesk</h3>
          <p class="mt-1 text-slate-500">Get assistance from our AI or chat with an agent.</p>
        </div>
      </router-link>

      <!-- Quick Action: Agent Dashboard (Conditional) -->
      <router-link v-if="authStore.isHelpdeskAgent" to="/helpdesk-agent" class="card group p-6 hover:ring-2 hover:ring-indigo-500 transition duration-300">
        <div class="flex items-start justify-between">
          <div class="p-3 bg-amber-50 text-amber-600 rounded-xl group-hover:bg-amber-600 group-hover:text-white transition duration-300">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
          </div>
          <span class="text-slate-300 group-hover:text-amber-200 transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
          </span>
        </div>
        <div class="mt-4">
          <h3 class="text-xl font-bold text-slate-900">Agent Panel</h3>
          <p class="mt-1 text-slate-500">View and respond to customer inquiries and chats.</p>
        </div>
      </router-link>
    </div>

    <!-- Stats or Info Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <div class="card p-6">
        <h3 class="text-lg font-bold text-slate-900 mb-4 flex items-center">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          Account Status
        </h3>
        <div class="space-y-4">
          <div class="flex justify-between items-center py-2 border-b border-slate-50">
            <span class="text-slate-600">MFA Protection</span>
            <span :class="[authStore.user?.mfa_enabled ? 'text-emerald-600 bg-emerald-50' : 'text-amber-600 bg-amber-50', 'px-2 py-1 rounded text-xs font-bold uppercase tracking-wider']">
              {{ authStore.user?.mfa_enabled ? 'Enabled' : 'Disabled' }}
            </span>
          </div>
          <div class="flex justify-between items-center py-2 border-b border-slate-50">
            <span class="text-slate-600">Access Level</span>
            <span class="text-indigo-600 bg-indigo-50 px-2 py-1 rounded text-xs font-bold uppercase tracking-wider">
              {{ authStore.user?.role || 'User' }}
            </span>
          </div>
          <div class="flex justify-between items-center py-2">
            <span class="text-slate-600">Member Since</span>
            <span class="text-slate-900 font-medium">March 2026</span>
          </div>
        </div>
      </div>

      <div class="card p-6">
        <h3 class="text-lg font-bold text-slate-900 mb-4 flex items-center">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
          </svg>
          System Updates
        </h3>
        <div class="space-y-4">
          <div class="p-3 bg-slate-50 rounded-lg">
            <p class="text-xs font-bold text-indigo-600 uppercase mb-1">Update • 2 hours ago</p>
            <p class="text-sm text-slate-700">New AI model deployed for Helpdesk chats. Performance improved by 25%.</p>
          </div>
          <div class="p-3 bg-slate-50 rounded-lg">
            <p class="text-xs font-bold text-indigo-600 uppercase mb-1">Maintenance • Yesterday</p>
            <p class="text-sm text-slate-700">Database optimization completed. Everything is running smoothly.</p>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
