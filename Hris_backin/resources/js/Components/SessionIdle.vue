<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps<{ lifetime?: number; warningSeconds?: number }>()

const showWarning = ref(false)
const countdown = ref(0)
let warningTimer: any = null
let logoutTimer: any = null
let countdownInterval: any = null

const getLifetimeMs = () => Math.max(1, (props.lifetime ?? 15)) * 60 * 1000
const getWarningMs = () => Math.min(Math.max(5, props.warningSeconds ?? 30), Math.floor(getLifetimeMs() / 1000)) * 1000

const startCountdown = (seconds: number) => {
  countdown.value = seconds
  if (countdownInterval) clearInterval(countdownInterval)
  countdownInterval = setInterval(() => {
    countdown.value = Math.max(0, countdown.value - 1)
    if (countdown.value === 0) {
      clearInterval(countdownInterval)
    }
  }, 1000)
}

const showWarningModal = () => {
  showWarning.value = true
  startCountdown(Math.floor(getWarningMs() / 1000))
}

const logoutNow = () => {
  clearAllTimers()
  router.post(route('logout'), {}, {
    onFinish: () => router.visit(route('login', { expired: 1 }))
  })
}

const clearAllTimers = () => {
  if (warningTimer) clearTimeout(warningTimer)
  if (logoutTimer) clearTimeout(logoutTimer)
  if (countdownInterval) clearInterval(countdownInterval)
}

const resetIdle = () => {
  showWarning.value = false
  clearAllTimers()
  const lifetimeMs = getLifetimeMs()
  const warningMs = getWarningMs()
  warningTimer = setTimeout(showWarningModal, Math.max(0, lifetimeMs - warningMs))
  logoutTimer = setTimeout(logoutNow, lifetimeMs)
}

const events = ['mousemove','keydown','click','scroll','touchstart']

onMounted(() => {
  events.forEach(e => window.addEventListener(e, resetIdle))
  resetIdle()
})

onUnmounted(() => {
  events.forEach(e => window.removeEventListener(e, resetIdle))
  clearAllTimers()
})

const staySignedIn = () => {
  resetIdle()
}
</script>

<template>
  <div v-if="showWarning" class="fixed inset-0 z-[9999] flex items-center justify-center bg-black bg-opacity-40">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md p-6">
      <div class="text-lg font-semibold text-gray-800 mb-2">Session Expiring Soon</div>
      <div class="text-sm text-gray-600 mb-4">Your session will expire in <span class="font-bold">{{ countdown }}</span> seconds due to inactivity.</div>
      <div class="flex justify-end gap-3">
        <button @click="logoutNow" class="px-4 py-2 rounded-md bg-red-600 text-white hover:bg-red-700">Log Out Now</button>
        <button @click="staySignedIn" class="px-4 py-2 rounded-md bg-blue-600 text-white hover:bg-blue-700">Stay Signed In</button>
      </div>
    </div>
  </div>

</template>

