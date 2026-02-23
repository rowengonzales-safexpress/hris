<template>
  <div class="details-container">
    <div class="details-header">
      <h2 class="details-title">Droptrip Details</h2>
      <BaseButton :icon="'mdiClose'" @click="closeDetails" class="close-button" title="Close" />
    </div>

    <div class="details-content">
      <div class="detail-section">
        <h3 class="section-title">Basic Information</h3>

        <FormField label="DRS Number">
          <FormControl :model-value="localDroptrip.drsino" :disabled="true" />
        </FormField>

        <FormField label="Sequence Number">
          <FormControl :model-value="localDroptrip.sqno" :disabled="true" />
        </FormField>

        <FormField label="Receiver Name">
          <FormControl :model-value="localDroptrip.receiver_name" :disabled="true" />
        </FormField>

        <FormField label="Delivery Status">
          <FormControl :model-value="localDroptrip.delivery_status" :disabled="true" />
        </FormField>
      </div>

      <div class="detail-section">
        <h3 class="section-title">Related Information</h3>

        <FormField label="Tracking Number" v-if="localDroptrip.tracking_header">
          <FormControl :model-value="localDroptrip.tracking_header?.tracking_number" :disabled="true" />
        </FormField>

        <FormField label="Client Name" v-if="localDroptrip.tracking_client">
          <FormControl :model-value="localDroptrip.tracking_client?.client_name" :disabled="true" />
        </FormField>

        <FormField label="Store Name" v-if="localDroptrip.tracking_client_store">
          <FormControl :model-value="localDroptrip.tracking_client_store?.store_name" :disabled="true" />
        </FormField>
      </div>

      <div class="detail-section">
        <h3 class="section-title">Time Information</h3>

        <FormField label="Store Time In">
          <FormControl :model-value="formattedStoreTimeIn" :disabled="true" />
        </FormField>

        <FormField label="Unloading Start">
          <FormControl :model-value="formattedUnloadingStart" :disabled="true" />
        </FormField>

        <FormField label="Unloading End">
          <FormControl :model-value="formattedUnloadingEnd" :disabled="true" />
        </FormField>

        <FormField label="Store Time Out">
          <FormControl :model-value="formattedStoreTimeOut" :disabled="true" />
        </FormField>
      </div>

      <div class="detail-section">
        <h3 class="section-title">Timestamps</h3>

        <FormField label="Created At">
          <FormControl :model-value="formattedCreatedAt" :disabled="true" />
        </FormField>

        <FormField label="Updated At">
          <FormControl :model-value="formattedUpdatedAt" :disabled="true" />
        </FormField>
      </div>
    </div>

    <div class="details-actions">
      <BaseButtons>
        <BaseButton :icon="'mdinav'" color="info" rounded-full small label="manage" @click="manageDroptrip" />
        <BaseButton :icon="'mdiClose'" color="danger" rounded-full small label="Cancel" @click="closeDetails" />
      </BaseButtons>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, computed } from 'vue'
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
  trackingheader_id: null,
  trackingclient_id: null,
  trackingclient_store_id: null,
  sqno: '',
  drsino: '',
  store_time_in: null,
  unloading_start: null,
  unloading_end: null,
  store_time_out: null,
  receiver_name: '',
  delivery_status: 'PENDING',
})

const localDroptrip = ref({
  id: null,
  trackingheader_id: null,
  trackingclient_id: null,
  trackingclient_store_id: null,
  sqno: '',
  drsino: '',
  store_time_in: null,
  unloading_start: null,
  unloading_end: null,
  store_time_out: null,
  receiver_name: '',
  delivery_status: 'PENDING',
  tracking_header: null,
  tracking_client: null,
  tracking_client_store: null,
  created_at: null,
  updated_at: null,
})

// Computed properties for formatted dates
const formattedStoreTimeIn = computed(() => {
  return localDroptrip.value.store_time_in
    ? new Date(localDroptrip.value.store_time_in).toLocaleString()
    : 'Not set'
})

const formattedUnloadingStart = computed(() => {
  return localDroptrip.value.unloading_start
    ? new Date(localDroptrip.value.unloading_start).toLocaleString()
    : 'Not set'
})

const formattedUnloadingEnd = computed(() => {
  return localDroptrip.value.unloading_end
    ? new Date(localDroptrip.value.unloading_end).toLocaleString()
    : 'Not set'
})

const formattedStoreTimeOut = computed(() => {
  return localDroptrip.value.store_time_out
    ? new Date(localDroptrip.value.store_time_out).toLocaleString()
    : 'Not set'
})

const formattedCreatedAt = computed(() => {
  return localDroptrip.value.created_at
    ? new Date(localDroptrip.value.created_at).toLocaleString()
    : 'Not available'
})

const formattedUpdatedAt = computed(() => {
  return localDroptrip.value.updated_at
    ? new Date(localDroptrip.value.updated_at).toLocaleString()
    : 'Not available'
})

// Watch for changes in props.data
watch(() => props.data, (newData) => {
  if (newData) {
    localDroptrip.value = { ...newData }

    // Update form data
    form.id = newData.id
    form.trackingheader_id = newData.trackingheader_id || null
    form.trackingclient_id = newData.trackingclient_id || null
    form.trackingclient_store_id = newData.trackingclient_store_id || null
    form.sqno = newData.sqno || ''
    form.drsino = newData.drsino || ''
    form.store_time_in = newData.store_time_in || null
    form.unloading_start = newData.unloading_start || null
    form.unloading_end = newData.unloading_end || null
    form.store_time_out = newData.store_time_out || null
    form.receiver_name = newData.receiver_name || ''
    form.delivery_status = newData.delivery_status || 'PENDING'
  }
}, { immediate: true })

const closeDetails = () => {
  emit('close')
}

const manageDroptrip = () => {
  // Navigate to manage page with form data
  emit('triggerTopRightButton', 'Manage', localDroptrip.value);
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
