import api from './api'

export interface Chat {
  id: number
  user_id: number
  agent_id?: number
  status: 'open' | 'in_progress' | 'closed'
  needs_human: boolean
  created_at: string
  updated_at: string
  messages?: ChatMessage[]
}

export interface ChatMessage {
  id: number
  chat_id: number
  sender_id?: number
  message: string
  sender_type: 'user' | 'agent' | 'ai'
  created_at: string
  sender?: any
}

export const chatService = {
  async getAll(): Promise<Chat[]> {
    const response = await api.get('/chats')
    return response.data
  },

  async get(id: number): Promise<Chat> {
    const response = await api.get(`/chats/${id}`)
    return response.data
  },

  async create(): Promise<Chat> {
    const response = await api.post('/chats')
    return response.data
  },

  async sendMessage(chatId: number, message: string) {
    const response = await api.post(`/chats/${chatId}/messages`, { message })
    return response.data
  },

  async delete(id: number): Promise<void> {
    await api.delete(`/chats/${id}`)
  }
}

export const helpdeskService = {
  async getChats(): Promise<Chat[]> {
    const response = await api.get('/helpdesk/chats')
    return response.data
  },

  async getChat(id: number): Promise<Chat> {
    const response = await api.get(`/helpdesk/chats/${id}`)
    return response.data
  },

  async assignChat(id: number): Promise<Chat> {
    const response = await api.post(`/helpdesk/chats/${id}/assign`)
    return response.data
  },

  async sendMessage(chatId: number, message: string): Promise<ChatMessage> {
    const response = await api.post(`/helpdesk/chats/${chatId}/messages`, { message })
    return response.data
  },

  async closeChat(id: number): Promise<void> {
    await api.post(`/helpdesk/chats/${id}/close`)
  }
}
