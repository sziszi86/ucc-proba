import api from './api'

export interface LoginCredentials {
  email: string
  password: string
}

export interface MFAVerification {
  user_id: number
  email: string
  password: string
  code: string
}

export interface ForgotPasswordData {
  email: string
}

export interface ResetPasswordData {
  token: string
  email: string
  password: string
  password_confirmation: string
}

export const authService = {
  async login(credentials: LoginCredentials) {
    const response = await api.post('/auth/login', credentials)
    if (response.data.access_token) {
      localStorage.setItem('token', response.data.access_token)
      localStorage.setItem('user', JSON.stringify(response.data.user))
    }
    return response.data
  },

  async verifyMFA(data: MFAVerification) {
    const response = await api.post('/auth/verify-mfa', data)
    if (response.data.access_token) {
      localStorage.setItem('token', response.data.access_token)
      localStorage.setItem('user', JSON.stringify(response.data.user))
    }
    return response.data
  },

  async logout() {
    await api.post('/auth/logout')
    localStorage.removeItem('token')
    localStorage.removeItem('user')
  },

  async getMe() {
    const response = await api.get('/auth/me')
    return response.data
  },

  async forgotPassword(data: ForgotPasswordData) {
    const response = await api.post('/auth/forgot-password', data)
    return response.data
  },

  async resetPassword(data: ResetPasswordData) {
    const response = await api.post('/auth/reset-password', data)
    return response.data
  },

  async enableMFA() {
    const response = await api.post('/auth/enable-mfa')
    return response.data
  },

  async confirmMFA(code: string) {
    const response = await api.post('/auth/confirm-mfa', { code })
    return response.data
  },

  async disableMFA(code: string) {
    const response = await api.post('/auth/disable-mfa', { code })
    return response.data
  }
}
