<template>
  <div class="tracking-create p-4">
    <div class="flex flex-col gap-6">
      <SectionTitleLineWithButton :icon="'mdiFileDocumentPlus'" :title="(props.action === 'Add' ? 'Add' : 'Manage') + ' Tracker'" main>
        <BaseButton @click="emit('triggerTopRightButton', 'lists')" :icon="'mdiViewList'" label="Request Lists" color="contrast" rounded-full small />
      </SectionTitleLineWithButton>

      <CardBox>
        <FormWizard
          @on-complete="submitForm"
          color="#094899"
          step-size="xs"
          :finish-disabled="!canFinish"
          :next-disabled="(i) => i === 0 && !(form.tracking_type_id && form.client_id)"
        >
          <TabContent title="Tracking Information" icon="mdiFileDocumentPlus">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">


              <FormField label="Tracking Number">
                <FormControl name="tracking_number" v-model="form.tracking_number" placeholder="Leave empty for auto-generation" />
              </FormField>

              <FormField label="Tracking Type">
                <FormControl
                  v-model="form.tracking_type_id"
                  :options="trackingTypeOptions"
                  placeholder="Select tracking type"
                />
              </FormField>
              <FormField :label="currentLocationLabel">
                <FormControl name="current_location" v-model="form.current_location" placeholder="Enter current location" />
              </FormField>
              <FormField label="Reference Number">
                <FormControl name="reference_number" v-model="form.reference_number" placeholder="Enter reference number" />
              </FormField>

              <div class="md:col-span-1">
                <FormField label="Client">
                  <FormControl :options="clientOptions" v-model="clientSelection" placeholder="Select Client" />
                </FormField>
              </div>
            </div>
          </TabContent>

          <TabContent title="Stores & Delivery" icon="mdiFormatListBulleted">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="md:col-span-2">
                <div class="flex justify-between items-center mb-4 mt-6">
                  <h3 class="text-lg font-semibold">Store Information</h3>
                  <BaseButton
                    @click="openCustomerModal"
                    color="info"
                    icon="mdiPlus"
                    small
                    :disabled="!form.client_id"
                    :label="selectedCustomers.length > 0 ? 'Change Stores' : 'Select Stores'"
                  />
                </div>
              </div>

              <div class="md:col-span-2">
                <div v-if="selectedCustomers.length > 0" class="store-table-container">
                  <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                            <tr>
                                <th scope="col" class="px-6 py-3">Sequence</th>
                                <th scope="col" class="px-6 py-3">Trip</th>
                                <th scope="col" class="px-6 py-3">Store Name</th>
                                <th scope="col" class="px-6 py-3">Address</th>
                                <th scope="col" class="px-6 py-3">Drop</th>
                                <th scope="col" class="px-6 py-3 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(row, index) in groupedStoresByCity" :key="index" class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                                <td class="px-6 py-4">
                                    <input
                                        v-model.number="row.sequence"
                                        type="number"
                                        min="1"
                                        :max="selectedCustomers.length"
                                        @input="updateSequence(row.originalIndex, $event)"
                                        class="w-20 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                    />
                                </td>
                                <td class="px-6 py-4">
                                    <div v-if="row.isFirstInCity" class="flex flex-col items-center">
                                        <span class="font-semibold text-lg text-yellow-700">1</span>
                                        <span class="text-sm text-gray-600 dark:text-gray-400">trip</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">{{ row.store_name }}</td>
                                <td class="px-6 py-4">{{ row.address }}</td>
                                <td class="px-6 py-4">
                                    <div v-if="row.isFirstInCity" class="flex flex-col items-center">
                                        <span class="font-semibold text-lg text-green-700">{{ row.cityCount }}</span>
                                        <span class="text-sm text-gray-600 dark:text-gray-400">{{ row.cityCount === 1 ? 'store' : 'stores' }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <BaseButton
                                        @click="removeCustomer(row.id)"
                                        icon="mdiDelete"
                                        color="danger"
                                        small
                                        outline
                                        label="Remove"
                                    />
                                </td>
                            </tr>
                        </tbody>
                    </table>
                  </div>

                  <div v-if="packageInfo.quantity" class="package-info bg-gray-50 dark:bg-gray-800 p-3 rounded mt-4">
                    <h5 class="font-medium mb-2 dark:text-white">Package Information</h5>
                    <div class="grid grid-cols-3 gap-4 text-sm">
                      <div>
                        <span class="text-gray-600 dark:text-gray-400">Quantity:</span>
                        <span class="ml-2 font-medium dark:text-white">{{ packageInfo.quantity }}</span>
                      </div>
                      <div v-if="packageInfo.weight">
                        <span class="text-gray-600 dark:text-gray-400">Weight:</span>
                        <span class="ml-2 font-medium dark:text-white">{{ packageInfo.weight }} kg</span>
                      </div>
                      <div v-if="packageInfo.value">
                        <span class="text-gray-600 dark:text-gray-400">Value:</span>
                        <span class="ml-2 font-medium dark:text-white">${{ packageInfo.value }}</span>
                      </div>
                    </div>
                  </div>
                </div>
                <div v-else class="no-customer-selected text-center py-8 border border-gray-200 dark:border-gray-700 rounded-lg">
                  <BaseIcon path="mdiStore" size="48" class="text-gray-400 mb-4 mx-auto" />
                  <p class="text-gray-600 dark:text-gray-400 mb-4">No stores selected</p>
                  <p class="text-sm text-gray-500 dark:text-gray-500">Click "Select Stores" to add stores to this tracking</p>
                </div>
              </div>
            </div>
          </TabContent>

          <TabContent title="Delivery Information" icon="mdiTruckDelivery">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

              <FormField label="Estimated Delivery Date">
                <FormControl
                  v-model="form.estimated_delivery_date"
                  placeholder="Select estimated delivery date"
                  type="datetime-local"
                />
              </FormField>

              <FormField label="Current Status">
                <FormControl
                  v-model="form.current_status_id"
                  :options="statusOptions"
                  placeholder="Select current status"
                />
              </FormField>


              <FormField label="Priority Level">
                <FormControl
                  v-model="form.priority_level"
                  :options="priorityOptions"
                  placeholder="Select priority level"
                />
              </FormField>

              <FormField label="Assign Driver">
                <FormControl
                  v-model="selectedDriver"
                  :options="driverOptions"
                  placeholder="Select driver (optional)"
                />
              </FormField>

              <div v-if="selectedDriver && driverInfo.full_name" class="md:col-span-2">
                <div class="driver-info bg-blue-50 dark:bg-blue-900/20 p-3 rounded mt-2">
                  <h5 class="font-medium mb-2 text-blue-800 dark:text-blue-300">Driver Information</h5>
                  <div class="grid grid-cols-2 md:grid-cols-3 gap-4 text-sm">
                    <div>
                      <span class="text-gray-600 dark:text-gray-400">Name:</span>
                      <span class="ml-2 font-medium dark:text-gray-200">{{ driverInfo.full_name }}</span>
                    </div>
                    <div>
                      <span class="text-gray-600 dark:text-gray-400">Mobile:</span>
                      <span class="ml-2 font-medium dark:text-gray-200">{{ driverInfo.mobile_no }}</span>
                    </div>
                    <div v-if="driverInfo.license_no">
                      <span class="text-gray-600 dark:text-gray-400">License:</span>
                      <span class="ml-2 font-medium dark:text-gray-200">{{ driverInfo.license_no }}</span>
                    </div>
                    <div v-if="driverInfo.license_type">
                      <span class="text-gray-600 dark:text-gray-400">License Type:</span>
                      <span class="ml-2 font-medium dark:text-gray-200">{{ driverInfo.license_type }}</span>
                    </div>
                    <div v-if="driverInfo.vehicle_name">
                      <span class="text-gray-600 dark:text-gray-400">Vehicle:</span>
                      <span class="ml-2 font-medium dark:text-gray-200">{{ driverInfo.vehicle_name }}</span>
                    </div>
                  </div>
                </div>
              </div>

              <FormField label="Helper Name">
                <FormControl
                  v-model="form.helper_name"
                  placeholder="Enter helper name (optional)"
                  type="text"
                />
              </FormField>

              <div class="md:col-span-2">
                <h3 class="text-lg font-semibold mb-4 mt-6 dark:text-white">Time Tracking</h3>
              </div>

              <FormField label="Call Time">
                <FormControl
                  v-model="form.call_time"
                  placeholder="Select call time"
                  type="datetime-local"
                />
              </FormField>

              <FormField label="Warehouse In">
                <FormControl
                  v-model="form.whse_in"
                  placeholder="Select warehouse in time"
                  type="datetime-local"
                />
              </FormField>

              <FormField label="Loading Start">
                <FormControl
                  v-model="form.loading_start"
                  placeholder="Select loading start time"
                  type="datetime-local"
                />
              </FormField>

              <FormField label="Loading End">
                <FormControl
                  v-model="form.loading_end"
                  placeholder="Select loading end time"
                  type="datetime-local"
                />
              </FormField>

              <FormField label="Warehouse Out">
                <FormControl
                  v-model="form.whse_out"
                  placeholder="Select warehouse out time"
                  type="datetime-local"
                />
              </FormField>

              <div class="md:col-span-2">
                <FormField label="Special Instructions">
                  <FormControl
                    v-model="form.special_instructions"
                    placeholder="Enter special instructions"
                    type="textarea"
                  />
                </FormField>
              </div>
            </div>
          </TabContent>

          <TabContent title="Review" icon="mdiEye">
            <div class="space-y-6">
              <div>
                <h3 class="text-lg font-semibold mb-4 dark:text-white">Tracking Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                  <div>
                    <span class="text-gray-600 dark:text-gray-400">Tracking Number:</span>
                    <span class="ml-2 font-medium dark:text-white">{{ form.tracking_number || '-' }}</span>
                  </div>
                  <div>
                    <span class="text-gray-600 dark:text-gray-400">Tracking Type:</span>
                    <span class="ml-2 font-medium dark:text-white">{{ selectedTrackingTypeName || '-' }}</span>
                  </div>
                  <div>
                    <span class="text-gray-600 dark:text-gray-400">Current Location:</span>
                    <span class="ml-2 font-medium dark:text-white">{{ form.current_location || '-' }}</span>
                  </div>
                  <div>
                    <span class="text-gray-600 dark:text-gray-400">Reference Number:</span>
                    <span class="ml-2 font-medium dark:text-white">{{ form.reference_number || '-' }}</span>
                  </div>
                  <div>
                    <span class="text-gray-600 dark:text-gray-400">Client:</span>
                    <span class="ml-2 font-medium dark:text-white">{{ selectedClientName || '-' }}</span>
                  </div>
                </div>
              </div>

              <div>
                <h3 class="text-lg font-semibold mb-4 dark:text-white">Store & Delivery</h3>
                <div v-if="selectedCustomers.length > 0" class="store-table-container">
                  <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                            <tr>
                                <th scope="col" class="px-6 py-3">Store Name</th>
                                <th scope="col" class="px-6 py-3">Address</th>
                                <th scope="col" class="px-6 py-3">Trip</th>
                                <th scope="col" class="px-6 py-3">Drop</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(row, index) in groupedStoresByCity" :key="index" class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                <td class="px-6 py-4">{{ row.store_name }}</td>
                                <td class="px-6 py-4">{{ row.address }}</td>
                                <td class="px-6 py-4">
                                    <div v-if="row.isFirstInCity" class="flex flex-col items-center">
                                        <span class="font-semibold text-lg text-yellow-700">1</span>
                                        <span class="text-sm text-gray-600 dark:text-gray-400">trip</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div v-if="row.isFirstInCity" class="flex flex-col items-center">
                                        <span class="font-semibold text-lg text-green-700">{{ row.cityCount }}</span>
                                        <span class="text-sm text-gray-600 dark:text-gray-400">{{ row.cityCount === 1 ? 'store' : 'stores' }}</span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                  </div>
                </div>
                <div v-else class="no-customer-selected text-center py-8 border border-gray-200 dark:border-gray-700 rounded-lg">
                  <BaseIcon path="mdiStore" size="48" class="text-gray-400 mb-4 mx-auto" />
                  <p class="text-gray-600 dark:text-gray-400 mb-4">No stores selected</p>
                  <p class="text-sm text-gray-500 dark:text-gray-500">Click "Select Stores" to add stores to this tracking</p>
                </div>
              </div>

              <div>
                <h3 class="text-lg font-semibold mb-4 dark:text-white">Delivery Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                  <div>
                    <span class="text-gray-600 dark:text-gray-400">Estimated Delivery:</span>
                    <span class="ml-2 font-medium dark:text-white">{{ formattedEstimatedDelivery || '-' }}</span>
                  </div>
                  <div>
                    <span class="text-gray-600 dark:text-gray-400">Status:</span>
                    <span class="ml-2 font-medium dark:text-white">{{ selectedStatusName || '-' }}</span>
                  </div>
                  <div>
                    <span class="text-gray-600 dark:text-gray-400">Priority:</span>
                    <span class="ml-2 font-medium dark:text-white">{{ selectedPriorityLabel || '-' }}</span>
                  </div>
                  <div>
                    <span class="text-gray-600 dark:text-gray-400">Driver:</span>
                    <span class="ml-2 font-medium dark:text-white">{{ selectedDriverName || '-' }}</span>
                  </div>
                  <div>
                    <span class="text-gray-600 dark:text-gray-400">Helper:</span>
                    <span class="ml-2 font-medium dark:text-white">{{ form.helper_name || '-' }}</span>
                  </div>
                  <div class="md:col-span-3">
                    <span class="text-gray-600 dark:text-gray-400">Special Instructions:</span>
                    <span class="ml-2 font-medium dark:text-white">{{ form.special_instructions || '-' }}</span>
                  </div>
                </div>
              </div>
            </div>
          </TabContent>
        </FormWizard>
      </CardBox>

    </div>

    <ClientSelectionModal
      v-model="showCustomerModal"
      :selected-customers="selectedCustomers"
      :package-info="packageInfo"
      :client-id="form.client_id"
      @stores-selected="onCustomersSelected"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed, watch } from 'vue'
