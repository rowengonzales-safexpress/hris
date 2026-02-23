<template>
  <div class="tracking-create">
    <div class="flex flex-col gap-6">
      <!-- Header -->

 <SectionTitleLineWithButton :icon="'mdiFileDocumentPlus'" :title="(props.action === 'Add' ? 'Add' : 'Update') + ' Event'" main>
        <BaseButton @click="emit('triggerTopRightButton', 'lists')" :icon="'mdiViewList'" label="Request Lists" color="contrast" rounded-full small />
      </SectionTitleLineWithButton>
      <!-- Form -->
      <CardBox class="p-6">
        <div class="text-lg font-semibold mb-4">Tracking Information</div>
        <form @submit.prevent="submitForm">
            <div v-if="formErrors.length" class="mb-4 text-danger">
              <ul>
                <li v-for="(err, i) in formErrors" :key="i">{{ err }}</li>
              </ul>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <!-- Basic Information -->
              <div class="md:col-span-2">
                <h3 class="text-lg font-semibold mb-4">Basic Information</h3>
              </div>

              <FormField label="Tracking Number">
                <FormControl v-model="form.tracking_number" placeholder="Leave empty for auto-generation" disabled />
              </FormField>
              <FormField label="Tracking Type">
                <FormControl :model-value="trackingTypeDisplay" disabled />
              </FormField>
              <FormField label="Current Location">
                <FormControl v-model="form.current_location" placeholder="Enter current location" disabled />
              </FormField>
              <FormField label="Reference Number">
                <FormControl v-model="form.reference_number" placeholder="Enter reference number" disabled />
              </FormField>



             <div>
              <FormField label="Client">
                <FormControl :model-value="clientDisplay" disabled />
              </FormField>
            </div>

              <div class="md:col-span-2">
                <h3 class="text-lg font-semibold mb-4 mt-6">Delivery Information</h3>
              </div>

              <FormField label="Estimated Delivery Date">
                <FormControl v-model="form.estimated_delivery_date" type="text" disabled />
              </FormField>

              <FormField label="Current Status">
                <FormControl :model-value="currentStatusDisplay" disabled />
              </FormField>
              <FormField label="Assign Driver">
                <FormControl :model-value="driverDisplay" disabled />
              </FormField>
               <!-- Helper Information -->
              <FormField label="Helper Name">
                <FormControl v-model="form.helper_name" placeholder="Enter helper name (optional)" disabled />
              </FormField>



              <!-- Driver Information Display -->
              <div v-if="selectedDriver && driverInfo.full_name" class="md:col-span-2">
                <div class="driver-info bg-blue-50 p-3 rounded mt-2">
                  <h5 class="font-medium mb-2 text-blue-800">Driver Information</h5>
                  <div class="grid grid-cols-2 md:grid-cols-3 gap-4 text-sm">
                    <div>
                      <span class="text-gray-600">Name:</span>
                      <span class="ml-2 font-medium">{{ driverInfo.full_name }}</span>
                    </div>
                    <div>
                      <span class="text-gray-600">Mobile:</span>
                      <span class="ml-2 font-medium">{{ driverInfo.mobile_no }}</span>
                    </div>
                    <div v-if="driverInfo.license_no">
                      <span class="text-gray-600">License:</span>
                      <span class="ml-2 font-medium">{{ driverInfo.license_no }}</span>
                    </div>
                    <div v-if="driverInfo.license_type">
                      <span class="text-gray-600">License Type:</span>
                      <span class="ml-2 font-medium">{{ driverInfo.license_type }}</span>
                    </div>
                    <div v-if="driverInfo.vehicle_name">
                      <span class="text-gray-600">Vehicle:</span>
                      <span class="ml-2 font-medium">{{ driverInfo.vehicle_name }}</span>
                    </div>
                  </div>
                </div>
              </div>



              <!-- Time Tracking -->
              <div class="md:col-span-2">
                <h3 class="text-lg font-semibold mb-4 mt-6">Time Tracking</h3>
              </div>

              <FormField label="Call Time">
                <FormControl :model-value="formatDateTime(form.call_time)" type="text" disabled />
              </FormField>

              <!-- Warehouse In: editable when empty, readonly when present -->
              <FormField label="Warehouse In">
                <FormControl v-if="!form.whse_in" v-model="form.whse_in" type="datetime-local" />
                <FormControl v-else :model-value="formatDateTime(form.whse_in)" type="text" disabled />
              </FormField>

              <!-- Loading Start: editable when empty, readonly when present -->
              <FormField label="Loading Start">
                <FormControl v-if="!form.loading_start" v-model="form.loading_start" type="datetime-local" />
                <FormControl v-else :model-value="formatDateTime(form.loading_start)" type="text" disabled />
              </FormField>

              <!-- Loading End: editable when empty, readonly when present -->
              <FormField label="Loading End">
                <FormControl v-if="!form.loading_end" v-model="form.loading_end" type="datetime-local" />
                <FormControl v-else :model-value="formatDateTime(form.loading_end)" type="text" disabled />
              </FormField>

              <!-- Warehouse Out: editable when empty, readonly when present -->
              <FormField label="Warehouse Out">
                <FormControl v-if="!form.whse_out" v-model="form.whse_out" type="datetime-local" />
                <FormControl v-else :model-value="formatDateTime(form.whse_out)" type="text" disabled />
              </FormField>

              <!-- Special Instructions -->
              <div class="md:col-span-2">
                <FormField label="Special Instructions">
                  <FormControl v-model="form.special_instructions" type="textarea" placeholder="Enter special instructions" disabled />
                </FormField>
              </div>


            </div>
          </form>
      </CardBox>


    </div>

    <!-- Customer Selection Modal -->
    <ClientSelectionModal
      v-model="showCustomerModal"
      :selected-customers="selectedCustomers"
      :package-info="packageInfo"
      :client-id="form.client_id"
      @stores-selected="onCustomersSelected"
    />

    <Modal :show="whOutModalVisible" maxWidth="md" @close="whOutModalVisible=false">
      <div class="p-4">
        <div class="flex flex-col">
          <h3>Warehouse OUT</h3>
          <div class="text-sm text-gray-600" v-if="whOutHeaderText">{{ whOutHeaderText }}</div>
        </div>
        <div class="grid grid-cols-1 gap-3">
          <FormField label="Store Time In">
            <FormControl v-model="whOutForm.store_time_in" type="datetime-local" />
          </FormField>
          <FormField label="Unloading Start">
            <FormControl v-model="whOutForm.unloading_start" type="datetime-local" />
          </FormField>
          <FormField label="Unloading End">
            <FormControl v-model="whOutForm.unloading_end" type="datetime-local" />
          </FormField>
          <FormField label="Store Time Out">
            <FormControl v-model="whOutForm.store_time_out" type="datetime-local" />
          </FormField>
          <div class="flex justify-end gap-2 mt-2">
            <BaseButton label="Cancel" color="contrast" @click="whOutModalVisible=false" />
            <BaseButton label="Save" color="info" @click="submitWhOut" />
          </div>
        </div>
      </div>
    </Modal>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed, watch } from 'vue'
