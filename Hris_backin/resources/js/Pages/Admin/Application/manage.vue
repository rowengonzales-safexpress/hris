<template>
  <Head title="Application Info" />
  <AdminLayout>
    <SectionTitleLineWithButton :icon="'mdiListBox'" :title="mode === 'Add' ? 'Add New Application' : 'Application Info'" main>
      <BaseButton @click="emit('triggerTopRightButton', 'lists')" :icon="'mdiViewList'" label="Back to List" color="contrast" rounded-full small />
    </SectionTitleLineWithButton>

    <div class="p-4">
      <div v-if="formErrors.length" class="mb-4 text-danger">
        <ul>
          <li v-for="(err, i) in formErrors" :key="i">{{ err }}</li>
        </ul>
      </div>
      <div class="grid grid-cols-12 gap-4">
        <div class="col-span-12 md:col-span-6">
          <FormField label="Application Code">
            <FormControl v-model="form.code" :rules="[required]" placeholder="Enter application code" />
          </FormField>
        </div>
        <div class="col-span-12 md:col-span-6">
          <FormField label="Application Name">
            <FormControl v-model="form.name" :rules="[required]" placeholder="Enter application name" />
          </FormField>
        </div>

        <div class="col-span-12">
          <FormField label="Description">
            <FormControl v-model="form.description" type="textarea" placeholder="Enter application description" />
          </FormField>
        </div>

        <div class="col-span-12">
          <h3 class="section-title">Route Information</h3>
        </div>
        <div class="col-span-12">
          <FormField label="Route">
            <FormControl v-model="form.route" placeholder="Enter application route" />
          </FormField>
        </div>

        <div class="col-span-12">
          <h3 class="section-title">Status Information</h3>
        </div>
        <div class="col-span-12 md:col-span-6">
          <FormField label="Status">
            <FormControl v-model="statusSelection" :options="statusOptions" placeholder="Select status" />
          </FormField>
        </div>
        <div class="col-span-12 md:col-span-6">
          <FormField label="Status Message">
            <FormControl v-model="form.status_message" placeholder="Enter status message" />
          </FormField>
        </div>

        <div class="col-span-12">
          <h3 class="section-title">Application Logo</h3>
        </div>
        <div class="col-span-12">
          <FormField label="Upload Logo">
            <FormControl v-model="logoFile" type="file" accept="image/*" @change="onLogoChange" />
          </FormField>
          <div v-if="form.logo && typeof form.logo === 'string'" class="mt-2">
            <p class="text-sm text-gray-600">Current logo: {{ form.logo }}</p>
          </div>
        </div>

        <div v-if="logoPreview" class="col-span-12">
          <div class="logo-preview">
            <h4 class="text-lg font-semibold mb-2">Logo Preview:</h4>
            <img :src="logoPreview" alt="Logo Preview" class="max-w-32 max-h-32 object-contain border rounded" />
          </div>
        </div>
      </div>

      <BaseButtons class="mt-6 justify-end">
        <BaseButton label="Reset" color="lightDark" small @click="resetForm" />
        <BaseButton :label="mode === 'Add' ? 'Create Application' : 'Update Application'" color="contrast" small @click="submit" />
      </BaseButtons>
    </div>
  </AdminLayout>
</template>

<script setup lang="ts">
import { ref, watch, computed } from 'vue'
import { Head, router, useForm, usePage } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import SectionTitleLineWithButton from '@/Components/SectionTitleLineWithButton.vue'
import BaseButton from '@/Components/BaseButton.vue'
import BaseButtons from '@/Components/BaseButtons.vue'
import FormField from '@/Components/FormField.vue'
import FormControl from '@/Components/FormControl.vue'
import service from '@/Components/Toast/service'

const props = defineProps({
  action: String,
  data: Object,
  formdata: Object,
  errors: Object
});

const logoFile = ref<any>(null)
const logoPreview = ref('')
const formRef = ref()
const formErrors = ref<any[]>([])
const toast = service()
const page = usePage()

