<template>


      <FormField label="Application Name">
        <FormControl v-model="localApplication.name" :disabled="true" />
      </FormField>
      <FormField label="Description">
        <FormControl v-model="localApplication.description" :disabled="true" />
      </FormField>
      <FormField label="URL">
        <FormControl v-model="localApplication.url" :disabled="true" />
      </FormField>
      <FormField label="Active">
        <FormControl v-model="localApplication.is_active" type="checkbox" :disabled="true" />
      </FormField>

    <BaseButtons>
      <BaseButton icon="mdiAccountBoxEditOutline"  color="info"  label="manage" @click="manageApplication"/>
      <BaseButton icon="mdiClose" color="danger" label="Cancel" @click="openDrawer = false" />
    </BaseButtons>

</template>

<script setup>
import { ref, watch } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import FormField from '@/Components/FormField.vue'
import FormControl from '@/Components/FormControl.vue'
import BaseButton from '@/Components/BaseButton.vue'
import BaseButtons from '@/Components/BaseButtons.vue'


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

const emit = defineEmits(['close', 'save', 'update', 'Manage', 'triggerTopRightButton']);

// Initialize form with useForm
const form = useForm({
  id: null,
  name: '',
  description: '',
  url: '',
  is_active: true,
})

const localApplication = ref({
  id: null,
  name: '',
  description: '',
  url: '',
  is_active: true,
})

// Watch for changes in props.data
watch(() => props.data, (newData) => {
  if (newData) {
    localApplication.value = { ...newData }

    // Update form data
    form.id = newData.id
    form.name = newData.name || ''
    form.description = newData.description || ''
    form.url = newData.url || ''
    form.is_active = newData.is_active || true
  }
}, { immediate: true })

const closeDetails = () => {
  emit('close')
}

const manageApplication = () => {
  // Navigate to manage page with form data
  emit('triggerTopRightButton', 'Manage', localApplication.value);
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
