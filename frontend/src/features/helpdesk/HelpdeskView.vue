<script setup lang="ts">
import { ref, onMounted, nextTick, watch } from 'vue'
import { chatService, type Chat, type ChatMessage } from '@/services/chatService'
import AppLayout from '@/components/layout/AppLayout.vue'

const chats = ref<Chat[]>([])
const selectedChat = ref<Chat | null>(null)
const newMessage = ref('')
const loading = ref(false)
const chatContainer = ref<HTMLElement | null>(null)

// Voice Support State
const isVoiceMode = ref(false)
const isListening = ref(false)
const recognition = ref<any>(null)

onMounted(async () => {
  await loadChats()
  setupSpeechRecognition()
})

// Setup Web Speech API for Recognition
function setupSpeechRecognition() {
  const SpeechRecognition = (window as any).SpeechRecognition || (window as any).webkitSpeechRecognition
  if (SpeechRecognition) {
    recognition.value = new SpeechRecognition()
    recognition.value.continuous = false
    recognition.value.interimResults = false
    recognition.value.lang = 'hu-HU' // Beállítva magyar nyelvre

    recognition.value.onresult = (event: any) => {
      const transcript = event.results[0][0].transcript
      newMessage.value = transcript
      isListening.value = false
      // Automatically send if in voice mode
      if (isVoiceMode.value) {
        sendMessage()
      }
    }

    recognition.value.onerror = (event: any) => {
      console.error('Speech recognition error', event.error)
      isListening.value = false
      
      if (event.error === 'not-allowed') {
        error.value = 'Mikrofon hozzáférés megtagadva. Kérlek, engedélyezd a böngészőben!'
      } else if (event.error === 'no-speech') {
        // Csend volt, nem csinálunk semmit, csak leállítjuk az animációt
      } else {
        error.value = 'Hiba történt a hangfelismerés során: ' + event.error
      }
    }

    recognition.value.onend = () => {
      isListening.value = false
    }
  } else {
    console.warn('Speech Recognition API not supported in this browser.')
  }
}

function toggleListening() {
  if (!recognition.value) {
    alert('A böngésződ nem támogatja a hangfelismerést. Kérlek, használj Chrome-ot vagy Edge-et!')
    return
  }
  
  error.value = '' // Töröljük az előző hibákat
  
  if (isListening.value) {
    recognition.value?.stop()
    isListening.value = false
  } else {
    try {
      recognition.value?.start()
      isListening.value = true
    } catch (e) {
      console.error('Failed to start recognition', e)
      isListening.value = false
    }
  }
}

// Text to Speech
function speak(text: string) {
  if (!isVoiceMode.value) return
  
  // Cancel any ongoing speech
  window.speechSynthesis.cancel()
  
  const utterance = new SpeechSynthesisUtterance(text)
  utterance.lang = 'hu-HU' // Felolvasás is magyarul
  utterance.rate = 1.0
  window.speechSynthesis.speak(utterance)
}

watch(() => selectedChat.value?.messages, (newMessages, oldMessages) => {
  scrollToBottom()
  
  // Speak the last message if it's from AI or Agent and voice mode is on
  if (isVoiceMode.value && newMessages && newMessages.length > (oldMessages?.length || 0)) {
    const lastMessage = newMessages[newMessages.length - 1]
    if (lastMessage.sender_type !== 'user') {
      speak(lastMessage.message)
    }
  }
}, { deep: true })

async function loadChats() {
  loading.value = true
  try {
    chats.value = await chatService.getAll()
  } catch (error) {
    console.error('Failed to load chats:', error)
  } finally {
    loading.value = false
  }
}

async function createNewChat() {
  try {
    const chat = await chatService.create()
    await loadChats()
    await selectChat(chat)
  } catch (error) {
    console.error('Failed to create chat:', error)
  }
}

async function selectChat(chat: Chat) {
  try {
    selectedChat.value = await chatService.get(chat.id)
    await nextTick()
    scrollToBottom()
  } catch (error) {
    console.error('Failed to load chat:', error)
  }
}

