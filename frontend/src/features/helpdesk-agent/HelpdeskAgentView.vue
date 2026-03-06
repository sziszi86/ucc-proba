<script setup lang="ts">
import { ref, onMounted, nextTick, watch } from 'vue'
import { helpdeskService, type Chat } from '@/services/chatService'
import AppLayout from '@/components/layout/AppLayout.vue'

const chats = ref<Chat[]>([])
const selectedChat = ref<Chat | null>(null)
const newMessage = ref('')
const loading = ref(false)
const chatContainer = ref<HTMLElement | null>(null)

onMounted(async () => {
  await loadChats()
})

watch(() => selectedChat.value?.messages, () => {
  scrollToBottom()
}, { deep: true })

async function loadChats() {
  loading.value = true
  try {
    chats.value = await helpdeskService.getChats()
  } catch (error) {
    console.error('Failed to load chats:', error)
  } finally {
    loading.value = false
  }
}

async function selectChat(chat: Chat) {
  try {
    selectedChat.value = await helpdeskService.getChat(chat.id)
    await nextTick()
    scrollToBottom()
  } catch (error) {
    console.error('Failed to load chat:', error)
  }
}

async function assignChat() {
  if (!selectedChat.value) return

  try {
    await helpdeskService.assignChat(selectedChat.value.id)
    await loadChats()
    if (selectedChat.value) {
      await selectChat(selectedChat.value)
    }
  } catch (error) {
    console.error('Failed to assign chat:', error)
  }
}

async function sendMessage() {
  if (!selectedChat.value || !newMessage.value.trim()) return

  try {
    const message = await helpdeskService.sendMessage(
      selectedChat.value.id,
      newMessage.value
    )
    newMessage.value = ''
    if (!selectedChat.value.messages) selectedChat.value.messages = []
    selectedChat.value.messages.push(message)
    scrollToBottom()
  } catch (error) {
    console.error('Failed to send message:', error)
  }
}

async function closeChat() {
  if (!selectedChat.value) return

  if (confirm('Are you sure you want to close this chat?')) {
    try {
      await helpdeskService.closeChat(selectedChat.value.id)
      await loadChats()
      selectedChat.value = null
    } catch (error) {
      console.error('Failed to close chat:', error)
    }
  }
}

function scrollToBottom() {
  nextTick(() => {
    if (chatContainer.value) {
      chatContainer.value.scrollTop = chatContainer.value.scrollHeight
    }
  })
}

function formatDate(dateString: string) {
  return new Date(dateString).toLocaleDateString([], { month: 'short', day: 'numeric' })
}

function formatTime(dateString: string) {
  return new Date(dateString).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
}

function getStatusClass(status: string) {
  switch (status) {
    case 'open': return 'bg-emerald-50 text-emerald-700 border-emerald-100'
    case 'in_progress': return 'bg-amber-50 text-amber-700 border-amber-100'
    case 'closed': return 'bg-slate-100 text-slate-600 border-slate-200'
    default: return 'bg-slate-50 text-slate-600 border-slate-100'
  }
}
</script>

