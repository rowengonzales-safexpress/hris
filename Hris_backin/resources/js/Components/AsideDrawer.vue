<script setup>

import SectionTitleLineWithButton from "@/Components/SectionTitleLineWithButton.vue";
import {mdiClose} from "@mdi/js";
import BaseButton from "@/Components/BaseButton.vue";
import CardBox from "./CardBox.vue";

defineProps({
  title: {
    type: String,
    required: true,
  },
  icon: {
    type: String,
    default: null,
  },
  isOpen : Boolean
});

</script>

<template>
  <aside
    class="transform top-0 right-0 w-96 bg-white fixed h-full overflow-y-auto ease-in-out transition-all duration-300 z-40 dark:bg-slate-900"
    :class="isOpen ? 'translate-x-0' : 'translate-x-full'">
    <SectionTitleLineWithButton class="px-6 pt-3" :title="title" :main="true">
      <BaseButton
        :icon="mdiClose"
        small
        @click="$emit('closeDrawer',false)"
        title="Close"
        class="dark:border-slate-900 ring-gray-200 dark:ring-gray-500 dark:bg-slate-900 dark:text-white hover:bg-gray-100 hover:dark:bg-slate-700"
      />
    </SectionTitleLineWithButton>
    <CardBox :has-component-layout="true" class="p-6">
      <slot />
    </CardBox>
  </aside>

</template>