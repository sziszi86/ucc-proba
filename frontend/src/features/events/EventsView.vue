<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { eventService, type Event } from '@/services/eventService'
import AppLayout from '@/components/layout/AppLayout.vue'

const events = ref<Event[]>([])
const loading = ref(false)
const showModal = ref(false)
const editingEvent = ref<Event | null>(null)
const error = ref('')

const formData = ref({
  title: '',
  occurrence: '',
  description: ''
})

onMounted(async () => {
  await loadEvents()
})

async function loadEvents() {
  loading.value = true
  try {
    events.value = await eventService.getAll()
  } catch (err) {
    console.error('Failed to load events:', err)
  } finally {
    loading.value = false
  }
}

function openCreateModal() {
  editingEvent.value = null
  formData.value = {
    title: '',
    occurrence: '',
    description: ''
  }
  showModal.value = true
}

function editEvent(event: Event) {
  editingEvent.value = event
  // Format occurrence for datetime-local input (YYYY-MM-DDTHH:mm)
  const date = new Date(event.occurrence)
  const tzOffset = date.getTimezoneOffset() * 60000
  const localISOTime = new Date(date.getTime() - tzOffset).toISOString().slice(0, 16)
  
  formData.value = {
    title: event.title,
    occurrence: localISOTime,
    description: event.description || ''
  }
  showModal.value = true
}

async function handleSubmit() {
  try {
    error.value = ''
    const data = {
      ...formData.value,
      // Ensure format is YYYY-MM-DD HH:mm:ss for backend
      occurrence: formData.value.occurrence.replace('T', ' ') + ':00'
    }

    if (editingEvent.value) {
      await eventService.update(editingEvent.value.id!, data)
    } else {
      await eventService.create(data as Event)
    }

    await loadEvents()
    closeModal()
  } catch (err: any) {
    error.value = err.response?.data?.message || 'Failed to save event'
  }
}

async function deleteEvent(id: number) {
  if (confirm('Are you sure you want to delete this event?')) {
    try {
      await eventService.delete(id)
      await loadEvents()
    } catch (err) {
      console.error('Failed to delete event:', err)
    }
  }
}

function closeModal() {
  showModal.value = false
  editingEvent.value = null
  error.value = ''
}

function formatDate(dateString: string) {
  return new Date(dateString).toLocaleString([], { 
    weekday: 'short', 
    year: 'numeric', 
    month: 'short', 
    day: 'numeric', 
    hour: '2-digit', 
    minute: '2-digit' 
  })
}
</script>

<template>
  <AppLayout>
    <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-8 gap-4">
      <div>
        <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Events</h1>
        <p class="mt-1 text-slate-600">Schedule and manage your personal or team events.</p>
      </div>
      <button
        @click="openCreateModal"
        class="btn btn-primary flex items-center justify-center shadow-lg shadow-indigo-200"
      >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
        </svg>
        New Event
      </button>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex flex-col items-center justify-center py-24">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"></div>
      <p class="mt-4 text-slate-500 font-medium">Fetching your events...</p>
    </div>

    <!-- Empty State -->
    <div v-else-if="events.length === 0" class="card py-16 px-4 text-center">
      <div class="inline-flex items-center justify-center p-4 bg-slate-50 rounded-full mb-4">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
        </svg>
      </div>
      <h3 class="text-lg font-bold text-slate-900">No events found</h3>
      <p class="text-slate-500 max-w-xs mx-auto mt-1">Get started by creating your first scheduled event.</p>
      <button @click="openCreateModal" class="mt-6 btn btn-secondary">Create Event</button>
    </div>

    <!-- Events Grid -->
    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div
        v-for="event in events"
        :key="event.id"
        class="card group flex flex-col hover:border-indigo-200 hover:shadow-md transition-all duration-300"
      >
        <div class="p-6 flex-1">
          <div class="flex justify-between items-start mb-4">
            <div class="p-2 bg-indigo-50 text-indigo-600 rounded-lg">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
            </div>
            <div class="flex space-x-1 opacity-0 group-hover:opacity-100 transition-opacity">
              <button
                @click="editEvent(event)"
                class="p-2 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition"
                title="Edit"
              >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
              </button>
              <button
                @click="deleteEvent(event.id!)"
                class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition"
                title="Delete"
              >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
              </button>
            </div>
          </div>
          <h3 class="text-xl font-bold text-slate-900 mb-2 leading-tight group-hover:text-indigo-600 transition">{{ event.title }}</h3>
          <div class="flex items-center text-sm text-slate-500 mb-4 font-medium">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            {{ formatDate(event.occurrence) }}
          </div>
          <p v-if="event.description" class="text-slate-600 text-sm line-clamp-3 leading-relaxed">
            {{ event.description }}
          </p>
        </div>
        <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex justify-end">
          <button @click="editEvent(event)" class="text-xs font-bold text-indigo-600 uppercase tracking-widest hover:text-indigo-800 transition">
            View Details
          </button>
        </div>
      </div>
    </div>

    <!-- Create/Edit Modal -->
    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6">
      <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="closeModal"></div>
      
      <div class="card w-full max-w-lg relative z-10 shadow-2xl animate-in fade-in zoom-in duration-200">
        <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-white sticky top-0">
          <h2 class="text-xl font-bold text-slate-900">
            {{ editingEvent ? 'Edit Event' : 'Create New Event' }}
          </h2>
          <button @click="closeModal" class="text-slate-400 hover:text-slate-600 p-1 rounded-full hover:bg-slate-100 transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <form @submit.prevent="handleSubmit" class="p-6 space-y-5 bg-white">
          <div v-if="error" class="p-3 bg-rose-50 text-rose-700 text-sm rounded-lg border border-rose-100">
            {{ error }}
          </div>

          <div>
            <label for="title" class="block text-sm font-semibold text-slate-700 mb-1">Event Title <span class="text-rose-500">*</span></label>
            <input
              type="text"
              id="title"
              v-model="formData.title"
              required
              placeholder="E.g. Team Sync"
              class="input-field"
            />
          </div>

          <div>
            <label for="occurrence" class="block text-sm font-semibold text-slate-700 mb-1">Date & Time <span class="text-rose-500">*</span></label>
            <input
              type="datetime-local"
              id="occurrence"
              v-model="formData.occurrence"
              required
              class="input-field"
            />
          </div>

          <div>
            <label for="description" class="block text-sm font-semibold text-slate-700 mb-1">Description</label>
            <textarea
              id="description"
              v-model="formData.description"
              rows="4"
              placeholder="What is this event about?"
              class="input-field resize-none"
            ></textarea>
          </div>

          <div class="flex flex-col sm:flex-row gap-3 pt-2">
            <button
              type="submit"
              class="btn btn-primary flex-1 py-2.5 shadow-md shadow-indigo-100"
            >
              {{ editingEvent ? 'Update Event' : 'Create Event' }}
            </button>
            <button
              type="button"
              @click="closeModal"
              class="btn btn-secondary flex-1 py-2.5"
            >
              Cancel
            </button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
.line-clamp-3 {
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
