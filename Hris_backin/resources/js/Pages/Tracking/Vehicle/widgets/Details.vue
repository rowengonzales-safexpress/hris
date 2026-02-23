<template>
  <div class="h-full flex flex-col bg-white dark:bg-gray-800">
    <div class="flex justify-between items-center pb-4 border-b border-gray-100 dark:border-gray-700 mb-4">
      <h2 class="text-xl font-semibold text-gray-800 dark:text-white">Vehicle Details</h2>
      <button 
        @click="closeDetails"
        class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition-colors p-1 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700"
      >
        <BaseIcon :path="mdiClose" size="24" />
      </button>
    </div>

    <div class="flex-1 overflow-y-auto">
      <div class="mb-6">
        <h3 class="text-base font-medium text-blue-600 dark:text-blue-400 border-b border-gray-100 dark:border-gray-700 pb-1 mb-3">Basic Information</h3>

        <FormField label="Vehicle Code">
          <FormControl
            v-model="localVehicle.vehicle_code"
            readonly
            class="mb-3"
          />
        </FormField>

        <FormField label="Plate Number">
          <FormControl
            v-model="localVehicle.plate_no"
            readonly
            class="mb-3"
          />
        </FormField>

        <FormField label="Vehicle Type">
          <FormControl
            v-model="localVehicle.vehicle_type"
            readonly
            class="mb-3"
          />
        </FormField>

        <FormField label="Current Status">
          <FormControl
            v-model="localVehicle.current_status"
            readonly
            class="mb-3"
          />
        </FormField>

        <FormField label="Warehouse ID">
          <FormControl
            v-model="localVehicle.warehouse_id"
            readonly
            class="mb-3"
          />
        </FormField>
      </div>

      <div class="mb-6">
        <h3 class="text-base font-medium text-blue-600 dark:text-blue-400 border-b border-gray-100 dark:border-gray-700 pb-1 mb-3">Status</h3>

        <FormField label="Active">
           <div class="flex items-center h-10">
              <span 
                class="px-2 py-1 rounded text-xs font-semibold"
                :class="localVehicle.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
              >
                {{ localVehicle.is_active ? 'Yes' : 'No' }}
              </span>
           </div>
        </FormField>
      </div>
    </div>

    <div class="pt-4 border-t border-gray-100 dark:border-gray-700 mt-auto">
      <BaseButtons>
        <BaseButton :icon="mdiPencil" color="info" rounded-full small label="Manage" @click="manageVehicle" />
        <BaseButton :icon="mdiClose" color="danger" rounded-full small label="Cancel" @click="closeDetails" />
      </BaseButtons>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import { useForm } from '@inertiajs/vue3'
import BaseButton from '@/Components/BaseButton.vue'
import BaseButtons from '@/Components/BaseButtons.vue'
import BaseIcon from '@/Components/BaseIcon.vue'
import FormField from '@/Components/FormField.vue'
import FormControl from '@/Components/FormControl.vue'
import { mdiClose, mdiPencil } from '@mdi/js'

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
  branch_id: null,
  vehicle_code: '',
  plate_no: '',
  vehicle_type: '',
  current_status: '',
  warehouse_id: null,
  is_active: true,
})

const localVehicle = ref({
  id: null,
  branch_id: null,
  vehicle_code: '',
  plate_no: '',
  vehicle_type: '',
  current_status: '',
  warehouse_id: null,
  is_active: true,
})

// Watch for changes in props.data
watch(() => props.data, (newData) => {
  if (newData) {
    localVehicle.value = { ...newData }

    // Update form data
    form.id = newData.id
    form.branch_id = newData.branch_id || null
    form.vehicle_code = newData.vehicle_code || ''
    form.plate_no = newData.plate_no || ''
    form.vehicle_type = newData.vehicle_type || ''
    form.current_status = newData.current_status || ''
    form.warehouse_id = newData.warehouse_id || null
    form.is_active = newData.is_active || true
  }
}, { immediate: true })

const closeDetails = () => {
  emit('close')
}

const manageVehicle = () => {
  // Navigate to manage page with form data
  emit('triggerTopRightButton', 'Manage', localVehicle.value);
}
</script>

<style scoped>
/* Scoped styles replaced by Tailwind classes */
</style>
