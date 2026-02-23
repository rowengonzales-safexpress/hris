<script setup lang="ts">
import { provide, ref } from 'vue'
import BaseButton from '@/Components/BaseButton.vue'
import BaseIcon from '@/Components/BaseIcon.vue'
import Dropdown from '@/Components/Dropdown.vue'

type TabEntry = { id: number; title: string; icon?: string; render: () => any }

const props = defineProps({
  color: { type: String, default: '#094899' },
  stepSize: { type: String, default: 'xs' },
  finishDisabled: { type: Boolean, default: false },
  nextDisabled: { type: [Boolean, Function], default: false },
  showFinishDropdown: { type: Boolean, default: false },
})

const emit = defineEmits<{
  (e: 'on-complete'): void
  (e: 'on-reject'): void
}>()

const tabs = ref<TabEntry[]>([])
const activeIndex = ref(0)
let nextId = 1

function registerTab(title: string, icon: string | undefined, render: () => any) {
  const id = nextId++
  tabs.value.push({ id, title, icon, render })
  return id
}

function unregisterTab(id: number) {
  const idx = tabs.value.findIndex(t => t.id === id)
  if (idx >= 0) {
    tabs.value.splice(idx, 1)
    if (activeIndex.value >= tabs.value.length) {
      activeIndex.value = Math.max(0, tabs.value.length - 1)
    }
  }
}

function next() {
  if (activeIndex.value < tabs.value.length - 1) {
    activeIndex.value++
  }
}

function back() {
  if (activeIndex.value > 0) {
    activeIndex.value--
  }
}

function complete() {
  emit('on-complete')
}

provide('formWizard', {
  registerTab,
  unregisterTab,
  getActiveId: () => tabs.value[activeIndex.value]?.id,
})
</script>

<template>
  <div>
    <!-- Step header -->
    <div class="mb-4 flex items-center gap-3">
      <template v-for="(t, i) in tabs" :key="t.id">
        <div
          class="flex items-center gap-2 rounded-md px-3 py-2"
          :class="[
            i === activeIndex ? 'bg-gray-100 dark:bg-slate-800' : 'bg-transparent',
          ]"
        >
          <span
            class="inline-flex h-6 w-6 items-center justify-center rounded-full text-xs font-semibold"
            :style="{ backgroundColor: i === activeIndex ? props.color : '#e5e7eb', color: i === activeIndex ? '#fff' : '#111827' }"
          >
            {{ i + 1 }}
          </span>
          <BaseIcon v-if="t.icon" :path="t.icon" :size="18" class="text-gray-700 dark:text-gray-200" />
          <span class="text-sm font-medium text-gray-800 dark:text-gray-100">{{ t.title }}</span>
        </div>
        <div v-if="i < tabs.length - 1" class="h-px w-8 bg-gray-300 dark:bg-gray-600" />
      </template>
    </div>

    <!-- Registered tab contents -->
    <div>
      <slot />
    </div>

    <!-- Footer actions -->
    <div class="mt-6 flex justify-between">
      <BaseButton
        :icon="'mdiChevronLeft'"
        label="Back"
        color="contrast"
        rounded-full
        small
        :disabled="activeIndex === 0"
        @click="back"
      />
      <div class="flex gap-2">
        <BaseButton
          v-if="activeIndex < tabs.length - 1"
          :icon="'mdiChevronRight'"
          label="Next"
          color="info"
          rounded-full
          small
          :disabled="typeof props.nextDisabled === 'function' ? props.nextDisabled(activeIndex) : props.nextDisabled"
          @click="next"
        />
        <template v-else>
          <Dropdown v-if="props.showFinishDropdown" width="64" align="right" direction="up">
            <template #trigger>
              <BaseButton
                :icon="'mdiCheck'"
                label="Finish"
                color="info"
                rounded-full
                small
                :disabled="props.finishDisabled"
              >
                <BaseIcon :path="'mdiChevronDown'" :size="18" class="ml-1" />
              </BaseButton>
            </template>
            <template #content>
              <div class="p-1">
                <button
                  @click="complete"
                  class="flex w-full items-center gap-3 px-4 py-2 text-left text-sm hover:bg-gray-100 dark:hover:bg-gray-800 rounded-md transition-colors"
                >
                  <div class="flex h-8 w-8 items-center justify-center rounded-full bg-green-100 text-green-600 dark:bg-green-900/30 dark:text-green-400">
                    <BaseIcon :path="'mdiCheckCircle'" :size="20" />
                  </div>
                  <div>
                    <div class="font-semibold text-gray-900 dark:text-gray-100">Approved</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">Mark as completed</div>
                  </div>
                </button>

                <button
                  @click="emit('on-reject')"
                  class="flex w-full items-center gap-3 px-4 py-2 text-left text-sm hover:bg-gray-100 dark:hover:bg-gray-800 rounded-md transition-colors"
                >
                  <div class="flex h-8 w-8 items-center justify-center rounded-full bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400">
                    <BaseIcon :path="'mdiCloseCircle'" :size="20" />
                  </div>
                  <div>
                    <div class="font-semibold text-gray-900 dark:text-gray-100">Rejected</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">Mark as declined</div>
                  </div>
                </button>
              </div>
            </template>
          </Dropdown>
          <BaseButton
            v-else
            :icon="'mdiCheck'"
            label="Finish"
            color="info"
            rounded-full
            small
            :disabled="props.finishDisabled"
            @click="complete"
          />
        </template>
      </div>
    </div>
  </div>
</template>
