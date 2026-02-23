<template>
    <Head title="Droptrip Management" />
    <TrackingLayout>
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold dark:text-white">{{ action === 'Add' ? 'Add New Droptrip' : 'Droptrip Information' }}</h1>

            <BaseButton
                @click="emit('triggerTopRightButton', 'lists')"
                color="secondary"
                icon="mdiArrowLeft"
                label="Back"
                outline
            />
        </div>
      <CardBox>
        <form @submit.prevent="submit">
          <div class="grid grid-cols-1 gap-6">
            <div v-if="formErrors.length" class="mb-4 text-red-600">
              <ul>
                <li v-for="(err, i) in formErrors" :key="i">{{ err }}</li>
              </ul>
            </div>
            <!-- Basic Information Section -->
            <div class="col-span-1">
              <h3 class="text-lg font-semibold text-blue-600 dark:text-blue-400 border-b border-gray-200 dark:border-gray-700 pb-2 mb-4">Basic Information</h3>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <FormField label="Tracking Header">
                <FormControl
                  v-model="form.trackingheader_id"
                  :options="trackingHeaderOptions"
                  type="select"
                  placeholder="Select tracking header"
                  required
                />
              </FormField>

              <FormField label="Client">
                <FormControl
                  v-model="form.trackingclient_id"
                  :options="trackingClientOptions"
                  type="select"
                  placeholder="Select client"
                  required
                  @update:modelValue="onClientChange"
                />
              </FormField>

              <FormField label="Client Store">
                <FormControl
                  v-model="form.trackingclient_store_id"
                  :options="filteredStoreOptions"
                  type="select"
                  placeholder="Select client store"
                  required
                />
              </FormField>

              <FormField label="Sequence Number">
                <FormControl
                  v-model="form.sqno"
                  type="number"
                  placeholder="Enter sequence number"
                  required
                />
              </FormField>

              <FormField label="DRS Number">
                <FormControl
                  v-model="form.drsino"
                  placeholder="Enter DRS number"
                  required
                />
              </FormField>

              <FormField label="Receiver Name">
                <FormControl
                  v-model="form.receiver_name"
                  placeholder="Enter receiver name"
                />
              </FormField>
            </div>

            <!-- Delivery Status Section -->
            <div class="col-span-1 mt-4">
              <h3 class="text-lg font-semibold text-blue-600 dark:text-blue-400 border-b border-gray-200 dark:border-gray-700 pb-2 mb-4">Delivery Status</h3>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <FormField label="Delivery Status">
                <FormControl
                  v-model="form.delivery_status"
                  :options="deliveryStatusOptions"
                  type="select"
                  placeholder="Select delivery status"
                  required
                />
              </FormField>
            </div>

            <!-- Time Information Section -->
            <div class="col-span-1 mt-4">
              <h3 class="text-lg font-semibold text-blue-600 dark:text-blue-400 border-b border-gray-200 dark:border-gray-700 pb-2 mb-4">Time Information</h3>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <FormField label="Store Time In">
                <FormControl
                  v-model="form.store_time_in"
                  type="datetime-local"
                  placeholder="Select store time in"
                />
              </FormField>

              <FormField label="Unloading Start">
                <FormControl
                  v-model="form.unloading_start"
                  type="datetime-local"
                  placeholder="Select unloading start time"
                />
              </FormField>

              <FormField label="Unloading End">
                <FormControl
                  v-model="form.unloading_end"
                  type="datetime-local"
                  placeholder="Select unloading end time"
                />
              </FormField>

              <FormField label="Store Time Out">
                <FormControl
                  v-model="form.store_time_out"
                  type="datetime-local"
                  placeholder="Select store time out"
                />
              </FormField>
            </div>
          </div>
 <!-- Form Actions -->
          <div class="mt-6 flex justify-end gap-2">
            <BaseButton
              @click="resetForm"
              color="info"
              outline
              label="Reset"
            />
            <BaseButton
              type="submit"
              color="info"
              :loading="form.processing"
              :label="action === 'Add' ? 'Create Droptrip' : 'Update Droptrip'"
            />
          </div>
        </form>
      </CardBox>
    </TrackingLayout>
</template>

<script setup lang="ts">
import { ref, onMounted, watch, computed } from 'vue'
import { Head, router, useForm, usePage } from '@inertiajs/vue3'
import service from '@/Components/Toast/service'
import TrackingLayout from '@/Layouts/TrackingLayout.vue';
import CardBox from '@/Components/CardBox.vue';
import BaseButton from '@/Components/BaseButton.vue';
import FormField from '@/Components/FormField.vue';
import FormControl from '@/Components/FormControl.vue';

const props = defineProps({
  action: String,
  data: Object,
  formdata: Object,
  trackingHeaders: {
    type: Array,
    default: () => []
  },
  trackingClients: {
    type: Array,
    default: () => []
  },
  trackingClientStores: {
    type: Array,
    default: () => []
  },
  errors: Object
});

const toast = service()
const page = usePage()

// Compute the action based on whether we have data
const action = computed(() => props.data?.id ? 'Edit' : 'Add')

