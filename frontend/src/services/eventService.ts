import api from './api'

export interface Event {
  id?: number
  title: string
  occurrence: string
  description?: string
  created_at?: string
  updated_at?: string
}

export const eventService = {
  async getAll(): Promise<Event[]> {
    const response = await api.get('/events')
    return response.data
  },

  async get(id: number): Promise<Event> {
    const response = await api.get(`/events/${id}`)
    return response.data
  },

  async create(event: Event): Promise<Event> {
    const response = await api.post('/events', event)
    return response.data
  },

  async update(id: number, event: Partial<Event>): Promise<Event> {
    const response = await api.put(`/events/${id}`, event)
    return response.data
  },

  async delete(id: number): Promise<void> {
    await api.delete(`/events/${id}`)
  }
}