async function sendMessage() {
  if (!selectedChat.value || !newMessage.value.trim()) return

  const messageText = newMessage.value
  newMessage.value = ''

  try {
    const response = await chatService.sendMessage(selectedChat.value.id, messageText)
    
    if (response.ai_message) {
      if (!selectedChat.value.messages) selectedChat.value.messages = []
      selectedChat.value.messages.push(response.user_message)
      selectedChat.value.messages.push(response.ai_message)
    } else {
      if (!selectedChat.value.messages) selectedChat.value.messages = []
      selectedChat.value.messages.push(response.user_message)
    }

    await loadChats()
    scrollToBottom()
  } catch (error) {
    console.error('Failed to send message:', error)
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
    <div class="mb-8">
      <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Helpdesk</h1>
      <p class="mt-2 text-slate-600">Get instant answers from our AI assistant or talk to a human agent.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 h-[700px]">
      <!-- Sidebar: Chat List -->
      <div class="lg:col-span-4 flex flex-col h-full overflow-hidden card bg-white">
        <div class="p-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
          <h3 class="font-bold text-slate-900 flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
            </svg>
            Recent Conversations
          </h3>
          <button
            @click="createNewChat"
            class="p-1.5 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition shadow-sm"
            title="Start New Chat"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
          </button>
        </div>

        <div class="flex-1 overflow-y-auto p-2 space-y-1">
          <div v-if="loading && chats.length === 0" class="flex flex-col items-center justify-center py-12">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
          </div>
          
          <div v-else-if="chats.length === 0" class="text-center py-12">
            <p class="text-slate-400 text-sm">No conversations yet.</p>
            <button @click="createNewChat" class="mt-4 text-indigo-600 text-sm font-bold uppercase tracking-wider">Start Chat</button>
          </div>

          <div
            v-for="chat in chats"
            :key="chat.id"
            @click="selectChat(chat)"
            :class="[
              'p-4 rounded-xl cursor-pointer transition border-2 flex flex-col group',
              selectedChat?.id === chat.id 
                ? 'bg-indigo-50 border-indigo-200 ring-2 ring-indigo-100 ring-offset-0' 
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
              <span class="text-[10px] font-medium text-slate-400 group-hover:text-slate-500">{{ formatDate(chat.created_at) }}</span>
            </div>
            
            <div class="flex items-center justify-between">
              <p class="text-sm font-semibold text-slate-900 truncate">Support Ticket #{{ chat.id }}</p>
              <div v-if="chat.needs_human" class="flex items-center">
                <span class="flex h-2 w-2 rounded-full bg-rose-500 mr-1 animate-pulse"></span>
                <span class="text-[10px] font-bold text-rose-600 uppercase">Agent</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Main: Chat Interface -->
      <div class="lg:col-span-8 flex flex-col h-full card bg-white">
        <template v-if="selectedChat">
          <!-- Chat Header -->
          <div class="px-6 py-4 border-b border-slate-100 bg-white flex justify-between items-center">
            <div class="flex items-center">
              <div class="h-10 w-10 bg-indigo-100 text-indigo-600 rounded-full flex items-center justify-center font-bold text-lg">
                #{{ selectedChat.id }}
              </div>
              <div class="ml-3">
                <h3 class="font-bold text-slate-900 leading-tight">Conversation with Support</h3>
                <p class="text-xs text-slate-500 flex items-center">
                  <span :class="['inline-block w-2 h-2 rounded-full mr-1.5', selectedChat.status === 'closed' ? 'bg-slate-400' : 'bg-emerald-500 animate-pulse']"></span>
                  {{ selectedChat.status === 'closed' ? 'Closed' : 'Active' }}
                </p>
              </div>
            </div>
            
            <div class="flex items-center space-x-4">
              <!-- Voice Mode Toggle -->
              <button 
                @click="isVoiceMode = !isVoiceMode"
                :class="[
                  'flex items-center space-x-2 px-3 py-1.5 rounded-full text-xs font-bold transition-all border',
                  isVoiceMode ? 'bg-indigo-600 text-white border-indigo-600 shadow-md shadow-indigo-100' : 'bg-white text-slate-500 border-slate-200 hover:border-indigo-300'
                ]"
              >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z" />
                </svg>
                <span>{{ isVoiceMode ? 'Voice Mode ON' : 'Voice Mode OFF' }}</span>
              </button>

              <div v-if="selectedChat.needs_human" class="bg-rose-50 text-rose-700 px-3 py-1 rounded-full text-xs font-bold border border-rose-100">
                Transferring to Agent...
              </div>
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
                message.sender_type === 'user' ? 'ml-auto items-end' : 'items-start'
              ]"
            >
              <div class="flex items-center mb-1 px-1">
                <span v-if="message.sender_type !== 'user'" class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                  {{ message.sender_type === 'agent' ? 'Human Agent' : 'AI Assistant' }}
                </span>
                <span class="text-[10px] font-medium text-slate-400 ml-2">{{ formatTime(message.created_at) }}</span>
              </div>
              
              <div
                :class="[
                  'px-4 py-3 rounded-2xl shadow-sm text-sm leading-relaxed',
                  message.sender_type === 'user' 
                    ? 'bg-indigo-600 text-white rounded-tr-none' 
                    : message.sender_type === 'agent'
                      ? 'bg-emerald-500 text-white rounded-tl-none'
                      : 'bg-white border border-slate-100 text-slate-700 rounded-tl-none'
                ]"
              >
                {{ message.message }}
              </div>
            </div>
            
            <div v-if="selectedChat.status === 'closed'" class="py-12 text-center">
              <div class="inline-block px-4 py-2 bg-slate-100 text-slate-500 text-xs font-bold rounded-full uppercase tracking-widest border border-slate-200">
                This conversation has ended
              </div>
            </div>
          </div>

          <!-- Input Area -->
          <div class="p-4 bg-white border-t border-slate-100">
            <form @submit.prevent="sendMessage" class="flex items-end gap-3 max-w-4xl mx-auto">
              <!-- Microphone Button -->
              <button
                type="button"
                @click="toggleListening"
                :disabled="selectedChat.status === 'closed'"
                :class="[
                  'mb-0.5 p-3 rounded-2xl transition-all duration-300 relative',
                  isListening ? 'bg-rose-500 text-white animate-pulse' : 'bg-slate-100 text-slate-500 hover:bg-slate-200'
                ]"
                title="Speak to support"
              >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
                </svg>
                <span v-if="isListening" class="absolute -top-1 -right-1 flex h-3 w-3">
                  <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                  <span class="relative inline-flex rounded-full h-3 w-3 bg-rose-500"></span>
                </span>
              </button>

              <div class="flex-1 relative">
                <textarea
                  v-model="newMessage"
                  placeholder="Type or speak your message..."
                  rows="1"
                  :disabled="selectedChat.status === 'closed'"
                  @keydown.enter.prevent="sendMessage"
                  class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl shadow-inner focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white text-slate-900 transition resize-none disabled:bg-slate-100 disabled:cursor-not-allowed"
                ></textarea>
              </div>
              <button
                type="submit"
                :disabled="!newMessage.trim() || selectedChat.status === 'closed'"
                class="mb-0.5 p-3 bg-indigo-600 text-white rounded-2xl hover:bg-indigo-700 transition shadow-lg shadow-indigo-100 disabled:bg-slate-300 disabled:shadow-none disabled:cursor-not-allowed group"
              >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 transform group-hover:translate-x-0.5 group-hover:-translate-y-0.5 transition" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                </svg>
              </button>
            </form>
            <p class="text-center text-[10px] text-slate-400 mt-3 font-medium uppercase tracking-widest italic">
              {{ isListening ? 'Listening... speak clearly into your microphone' : (isVoiceMode ? 'Voice Mode is ON: Messages will be read aloud' : 'AI model can make mistakes. Verify important information.') }}
            </p>
          </div>
        </template>

        <div v-else class="flex-1 flex flex-col items-center justify-center bg-slate-50/20 p-8 text-center">
          <div class="h-24 w-24 bg-indigo-50 rounded-full flex items-center justify-center mb-6">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-indigo-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
            </svg>
          </div>
          <h3 class="text-xl font-bold text-slate-900">Select a Conversation</h3>
          <p class="text-slate-500 max-w-xs mt-2">Pick a previous chat from the list or start a new one to get help with your account or events.</p>
          <button @click="createNewChat" class="mt-8 btn btn-primary px-8">Start New Chat</button>
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
