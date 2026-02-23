<template>
  <Modal
    :show="isVisible"
    maxWidth="2xl"
    @close="handleClose"
  >
    <div class="p-6">
      <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100 mb-4">Select Store</h3>
      <div class="store-modal-container">
        <!-- Search Section -->


        <!-- Store List Section -->
        <div class="store-list-section mb-6">

            <CoreTable
              :table-rows="filteredStores"
              :table-header="storeTableHeader"
              table-name="stores-select"
              searchable-fields="store_code,store_name,client_name,city,contact_phone,contact_email,address"
              :is-paginated="true"
              :is-show-row-checkbox="true"
              :show-total="false"
              :show-page-info="false"
              :show-download-csv="false"
              @checkRows="onCheckedRows"
              class="store-data-table"
            />

        </div>

        <!-- Modal Footer -->
        <div class="modal-footer">
          <BaseButton
            @click="handleClose"
            color="contrast"
            label="Cancel"
            class="mr-3"
          />
          <BaseButton
            @click="confirmSelection"
            color="info"
            :disabled="selectedStores.length === 0"
            :label="`OK (${selectedStores.length} selected)`"
          />
        </div>
      </div>
    </div>
  </Modal>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'
import miniToastr from 'mini-toastr'
import FormControl from '@/Components/FormControl.vue'
import CoreTable from '@/Components/CoreTable.vue'
import BaseButton from '@/Components/BaseButton.vue'
import Modal from '@/Components/Modal.vue'

export interface TrackingClientStoreAddress {
  id: number
  client_id: number
  store_code: string
  store_name: string
  address: string
  city: string
  state: string
  postal_code: string
  country: string
  contact_person: string
  contact_phone: string
  contact_email: string
  latitude?: number
  longitude?: number
  is_active: boolean
  created_at: string
  updated_at: string
  client_name?: string
  creator?: string
}

interface PackageInfo {
  quantity: number | null
  weight: number | null
  value: number | null
}

interface StoreSelection {
  stores: TrackingClientStoreAddress[]
  packageInfo: PackageInfo
}

const props = defineProps<{
  modelValue: boolean
  selectedStore?: TrackingClientStoreAddress | null
  packageInfo?: PackageInfo
  clientId: number
}>()

const emit = defineEmits<{
  'update:modelValue': [value: boolean]
  'stores-selected': [selection: StoreSelection]
}>()

// Reactive data
const isVisible = computed({
  get: () => props.modelValue,
  set: (value) => emit('update:modelValue', value)
})

const stores = ref<TrackingClientStoreAddress[]>([])
const filteredStores = ref<TrackingClientStoreAddress[]>([])
const selectedStores = ref<TrackingClientStoreAddress[]>([])
const selectedStore = ref<TrackingClientStoreAddress | null>(props.selectedStore || null)
const searchQuery = ref('')
const isLoading = ref(false)
const showAddStoreForm = ref(false)
const editingStore = ref<TrackingClientStoreAddress | null>(null)

const packageInfo = ref<PackageInfo>({
  quantity: props.packageInfo?.quantity || null,
  weight: props.packageInfo?.weight || null,
  value: props.packageInfo?.value || null
})

// CoreTable header
const storeTableHeader = [
  { label: 'Store Code', fieldName: 'store_code' },
  { label: 'Store Name', fieldName: 'store_name' },
  { label: 'Client', fieldName: 'client_name' },
  { label: 'Email', fieldName: 'contact_email' },
  { label: 'Phone', fieldName: 'contact_phone' },
  { label: 'City', fieldName: 'city' },
  { label: 'Status', fieldName: 'is_active', type: 'activeinactive' },
]

// Validation rules
const rules = {
  required: (value: any) => !!value || 'This field is required',
  minValue: (value: any) => (value && value > 0) || 'Must be greater than 0'
}

