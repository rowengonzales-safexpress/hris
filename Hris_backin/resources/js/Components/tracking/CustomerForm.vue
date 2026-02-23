<template>
  <div class="customer-form">
    <form @submit.prevent="handleSubmit">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <!-- Basic Information -->
        <div class="md:col-span-2">
          <h4 class="text-lg font-semibold mb-4">Basic Information</h4>
        </div>

        <FormField label="Customer Code">
          <FormControl
            v-model="form.customer_code"
            placeholder="Enter customer code"
            required
            :disabled="isEditing"
          />
        </FormField>

        <FormField label="Customer Name">
          <FormControl
            v-model="form.customer_name"
            placeholder="Enter customer name"
            required
          />
        </FormField>

        <FormField label="Company Name">
          <FormControl
            v-model="form.company_name"
            placeholder="Enter company name"
          />
        </FormField>

        <FormField label="Contact Person">
          <FormControl
            v-model="form.contact_person"
            placeholder="Enter contact person"
          />
        </FormField>

        <!-- Contact Information -->
        <div class="md:col-span-2">
          <h4 class="text-lg font-semibold mb-4 mt-6">Contact Information</h4>
        </div>

        <FormField label="Email">
          <FormControl
            v-model="form.email"
            placeholder="Enter email address"
            type="email"
          />
        </FormField>

        <FormField label="Phone">
          <FormControl
            v-model="form.phone"
            placeholder="Enter phone number"
            required
          />
        </FormField>

        <FormField label="Mobile">
          <FormControl
            v-model="form.mobile"
            placeholder="Enter mobile number"
          />
        </FormField>

        <FormField label="Fax">
          <FormControl
            v-model="form.fax"
            placeholder="Enter fax number"
          />
        </FormField>

        <div class="md:col-span-2">
          <FormField label="Website">
            <FormControl
              v-model="form.website"
              placeholder="Enter website URL"
            />
          </FormField>
        </div>

        <!-- Address Information -->
        <div class="md:col-span-2">
          <h4 class="text-lg font-semibold mb-4 mt-6">Address Information</h4>
        </div>

        <FormField label="Address Line 1">
          <FormControl
            v-model="form.address_line1"
            placeholder="Enter address line 1"
            required
          />
        </FormField>

        <FormField label="Address Line 2">
          <FormControl
            v-model="form.address_line2"
            placeholder="Enter address line 2"
          />
        </FormField>

        <FormField label="City">
          <FormControl
            v-model="form.city"
            placeholder="Enter city"
            required
          />
        </FormField>

        <FormField label="State/Province">
          <FormControl
            v-model="form.state_province"
            placeholder="Enter state or province"
            required
          />
        </FormField>

        <FormField label="Postal Code">
          <FormControl
            v-model="form.postal_code"
            placeholder="Enter postal code"
            required
          />
        </FormField>

        <FormField label="Country">
          <FormControl
            v-model="form.country"
            placeholder="Enter country"
            required
          />
        </FormField>

        <!-- Business Information -->
        <div class="md:col-span-2">
          <h4 class="text-lg font-semibold mb-4 mt-6">Business Information</h4>
        </div>

        <FormField label="Customer Type">
          <FormControl
            v-model="form.customer_type"
            :options="customerTypeOptions"
            placeholder="Select customer type"
            required
          />
        </FormField>

        <FormField label="Business Registration No.">
          <FormControl
            v-model="form.business_registration_no"
            placeholder="Enter business registration number"
          />
        </FormField>

        <FormField label="Tax ID">
          <FormControl
            v-model="form.tax_id"
            placeholder="Enter tax ID"
          />
        </FormField>

        <FormField label="Payment Terms">
          <FormControl
            v-model="form.payment_terms"
            :options="paymentTermsOptions"
            placeholder="Select payment terms"
          />
        </FormField>

        <FormField label="Preferred Service Type">
          <FormControl
            v-model="form.preferred_service_type"
            :options="serviceTypeOptions"
            placeholder="Select preferred service type"
          />
        </FormField>

        <FormField label="Status">
          <FormControl
            v-model="form.status"
            :options="statusOptions"
            placeholder="Select status"
            required
          />
        </FormField>

        <!-- Additional Information -->
        <div class="md:col-span-2">
          <FormField label="Remarks">
            <FormControl
              v-model="form.remarks"
              type="textarea"
              placeholder="Enter any additional remarks"
              class="h-24"
            />
          </FormField>
        </div>

        <!-- Form Actions -->
        <div class="md:col-span-2 flex justify-end gap-2 mt-6">
          <BaseButton
            @click="handleCancel"
            color="contrast"
            outline
            label="Cancel"
          />
          <BaseButton
            type="submit"
            color="info"
            :loading="isSubmitting"
            :label="isEditing ? 'Update Customer' : 'Create Customer'"
          />
        </div>
      </div>
    </form>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import FormField from '@/Components/FormField.vue'
