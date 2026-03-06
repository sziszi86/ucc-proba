<script setup lang="ts">
import { ref } from 'vue'
import { useAuthStore } from '@/stores/auth'
import AppLayout from '@/components/layout/AppLayout.vue'

const authStore = useAuthStore()
const mfaCode = ref('')
const mfaData = ref<any>(null)
const step = ref('initial') // 'initial', 'setup', 'confirm'
const loading = ref(false)
const error = ref('')
const successMessage = ref('')

async function startMfaSetup() {
  try {
    loading.value = true
    error.value = ''
    mfaData.value = await authStore.enableMFA()
    step.value = 'setup'
  } catch (err: any) {
    error.value = err.response?.data?.error || 'Failed to start MFA setup'
  } finally {
    loading.value = false
  }
}

async function confirmMfa() {
  try {
    loading.value = true
    error.value = ''
    await authStore.confirmMFA(mfaCode.value)
    successMessage.value = 'Multi-Factor Authentication enabled successfully!'
    step.value = 'initial'
    mfaCode.value = ''
  } catch (err: any) {
    error.value = err.response?.data?.error || 'Invalid MFA code'
  } finally {
    loading.value = false
  }
}

async function disableMfa() {
  if (!confirm('Are you sure you want to disable MFA? This will make your account less secure.')) return
  
  try {
    loading.value = true
    error.value = ''
    await authStore.disableMFA(mfaCode.value)
    successMessage.value = 'Multi-Factor Authentication disabled.'
    mfaCode.value = ''
    step.value = 'initial'
  } catch (err: any) {
    error.value = err.response?.data?.error || 'Invalid MFA code'
  } finally {
    loading.value = false
  }
}

function cancelSetup() {
  step.value = 'initial'
  mfaCode.value = ''
  mfaData.value = null
  error.value = ''
}
</script>