import { Head, router, useForm, usePage } from '@inertiajs/vue3'
import miniToastr from 'mini-toastr'
import SectionTitleLineWithButton from '../../../Components/SectionTitleLineWithButton.vue'
import BaseButton from '@/Components/BaseButton.vue'
import BaseIcon from '@/Components/BaseIcon.vue'
import FormField from '@/Components/FormField.vue'
import FormControl from '@/Components/FormControl.vue'
import CardBox from '@/Components/CardBox.vue'
import CoreTable from '@/Components/CoreTable.vue'
import Modal from '@/Components/Modal.vue'
import ClientSelectionModal from '@/Components/tracking/ClientSelectionModal.vue'
import { mdiStore } from '@mdi/js'
miniToastr.init()
import axios from 'axios'
import moment from 'moment'
const props = defineProps({
  action: String,
  data: Object,
  formdata: Object,
  errors: Object,
  clients: Array,
  trackingTypes: {
    type: Array,
    default: () => []
  },
  trackingStatuses: {
    type: Array,
    default: () => []
  }
});

const page = usePage()
// Format date/time to MM/DD/YYYY hh:mm a
const formatDateTime = (val?: string | null) => {
  if (!val) return ''
  const m = moment(val)
  return m.isValid() ? m.format('MM/DD/YYYY hh:mm a') : String(val)
}
const emit = defineEmits(["triggerTopRightButton"])
const isSubmitting = ref(false)

