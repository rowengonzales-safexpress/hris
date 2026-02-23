<script setup>
import { Head } from '@inertiajs/vue3'
import FRMSLayout from '@/Layouts/FRMSLayout.vue'
import { ref, computed, onMounted } from 'vue'

// Props from controller
const props = defineProps({
  summary: {
    type: Object,
    default: () => ({})
  },
  recentRequests: {
    type: Array,
    default: () => []
  }
})

const currentTime = ref(new Date())

// Format date and time
const formatDate = computed(() => {
  return currentTime.value.toLocaleDateString('en-US', {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
})

const formatTime = computed(() => {
  return currentTime.value.toLocaleTimeString('en-US', {
    hour: '2-digit',
    minute: '2-digit',
    second: '2-digit'
  })
})

// Quick actions with proper routing
const quickActions = [
  {
    name: 'New Request',
    description: 'Create fund request',
    icon: 'M12 4v16m8-8H4',
    color: 'from-blue-500 to-blue-600',
    route: 'frms.form.index'
  },
  {
    name: 'Approval',
    description: 'Review approvals',
    icon: 'M5 13l4 4L19 7',
    color: 'from-yellow-500 to-yellow-600',
    route: 'frms.approval.index'
  },
  {
    name: 'Disbursement',
    description: 'Process payments',
    icon: 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v2a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z',
    color: 'from-green-500 to-green-600',
    route: 'frms.finance-disbursement.index'
  },
  {
    name: 'Add Expense',
    description: 'Record expenses',
    icon: 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v2a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z',
    color: 'from-orange-500 to-orange-600',
    route: 'frms.liquidation-expenses.index'
  },
  {
    name: 'Review',
    description: 'View/liquidation review',
    icon: 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
    color: 'from-purple-500 to-purple-600',
    route: 'frms.review.index'
  },
  {
    name: 'Reports',
    description: 'Generate reports',
    icon: 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
    color: 'from-purple-500 to-purple-600',
    route: 'frms.dashboard'
  }
]

// Navigation function
const navigateTo = (routeName) => {
  window.location.href = route(routeName)
}

// Status color mapping
const getStatusColor = (status) => {
  const key = (status || '').toLowerCase()
  const colors = {
    'for approved': 'bg-yellow-100 text-yellow-800 border-yellow-200 dark:bg-yellow-900 dark:text-yellow-100 dark:border-yellow-800',
    'approved': 'bg-green-100 text-green-800 border-green-200 dark:bg-green-900 dark:text-green-100 dark:border-green-800',
    'for disbursement': 'bg-purple-100 text-purple-800 border-purple-200 dark:bg-purple-900 dark:text-purple-100 dark:border-purple-800',
    'for liquidation': 'bg-blue-100 text-blue-800 border-blue-200 dark:bg-blue-900 dark:text-blue-100 dark:border-blue-800',
    'closed': 'bg-gray-100 text-gray-800 border-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600',
    'canceled': 'bg-red-100 text-red-800 border-red-200 dark:bg-red-900 dark:text-red-100 dark:border-red-800'
  }
  return colors[key] || 'bg-gray-100 text-gray-800 border-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600'
}

// Update time every second
onMounted(() => {
  setInterval(() => {
    currentTime.value = new Date()
  }, 1000)
})
</script>

<template>
  <Head title="FRMS Dashboard" />
  <FRMSLayout>
    <div class="space-y-6 frms-dashboard">
  

      <!-- Stats Grid -->
      <div class="grid grid-cols-1 md:grid-cols-5 gap-6">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 border-l-4 border-blue-500 hover:shadow-xl transition-all duration-300 hover:scale-105">
          <div class="flex items-center">
            <div class="flex-1">
              <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Request</p>
              <p class="text-3xl font-bold text-blue-600 dark:text-blue-400">{{ props.summary.requests ?? 0 }}</p>
              <p class="text-sm text-blue-600 dark:text-blue-400 mt-2 flex items-center">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                </svg>
                All fund requests
              </p>
            </div>
            <div class="p-3 bg-blue-100 dark:bg-blue-900 rounded-full">
              <svg class="w-6 h-6 text-blue-600 dark:text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
              </svg>
            </div>
          </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 border-l-4 border-yellow-500 hover:shadow-xl transition-all duration-300 hover:scale-105">
          <div class="flex items-center">
            <div class="flex-1">
              <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Approval</p>
              <p class="text-3xl font-bold text-yellow-600 dark:text-yellow-400">{{ props.summary.approval ?? 0 }}</p>
              <p class="text-sm text-yellow-600 dark:text-yellow-400 mt-2 flex items-center">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                For Approved (FA)
              </p>
            </div>
            <div class="p-3 bg-yellow-100 dark:bg-yellow-900 rounded-full">
              <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
            </div>
          </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 border-l-4 border-green-500 hover:shadow-xl transition-all duration-300 hover:scale-105">
          <div class="flex items-center">
            <div class="flex-1">
              <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Disbursement</p>
              <p class="text-3xl font-bold text-green-600 dark:text-green-400">{{ props.summary.disbursement ?? 0 }}</p>
              <p class="text-sm text-green-600 dark:text-green-400 mt-2 flex items-center">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v2a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
                For Disbursement (FD)
              </p>
            </div>
            <div class="p-3 bg-green-100 dark:bg-green-900 rounded-full">
              <svg class="w-6 h-6 text-green-600 dark:text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v2a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
              </svg>
            </div>
          </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 border-l-4 border-purple-500 hover:shadow-xl transition-all duration-300 hover:scale-105">
          <div class="flex items-center">
            <div class="flex-1">
              <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Liquidation</p>
              <p class="text-3xl font-bold text-purple-600 dark:text-purple-400">{{ props.summary.liquidation ?? 0 }}</p>
              <p class="text-sm text-purple-600 dark:text-purple-400 mt-2 flex items-center">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                For Liquidation (FL)
              </p>
            </div>
            <div class="p-3 bg-purple-100 dark:bg-purple-900 rounded-full">
              <svg class="w-6 h-6 text-purple-600 dark:text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
              </svg>
            </div>
          </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 border-l-4 border-indigo-500 hover:shadow-xl transition-all duration-300 hover:scale-105">
          <div class="flex items-center">
            <div class="flex-1">
              <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Review</p>
              <p class="text-3xl font-bold text-indigo-600 dark:text-indigo-400">{{ props.summary.review ?? 0 }}</p>
              <p class="text-sm text-indigo-600 dark:text-indigo-400 mt-2 flex items-center">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Liquidation review
              </p>
            </div>
            <div class="p-3 bg-indigo-100 dark:bg-indigo-900 rounded-full">
              <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
              </svg>
            </div>
          </div>
        </div>
      </div>

      <!-- Main Content Grid -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Recent Fund Requests (Top 5) -->
        <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700">
          <div class="p-6 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
            <h3 class="text-xl font-bold text-gray-900 dark:text-white">Recent Fund Requests</h3>
            <button
              @click="navigateTo('frms.form.index')"
              class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 font-medium text-sm transition-colors duration-200"
            >
              View All →
            </button>
          </div>
          <div class="p-6">
            <div class="space-y-4">
              <div
                v-for="(request, index) in props.recentRequests.slice(0, 5)"
                :key="request.id"
                class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors duration-200"
                :style="{ animationDelay: `${index * 100}ms` }"
              >
                <div class="flex items-center space-x-4">
                  <div class="w-12 h-12 bg-gradient-to-r from-green-500 to-emerald-500 rounded-xl flex items-center justify-center text-white font-bold">
                    {{ request.request_number?.slice(-2) }}
                  </div>
                  <div>
                    <p class="font-semibold text-gray-900 dark:text-white">{{ request.request_number }}</p>
                    <p class="text-sm text-gray-600 dark:text-gray-300">{{ request.requester_name }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ request.department }} • ₱{{ request.amount?.toLocaleString() }}</p>
                  </div>
                </div>
                <div class="text-right">
                  <span
                    :class="['inline-flex items-center px-3 py-1 rounded-full text-xs font-medium border', getStatusColor(request.status)]"
                  >
                    {{ request.status }}
                  </span>
                  <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ request.created_date }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Quick Actions -->
        <div class="space-y-6">
          <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700">
            <div class="p-6 border-b border-gray-100 dark:border-gray-700">
              <h3 class="text-xl font-bold text-gray-900 dark:text-white">Quick Actions</h3>
            </div>
            <div class="p-6">
              <div class="grid grid-cols-2 gap-4">
                <button
                  v-for="action in quickActions"
                  :key="action.name"
                  @click="navigateTo(action.route)"
                  class="group p-4 bg-gray-50 dark:bg-gray-700 rounded-xl hover:bg-gradient-to-r transition-all duration-300 hover:scale-105 hover:shadow-lg text-gray-700 dark:text-gray-200 hover:text-white dark:hover:text-white"
                  :class="`hover:${action.color}`"
                >
                  <div class="flex flex-col items-center text-center space-y-2">
                    <div class="w-10 h-10 bg-white dark:bg-gray-600 rounded-lg flex items-center justify-center group-hover:bg-white group-hover:bg-opacity-20 transition-all duration-300">
                      <svg class="w-5 h-5 text-gray-600 dark:text-gray-300 group-hover:text-white transition-colors duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="action.icon"></path>
                      </svg>
                    </div>
                    <div>
                      <p class="font-semibold text-sm group-hover:text-white transition-colors duration-300">{{ action.name }}</p>
                      <p class="text-xs opacity-75 group-hover:text-white group-hover:opacity-90 transition-all duration-300">{{ action.description }}</p>
                    </div>
                  </div>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </FRMSLayout>
</template>
