<template>
  <div class="details-container">
    <div class="details-content">
      <div class="detail-section">

<FormField label="Trackin Number">
    <FormControl readonly name="tracking_number" v-model="localHeader.tracking_number" type="text" />
</FormField>
      <FormField label="Reference Number">
        <FormControl readonly name="reference_number" v-model="localHeader.reference_number" type="text" />
      </FormField>


<FormField label="Tracking Type">
        <FormControl readonly name="tracking_type" v-model="localHeader.tracking_type" type="text" />
</FormField>

<FormField label="Current Status">
        <FormControl readonly name="current_status" v-model="localHeader.current_status" type="text" />
</FormField>

      </div>
      <div class="detail-section">
        <h3 class="section-title">Store Information</h3>

        <div v-if="groupedStoresByCity.length > 0" class="store-table-container overflow-x-auto">
          <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
              <tr>
                <th scope="col" class="px-6 py-3">Trip</th>
                <th scope="col" class="px-6 py-3">Client</th>
                <th scope="col" class="px-6 py-3">Consignee/Store</th>
                <th scope="col" class="px-6 py-3">Address</th>
                <th scope="col" class="px-6 py-3">Drops</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(row, index) in groupedStoresByCity" :key="index" class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                <td class="px-6 py-4">
                  <div v-if="row.isFirstInCity" class="flex flex-col items-center bg-green-200 px-2 py-2 rounded w-16">
                    <span class="font-semibold text-lg text-green-800">1</span>
                    <span class="text-xs text-gray-700">TRIP</span>
                  </div>
                </td>
                <td class="px-6 py-4">
                  {{ row.Client || '-' }}
                </td>
                <td class="px-6 py-4">
                  {{ row.ClientStore }}
                </td>
                <td class="px-6 py-4">
                  {{ row.Address }}
                </td>
                <td class="px-6 py-4">
                  <div v-if="row.isFirstInCity" class="flex flex-col items-center bg-green-50 px-2 py-2 rounded w-16">
                    <span class="font-semibold text-lg text-green-700">{{ row.cityCount }}</span>
                    <span class="text-xs text-gray-600">{{ row.cityCount === 1 ? 'drop' : 'drops' }}</span>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div v-else class="text-sm text-gray-500">No store information found for this event.</div>
      </div>

      <div class="detail-section">
        <h3 class="section-title">Location & Delivery</h3>
<FormField label="Current Location">
        <FormControl name="current_location" v-model="localHeader.current_location" type="text" />
</FormField>
     <FormField label="Priority Level">
        <FormControl name="priority_level" v-model="localHeader.priority_level" type="text" />
     </FormField>

       <FormField label="Total Weight">
        <FormControl readonly name="total_weight" v-model="formattedEstimatedDelivery" type="text" />
     </FormField>


      </div>





<FormField label="Special Instructions">
        <FormControl name="special_instructions" v-model="localHeader.special_instructions" type="textarea" />
</FormField>



      <div class="detail-section">
        <h3 class="section-title">Status</h3>

        <label class="flex items-center space-x-2 mb-3">
            <Checkbox v-model:checked="localHeader.is_active" :value="true" />
            <span class="text-sm text-gray-700">Active</span>
        </label>
      </div>
    </div>

    <div class="details-actions">
      <BaseButtons>
        <BaseButton :icon="'mdinav'" color="info" rounded-full small label="manage" @click="manageHeader" />
        <BaseButton :icon="'mdiClose'" color="danger" rounded-full small label="Cancel" @click="closeDetails" />
      </BaseButtons>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, computed } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import moment from 'moment'
import axios from 'axios'
import BaseButton from '@/Components/BaseButton.vue'
import BaseButtons from '@/Components/BaseButtons.vue'
import CardBox from '@/Components/CardBox.vue'
import FormField from '@/Components/FormField.vue'
import FormControl from '@/Components/FormControl.vue'
import Checkbox from '@/Components/Checkbox.vue'


