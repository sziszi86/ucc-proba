<script setup lang="ts">
import { ref } from 'vue'
import { useAuthStore } from '@/stores/auth'

const authStore = useAuthStore()
const email = ref('')
const loading = ref(false)
const error = ref('')
const success = ref(false)

async function handleSubmit() {
  try {
    loading.value = true
    error.value = ''
    await authStore.forgotPassword(email.value)
    success.value = true
  } catch (err: any) {
    error.value = err.response?.data?.error || 'Failed to send reset link'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="min-h-screen flex flex-col justify-center py-12 px-4 sm:px-6 lg:px-8 bg-slate-50">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
      <div class="flex justify-center mb-6">
        <div class="p-3 bg-indigo-600 rounded-2xl shadow-lg shadow-indigo-200">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
          </svg>
        </div>
      </div>
      <h2 class="text-center text-3xl font-extrabold text-slate-900 tracking-tight">
        Reset Password
      </h2>
      <p class="mt-2 text-center text-sm text-slate-600">
        Enter your email to receive a password reset link
      </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
      <div class="card p-8 bg-white border border-slate-200">
        <div v-if="success" class="text-center py-4">
          <div class="mb-4 inline-flex items-center justify-center p-3 bg-emerald-100 text-emerald-600 rounded-full">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
          </div>
          <h3 class="text-xl font-bold text-slate-900">Check your email</h3>
          <p class="mt-2 text-slate-600">We've sent a password reset link to <span class="font-semibold text-slate-900">{{ email }}</span>.</p>
          <div class="mt-8">
            <router-link to="/login" class="btn btn-primary w-full inline-block text-center">
              Return to login
            </router-link>
          </div>
        </div>

        <form v-else @submit.prevent="handleSubmit" class="space-y-6">
          <div v-if="error" class="p-3 bg-rose-50 text-rose-700 text-sm rounded-lg border border-rose-100">
            {{ error }}
          </div>

          <div>
            <label for="email" class="block text-sm font-semibold text-slate-700">Email Address</label>
            <div class="mt-1">
              <input
                id="email"
                v-model="email"
                type="email"
                required
                placeholder="name@example.com"
                class="input-field"
              />
            </div>
          </div>

          <div>
            <button
              type="submit"
              :disabled="loading"
              class="w-full btn btn-primary py-2.5 shadow-md shadow-indigo-100 flex justify-center items-center"
            >
              <svg v-if="loading" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              {{ loading ? 'Sending link...' : 'Send reset link' }}
            </button>
          </div>

          <div class="text-center">
            <router-link to="/login" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">
              Back to sign in
            </router-link>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>