// Form data using Inertia's useForm
const form = useForm({
  _token: page.props.csrf_token,
  id: props.formdata?.id || props.data?.id || null,
  warehouse_id: props.formdata?.warehouse_id || props.data?.warehouse_id || '',
  tracking_number: props.formdata?.tracking_number || props.data?.tracking_number || '',
  reference_number: props.formdata?.reference_number || props.data?.reference_number || '',
  client_id: props.formdata?.client_id || props.data?.client_id || null,
  tracking_type_id: props.formdata?.tracking_type_id || props.data?.tracking_type_id || null,
  estimated_delivery_date: props.formdata?.estimated_delivery_date || props.data?.estimated_delivery_date || '',
  actual_delivery_date: props.formdata?.actual_delivery_date || props.data?.actual_delivery_date || '',
  current_status_id: props.formdata?.current_status_id || props.data?.current_status_id || null,
  current_location: props.formdata?.current_location || props.data?.current_location || '',
  priority_level: props.formdata?.priority_level || props.data?.priority_level || 'Medium',
  total_weight: props.formdata?.total_weight || props.data?.total_weight || null,
  total_volume: props.formdata?.total_volume || props.data?.total_volume || null,
  package_count: props.formdata?.package_count || props.data?.package_count || null,
  special_instructions: props.formdata?.special_instructions || props.data?.special_instructions || '',
  driver_id: props.formdata?.driver_id || props.data?.driver_id || null,
  helper_name: props.formdata?.helper_name || props.data?.helper_name || '',
  call_time: props.formdata?.call_time || props.data?.call_time || '',
  whse_in: props.formdata?.whse_in || props.data?.whse_in || '',
  loading_start: props.formdata?.loading_start || props.data?.loading_start || '',
  loading_end: props.formdata?.loading_end || props.data?.loading_end || '',
  whse_out: props.formdata?.whse_out || props.data?.whse_out || '',
  is_active: props.formdata?.is_active !== undefined ? props.formdata.is_active : (props.data?.is_active !== undefined ? props.data.is_active : true),
  droptrips: props.formdata?.droptrips || props.data?.droptrips || []
})
const formErrors = ref<string[]>([])

const openCustomerModal = () => {
  console.log('openCustomerModal called, form.client_id:', form.client_id)
  if (!form.client_id) {
    miniToastr.error('Please select a client first')
    return
  }
  console.log('Opening modal with client_id:', form.client_id)
  showCustomerModal.value = true
}
// Options
const priorityOptions = ref([
  { value: 'Low', text: 'Low' },
  { value: 'Medium', text: 'Medium' },
  { value: 'High', text: 'High' },
  { value: 'Urgent', text: 'Urgent' }
])

// Computed options for dropdowns
const trackingTypeOptions = computed(() => {
  return (props.trackingTypes || []).map(type => ({
    value: type.id,
    text: type.type_name
  }))
})
const trackingStatusOptions = computed(() => {
  return (props.trackingStatuses || []).map(status => ({
    value: status.id,
    text: status.status_name
  }))
})

// Hydrated header details used as fallback labels
const headerDetails = ref<{ tracking_type?: string; current_status?: string; client_name?: string; driver_name?: string }>({})

// Read-only display labels (support IDs or pre-fetched text fields)
const trackingTypeDisplay = computed(() => {
  if (form.tracking_type_id) {
    const opt = trackingTypeOptions.value.find(o => o.value === form.tracking_type_id)
    if (opt) return opt.text
  }
  return (headerDetails.value.tracking_type || (props.formdata?.tracking_type || props.data?.tracking_type || '')) as string
})

const clientDisplay = computed(() => {
  if (form.client_id) {
    const c = (props.clients || []).find(c => c.id === form.client_id)
    if (c) return c.client_name
  }
  return (headerDetails.value.client_name || (props.formdata?.client_name || props.data?.client_name || '')) as string
})

