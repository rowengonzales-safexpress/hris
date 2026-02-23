<template>
  <Head title="Driver Management" />
  <TrackingLayout>
    <SectionTitleLineWithButton :icon="'mdiFileDocumentPlus'" :title="(props.action === 'Add' ? 'Add' : 'Manage') + ' Driver'" main>
      <BaseButton @click="emit('triggerTopRightButton', 'lists')" :icon="'mdiViewList'" label="Driver Lists" color="contrast" rounded-full small />
    </SectionTitleLineWithButton>

    <CardBox>
      <div v-if="formErrors.length" class="mb-4 text-red-600">
        <ul>
          <li v-for="(err, i) in formErrors" :key="i">{{ err }}</li>
        </ul>
      </div>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <FormField label="Driver Code">
          <FormControl v-model="form.driver_code" />
        </FormField>

        <FormField label="First Name">
          <FormControl v-model="form.first_name" />
        </FormField>

        <FormField label="Last Name">
          <FormControl v-model="form.last_name" />
        </FormField>

        <FormField label="Mobile No">
          <FormControl v-model="form.mobile_no" />
        </FormField>

        <FormField label="Email">
          <FormControl v-model="form.email" type="email" />
        </FormField>

        <FormField label="License No">
          <FormControl v-model="form.license_no" />
        </FormField>

        <FormField label="License Type">
          <FormControl v-model="form.license_type" />
        </FormField>

        <FormField label="License Expiry">
          <FormControl v-model="form.license_expiry" type="date" />
        </FormField>

        <FormField label="Emergency Contact No">
          <FormControl v-model="form.emergency_contact_no" />
        </FormField>

        <FormField label="Active">
          <Checkbox v-model:checked="form.is_active" />
        </FormField>
      </div>

      <div class="mt-6 flex justify-end gap-2">
        <BaseButton @click="resetForm" :icon="'mdiRefresh'" color="contrast" rounded-full small label="Reset" />
        <BaseButton @click="submit" :icon="'mdiContentSave'" color="info" rounded-full small :label="action === 'Add' ? 'Create Driver' : 'Update Driver'" />
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
  formdata: Object
})

const emit = defineEmits(['triggerTopRightButton'])

const page = usePage()
const currentUser = page.props.auth?.user

const action = computed(() => (props.formdata?.id ? 'Manage' : 'Add'))

const form = useForm({
  _token: page.props.csrf_token,
  id: props.formdata?.id || null,
  branch_id: props.formdata?.branch_id ?? currentUser?.branch_id ?? null,
  driver_code: props.formdata?.driver_code || '',
  first_name: props.formdata?.first_name || '',
  last_name: props.formdata?.last_name || '',
  mobile_no: props.formdata?.mobile_no || '',
  email: props.formdata?.email || '',
  license_no: props.formdata?.license_no || '',
  license_type: props.formdata?.license_type || '',
  license_expiry: props.formdata?.license_expiry || '',
  emergency_contact_no: props.formdata?.emergency_contact_no || '',
  is_active: props.formdata?.is_active !== undefined ? props.formdata.is_active : true,
})

const formErrors = ref([])

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

const submit = () => {
  formErrors.value = []
  if (!form.branch_id) form.branch_id = currentUser?.branch_id ?? null
  if (!form.driver_code) formErrors.value.push('Driver code is required')
  if (!form.first_name) formErrors.value.push('First name is required')
  if (!form.last_name) formErrors.value.push('Last name is required')
  if (!form.mobile_no) formErrors.value.push('Mobile no is required')
  if (!form.license_no) formErrors.value.push('License no is required')
  if (formErrors.value.length > 0) return

  const url = form.id ? `/tracking/driver/${form.id}` : '/tracking/driver'
  const method = form.id ? 'put' : 'post'

  const toast = service()
  form[method](url, {
    preserveScroll: true,
    onSuccess: () => {
      toast.success(`Driver successfully ${form.id ? 'updated' : 'created'}!`)
      emit('triggerTopRightButton', 'lists')
    },
    onError: (errors) => {
      formErrors.value = Object.values(errors || {})
      toast.error('Failed to save driver')
    },
  })
}

const resetForm = () => {
  form.clearErrors()
  form.reset()
}
</script>

<style scoped>
</style>
