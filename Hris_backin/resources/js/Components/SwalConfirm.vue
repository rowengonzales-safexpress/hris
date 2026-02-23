<script setup lang="ts">
import { computed } from 'vue'
import Modal from '@/Components/Modal.vue'
import BaseIcon from '@/Components/BaseIcon.vue'

type SwalType = 'success' | 'warning' | 'danger'

const props = defineProps({
  modelValue: { type: Boolean, default: false },
  type: { type: String as () => SwalType, default: 'warning' },
  title: { type: String, default: 'Are you sure?' },
  text: { type: String, default: 'Once submitted, you cannot undo this action!' },
  confirmText: { type: String, default: 'Confirm' },
  cancelText: { type: String, default: 'Cancel' },
  showCancel: { type: Boolean, default: true },
})

const emit = defineEmits<{(e: 'update:modelValue', value: boolean): void; (e: 'confirm'): void; (e: 'cancel'): void}>()

const show = computed({
  get: () => props.modelValue,
  set: (v: boolean) => emit('update:modelValue', v),
})

const iconPath = computed(() => {
  switch (props.type) {
    case 'success':
      return 'mdiCheckCircleOutline'
    case 'danger':
      return 'mdiCloseOctagonOutline'
    default:
      return 'mdiAlert'
  }
})

const accentClass = computed(() => {
  switch (props.type) {
    case 'success':
      return 'text-emerald-600 dark:text-emerald-400'
    case 'danger':
      return 'text-red-600 dark:text-red-400'
    default:
      return 'text-amber-600 dark:text-amber-400'
  }
})

function onConfirm() {
  emit('confirm')
  emit('update:modelValue', false)
}

function onCancel() {
  emit('cancel')
  emit('update:modelValue', false)
}
</script>

<template>
  <Modal :show="show" maxWidth="sm" :closeable="false">
    <div class="p-6">
      <div class="flex items-start gap-4">
        <div class="mt-1">
          <BaseIcon :path="iconPath" :size="28" :class="accentClass" />
        </div>
        <div class="flex-1">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ title }}</h3>
          <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">{{ text }}</p>
        </div>
      </div>

      <div class="mt-6 flex justify-end gap-3">
        <button
          v-if="showCancel"
          type="button"
          class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-slate-800"
          @click="onCancel"
        >
          {{ cancelText }}
        </button>
        <button
          type="button"
           class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-slate-800"
          @click="onConfirm"
        >
          {{ confirmText }}
        </button>
      </div>
    </div>
  </Modal>

</template>