// Compute the action based on whether we have data
const mode = computed(() => props.data?.id ? 'Edit' : 'Add')

// Initialize form with empty values first
const form = useForm({
  _token: page.props.csrf_token,
  id: props.formdata?.id || props.data?.id || null,
  code: props.formdata?.code || props.data?.code || '',
  name: props.formdata?.name || props.data?.name || '',
  description: props.formdata?.description || props.data?.description || '',
  status: props.formdata?.status || props.data?.status || 'A',
  status_message: props.formdata?.status_message || props.data?.status_message || '',
  logo: props.formdata?.logo || props.data?.logo || '',
  route: props.formdata?.route || props.data?.route || ''
})

// Options
const statusOptions = [
  { label: 'Active', value: 'A' },
  { label: 'Inactive', value: 'I' },
  { label: 'Maintenance', value: 'M' },
  { label: 'Development', value: 'D' }
]
const statusSelection = ref<any>(null)

const emit = defineEmits(["triggerTopRightButton"]);

// Watch for changes in formdata prop
watch(() => props.formdata, (newFormdata) => {
  if (newFormdata) {
    form.id = newFormdata.id || null
    form.code = newFormdata.code || ''
    form.name = newFormdata.name || ''
    form.description = newFormdata.description || ''
    form.status = newFormdata.status || 'A'
    statusSelection.value = statusOptions.find(o => o.value === form.status) || null
    form.status_message = newFormdata.status_message || ''
    form.logo = newFormdata.logo || ''
    form.route = newFormdata.route || ''
  }
}, { immediate: true })

const submit = () => {
  const status = typeof statusSelection.value === 'object' && statusSelection.value !== null
    ? statusSelection.value.value
    : form.status

  form.status = status
  form.post('/admin/application', {
    preserveScroll: true,
    onSuccess: () => {
      toast.success('Application saved')
      router.visit('/admin/application')
    },
    onError: (errors) => {
      formErrors.value = Object.values(errors || {})
      toast.error('Please check the form')
    },
  })

}
// Validation rules
const required = (value: any) => {
  if (value === null || value === undefined || value === '') {
    return 'This field is required'
  }
  return true
}

// Logo handling methods
const onLogoChange = (e: any) => {
  const file = e?.target?.files?.[0] || logoFile.value
  if (!file) return
  const reader = new FileReader()
  reader.onload = (ev) => { logoPreview.value = ev.target?.result as string }
  reader.readAsDataURL(file)
  form.logo = file
}

const handleLogoRemove = () => {
  logoPreview.value = ''
  form.logo = ''
  logoFile.value = null
}

const resetForm = () => {
  form.clearErrors()
  form.reset()
  logoPreview.value = ''
  logoFiles.value = []
  if (formRef.value) {
    // Intentionally left blank: FormControl doesn't expose resetValidation
  }
}



// Only watch for deep changes if absolutely necessary
watch(() => props.formdata, (newvalue, oldvalue) => {
  if (newvalue != oldvalue && newvalue) {
    for (let key in newvalue) {
      if (newvalue.hasOwnProperty(key)) {
        form[key] = newvalue[key];
      }
    }
    //form.status = selectStatusOptions.find(status => status.id === newvalue.status)
  }
});
</script>

<style scoped>
.application-create {
  padding: 1rem;
}

.page-title {
  font-size: 1.5rem;
  font-weight: 600;
  margin: 0;
}

.section-title {
  font-size: 1.2rem;
  font-weight: 500;
  margin: 0 0 1rem 0;
  color: var(--va-primary);
  padding-bottom: 0.5rem;
}

.logo-preview {
  padding: 1rem;
  border: 1px solid var(--va-background-border);
  border-radius: 0.5rem;
  background-color: var(--va-background-primary);
}
</style>
watch(logoFile, (f) => {
  if (f) onLogoChange({ target: { files: [f] } })
})