import { Head, router, useForm, usePage } from '@inertiajs/vue3'
import service from '@/Components/Toast/service'
import FormField from '@/Components/FormField.vue'
import FormControl from '@/Components/FormControl.vue'
import BaseButton from '@/Components/BaseButton.vue'
import BaseIcon from '@/Components/BaseIcon.vue'
import SectionTitleLineWithButton from '@/Components/SectionTitleLineWithButton.vue'
import ClientSelectionModal from '../../../Components/tracking/ClientSelectionModal.vue'
import CardBox from '@/Components/CardBox.vue'
import FormWizard from '@/Components/FormWizard.vue'
import TabContent from '@/Components/TabContent.vue'
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

const toast = service()
const page = usePage()
const emit = defineEmits(["triggerTopRightButton"])
const isSubmitting = ref(false)
const canFinish = computed(() => {
  const hasType = !!form.tracking_type_id
  const hasClient = !!form.client_id
  const hasStores = selectedCustomers.value.length > 0
  return hasType && hasClient && hasStores
})

const clientOptions = computed(() => {
  return (props.clients || []).map((c: any) => ({ id: c.id, label: c.client_name }))
})
const clientSelection = ref<any>(null)
watch(clientSelection, (val) => {
  if (val && typeof val === 'object') {
    form.client_id = val.id
  } else {
    form.client_id = val as any
  }
})

