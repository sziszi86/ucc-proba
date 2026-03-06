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
  const isHelpdeskAgent = computed(() => user.value?.role === 'agent' || user.value?.is_helpdesk_agent || false)

  function loadFromStorage() {
    const savedToken = localStorage.getItem('token')
    const savedUser = localStorage.getItem('user')

    if (savedToken) {
      token.value = savedToken
    }
    if (savedUser) {
      try {
        user.value = JSON.parse(savedUser)
      } catch (e) {
        localStorage.removeItem('user')
      }
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
      localStorage.setItem('token', token.value!)
      localStorage.setItem('user', JSON.stringify(user.value))
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
      localStorage.setItem('token', token.value!)
      localStorage.setItem('user', JSON.stringify(user.value))
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
    } catch (e) {
      console.error('Logout failed:', e)
    } finally {
      token.value = null
      user.value = null
      localStorage.removeItem('token')
      localStorage.removeItem('user')
    }
  }

  async function fetchUser() {
    try {
      user.value = await authService.getMe()
      localStorage.setItem('user', JSON.stringify(user.value))
    } catch (err) {
      console.error('Failed to fetch user:', err)
    }
  }

  async function enableMFA() {
    return await authService.enableMFA()
  }

  async function confirmMFA(code: string) {
    const res = await authService.confirmMFA(code)
    await fetchUser()
    return res
  }

  async function disableMFA(code: string) {
    const res = await authService.disableMFA(code)
    await fetchUser()
    return res
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
    fetchUser,
    enableMFA,
    confirmMFA,
    disableMFA
  }
})