const currentStatusDisplay = computed(() => {
  if (form.current_status_id) {
    const opt = trackingStatusOptions.value.find(o => o.value === form.current_status_id)
    if (opt) return opt.text
  }
  return (headerDetails.value.current_status || (props.formdata?.current_status || props.data?.current_status || '')) as string
})

const driverDisplay = computed(() => {
  if (driverInfo.value.full_name) return driverInfo.value.full_name
  if (selectedDriver.value) {
    const d = drivers.value.find(dr => dr.value === selectedDriver.value)
    if (d) return d.text
  }
  return (headerDetails.value.driver_name || (props.formdata?.driver_name || props.data?.driver_name || '')) as string
})

// Computed property to group stores by city for rowspan calculation
const groupedStoresByCity = computed(() => {
  const grouped = []
  const cityGroups = new Map()

  // Group stores by city
  selectedCustomers.value.forEach((store, index) => {
    const city = store.city || 'Unknown'
    if (!cityGroups.has(city)) {
      cityGroups.set(city, [])
    }
    cityGroups.get(city).push({ ...store, originalIndex: index })
  })

  // Create grouped array with rowspan information
  cityGroups.forEach((stores, city) => {
    stores.forEach((store, cityIndex) => {
      grouped.push({
        ...store,
        cityRowspan: cityIndex === 0 ? stores.length : 0,
        cityCount: stores.length,
        isFirstInCity: cityIndex === 0
      })
    })
  })

  return grouped
})

// Columns for Store Information Table in Manage view
const storeTableHeaderCore = [
  { label: 'Sequence', fieldName: 'sequence', columnRowClass: 'text-center w-24' },
  { label: 'Trip', fieldName: 'trip', columnRowClass: 'text-center w-24' },
  { label: 'Store Name', fieldName: 'store_name' },
  { label: 'Address', fieldName: 'address' },
  { label: 'Drop', fieldName: 'drops', columnRowClass: 'text-center w-24' },
  { label: 'Actions', fieldName: 'id', type: 'slot', columnRowClass: 'text-center w-32' },
]

const groupedStoresByCityForTable = computed(() => {
  return groupedStoresByCity.value.map((s: any) => ({
    id: s.id,
    sequence: s.sequence,
    trip: s.isFirstInCity ? 1 : '',
    store_name: s.store_name,
    address: s.address,
    drops: s.isFirstInCity ? s.cityCount : '',
  }))
})

// Driver selection
const drivers = ref([])
const selectedDriver = ref(null)

// Customer/Store selection
const selectedCustomers = ref([])
const showCustomerModal = ref(false)

// Package information for modal
const packageInfo = ref({
  quantity: null,
  weight: null,
  value: null
})

// Handle customer selection from modal
const onCustomersSelected = (selection) => {
  // Prevent duplicate stores by filtering out stores that already exist
  const newStores = selection.stores.filter(newStore =>
    !selectedCustomers.value.some(existingStore => existingStore.id === newStore.id)
  )

  // Assign sequence numbers to new stores
  const storesWithSequence = newStores.map((store, index) => ({
    ...store,
    sequence: selectedCustomers.value.length + index + 1
  }))

  // Add only new stores to the existing list
  selectedCustomers.value = [...selectedCustomers.value, ...storesWithSequence]

  // Create droptrip entries for new stores
  const newDroptrips = storesWithSequence.map((store, index) => ({
    trackingclient_id: form.client_id,
    trackingclient_store_id: store.id,
    sqno: selectedCustomers.value.length + index + 1,
    drsino: '',
    store_time_in: '',
    unloading_start: '',
    unloading_end: '',
    store_time_out: '',
    receiver_name: '',
    delivery_status: 'PENDING'
  }))

  // Add new droptrips to form
  form.droptrips = [...form.droptrips, ...newDroptrips]

  // Update form with first selected store data (for backward compatibility)
  if (selectedCustomers.value.length > 0) {
    const firstStore = selectedCustomers.value[0]
    form.customer_id = firstStore.id
    form.customer_name = firstStore.customer_name
    form.customer_phone = firstStore.phone || ''
    form.customer_email = firstStore.email || ''
    form.customer_address = `${firstStore.address_line1 || ''} ${firstStore.address_line2 || ''}`.trim()
  }

  // Show message if duplicates were prevented
  if (newStores.length < selection.stores.length) {
    const duplicateCount = selection.stores.length - newStores.length
    miniToastr.info(`${duplicateCount} duplicate store(s) were not added as they already exist in the list.`)
  }
}