// Form data using Inertia's useForm
const form = useForm({
  _token: page.props.csrf_token,
  id: props.formdata?.id || props.data?.id || null,
  branch_id: props.formdata?.branch_id || props.data?.branch_id || '',
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
  customer_id: null,
  customer_name: '',
  customer_phone: '',
  customer_email: '',
  customer_address: '',
  droptrips: props.formdata?.droptrips || props.data?.droptrips || []
})
const formErrors = ref<string[]>([])

const openCustomerModal = () => {
  console.log('openCustomerModal called, form.client_id:', form.client_id)
  if (!form.client_id) {
    toast.error('Please select a client first')
    return
  }
  console.log('Opening modal with client_id:', form.client_id)
  showCustomerModal.value = true
}
// Options
const priorityOptions = ref([
  { id: 'Low', label: 'Low' },
  { id: 'Medium', label: 'Medium' },
  { id: 'High', label: 'High' },
  { id: 'Urgent', label: 'Urgent' }
])

// Computed options for dropdowns
const trackingTypeOptions = computed(() => {
  return (props.trackingTypes || []).map(type => ({
    id: type.id,
    label: type.type_name
  }))
})
const statusOptions = computed(() => {
  return (props.trackingStatuses || []).map(status => ({
    id: status.id,
    label: status.status_name
  }))
})