const props = defineProps({
  data: {
    type: Object,
    required: true
  },
  trackingTypes: {
    type: Array,
    default: () => []
  },
  trackingStatuses: {
    type: Array,
    default: () => []
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
  branch_id: '',
  tracking_number: '',
  reference_number: '',
  tracking_type_id: '',
  estimated_delivery_date: '',
  actual_delivery_date: '',
  current_status_id: '',
  current_location: '',
  priority_level: '',
  total_weight: '',
  total_volume: '',
  package_count: '',
  special_instructions: '',
  driver_id: '',
  helper_name: '',
  call_time: '',
  whse_in: '',
  loading_start: '',
  loading_end: '',
  whse_out: '',
  is_active: true,
})

const localHeader = ref({
  id: null,
  warehouse_id: '',
  tracking_number: '',
  reference_number: '',
  tracking_type: '',
  current_status: '',
  estimated_delivery_date: '',
  actual_delivery_date: '',
  current_location: '',
  priority_level: '',
  total_weight: '',
  total_volume: '',
  package_count: '',
  special_instructions: '',
  driver_id: '',
  helper_name: '',
  call_time: '',
  whse_in: '',
  loading_start: '',
  loading_end: '',
  whse_out: '',
  is_active: true,
})

// Computed property for formatted estimated delivery date
const formattedEstimatedDelivery = computed(() => {
  if (localHeader.value.estimated_delivery_date) {
    return moment(localHeader.value.estimated_delivery_date).format('MMMM DD, YYYY')
  }
  return '-'
})

// Droptrip summary rows for this header
const droptripRows = ref([])

// Pickup location derived from header
const pickupLocation = computed(() => localHeader.value.current_location || '-')

// Plate selections per city (if needed in future)
const selectedPlate = ref({})
const plateOptionsByCity = computed(() => {
  const map = {}
  droptripRows.value.forEach(r => {
    const city = r.city || 'Unknown'
    if (!map[city]) map[city] = []
    if (r.Plateno && !map[city].includes(r.Plateno)) map[city].push(r.Plateno)
  })
  return map
})

// Group rows by city and calculate rowspan and counts
const groupedStoresByCity = computed(() => {
  const grouped = []
  const cityGroups = new Map()

  droptripRows.value.forEach((r, idx) => {
    const city = r.city || 'Unknown'
    if (!cityGroups.has(city)) cityGroups.set(city, [])
    cityGroups.get(city).push({ ...r, originalIndex: idx })
  })

  cityGroups.forEach((rows, city) => {
    rows.forEach((row, cityIndex) => {
      grouped.push({
        ...row,
        cityRowspan: cityIndex === 0 ? rows.length : 0,
        cityCount: rows.length,
        isFirstInCity: cityIndex === 0,
      })
    })
  })

  return grouped
})

// Watch for changes in props.data
watch(() => props.data, (newData) => {
  if (newData) {
    localHeader.value = { ...newData }

    // Update form data
    form.id = newData.id
    form.branch_id = newData.branch_id || ''
    form.tracking_number = newData.tracking_number || ''
    form.reference_number = newData.reference_number || ''
    form.tracking_type_id = newData.tracking_type_id || ''
    form.estimated_delivery_date = newData.estimated_delivery_date || ''
    form.actual_delivery_date = newData.actual_delivery_date || ''
    form.current_status_id = newData.current_status_id || ''
    form.current_location = newData.current_location || ''
    form.priority_level = newData.priority_level || ''
    form.total_weight = newData.total_weight || ''
    form.total_volume = newData.total_volume || ''
    form.package_count = newData.package_count || ''
    form.special_instructions = newData.special_instructions || ''
    form.driver_id = newData.driver_id || ''
    form.helper_name = newData.helper_name || ''
    form.call_time = newData.call_time || ''
    form.whse_in = newData.whse_in || ''
    form.loading_start = newData.loading_start || ''
    form.loading_end = newData.loading_end || ''
    form.whse_out = newData.whse_out || ''
    form.is_active = newData.is_active !== undefined ? newData.is_active : true

    // Hydrate enriched header details (driver + time fields), then droptrip summary
    if (newData.id) {
      axios.get(`/tracking/tracking-event/header/${newData.id}`)
        .then((resp) => {
          const h = resp.data || {}
          localHeader.value = { ...localHeader.value, ...h }
          form.driver_id = h.driver_id ?? form.driver_id
          form.call_time = h.call_time ?? form.call_time
          form.whse_in = h.whse_in ?? form.whse_in
          form.loading_start = h.loading_start ?? form.loading_start
          form.loading_end = h.loading_end ?? form.loading_end
          form.whse_out = h.whse_out ?? form.whse_out
        })
        .catch((err) => {
          console.error('Failed loading header details:', err)
        })

      axios.get(`/tracking/tracker/api/droptrip-summary/${newData.id}`)
        .then((resp) => {
          droptripRows.value = resp.data || []
        })
        .catch((err) => {
          console.error('Failed loading droptrip summary:', err)
          droptripRows.value = []
        })
    } else {
      droptripRows.value = []
    }
  }
}, { immediate: true })

const closeDetails = () => {
  emit('close')
}

const manageHeader = () => {
  // Navigate to manage page with form data
  emit('triggerTopRightButton', 'Manage', localHeader.value);
}
</script>

