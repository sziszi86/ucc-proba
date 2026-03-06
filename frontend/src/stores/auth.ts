import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { authService } from '@/services/authService'
import type { LoginCredentials, MFAVerification } from '@/services/authService'

export const useAuthStore = defineStore('auth', () => {
  const user = ref<any>(null)
  const token = ref<string | null>(null)
  const loading = ref(false)
  const error = ref<string | null>(null)

  const isAuthenticated = computed(() => !!token.value)
  const isHelpdeskAgent = computed(() => user.value?.is_helpdesk_agent || false)

  function loadFromStorage() {
    const savedToken = localStorage.getItem('token')
    const savedUser = localStorage.getItem('user')

    if (savedToken) {
      token.value = savedToken
    }
    if (savedUser) {
      user.value = JSON.parse(savedUser)
    }
  }

  async function login(credentials: LoginCredentials) {
    try {
      loading.value = true
      error.value = null
      const data = await authService.login(credentials)

      if (data.mfa_required) {
        return data
      }

      token.value = data.access_token
      user.value = data.user
      return data
    } catch (err: any) {
      error.value = err.response?.data?.error || 'Login failed'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function verifyMFA(data: MFAVerification) {
    try {
      loading.value = true
      error.value = null
      const response = await authService.verifyMFA(data)
      token.value = response.access_token
      user.value = response.user
      return response
    } catch (err: any) {
      error.value = err.response?.data?.error || 'MFA verification failed'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function logout() {
    try {
      await authService.logout()
    } finally {
      token.value = null
      user.value = null
    }
  }

  async function fetchUser() {
    try {
      user.value = await authService.getMe()
    } catch (err) {
      console.error('Failed to fetch user:', err)
    }
  }

  loadFromStorage()

  return {
    user,
    token,
    loading,
    error,
    isAuthenticated,
    isHelpdeskAgent,
    login,
    verifyMFA,
    logout,
    fetchUser
  }
})
