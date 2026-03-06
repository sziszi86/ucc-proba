<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const authStore = useAuthStore()

const credentials = ref({
  email: '',
  password: ''
})

const mfaCode = ref('')
const showMfa = ref(false)
const mfaData = ref<any>(null)
const loading = ref(false)
const error = ref('')

async function handleLogin() {
  try {
    loading.value = true
    error.value = ''

    const response = await authStore.login(credentials.value)

    if (response.mfa_required) {
      showMfa.value = true
      mfaData.value = response
    } else {
      router.push('/')
    }
  } catch (err: any) {
    error.value = err.response?.data?.error || 'Login failed. Please check your credentials.'
  } finally {
    loading.value = false
  }
}

async function handleMfaVerify() {
  try {
    loading.value = true
    error.value = ''

    await authStore.verifyMFA({
      user_id: mfaData.value.user_id,
      email: credentials.value.email,
      password: credentials.value.password,
      code: mfaCode.value
    })

    router.push('/')
  } catch (err: any) {
    error.value = err.response?.data?.error || 'MFA verification failed'
  } finally {
    loading.value = false
  }
}

function cancelMfa() {
  showMfa.value = false
  mfaCode.value = ''
  mfaData.value = null
}
</script>

<template>
  <div class="min-h-screen flex flex-col justify-center py-12 px-4 sm:px-6 lg:px-8 bg-slate-50">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
      <div class="flex justify-center mb-6">
        <div class="p-3 bg-indigo-600 rounded-2xl shadow-lg shadow-indigo-200">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
          </svg>
        </div>
      </div>
      <h2 class="text-center text-3xl font-extrabold text-slate-900 tracking-tight">
        Welcome Back
      </h2>
      <p class="mt-2 text-center text-sm text-slate-600">
        Sign in to manage your events and support
      </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
      <div class="card p-8 bg-white border border-slate-200">
        <div v-if="error" class="mb-6 p-4 bg-rose-50 border-l-4 border-rose-500 rounded-md">
          <div class="flex">
            <div class="flex-shrink-0">
              <svg class="h-5 w-5 text-rose-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
              </svg>
            </div>
            <div class="ml-3">
              <p class="text-sm text-rose-700 font-medium">{{ error }}</p>
            </div>
          </div>
        </div>

        <form v-if="!showMfa" @submit.prevent="handleLogin" class="space-y-6">
          <div>
            <label for="email" class="block text-sm font-semibold text-slate-700">Email Address</label>
            <div class="mt-1 relative">
              <input
                id="email"
                v-model="credentials.email"
                type="email"
                required
                autocomplete="email"
                placeholder="name@example.com"
                class="input-field"
              />
            </div>
          </div>

          <div>
            <label for="password" class="block text-sm font-semibold text-slate-700">Password</label>
            <div class="mt-1">
              <input
                id="password"
                v-model="credentials.password"
                type="password"
                required
                autocomplete="current-password"
                placeholder="••••••••"
                class="input-field"
              />
            </div>
          </div>

          <div class="flex items-center justify-between">
            <div class="flex items-center">
              <input id="remember-me" name="remember-me" type="checkbox" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-slate-300 rounded" />
              <label for="remember-me" class="ml-2 block text-sm text-slate-600"> Remember me </label>
            </div>

            <div class="text-sm">
              <router-link to="/forgot-password" class="font-medium text-indigo-600 hover:text-indigo-500">
                Forgot password?
              </router-link>
            </div>
          </div>

          <div>
            <button
              type="submit"
              :disabled="loading"
              class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-lg shadow-sm text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed transition transform active:scale-95"
            >
              <svg v-if="loading" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              {{ loading ? 'Signing in...' : 'Sign in' }}
            </button>
          </div>
        </form>

        <form v-else @submit.prevent="handleMfaVerify" class="space-y-6 text-center">
          <div class="flex justify-center mb-4">
            <div class="p-3 bg-indigo-100 rounded-full">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
              </svg>
            </div>
          </div>
          <h3 class="text-xl font-bold text-slate-900">Two-Factor Authentication</h3>
          <p class="text-sm text-slate-600">Enter the 6-digit verification code generated by your authenticator app.</p>

          <div>
            <input
              id="mfa-code"
              v-model="mfaCode"
              type="text"
              required
              maxlength="6"
              placeholder="000 000"
              class="input-field text-center text-2xl tracking-[0.5em] font-mono"
            />
          </div>

          <div class="space-y-3">
            <button
              type="submit"
              :disabled="loading"
              class="w-full btn btn-primary py-2.5"
            >
              {{ loading ? 'Verifying...' : 'Verify' }}
            </button>
            <button
              type="button"
              @click="cancelMfa"
              class="w-full btn btn-secondary"
            >
              Cancel
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>
