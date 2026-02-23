<template>
  <div class="tooltip-container" @mouseenter="showTooltip" @mouseleave="hideTooltip">
    <slot />
    <Transition
      enter-active-class="transition-all duration-200 ease-out"
      enter-from-class="opacity-0 scale-95 translate-x-2"
      enter-to-class="opacity-100 scale-100 translate-x-0"
      leave-active-class="transition-all duration-150 ease-in"
      leave-from-class="opacity-100 scale-100 translate-x-0"
      leave-to-class="opacity-0 scale-95 translate-x-2"
    >
      <div
        v-if="visible && text"
        :class="[
          'tooltip-content',
          'absolute left-full ml-3 px-0 py-0 text-sm font-medium text-white bg-gray-800 rounded-lg shadow-xl z-50',
          'whitespace-nowrap pointer-events-none border border-gray-700',
          'before:content-[\'\'] before:absolute before:right-full before:top-4 before:-translate-y-1/2',
          'before:border-4 before:border-transparent before:border-r-gray-800',
          props.submenus && props.submenus.length > 0 ? 'min-w-52' : ''
        ]"
        :style="{ top: '0', transform: 'translateY(0)' }"
      >
        <!-- Main menu title -->
        <div class="px-4 py-3 border-b border-gray-600 bg-gray-700 rounded-t-lg font-semibold">
          {{ text }}
        </div>
        
        <!-- Submenus -->
        <div v-if="props.submenus && props.submenus.length > 0" class="py-2">
          <div 
            v-for="submenu in props.submenus" 
            :key="submenu.href"
            class="px-4 py-2 hover:bg-gray-700 transition-colors duration-150 flex items-center cursor-pointer"
          >
            <i v-if="submenu.icon" :class="submenu.icon" class="mr-3 text-sm w-4 text-gray-300"></i>
            <span class="text-sm text-gray-100">{{ submenu.name }}</span>
          </div>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';

interface Props {
  text: string;
  disabled?: boolean;
  delay?: number;
  submenus?: Array<{
    name: string;
    href: string;
    icon?: string;
  }>;
}

const props = withDefaults(defineProps<Props>(), {
  disabled: false,
  delay: 300,
  submenus: () => []
});

const visible = ref(false);
let timeoutId: NodeJS.Timeout | null = null;

const showTooltip = () => {
  if (props.disabled || !props.text) return;
  
  if (timeoutId) {
    clearTimeout(timeoutId);
  }
  
  timeoutId = setTimeout(() => {
    visible.value = true;
  }, props.delay);
};

const hideTooltip = () => {
  if (timeoutId) {
    clearTimeout(timeoutId);
    timeoutId = null;
  }
  visible.value = false;
};
</script>

<style scoped>
.tooltip-container {
  position: relative;
  display: inline-block;
}

.tooltip-content::before {
  content: '';
  position: absolute;
  right: 100%;
  top: 50%;
  transform: translateY(-50%);
  border: 4px solid transparent;
  border-right-color: #374151;
}
</style>