const currentLocationLabel = computed(() => {
  const v: any = form.tracking_type_id as any
  let label = ''
  if (v && typeof v === 'object') {
    label = v.label || ''
  } else {
    const selected = trackingTypeOptions.value.find(option => option.id === v)
    label = selected?.label || ''
  }
  const base = label ? label.replace(/\s*Tracking\s*$/i, '') : ''
  return base ? `${base} Location` : 'Current Location'
})
const selectedTrackingTypeName = computed(() => {
  const v: any = form.tracking_type_id as any
  if (v && typeof v === 'object') return v.label || ''
  return trackingTypeOptions.value.find(o => o.id === v)?.label || ''
})
const selectedStatusName = computed(() => {
  const v: any = form.current_status_id as any
  if (v && typeof v === 'object') return v.label || ''
  return statusOptions.value.find(o => o.id === v)?.label || String(v || '')
})
const selectedPriorityLabel = computed(() => {
  const v: any = form.priority_level as any
  if (v && typeof v === 'object') return v.label || ''
  return priorityOptions.value.find(o => o.id === v)?.label || String(v || '')
})
const selectedClientName = computed(() => {
  if (clientSelection.value && typeof clientSelection.value === 'object') return clientSelection.value.label
  const opt = clientOptions.value.find(c => c.id === form.client_id)
  return opt?.label || ''
})
const selectedDriverName = computed(() => {
  const v: any = selectedDriver.value
  if (v && typeof v === 'object') return v.label || ''
  const d = drivers.value.find(dr => (dr.value ?? dr.id) === v)
  return d?.text ?? d?.full_name ?? String(v || '')
})
const formattedEstimatedDelivery = computed(() => {
  const val = form.estimated_delivery_date
  if (!val) return ''
  const m = moment(val)
  return m.isValid() ? m.format('MMMM DD, YYYY') : String(val)
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

// Driver selection
const drivers = ref<any[]>([])
const selectedDriver = ref<any>(null)
const driverOptions = computed(() => drivers.value.map(d => ({ id: d.value ?? d.id, label: d.text ?? d.full_name })))

// Customer/Store selection
const selectedCustomers = ref<any[]>([])
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
    toast.info(`${duplicateCount} duplicate store(s) were not added as they already exist in the list.`)
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

// Remove customer from selection
const removeCustomer = (customerId) => {
  selectedCustomers.value = selectedCustomers.value.filter(customer => customer.id !== customerId)

  // Remove corresponding droptrip entries
  form.droptrips = form.droptrips.filter(droptrip => droptrip.trackingclient_store_id !== customerId)

  // Reassign sequence numbers after removal
  selectedCustomers.value.forEach((customer, index) => {
    customer.sequence = index + 1
  })

  // Update sequence numbers in droptrips
  form.droptrips.forEach((droptrip, index) => {
    droptrip.sqno = index + 1
  })
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

  } catch (error) {
    console.error('Error loading drivers:', error)
  }
}

// Handle driver selection via watch to support object or id
watch(selectedDriver, (val) => {
  let id: any = null
  if (val && typeof val === 'object') id = val.id
  else id = val
  const driver = drivers.value.find(d => (d.value ?? d.id) === id)
  if (driver) {
    driverInfo.value = {
      id: driver.id || id || null,
      full_name: driver.full_name || '',
      mobile_no: driver.mobile_no || '',
      license_no: driver.license_no || '',
      license_type: driver.license_type || '',
      vehicle_name: driver.vehicle_name || '',
      current_vehicle_id: driver.current_vehicle_id || null
    }
    form.driver_id = id
  } else {
    form.driver_id = id
  }
})

// Submit form
const submitForm = async () => {
  isSubmitting.value = true

  try {
    const url = form.id ? `/tracking/tracker/${form.id}` : '/tracking/tracker'
    const method = form.id ? 'put' : 'post'

    if (form.client_id && typeof form.client_id === 'object') {
      // Normalize client_id if bound to object via FormControl
      form.client_id = (form.client_id as any).id
    }

    if (form.priority_level && typeof form.priority_level === 'object') {
      form.priority_level = (form.priority_level as any).id ?? (form.priority_level as any).label
    }
    if (form.current_status_id && typeof form.current_status_id === 'object') {
      form.current_status_id = (form.current_status_id as any).id ?? (form.current_status_id as any).label
    }
    if (form.driver_id && typeof form.driver_id === 'object') {
      form.driver_id = (form.driver_id as any).id
    }

    form[method](url, {
      preserveScroll: true,
      onSuccess: () => {
        toast.success(`Tracking Header successfully ${form.id ? 'updated' : 'created'}!`)
        emit('triggerTopRightButton', 'lists')
      },
      onError: (errors) => {
        formErrors.value = Object.values(errors || {}) as string[]
        toast.error('Please check the form for errors')
      },
      onFinish: () => {
        isSubmitting.value = false
      }
    })
  } catch (error: any) {
    console.error('Error submitting form:', error)
    toast.error(error?.response?.data?.message || 'Error submitting form')
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

  // Set initial driver selection if editing
  if (form.driver_id) {
    selectedDriver.value = form.driver_id
    // onDriverSelected(form.driver_id) // This function doesn't exist, logic handled in watcher? Or should be defined?
    // Looking at original code, onDriverSelected is not defined but called in watch and onMounted.
    // However, the logic is in the watch(selectedDriver).
    // Let's rely on the watcher by setting selectedDriver.value
  }

  // If editing an existing header, pre-load droptrip summary and hydrate UI
  if (form.id) {
    axios.get(`/tracking/tracker/api/droptrip-summary/${form.id}`)
      .then((resp) => {
        const rows = resp.data || []
        // Build selectedCustomers entries from rows
        selectedCustomers.value = rows.map((r, idx) => ({
          id: r.store_id,
          store_name: r.ClientStore,
          address: r.Address,
          city: r.city,
          sequence: (r.sqno ?? idx + 1),
        }))

        // Build form.droptrips consistent with backend
        form.droptrips = rows.map((r, idx) => ({
          id: r.id,
          trackingclient_id: r.trackingclient_id ?? form.client_id,
          trackingclient_store_id: r.store_id,
          sqno: (r.sqno ?? idx + 1),
          drsino: '',
          store_time_in: '',
          unloading_start: '',
          unloading_end: '',
          store_time_out: '',
          receiver_name: '',
          delivery_status: 'PENDING'
        }))
      })
      .catch((err) => {
        console.error('Failed loading droptrip summary:', err)
      })
  }
})
</script>

<style scoped>
/* Scoped styles removed as they were duplicating Tailwind classes */
</style>