// Methods
const loadStores = async () => {
  console.log('loadStores called with clientId:', props.clientId)

  if (!props.clientId) {
    console.log('No clientId provided, clearing stores')
    stores.value = []
    filteredStores.value = []
    return
  }

  isLoading.value = true
  try {
    const response = await fetch(`/tracking/store/api/by-client/${props.clientId}`, {
    })
    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`)
    }

    const data = await response.json()
    stores.value = data || []
    filteredStores.value = stores.value
  } catch (error) {
    miniToastr.error('Error loading stores')
    stores.value = []
    filteredStores.value = []
  } finally {
    isLoading.value = false
  }
}

const searchStores = () => {
  if (!searchQuery.value.trim()) {
    filteredStores.value = stores.value
    return
  }

  const query = searchQuery.value.toLowerCase()
  filteredStores.value = stores.value.filter(store =>
    store.store_code.toLowerCase().includes(query) ||
    store.store_name.toLowerCase().includes(query) ||
    (store.client_name && store.client_name.toLowerCase().includes(query)) ||
    (store.contact_email && store.contact_email.toLowerCase().includes(query))
  )
}

const onCheckedRows = (rows: TrackingClientStoreAddress[]) => {
  selectedStores.value = rows || []
}

const editStore = (store: TrackingClientStoreAddress) => {
  editingStore.value = store
  showAddStoreForm.value = true
}

const removeStore = async (store: TrackingClientStoreAddress) => {
  try {
    // Note: Implement delete method in service if needed
    // await deleteRecord(store)
    await loadStores()
    miniToastr.success('Store removed successfully')
  } catch (error) {
    console.error('Error removing store:', error)
    miniToastr.error('Error removing store')
  }
}

const handleStoreSave = async (storeData: any) => {
  try {
    if (editingStore.value) {
      // Note: Implement update method in service if needed
      // await updateRecord(storeData)
      miniToastr.success('Store updated successfully')
    } else {
      // Note: Implement create method in service if needed
      // await addRecord(storeData)
      miniToastr.success('Store added successfully')
    }
    await loadStores()
    closeStoreForm()
  } catch (error: any) {
    console.error('Error saving store:', error)
    miniToastr.error(error?.response?.data?.message || 'Error saving store')
  }
}

const closeStoreForm = () => {
  showAddStoreForm.value = false
  editingStore.value = null
}

const confirmSelection = () => {
  if (selectedStores.value.length > 0) {
    emit('stores-selected', {
      stores: selectedStores.value,
      packageInfo: packageInfo.value
    })
    handleClose()
  }
}

const handleClose = () => {
  isVisible.value = false
}

// Watch for modal visibility changes
watch(isVisible, (newValue) => {
  if (newValue && props.clientId) {
    loadStores()
    // Clear previous selections when modal opens
    selectedStores.value = []
  }
})

// Watch for clientId changes
watch(() => props.clientId, (newClientId) => {
  if (newClientId && isVisible.value) {
    loadStores()
    selectedStores.value = []
  }
})

// Initialize on mount
onMounted(() => {
  if (props.selectedStore) {
    selectedStores.value = [props.selectedStore]
  }
})
</script>

<style scoped>
.store-modal-container {
  min-height: 500px;
  display: flex;
  flex-direction: column;
}

.search-section {
  flex-shrink: 0;
}

.store-list-section {
  flex: 1;
  min-height: 0;
}

.store-table-container {
  height: 400px;
  overflow-y: auto;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
}

.store-data-table {
  height: 100%;
}

.modal-footer {
  flex-shrink: 0;
  display: flex;
  justify-content: flex-end;
  align-items: center;
  padding-top: 1.5rem;
  border-top: 1px solid #e5e7eb;
  margin-top: 1.5rem;
}

.w-full {
  width: 100%;
}

.mb-6 {
  margin-bottom: 1.5rem;
}

.mr-3 {
  margin-right: 0.75rem;
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .store-modal-container {
    min-height: 400px;
  }

  .store-table-container {
    height: 300px;
  }
}
</style>