// Update sequence number for a specific customer
const updateSequence = (rowIndex, newSequence) => {
  const customer = selectedCustomers.value[rowIndex]
  const oldSequence = customer.sequence

  // Validate sequence number
  if (newSequence < 1 || newSequence > selectedCustomers.value.length) {
    customer.sequence = oldSequence
    return
  }

  // Check for duplicate sequence numbers
  const existingCustomer = selectedCustomers.value.find((c, index) =>
    index !== rowIndex && c.sequence === newSequence
  )

  if (existingCustomer) {
    // Swap sequence numbers
    existingCustomer.sequence = oldSequence
  }

  customer.sequence = newSequence

  // Sort customers by sequence number
  selectedCustomers.value.sort((a, b) => a.sequence - b.sequence)
}

// Warehouse OUT modal state and actions
const whOutModalVisible = ref(false)
const whOutEditing = ref<{ headerId: number | null, storeId: number | null } | null>({ headerId: null, storeId: null })
const whOutHeaderText = ref('')
const whOutForm = ref({
  store_time_in: '',
  unloading_start: '',
  unloading_end: '',
  store_time_out: '',
})

const openWhOutModal = (storeId: number) => {
  whOutEditing.value = { headerId: form.id, storeId }
  const row = form.droptrips.find((d: any) => d.trackingclient_store_id === storeId)
  whOutForm.value.store_time_in = row?.store_time_in || ''
  whOutForm.value.unloading_start = row?.unloading_start || ''
  whOutForm.value.unloading_end = row?.unloading_end || ''
  whOutForm.value.store_time_out = row?.store_time_out || ''

  const storeInfo = selectedCustomers.value.find((s: any) => s.id === storeId)
  whOutHeaderText.value = storeInfo
    ? `${storeInfo.store_name} — ${storeInfo.address}${storeInfo.city ? ', ' + storeInfo.city : ''}`
    : ''

  whOutModalVisible.value = true
}

const submitWhOut = async () => {
  if (!whOutEditing.value?.headerId || !whOutEditing.value?.storeId) return
  try {
    const url = `/tracking/tracking-event/${whOutEditing.value.headerId}/store/${whOutEditing.value.storeId}/update-times`
    await axios.post(url, { ...whOutForm.value })
    miniToastr.success('Droptrip times updated')
    whOutModalVisible.value = false
    if (form.id) {
      const r = await axios.get(`/tracking/tracker/api/droptrip-summary/${form.id}`)
      const rows = r.data || []
      selectedCustomers.value = rows.map((r: any, idx: number) => ({
        id: r.store_id,
        store_name: r.ClientStore,
        address: r.Address,
        city: r.city,
        sequence: (r.sqno ?? idx + 1),
      }))
      form.droptrips = rows.map((r: any, idx: number) => ({
        id: r.id,
        trackingclient_id: r.trackingclient_id ?? form.client_id,
        trackingclient_store_id: r.store_id,
        sqno: (r.sqno ?? idx + 1),
        drsino: r.drsino ?? '',
        store_time_in: r.store_time_in ?? '',
        unloading_start: r.unloading_start ?? '',
        unloading_end: r.unloading_end ?? '',
        store_time_out: r.store_time_out ?? '',
        receiver_name: r.receiver_name ?? '',
        delivery_status: r.delivery_status ?? 'PENDING'
      }))
      if (!form.client_id && rows.length > 0) {
        form.client_id = rows[0]?.trackingclient_id ?? form.client_id
      }
      headerDetails.value.client_name = rows[0]?.Client || headerDetails.value.client_name
    }
  } catch (e: any) {
    miniToastr.error(e?.response?.data?.message || 'Failed to update droptrip times')
  }
}