// Delivery status options
const deliveryStatusOptions = ref([
  { label: 'Pending', id: 'PENDING' },
  { label: 'In Progress', id: 'IN_PROGRESS' },
  { label: 'Completed', id: 'COMPLETED' },
  { label: 'Cancelled', id: 'CANCELLED' }
])

// Computed options for dropdowns
// Transform options to match FormControl expected format (id/label or generic object with id/label mapping if implemented, but usually id/label is safe)
// Assuming FormControl uses 'id' for value and 'label' for text by default or we need to map them.
// Let's check FormControl implementation or assume standard {id, label}.
// The previous code used text-by and value-by props on VaSelect. FormControl usually takes an array of objects.
// Let's map them to {id, label} to be safe.

const trackingHeaderOptions = computed(() => (props.trackingHeaders || []).map((h: any) => ({ id: h.id, label: h.tracking_number })))
const trackingClientOptions = computed(() => (props.trackingClients || []).map((c: any) => ({ id: c.id, label: c.client_name })))

// Filtered store options based on selected client
const filteredStoreOptions = computed(() => {
  if (!form.trackingclient_id) return []
  return (props.trackingClientStores || [])
    .filter((store: any) => store.client_id === form.trackingclient_id)
    .map((s: any) => ({ id: s.id, label: s.store_name }))
})

// Helper to format date for datetime-local input (YYYY-MM-DDTHH:mm)
const formatForInput = (dateStr: string | null) => {
    if (!dateStr) return '';
    return dateStr.replace(' ', 'T').slice(0, 16);
}

// Initialize form with empty values first
const form = useForm({
  _token: page.props.csrf_token,
  id: props.formdata?.id || props.data?.id || null,
  trackingheader_id: props.formdata?.trackingheader_id || props.data?.trackingheader_id || '',
  trackingclient_id: props.formdata?.trackingclient_id || props.data?.trackingclient_id || '',
  trackingclient_store_id: props.formdata?.trackingclient_store_id || props.data?.trackingclient_store_id || '',
  sqno: props.formdata?.sqno || props.data?.sqno || '',
  drsino: props.formdata?.drsino || props.data?.drsino || '',
  store_time_in: formatForInput(props.formdata?.store_time_in || props.data?.store_time_in),
  unloading_start: formatForInput(props.formdata?.unloading_start || props.data?.unloading_start),
  unloading_end: formatForInput(props.formdata?.unloading_end || props.data?.unloading_end),
  store_time_out: formatForInput(props.formdata?.store_time_out || props.data?.store_time_out),
  receiver_name: props.formdata?.receiver_name || props.data?.receiver_name || '',
  delivery_status: props.formdata?.delivery_status || props.data?.delivery_status || 'PENDING'
})
const formErrors = ref<string[]>([])

const emit = defineEmits(["triggerTopRightButton"]);

// Watch for changes in formdata prop
watch(() => props.formdata, (newFormdata) => {
  if (newFormdata) {
    form.id = newFormdata.id || null
    form.trackingheader_id = newFormdata.trackingheader_id || ''
    form.trackingclient_id = newFormdata.trackingclient_id || ''
    form.trackingclient_store_id = newFormdata.trackingclient_store_id || ''
    form.sqno = newFormdata.sqno || ''
    form.drsino = newFormdata.drsino || ''
    form.store_time_in = formatForInput(newFormdata.store_time_in)
    form.unloading_start = formatForInput(newFormdata.unloading_start)
    form.unloading_end = formatForInput(newFormdata.unloading_end)
    form.store_time_out = formatForInput(newFormdata.store_time_out)
    form.receiver_name = newFormdata.receiver_name || ''
    form.delivery_status = newFormdata.delivery_status || 'PENDING'
  }
}, { immediate: true })

// Handle client change to reset store selection
const onClientChange = () => {
  form.trackingclient_store_id = ''
}

// Form submission
const submit = async () => {
  const url = form.id
    ? route('tracking.droptrip.update', form.id)
    : route('tracking.droptrip.store')

  const method = form.id ? 'put' : 'post'

  // Transform dates back to MySQL format if needed, or let Laravel handle ISO format.
  // Ideally, Laravel casts usually handle standard ISO, but let's stick to the previous format logic just in case,
  // but replacing T with space.
  form.transform((data) => ({
    ...data,
    store_time_in: data.store_time_in ? data.store_time_in.replace('T', ' ') + ':00' : null,
    unloading_start: data.unloading_start ? data.unloading_start.replace('T', ' ') + ':00' : null,
    unloading_end: data.unloading_end ? data.unloading_end.replace('T', ' ') + ':00' : null,
    store_time_out: data.store_time_out ? data.store_time_out.replace('T', ' ') + ':00' : null,
  }))

  form.submit(method, url, {
    preserveScroll: true,
    onSuccess: () => {
      toast.success(`Droptrip ${form.id ? 'updated' : 'created'} successfully!`)
      emit('triggerTopRightButton', 'lists')
    },
    onError: (errors) => {
      formErrors.value = Object.values(errors || {}) as string[]
      toast.error('Please check the form for errors')
    }
  })
}

// Reset form
const resetForm = () => {
  form.reset()
}

onMounted(() => {
  console.log('Droptrip manage component mounted')
})
</script>

<style scoped>
/* Scoped styles removed as we use Tailwind classes */
</style>
