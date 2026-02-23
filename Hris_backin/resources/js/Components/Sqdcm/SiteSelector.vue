<script setup>
import { ref, watch } from 'vue'

const props = defineProps({
  sites: {
    type: Array,
    default: () => []
  },
  selected: {
    type: String,
    default: ''
  }
})

const emit = defineEmits(['change'])

const selectedSite = ref(props.selected)

watch(() => props.selected, (newValue) => {
  selectedSite.value = newValue
})

const handleChange = (event) => {
  selectedSite.value = event.target.value
  emit('change', event.target.value)
}
</script>

<template>
  <div class="relative">
    <label class="block text-sm font-medium text-gray-700 mb-1">Site</label>
    <select 
      :value="selectedSite"
      @change="handleChange"
      class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
    >
      <option value="">All Sites</option>
      <option 
        v-for="site in sites" 
        :key="site.site_code" 
        :value="site.site_code"
      >
        {{ site.site_name }} ({{ site.site_code }})
      </option>
    </select>
  </div>
</template>