// Open WH OUT modal on button click
const isWarehouseOut = (customerId: number) => {
  openWhOutModal(customerId)
}
const driverInfo = ref({
  id: null,
  full_name: '',
  mobile_no: '',
  license_no: '',
  license_type: '',
  vehicle_name: '',
  current_vehicle_id: null
})

// Validation rules
const rules = {
  required: (value: any) => !!value || 'This field is required',
  droptrip: {
    trackingclient_id: (value: any) => !!value || 'Client ID is required',
    trackingclient_store_id: (value: any) => !!value || 'Store ID is required',
    sqno: (value: any) => (value && value > 0) || 'Sequence number must be greater than 0',
    delivery_status: (value: any) => !!value || 'Delivery status is required'
  }
}

// Load drivers from API
const loadDrivers = async () => {
  try {
    const response = await axios.get('/tracking/tracker/api/drivers')
    console.log('Raw drivers response:', response.data)
    drivers.value = response.data.map(driver => ({
      value: driver.id,
      text: driver.full_name,
      id: driver.id,
      full_name: driver.full_name,
      mobile_no: driver.mobile_no,
      license_no: driver.license_no,
      license_type: driver.license_type,
      vehicle_name: driver.vehicle_name,
      current_vehicle_id: driver.current_vehicle_id
    }))
    console.log('Processed drivers:', drivers.value)
  } catch (error) {
    console.error('Error loading drivers:', error)
  }
}

// Handle driver selection
const onDriverSelected = (driverId: number) => {
  const driver = drivers.value.find(d => d.value === driverId)
  if (driver) {
    driverInfo.value = {
      id: driver.id || null,
      full_name: driver.full_name || '',
      mobile_no: driver.mobile_no || '',
      license_no: driver.license_no || '',
      license_type: driver.license_type || '',
      vehicle_name: driver.vehicle_name || '',
      current_vehicle_id: driver.current_vehicle_id || null
    }
    form.driver_id = driverId
  }
}

// Submit form
const submitForm = async () => {
  isSubmitting.value = true

  try {
    const url = form.id ? `/tracking/tracker/${form.id}` : '/tracking/tracker'
    const method = form.id ? 'put' : 'post'

    form[method](url, {
      preserveScroll: true,
      onSuccess: () => {
        miniToastr.success(`Tracking Header successfully ${form.id ? 'updated' : 'created'}!`)
        emit('triggerTopRightButton', 'lists')
      },
      onError: (errors) => {
        formErrors.value = Object.values(errors || {}) as string[]
        miniToastr.error('Please check the form for errors')
      },
      onFinish: () => {
        isSubmitting.value = false
      }
    })
  } catch (error: any) {
    console.error('Error submitting form:', error)
    miniToastr.error(error?.response?.data?.message || 'Error submitting form')
    isSubmitting.value = false
  }
}

// Reset form
const resetForm = () => {
  form.clearErrors()
  form.reset()
  selectedDriver.value = null
  driverInfo.value = {
    id: null,
    full_name: '',
    mobile_no: '',
    license_no: '',
    license_type: '',
    vehicle_name: '',
    current_vehicle_id: null
  }
}

// Watch for changes in formdata prop
watch(() => props.formdata, (newFormdata) => {
  if (newFormdata) {
    Object.keys(newFormdata).forEach(key => {
      if (form.hasOwnProperty(key)) {
        form[key] = newFormdata[key]
      }
    })

    // Set driver selection if driver_id exists
    if (newFormdata.driver_id) {
      selectedDriver.value = newFormdata.driver_id
      onDriverSelected(newFormdata.driver_id)
    }
  }
}, { immediate: true })

