<script setup>
import { computed } from 'vue'
import * as mdiIcons from '@mdi/js'

const props = defineProps({
  path: {
    type: String,
    required: true
  },
  w: {
    type: String,
    default: 'w-6'
  },
  h: {
    type: String,
    default: 'h-6'
  },
  size: {
    type: [String, Number],
    default: 16
  }
})

const spanClass = computed(() => `inline-flex justify-center items-center ${props.w} ${props.h}`)

// Allow passing either an actual SVG path or an icon name key from @mdi/js (e.g., 'mdiPlusCircle')
const resolvedPath = computed(() => (mdiIcons[props.path] || props.path))
</script>

<template>
  <span :class="spanClass">
    <svg
      viewBox="0 0 24 24"
      :width="size"
      :height="size"
      class="inline-block"
    >
      <path
        fill="currentColor"
        :d="resolvedPath"
      />
    </svg>
  </span>
</template>
