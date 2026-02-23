<template>
  <div class="details-container">
    <div class="details-header">
      <h2 class="details-title">Store Details</h2>
      <BaseButton :icon="'mdiClose'" @click="closeDetails" class="close-button" title="Close" />
    </div>

    <div class="details-content">
      <div class="detail-section">


        <FormField label="Store Name">
          <FormControl :model-value="localStore.store_name" :disabled="true" />
        </FormField>

        <FormField label="Store Code">
          <FormControl :model-value="localStore.store_code" :disabled="true" />
        </FormField>

        <FormField label="Client">
          <FormControl :model-value="localStore.client_name" :disabled="true" />
        </FormField>
      </div>

      <div class="detail-section">
        <h3 class="section-title">Contact Information</h3>

        <FormField label="Email">
          <FormControl :model-value="localStore.email" :disabled="true" />
        </FormField>

        <FormField label="Phone">
          <FormControl :model-value="localStore.phone" :disabled="true" />
        </FormField>
      </div>

      <div class="detail-section">
        <h3 class="section-title">Address</h3>

        <FormField label="Address">
          <FormControl :model-value="localStore.address" type="textarea" :disabled="true" />
        </FormField>

        <div class="flex flex-wrap -mx-2">
          <div class="w-full md:w-1/2 px-2">
            <FormField label="City">
              <FormControl :model-value="localStore.city" :disabled="true" />
            </FormField>
          </div>
          <div class="w-full md:w-1/2 px-2">
            <FormField label="State/Province">
              <FormControl :model-value="localStore.state_province" :disabled="true" />
            </FormField>
          </div>
        </div>

        <FormField label="ZIP Code">
          <FormControl :model-value="localStore.zip_code" :disabled="true" />
        </FormField>
      </div>
    </div>

    <div class="details-actions">
      <BaseButtons>
        <BaseButton :icon="'mdinav'" color="info" rounded-full small label="manage" @click="manageStore" />
        <BaseButton :icon="'mdiClose'" color="danger" rounded-full small label="Cancel" @click="closeDetails" />
      </BaseButtons>
    </div>
  </div>
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
  client_id: null,
  store_code: '',
  store_name: '',
  email: '',
  phone: '',
  address: '',
  city: '',
  state_province: '',
  zip_code: ''
})

const localStore = ref({
  id: null,
  client_id: null,
  client_name: '',
  store_code: '',
  store_name: '',
  email: '',
  phone: '',
  address: '',
  city: '',
  state_province: '',
  zip_code: ''
})

// Watch for changes in props.data
watch(() => props.data, (newData) => {
  if (newData) {
    localStore.value = {
      ...newData,
      client_name: newData.client?.client_name || newData.client_name || ''
    }

    // Update form data
    form.id = newData.id
    form.client_id = newData.client_id || newData.client?.id
    form.store_code = newData.store_code || ''
    form.store_name = newData.store_name || ''
    form.email = newData.email || ''
    form.phone = newData.phone || ''
    form.address = newData.address || ''
    form.city = newData.city || ''
    form.state_province = newData.state_province || ''
    form.zip_code = newData.zip_code || ''
  }
}, { immediate: true })

const closeDetails = () => {
  emit('close')
}

const manageStore = () => {
  // Navigate to manage page with form data
  emit('triggerTopRightButton', 'Manage', localStore.value);
}
</script>

<style scoped>
.details-container {
  height: 100%;
  display: flex;
  flex-direction: column;
}

.details-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding-bottom: 1rem;
  border-bottom: 1px solid #e5e7eb;
  margin-bottom: 1rem;
}

.details-title {
  font-size: 1.25rem;
  font-weight: 600;
  margin: 0;
}

.close-button {
  min-width: auto !important;
  width: 2rem;
  height: 2rem;
}

.details-content {
  flex: 1;
  overflow-y: auto;
}

.detail-section {
  margin-bottom: 1.5rem;
}

.section-title {
  font-size: 1rem;
  font-weight: 500;
  margin-bottom: 0.75rem;
  color: #2563eb;
  border-bottom: 1px solid #e5e7eb;
  padding-bottom: 0.25rem;
}

.details-actions {
  padding-top: 1rem;
  border-top: 1px solid #e5e7eb;
  margin-top: auto;
}
</style>
