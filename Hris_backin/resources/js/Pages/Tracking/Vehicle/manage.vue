<template>
  <Head title="Vehicle Management" />
  <TrackingLayout>
    <SectionTitleLineWithButton :icon="'mdiFileDocumentPlus'" :title="(props.action === 'Add' ? 'Add' : 'Manage') + ' Vehicle'" main>
      <BaseButton @click="emit('triggerTopRightButton', 'lists')" :icon="'mdiViewList'" label="Vehicle Lists" color="contrast" rounded-full small />
    </SectionTitleLineWithButton>

    <CardBox>
      <div v-if="formErrors.length" class="mb-4 text-red-600">
        <ul>
          <li v-for="(err, i) in formErrors" :key="i">{{ err }}</li>
        </ul>
      </div>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <FormField label="Vehicle Code">
          <FormControl v-model="form.vehicle_code" />
        </FormField>

        <FormField label="Plate Number">
          <FormControl v-model="form.plate_no" />
        </FormField>

        <FormField label="Vehicle Type">
          <FormControl v-model="form.vehicle_type" />
        </FormField>

        <FormField label="Current Status">
          <FormControl v-model="form.current_status" />
        </FormField>

        <FormField label="Warehouse ID">
          <FormControl v-model="form.warehouse_id" type="number" />
        </FormField>

        <FormField label="Active">
          <Checkbox v-model:checked="form.is_active" />
        </FormField>
      </div>

      <div class="mt-6 flex justify-end gap-2">
        <BaseButton @click="resetForm" :icon="'mdiRefresh'" color="contrast" rounded-full small label="Reset" />
        <BaseButton @click="submit" :icon="'mdiContentSave'" color="info" rounded-full small :label="action === 'Add' ? 'Create Vehicle' : 'Update Vehicle'" />
      </div>
    </CardBox>
  </TrackingLayout>
</template>

<script setup>
import { ref, watch, computed } from 'vue'
import { Head, useForm, usePage } from '@inertiajs/vue3'
import TrackingLayout from '@/Layouts/TrackingLayout.vue';
import SectionTitleLineWithButton from '@/Components/SectionTitleLineWithButton.vue';
import CardBox from '@/Components/CardBox.vue'
import FormField from '@/Components/FormField.vue'
import FormControl from '@/Components/FormControl.vue'
import BaseButton from '@/Components/BaseButton.vue'
import Checkbox from '@/Components/Checkbox.vue'
import service from '@/Components/Toast/service'

const props = defineProps({
  action: String,
  data: Object,
  formdata: Object,
  errors: Object
});

const page = usePage()
const currentUser = page.props.auth?.user

const formRef = ref()

// Compute the action based on whether we have data
const action = computed(() => (props.formdata?.id ? 'Manage' : 'Add'))

// Initialize form with empty values first
const form = useForm({
  _token: page.props.csrf_token,
  id: props.formdata?.id || null,
  branch_id: props.formdata?.branch_id ?? currentUser?.branch_id ?? null,
  vehicle_code: props.formdata?.vehicle_code || '',
  plate_no: props.formdata?.plate_no || '',
  vehicle_type: props.formdata?.vehicle_type || '',
  current_status: props.formdata?.current_status || '',
  warehouse_id: props.formdata?.warehouse_id || null,
  is_active: props.formdata?.is_active !== undefined ? props.formdata.is_active : true
})
const formErrors = ref([])

const emit = defineEmits(["triggerTopRightButton"]);

const normalizeActive = (v) => {
  return v === true || v === 1 || v === '1' || v === 'A' || v === 'ACTIVE'
}

form.is_active = normalizeActive(form.is_active)

watch(() => props.formdata, (newData) => {
  if (newData) {
    Object.keys(newData).forEach(k => {
      if (Object.prototype.hasOwnProperty.call(form, k)) {
        form[k] = k === 'is_active' ? normalizeActive(newData[k]) : newData[k]
      }
    })
  }
}, { immediate: true })
// Watch for changes in formdata prop
watch(() => props.formdata, (newFormdata) => {
  if (newFormdata) {
    form.id = newFormdata.id || null
    form.branch_id = newFormdata.branch_id ?? currentUser?.branch_id ?? null
    form.vehicle_code = newFormdata.vehicle_code || ''
    form.plate_no = newFormdata.plate_no || ''
    form.vehicle_type = newFormdata.vehicle_type || ''
    form.current_status = newFormdata.current_status || ''
    form.warehouse_id = newFormdata.warehouse_id || null
    form.is_active = normalizeActive(newFormdata.is_active)
  }
}, { immediate: true })

const submit = () => {
  formErrors.value = []
  if (!form.branch_id) form.branch_id = currentUser?.branch_id ?? null
  if (!form.vehicle_code) formErrors.value.push('Vehicle code is required')
  if (!form.plate_no) formErrors.value.push('Plate number is required')
  if (!form.vehicle_type) formErrors.value.push('Vehicle type is required')
  if (formErrors.value.length > 0) return

  const url = form.id ? `/tracking/vehicle/${form.id}` : '/tracking/vehicle'
  const method = form.id ? 'put' : 'post'
  const toast = service()

  form[method](url, {
    preserveScroll: true,
    onSuccess: () => {
      toast.success(`Vehicle successfully ${form.id ? 'updated' : 'created'}!`)
      emit('triggerTopRightButton', 'lists')
    },
    onError: (errors) => {
      formErrors.value = Object.values(errors || {})
      toast.error('Failed to save vehicle')
    },
  })
}

// Validation rules
const required = (value) => {
  if (value === null || value === undefined || value === '') {
    return 'This field is required'
  }
  return true
}

const resetForm = () => {
  form.clearErrors()
  form.reset()
  if (formRef.value) {
    formRef.value.resetValidation()
  }
}

// Only watch for deep changes if absolutely necessary
watch(() => props.formdata, (newvalue, oldvalue) => {
  if (newvalue != oldvalue && newvalue) {
    for (let key in newvalue) {
      if (newvalue.hasOwnProperty(key)) {
        form[key] = key === 'is_active' ? normalizeActive(newvalue[key]) : newvalue[key]
      }
    }
  }
});
</script>

<style scoped>
</style>

