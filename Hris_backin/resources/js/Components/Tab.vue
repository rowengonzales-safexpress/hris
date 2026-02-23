<script>
export default {
  name: 'Tab',
}
</script>
<script setup>
import BaseButton from './BaseButton.vue'

const tabClass = () => {
  if (props.activeTab === props.name) {
    return {
      a: 'text-blue-600 border-b-2 border-blue-600 rounded-t-lg dark:text-blue-500 dark:border-blue-500 group',
      svg: ' text-blue-600 dark:text-blue-500',
    }
  }
  return {
    a: 'border-b-2 border-transparent rounded-t-lg hover:text-gray-600 hover:border-gray-300 dark:hover:text-gray-300 group',
    svg: ' text-gray-400 group-hover:text-gray-500 dark:text-gray-500 dark:group-hover:text-gray-300',
  }
}

const props = defineProps({
  label: {
    type: [String, Number],
    default: null,
  },
  icon: {
    type: String,
    default: null,
  },
  iconSize: {
    type: [String, Number],
    default: null,
  },
  name: {
    type: String,
    default: null,
  },
  activeTab: {
    type: String,
    default: null,
  },
  disabled: Boolean,
})
</script>

<template>
  <a href="#" class="inline-flex p-4" :class="tabClass().a">
    <BaseButton
      :icon="icon"
      :label="label"
      color="transparent"
      small
      :border="false"
      :disabled="disabled"
      class="focus:outline-none focus:ring-0"
    />
  </a>
</template>
