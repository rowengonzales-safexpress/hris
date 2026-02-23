<template>
    <Head title="Application Info" />
    <AdminLayout>
      <SectionTitleLineWithButton :icon="'mdiFileDocumentPlus'" :title="(props.action === 'Add' ? 'Add' : 'Manage') + ' Store'" main>
        <BaseButton @click="emit('triggerTopRightButton', 'lists')" :icon="'mdiViewList'" label="Store Lists" color="contrast" rounded-full small />
      </SectionTitleLineWithButton>

      <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md">
        <h3 class="text-xl font-bold mb-4 dark:text-white">Application Info</h3>
        <div>
          <form @submit.prevent="submit">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
              <div v-if="formErrors.length" class="col-span-full mb-4 text-red-600">
                <ul>
                  <li v-for="(err, i) in formErrors" :key="i">{{ err }}</li>
                </ul>
              </div>

              <FormField label="Client">
                 <select
                    v-model="form.client_id"
                    class="px-3 py-2 max-w-full focus:ring focus:outline-none border-gray-700 rounded w-full dark:placeholder-gray-400 h-12 border bg-white dark:bg-slate-800 dark:text-white"
                  >
                    <option :value="null" disabled>Select Client</option>
                    <option v-for="client in props.clients" :key="client.id" :value="client.id">
                      {{ client.client_name }}
                    </option>
                  </select>
                  <InputError :message="props.errors?.client_id" />
              </FormField>

              <FormField label="Store Code">
                <FormControl
                  v-model="form.store_code"
                  placeholder="Store Code"
                />
                <InputError :message="props.errors?.store_code" />
              </FormField>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
              <FormField label="Store Name">
                <FormControl
                  v-model="form.store_name"
                  placeholder="Store Name"
                />
                <InputError :message="props.errors?.store_name" />
              </FormField>

              <FormField label="Email">
                <FormControl
                  v-model="form.email"
                  placeholder="Email"
                />
                <InputError :message="props.errors?.email" />
              </FormField>
            </div>

            <!-- Contact Information -->
            <div class="mt-4 mb-4">
              <h3 class="text-lg font-medium text-blue-600 dark:text-blue-400 border-b pb-2">Contact Information</h3>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
              <FormField label="Phone">
                <FormControl
                  v-model="form.phone"
                  placeholder="Phone Number"
                />
                <InputError :message="props.errors?.phone" />
              </FormField>
            </div>

            <!-- Address Information -->
            <div class="mt-4 mb-4">
               <h3 class="text-lg font-medium text-blue-600 dark:text-blue-400 border-b pb-2">Address Information</h3>
            </div>

            <div class="mb-6">
              <FormField label="Address">
                <FormControl
                  v-model="form.address"
                  type="textarea"
                  placeholder="Enter store address"
                />
                <InputError :message="props.errors?.address" />
              </FormField>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
              <FormField label="City">
                <FormControl
                  v-model="form.city"
                  placeholder="City"
                />
                <InputError :message="props.errors?.city" />
              </FormField>

              <FormField label="State/Province">
                <FormControl
                  v-model="form.state_province"
                  placeholder="State/Province"
                />
                <InputError :message="props.errors?.state_province" />
              </FormField>

              <FormField label="ZIP Code">
                <FormControl
                  v-model="form.zip_code"
                  placeholder="ZIP Code"
                />
                <InputError :message="props.errors?.zip_code" />
              </FormField>
            </div>

            <!-- Logo Preview -->
            <div v-if="logoPreview" class="mt-4 mb-6">
              <div class="logo-preview">
                <h4 class="text-lg font-semibold mb-2 dark:text-white">Logo Preview:</h4>
                <img :src="logoPreview" alt="Logo Preview" class="max-w-32 max-h-32 object-contain border rounded">
              </div>
            </div>

            <!-- Form Actions -->
            <div class="flex gap-2 justify-end w-full mt-6">
              <BaseButton
                @click="resetForm"
                color="info"
                outline
                label="Reset"
              />
              <BaseButton
                type="submit"
                color="info"
                :label="action === 'Add' ? 'Create Store' : 'Update Store'"
                :loading="form.processing"
              />
            </div>
          </form>
        </div>
      </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import { ref, onMounted, watch, computed } from 'vue'
import { Head, router, useForm, usePage } from '@inertiajs/vue3'
import service from '@/Components/Toast/service'
import AdminLayout from '@/Layouts/AdminLayout.vue';
import SectionTitleLineWithButton from '@/Components/SectionTitleLineWithButton.vue';
import BaseButton from '@/Components/BaseButton.vue';
import FormControl from '@/Components/FormControl.vue';
import FormField from '@/Components/FormField.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
  action: String,
  data: Object,
  formdata: Object,
  errors: Object,
  clients: Array
});

const toast = service()
const page = usePage()

const formRef = ref()

// Compute the action based on whether we have data
const action = computed(() => props.data?.id ? 'Edit' : 'Add')

// Initialize form with empty values first
const form = useForm({
  _token: page.props.csrf_token,
  id: props.formdata?.id || props.data?.id || null,
  store_code: props.formdata?.store_code || props.data?.store_code || '',
  store_name: props.formdata?.store_name || props.data?.store_name || '',
  email: props.formdata?.email || props.data?.email || '',
  phone: props.formdata?.phone || props.data?.phone || '',
  address: props.formdata?.address || props.data?.address || '',
  city: props.formdata?.city || props.data?.city || '',
  state_province: props.formdata?.state_province || props.data?.state_province || '',
  zip_code: props.formdata?.zip_code || props.data?.zip_code || '',
  status: props.formdata?.status || props.data?.status || 'A',
  client_id: props.formdata?.client_id || props.data?.client_id || null
})
const formErrors = ref<string[]>([])

// Options
const statusOptions = [
  { text: 'Active', value: 'A' },
  { text: 'Inactive', value: 'I' },
  { text: 'Maintenance', value: 'M' },
  { text: 'Development', value: 'D' }
]

const emit = defineEmits(["triggerTopRightButton"]);

// Watch for changes in formdata prop
watch(() => props.formdata, (newFormdata) => {
  if (newFormdata) {
    form.id = newFormdata.id || null
    form.store_code = newFormdata.store_code || ''
    form.store_name = newFormdata.store_name || ''
    form.email = newFormdata.email || ''
    form.phone = newFormdata.phone || ''
    form.address = newFormdata.address || ''
    form.city = newFormdata.city || ''
    form.state_province = newFormdata.state_province || ''
    form.zip_code = newFormdata.zip_code || ''
    form.status = newFormdata.status || 'A'
    form.client_id = newFormdata.client_id || null
  }
}, { immediate: true })

const submit = () => {
  let data = { ...form };
  form.post('/admin/tracking/store', {
    preserveScroll: true,
    onSuccess: () => {
      toast.success(`Store successfully saved!`);
      router.visit('/admin/tracking/store')
    },
    onError: (errors) => {
      formErrors.value = Object.values(errors || {}) as string[]
      toast.error('Error saving store. Please check the form.')
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

const resetForm = () => {
  form.clearErrors()
  form.reset()
  // formRef not needed for standard form
}

// Only watch for deep changes if absolutely necessary
watch(() => props.formdata, (newvalue, oldvalue) => {
  if (newvalue != oldvalue && newvalue) {
    for (let key in newvalue) {
      if (newvalue.hasOwnProperty(key)) {
        form[key] = newvalue[key];
      }
    }
  }
});
</script>

<style scoped>
/* Removed Vuestic scoped styles */
</style>
