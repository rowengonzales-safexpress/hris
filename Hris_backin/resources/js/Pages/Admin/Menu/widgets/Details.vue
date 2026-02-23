<template>
  <div class="details-container">
    <div class="details-content">
      <FormField label="Menu Name">
        <FormControl v-model="localMenu.name" :disabled="true" />
      </FormField>
      <FormField label="Route">
        <FormControl v-model="localMenu.route" :disabled="true" />
      </FormField>
      <FormField label="Icon">
        <FormControl v-model="localMenu.icon" :disabled="true" />
      </FormField>
      <FormField label="Sort Order">
        <FormControl v-model="localMenu.sort_order" :disabled="true" />
      </FormField>
      <FormField label="Application">
        <FormControl v-model="localMenu.app_name" :disabled="true" />
      </FormField>
      <FormField label="Parent Menu">
        <FormControl v-model="localMenu.parent_name" :disabled="true" />
      </FormField>
      <FormField label="Active">
        <FormControl v-model="localMenu.is_active" type="checkbox" :disabled="true" />
      </FormField>
    </div>
    <div class="details-actions">
      <button class="w-full mb-2 action-button" @click="manageMenu">Manage Menu</button>
      <button class="w-full action-button" @click="closeDetails">Close</button>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import FormField from '@/Components/FormField.vue'
import FormControl from '@/Components/FormControl.vue'

const props = defineProps({
  data: {
    type: Object,
    required: true
  },
  isNew: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['close', 'Manage', 'triggerTopRightButton']);

// Initialize form with useForm
const form = useForm({
  id: null,
  app_id: '',
  parent_id: null,
  name: '',
  route: '',
  icon: '',
  sort_order: 0,
  is_active: true,
})

const localMenu = ref({
  id: null,
  name: '',
  route: '',
  icon: '',
  sort_order: 0,
  is_active: true,
  app_name: '',
  parent_name: ''
})

// Watch for changes in props.data
watch(() => props.data, (newData) => {
  if (newData) {
    localMenu.value = { ...newData }

    // Update form data
    form.id = newData.id
    form.app_id = newData.app_id || ''
    form.parent_id = newData.parent_id
    form.name = newData.name || ''
    form.route = newData.route || ''
    form.icon = newData.icon || ''
    form.sort_order = newData.sort_order || 0
    form.is_active = newData.is_active || true
  }
}, { immediate: true })

const closeDetails = () => {
  emit('close')
}

const manageMenu = () => {
  // Navigate to manage page with form data
  emit('triggerTopRightButton', 'Manage', localMenu.value);
}
</script>

<style scoped>
.details-container {
  height: 100%;
  display: flex;
  flex-direction: column;
}

.details-content {
  flex: 1;
  overflow-y: auto;
}

.details-actions {
  padding-top: 1rem;
  border-top: 1px solid var(--va-background-border);
  margin-top: auto;
}
.action-button { padding: 0.5rem 1rem; border: 1px solid #e5e7eb; border-radius: 0.375rem; }
</style>