import FormControl from '@/Components/FormControl.vue'
import BaseButton from '@/Components/BaseButton.vue'

interface Customer {
  id?: number
  customer_code: string
  customer_name: string
  company_name?: string
  contact_person?: string
  email?: string
  phone: string
  mobile?: string
  fax?: string
  website?: string
  address_line1: string
  address_line2?: string
  city: string
  state_province: string
  postal_code: string
  country: string
  customer_type: string
  business_registration_no?: string
  tax_id?: string
  payment_terms?: string
  preferred_service_type?: string
  status: string
  remarks?: string
}

const props = defineProps<{
  customer?: Customer | null
}>()

const emit = defineEmits<{
  save: [customer: Partial<Customer>]
  cancel: []
}>()

// Reactive data
const isSubmitting = ref(false)
const isEditing = computed(() => !!props.customer?.id)

const form = ref<Partial<Customer>>({
  customer_code: '',
  customer_name: '',
  company_name: '',
  contact_person: '',
  email: '',
  phone: '',
  mobile: '',
  fax: '',
  website: '',
  address_line1: '',
  address_line2: '',
  city: '',
  state_province: '',
  postal_code: '',
  country: 'Philippines',
  customer_type: 'INDIVIDUAL',
  business_registration_no: '',
  tax_id: '',
  payment_terms: 'COD',
  preferred_service_type: 'STANDARD',
  status: 'ACTIVE',
  remarks: ''
})

// Options
const customerTypeOptions = [
  { value: 'INDIVIDUAL', text: 'Individual' },
  { value: 'CORPORATE', text: 'Corporate' },
  { value: 'GOVERNMENT', text: 'Government' }
]

const paymentTermsOptions = [
  { value: 'COD', text: 'Cash on Delivery' },
  { value: 'NET15', text: 'Net 15 Days' },
  { value: 'NET30', text: 'Net 30 Days' },
  { value: 'NET60', text: 'Net 60 Days' },
  { value: 'PREPAID', text: 'Prepaid' }
]

const serviceTypeOptions = [
  { value: 'STANDARD', text: 'Standard' },
  { value: 'EXPRESS', text: 'Express' },
  { value: 'OVERNIGHT', text: 'Overnight' },
  { value: 'ECONOMY', text: 'Economy' }
]

const statusOptions = [
  { value: 'ACTIVE', text: 'Active' },
  { value: 'INACTIVE', text: 'Inactive' },
  { value: 'SUSPENDED', text: 'Suspended' }
]

// Methods
const handleSubmit = async () => {
  isSubmitting.value = true
  try {
    emit('save', form.value)
  } finally {
    isSubmitting.value = false
  }
}

const handleCancel = () => {
  emit('cancel')
}

// Watch for customer prop changes
watch(
  () => props.customer,
  (newCustomer) => {
    if (newCustomer) {
      form.value = { ...newCustomer }
    } else {
      // Reset form for new customer
      form.value = {
        customer_code: '',
        customer_name: '',
        company_name: '',
        contact_person: '',
        email: '',
        phone: '',
        mobile: '',
        fax: '',
        website: '',
        address_line1: '',
        address_line2: '',
        city: '',
        state_province: '',
        postal_code: '',
        country: 'Philippines',
        customer_type: 'INDIVIDUAL',
        business_registration_no: '',
        tax_id: '',
        payment_terms: 'COD',
        preferred_service_type: 'STANDARD',
        status: 'ACTIVE',
        remarks: ''
      }
    }
  },
  { immediate: true }
)
</script>

<style scoped>
.customer-form {
  padding: 1rem;
}
/* Removed grid styles as we use Tailwind classes now */
</style>