// Load data on component mount
onMounted(() => {
  loadDrivers()
  if (form.driver_id) {
    selectedDriver.value = form.driver_id
    onDriverSelected(form.driver_id)
  }
  if (form.id) {
    axios.get(`/tracking/tracker/api/droptrip-summary/${form.id}`)
      .then((resp) => {
        const rows = resp.data || []
        selectedCustomers.value = rows.map((r, idx) => ({
          id: r.store_id,
          store_name: r.ClientStore,
          address: r.Address,
          city: r.city,
          sequence: (r.sqno ?? idx + 1),
        }))
        form.droptrips = rows.map((r, idx) => ({
          id: r.id,
          trackingclient_id: r.trackingclient_id ?? form.client_id,
          trackingclient_store_id: r.store_id,
          sqno: (r.sqno ?? idx + 1),
          drsino: r.drsino ?? '',
          store_time_in: r.store_time_in ?? '',
          unloading_start: r.unloading_start ?? '',
          unloading_end: r.unloading_end ?? '',
          store_time_out: r.store_time_out ?? '',
          receiver_name: r.receiver_name ?? '',
          delivery_status: r.delivery_status ?? 'PENDING'
        }))
        // Derive and set client info from first row if available
        if (!form.client_id && rows.length > 0) {
          form.client_id = rows[0]?.trackingclient_id ?? form.client_id
        }
        headerDetails.value.client_name = rows[0]?.Client || headerDetails.value.client_name
      })
      .catch((err) => {
        console.error('Failed loading droptrip summary:', err)
      })

    axios.get(`/tracking/tracking-event/header/${form.id}`)
      .then((resp) => {
        const h = resp.data || {}
        headerDetails.value.tracking_type = h.tracking_type || headerDetails.value.tracking_type
        headerDetails.value.current_status = h.current_status || headerDetails.value.current_status
        headerDetails.value.driver_name = h.driver_name || headerDetails.value.driver_name
        if (h.driver_id) {
          form.driver_id = h.driver_id
          selectedDriver.value = h.driver_id
          onDriverSelected(h.driver_id)
        }
        form.call_time = h.call_time ?? form.call_time
        form.whse_in = h.whse_in ?? form.whse_in
        form.loading_start = h.loading_start ?? form.loading_start
        form.loading_end = h.loading_end ?? form.loading_end
        form.whse_out = h.whse_out ?? form.whse_out
      // Ensure helper_name is hydrated from header response
      form.helper_name = h.helper_name ?? form.helper_name
      })
      .catch((err) => {
        console.error('Failed loading enriched header details:', err)
      })
  }
})
</script>

<style scoped>
.tracking-create {
  padding: 1rem;
}

.grid {
  display: grid;
}

.grid-cols-1 {
  grid-template-columns: repeat(1, minmax(0, 1fr));
}

@media (min-width: 768px) {
  .md\:grid-cols-2 {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
  .md\:col-span-2 {
    grid-column: span 2 / span 2;
  }
}

.gap-4 {
  gap: 1rem;
}

.gap-6 {
  gap: 1.5rem;
}

.mt-6 {
  margin-top: 1.5rem;
}

.mb-4 {
  margin-bottom: 1rem;
}

.mb-2 {
  margin-bottom: 0.5rem;
}

.ml-2 {
  margin-left: 0.5rem;
}

.p-4 {
  padding: 1rem;
}

.p-3 {
  padding: 0.75rem;
}

.py-8 {
  padding-top: 2rem;
  padding-bottom: 2rem;
}

.border {
  border-width: 1px;
}

.border-gray-200 {
  border-color: #e5e7eb;
}

.rounded-lg {
  border-radius: 0.5rem;
}

.rounded {
  border-radius: 0.25rem;
}

.bg-gray-50 {
  background-color: #f9fafb;
}

.bg-blue-50 {
  background-color: #eff6ff;
}

.text-blue-800 {
  color: #1e40af;
}

.text-center {
  text-align: center;
}

.text-gray-600 {
  color: #4b5563;
}

.text-sm {
  font-size: 0.875rem;
}

.font-medium {
  font-weight: 500;
}

.grid-cols-3 {
  grid-template-columns: repeat(3, minmax(0, 1fr));
}

.items-start {
  align-items: flex-start;
}

.space-y-4 > * + * {
  margin-top: 1rem;
}

.flex {
  display: flex;
}

.justify-end {
  justify-content: flex-end;
}

.gap-2 {
  gap: 0.5rem;
}
</style>
