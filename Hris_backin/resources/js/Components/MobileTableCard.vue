<template>
  <div class="mobile-table-container space-y-3 mb-4">
    <div v-for="(item, index) in items" :key="index" class="bg-white dark:bg-slate-800 rounded-lg p-3 shadow border border-gray-100 dark:border-gray-700 transition-all duration-300">
      <div v-for="column in columns" :key="column.key" class="flex justify-between py-1.5 border-b border-gray-100 dark:border-gray-700 last:border-0 transition-colors duration-300">
        <div class="font-semibold text-gray-500 dark:text-gray-400 text-xs transition-colors duration-300">{{ column.label }}:</div>
        <div
          class="text-right text-sm max-w-[60%] break-words text-gray-800 dark:text-gray-200 transition-colors duration-300"
          :class="{ 'text-blue-600 dark:text-blue-400 cursor-pointer hover:underline hover:opacity-80': clickableColumns.includes(column.key) }"
          @click="clickableColumns.includes(column.key) ? $emit('row-click', item) : null"
        >
          {{ item[column.key] }}
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue'

interface Column {
  key: string
  label: string
  sortable?: boolean
  clickable?: boolean
  numeric?: boolean
  totalLabel?: string
  formatTotal?: (value: number) => string | number
}

export default defineComponent({
  name: 'MobileTableCard',
  props: {
    items: {
      type: Array as PropType<any[]>,
      required: true,
    },
    columns: {
      type: Array as PropType<Column[]>,
      required: true,
    },
    clickableColumns: {
      type: Array as PropType<string[]>,
      default: () => [],
    },
  },
  emits: ['row-click'],
})
</script>
