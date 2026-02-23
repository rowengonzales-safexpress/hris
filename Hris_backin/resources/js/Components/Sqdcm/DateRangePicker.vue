<script setup>
import { ref, watch } from 'vue'

const props = defineProps({
  startDate: {
    type: String,
    default: ''
  },
  endDate: {
    type: String,
    default: ''
  }
})

const emit = defineEmits(['change'])

const localStartDate = ref(props.startDate)
const localEndDate = ref(props.endDate)

watch(() => props.startDate, (newValue) => {
  localStartDate.value = newValue
})

watch(() => props.endDate, (newValue) => {
  localEndDate.value = newValue
})

const handleStartDateChange = (event) => {
  localStartDate.value = event.target.value
  emitChange()
}

const handleEndDateChange = (event) => {
  localEndDate.value = event.target.value
  emitChange()
}

const emitChange = () => {
  emit('change', {
    start: localStartDate.value,
    end: localEndDate.value
  })
}

const setQuickRange = (days) => {
  const today = new Date()
  const startDate = new Date(today.getTime() - (days * 24 * 60 * 60 * 1000))
  
  localStartDate.value = startDate.toISOString().split('T')[0]
  localEndDate.value = today.toISOString().split('T')[0]
  emitChange()
}
</script>

<template>
  <div class="flex flex-col space-y-2">
    <label class="block text-sm font-medium text-gray-700">Date Range</label>
    <div class="flex space-x-2">
      <input
        type="date"
        :value="localStartDate"
        @change="handleStartDateChange"
        class="block px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
        placeholder="Start Date"
      />
      <span class="flex items-center text-gray-500">to</span>
      <input
        type="date"
        :value="localEndDate"
        @change="handleEndDateChange"
        class="block px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
        placeholder="End Date"
      />
    </div>
    <div class="flex space-x-2">
      <button
        @click="setQuickRange(7)"
        class="px-2 py-1 text-xs bg-gray-100 hover:bg-gray-200 rounded text-gray-700"
      >
        Last 7 days
      </button>
      <button
        @click="setQuickRange(30)"
        class="px-2 py-1 text-xs bg-gray-100 hover:bg-gray-200 rounded text-gray-700"
      >
        Last 30 days
      </button>
      <button
        @click="setQuickRange(90)"
        class="px-2 py-1 text-xs bg-gray-100 hover:bg-gray-200 rounded text-gray-700"
      >
        Last 90 days
      </button>
    </div>
  </div>
</template>