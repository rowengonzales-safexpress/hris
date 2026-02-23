<script setup lang="ts">
import { inject, onMounted, onUnmounted, computed, ref, useSlots } from 'vue'

const props = defineProps({
  title: { type: String, required: true },
  icon: { type: String, default: undefined },
})

const slots = useSlots()

const wizard = inject('formWizard') as {
  registerTab: (title: string, icon: string | undefined, render: () => any) => number
  unregisterTab: (id: number) => void
  getActiveId: () => number | undefined
} | undefined

const id = ref<number | null>(null)

onMounted(() => {
  id.value = wizard?.registerTab(props.title, props.icon, () => slots.default?.()) ?? null
})

onUnmounted(() => {
  if (wizard && id.value != null) wizard.unregisterTab(id.value)
})

const isActive = computed(() => wizard?.getActiveId() === id.value)
</script>

<template>
  <div v-show="isActive">
    <slot />
  </div>
</template>