<template>
  <AppLayout>
    <div class="mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Agent Dashboard</h1>
        <p class="mt-1 text-slate-600">Review and respond to customer support requests.</p>
      </div>
      <button
        @click="loadChats"
        class="btn btn-secondary flex items-center justify-center"
      >
        <svg xmlns="http://www.w3.org/2000/svg" :class="['h-5 w-5 mr-2', loading ? 'animate-spin' : '']" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
        </svg>
        Refresh List
      </button>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 h-[700px]">
      <!-- Sidebar: All Chats -->
      <div class="lg:col-span-4 flex flex-col h-full overflow-hidden card bg-white">
        <div class="p-4 border-b border-slate-100 bg-slate-50/50">
          <h3 class="font-bold text-slate-900 flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
            Support Queue
          </h3>
        </div>

        <div class="flex-1 overflow-y-auto p-2 space-y-1">
          <div v-if="loading && chats.length === 0" class="flex flex-col items-center justify-center py-12">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
          </div>
          
          <div v-else-if="chats.length === 0" class="text-center py-12">
            <p class="text-slate-400 text-sm italic">No tickets in the queue.</p>
          </div>

          <div
            v-for="chat in chats"
            :key="chat.id"
            @click="selectChat(chat)"
            :class="[
              'p-4 rounded-xl cursor-pointer transition border-2 group',
              selectedChat?.id === chat.id 
                ? 'bg-indigo-50 border-indigo-200 ring-2 ring-indigo-100' 
                : 'bg-white border-transparent hover:bg-slate-50 hover:border-slate-100'
            ]"
          >
            <div class="flex justify-between items-start mb-2">
              <span
                :class="[
                  'px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-widest border',
                  getStatusClass(chat.status)
                ]"
              >
                {{ chat.status.replace('_', ' ') }}
              </span>
              <span class="text-[10px] font-medium text-slate-400">{{ formatDate(chat.created_at) }}</span>
            </div>
            
            <div class="mb-2">
              <p class="text-sm font-bold text-slate-900 group-hover:text-indigo-600 transition">{{ chat.user?.name }}</p>
              <p class="text-[10px] text-slate-500 font-medium truncate">{{ chat.user?.email }}</p>
            </div>

            <div class="flex gap-2 flex-wrap">
              <span
                v-if="chat.needs_human"
                class="bg-rose-100 text-rose-700 px-2 py-0.5 rounded text-[10px] font-bold border border-rose-200 uppercase tracking-tight flex items-center"
              >
                <span class="h-1.5 w-1.5 rounded-full bg-rose-500 mr-1 animate-pulse"></span>
                Needs Human
              </span>
              <span
                v-if="chat.agent_id"
                class="bg-blue-100 text-blue-700 px-2 py-0.5 rounded text-[10px] font-bold border border-blue-200 uppercase tracking-tight"
              >
                Assigned
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- Main: Chat Management -->
      <div class="lg:col-span-8 flex flex-col h-full card bg-white">
        <template v-if="selectedChat">
          <!-- Chat Header -->
          <div class="px-6 py-4 border-b border-slate-100 bg-white flex justify-between items-center">
            <div class="flex items-center">
              <div class="h-10 w-10 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center font-bold text-lg">
                {{ selectedChat.user?.name.charAt(0).toUpperCase() }}
              </div>
              <div class="ml-3">
                <h3 class="font-bold text-slate-900 leading-tight">{{ selectedChat.user?.name }}</h3>
                <p class="text-xs text-slate-500">Support Ticket #{{ selectedChat.id }}</p>
              </div>
            </div>
            
            <div class="flex items-center space-x-2">
              <button
                v-if="!selectedChat.agent_id"
                @click="assignChat"
                class="btn btn-primary text-xs py-1.5 px-4 shadow-md shadow-indigo-100"
              >
                Claim Ticket
              </button>
              <button
                v-if="selectedChat.status !== 'closed'"
                @click="closeChat"
                class="btn btn-secondary border-rose-200 text-rose-600 hover:bg-rose-50 text-xs py-1.5 px-4"
              >
                Close Ticket
              </button>
            </div>
          </div>

          <!-- Messages Area -->
          <div 
            ref="chatContainer"
            class="flex-1 overflow-y-auto p-6 space-y-6 bg-slate-50/30"
          >
            <div
              v-for="message in selectedChat.messages"
              :key="message.id"
              :class="[
                'flex flex-col max-w-[85%] sm:max-w-[70%]',
                message.sender_type === 'agent' ? 'ml-auto items-end' : 'items-start'
              ]"
            >
              <div class="flex items-center mb-1 px-1">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                  <template v-if="message.sender_type === 'user'">Customer</template>
                  <template v-else-if="message.sender_type === 'agent'">You</template>
                  <template v-else>AI Assistant</template>
                </span>
                <span class="text-[10px] font-medium text-slate-400 ml-2">{{ formatTime(message.created_at) }}</span>
              </div>
              
              <div
                :class="[
                  'px-4 py-3 rounded-2xl shadow-sm text-sm leading-relaxed',
                  message.sender_type === 'agent' 
                    ? 'bg-emerald-500 text-white rounded-tr-none' 
                    : message.sender_type === 'user'
                      ? 'bg-white border border-slate-100 text-slate-700 rounded-tl-none'
                      : 'bg-indigo-50 border border-indigo-100 text-indigo-700 rounded-tl-none italic'
                ]"
              >
                {{ message.message }}
              </div>
            </div>
          </div>

          <!-- Input Area -->
          <div v-if="selectedChat.status !== 'closed'" class="p-4 bg-white border-t border-slate-100">
            <form @submit.prevent="sendMessage" class="flex items-end gap-3 max-w-4xl mx-auto">
              <div class="flex-1 relative">
                <textarea
                  v-model="newMessage"
                  placeholder="Type your official response..."
                  rows="1"
                  @keydown.enter.prevent="sendMessage"
                  class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl shadow-inner focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white text-slate-900 transition resize-none"
                ></textarea>
              </div>
              <button
                type="submit"
                :disabled="!newMessage.trim()"
                class="mb-0.5 p-3 bg-emerald-600 text-white rounded-2xl hover:bg-emerald-700 transition shadow-lg shadow-emerald-100 disabled:bg-slate-300 disabled:shadow-none disabled:cursor-not-allowed group"
              >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 transform group-hover:translate-x-0.5 group-hover:-translate-y-0.5 transition" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                </svg>
              </button>
            </form>
          </div>
          <div v-else class="p-8 bg-slate-50 text-center border-t border-slate-100">
            <span class="px-4 py-2 bg-slate-200 text-slate-500 text-xs font-bold rounded-full uppercase tracking-widest">This ticket is closed</span>
          </div>
        </template>

        <div v-else class="flex-1 flex flex-col items-center justify-center bg-slate-50/20 p-8 text-center">
          <div class="h-24 w-24 bg-indigo-50 rounded-full flex items-center justify-center mb-6">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-indigo-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z" />
            </svg>
          </div>
          <h3 class="text-xl font-bold text-slate-900">Agent Command Center</h3>
          <p class="text-slate-500 max-w-xs mt-2">Select a support ticket from the queue to start assisting a customer.</p>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
/* Custom scrollbar for webkit */
::-webkit-scrollbar {
  width: 6px;
}
::-webkit-scrollbar-track {
  background: transparent;
}
::-webkit-scrollbar-thumb {
  background: #e2e8f0;
  border-radius: 10px;
}
::-webkit-scrollbar-thumb:hover {
  background: #cbd5e1;
}
</style>