<template>
  <AppLayout>
    <div class="mb-8">
      <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Account Settings</h1>
      <p class="mt-2 text-slate-600">Manage your security preferences and profile information.</p>
    </div>

    <div class="max-w-4xl space-y-6">
      <!-- Profile Card -->
      <div class="card p-6">
        <h3 class="text-lg font-bold text-slate-900 mb-4 flex items-center">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
          </svg>
          Profile Information
        </h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
          <div>
            <label class="block text-sm font-semibold text-slate-500 uppercase tracking-wider">Full Name</label>
            <p class="mt-1 text-slate-900 font-medium">{{ authStore.user?.name }}</p>
          </div>
          <div>
            <label class="block text-sm font-semibold text-slate-500 uppercase tracking-wider">Email Address</label>
            <p class="mt-1 text-slate-900 font-medium">{{ authStore.user?.email }}</p>
          </div>
          <div>
            <label class="block text-sm font-semibold text-slate-500 uppercase tracking-wider">Account Role</label>
            <p class="mt-1">
              <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 uppercase">
                {{ authStore.user?.role || 'User' }}
              </span>
            </p>
          </div>
        </div>
      </div>

      <!-- Security / MFA Card -->
      <div class="card p-6 border-l-4" :class="authStore.user?.mfa_enabled ? 'border-l-emerald-500' : 'border-l-amber-500'">
        <div class="flex justify-between items-start mb-6">
          <div>
            <h3 class="text-lg font-bold text-slate-900 flex items-center">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
              </svg>
              Multi-Factor Authentication (MFA)
            </h3>
            <p class="text-slate-500 text-sm mt-1">Add an extra layer of security to your account using Google Authenticator.</p>
          </div>
          <span :class="[authStore.user?.mfa_enabled ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700', 'px-3 py-1 rounded-full text-xs font-bold uppercase tracking-widest border border-current opacity-80']">
            {{ authStore.user?.mfa_enabled ? 'Active' : 'Inactive' }}
          </span>
        </div>

        <div v-if="successMessage" class="mb-6 p-4 bg-emerald-50 border border-emerald-100 text-emerald-700 rounded-lg flex items-center">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
          </svg>
          {{ successMessage }}
        </div>

        <div v-if="error" class="mb-6 p-4 bg-rose-50 border border-rose-100 text-rose-700 rounded-lg text-sm">
          {{ error }}
        </div>

        <!-- Initial State -->
        <div v-if="step === 'initial'">
          <div v-if="!authStore.user?.mfa_enabled" class="bg-slate-50 p-6 rounded-xl border border-dashed border-slate-300 text-center">
            <div class="h-16 w-16 bg-white rounded-full flex items-center justify-center mx-auto mb-4 shadow-sm">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
              </svg>
            </div>
            <h4 class="font-bold text-slate-900">Enable Two-Factor Protection</h4>
            <p class="text-sm text-slate-500 mt-1 max-w-xs mx-auto">Protect your account from unauthorized access by requiring a temporary code from your mobile device.</p>
            <button @click="startMfaSetup" :disabled="loading" class="mt-6 btn btn-primary px-8">
              {{ loading ? 'Starting...' : 'Setup MFA' }}
            </button>
          </div>

          <div v-else class="space-y-4">
            <div class="p-4 bg-emerald-50/50 rounded-lg border border-emerald-100 flex items-start">
              <div class="p-2 bg-emerald-100 rounded-lg text-emerald-600 mr-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
              </div>
              <div>
                <h4 class="font-bold text-slate-900">Your account is secured</h4>
                <p class="text-sm text-slate-600 mt-1">MFA is currently active. You will be prompted for a code whenever you sign in from a new device.</p>
              </div>
            </div>

            <div class="pt-4 border-t border-slate-100">
              <label class="block text-sm font-semibold text-slate-700 mb-2">Disable MFA</label>
              <div class="flex flex-col sm:flex-row gap-3">
                <input 
                  v-model="mfaCode" 
                  type="text" 
                  maxlength="6" 
                  placeholder="Enter 6-digit code" 
                  class="input-field sm:w-48 text-center font-mono tracking-widest"
                >
                <button 
                  @click="disableMfa" 
                  :disabled="loading || mfaCode.length !== 6" 
                  class="btn btn-secondary border-rose-200 text-rose-600 hover:bg-rose-50"
                >
                  Deactivate MFA
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Setup Step (QR Code) -->
        <div v-if="step === 'setup'" class="space-y-6 animate-in fade-in slide-in-from-bottom-2 duration-300">
          <div class="flex flex-col md:flex-row gap-8 items-center md:items-start">
            <div class="p-4 bg-white border-2 border-slate-100 rounded-2xl shadow-sm">
              <img v-if="mfaData?.qr_code_url" :src="'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' + encodeURIComponent(mfaData.qr_code_url)" alt="MFA QR Code" class="w-48 h-48">
              <div v-else class="w-48 h-48 bg-slate-100 animate-pulse flex items-center justify-center text-slate-400">Loading QR...</div>
            </div>
            
            <div class="flex-1 space-y-4 text-center md:text-left">
              <h4 class="text-xl font-bold text-slate-900">Scan this QR Code</h4>
              <ol class="text-sm text-slate-600 space-y-3 list-decimal list-inside">
                <li>Open your Authenticator app (Google Authenticator, Authy, etc.)</li>
                <li>Add a new account and select <span class="font-bold">"Scan QR Code"</span></li>
                <li>Scan the image on the left</li>
                <li>Enter the 6-digit code from the app below to confirm</li>
              </ol>
              
              <div class="pt-4">
                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Manual Entry Key</p>
                <code class="px-3 py-1 bg-slate-100 rounded text-indigo-600 font-mono font-bold">{{ mfaData?.secret }}</code>
              </div>
            </div>
          </div>

          <div class="bg-indigo-50 p-6 rounded-xl border border-indigo-100">
            <label class="block text-sm font-bold text-indigo-900 mb-2 text-center uppercase tracking-widest">Verification Code</label>
            <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
              <input 
                v-model="mfaCode" 
                type="text" 
                maxlength="6" 
                placeholder="000000" 
                class="input-field text-center text-2xl font-mono tracking-[0.25em] w-full max-w-[240px]"
              >
              <div class="flex gap-2 w-full sm:w-auto">
                <button @click="confirmMfa" :disabled="loading || mfaCode.length !== 6" class="btn btn-primary flex-1">
                  Verify & Activate
                </button>
                <button @click="cancelSetup" class="btn btn-secondary flex-1">
                  Cancel
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
