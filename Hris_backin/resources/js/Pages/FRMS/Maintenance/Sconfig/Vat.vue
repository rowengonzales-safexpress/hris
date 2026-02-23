<script setup>
import { ref, onMounted, computed } from 'vue'
import { useForm, router, usePage } from '@inertiajs/vue3'
import CardBox from '@/Components/CardBox.vue'
import CoreTable from '@/Components/CoreTable.vue'
import AsideDrawer from '@/Components/AsideDrawer.vue'
import FormField from '@/Components/FormField.vue'
import FormControl from '@/Components/FormControl.vue'
import BaseButtons from '@/Components/BaseButtons.vue'
import BaseButton from '@/Components/BaseButton.vue'
import axios from 'axios'
import SwalConfirm from '@/Components/SwalConfirm.vue'
import service from '@/Components/Toast/service'

const rows = ref([])
const isLoading = ref(false)
const openDrawer = ref(false)
const page = usePage()
const form = useForm({
  _token: page.props.csrf_token,
  id: null,
  transaction_name: 'FRMS-Vat',
  transaction_key: 'TYPE',
  identitycode: '',
  description: '',
  sortorder: 0,
  trans_value: 0,
  isactive: true
})
const formErrors = ref([])
let toast = service()
const isConfirmOpen = ref(false)
const action = ref('')
const drawerTitle = computed(() => action.value === 'Add' ? 'Add VAT' : action.value === 'Edit' ? 'Edit VAT' : 'VAT')

const headers = [
      { label: 'VAT Code', fieldName: 'identitycode', type: 'link' },
  { label: 'Description', fieldName: 'description' },

  { label: 'VAT Value', fieldName: 'trans_value' },
  { label: 'Sort Order', fieldName: 'sortorder' },
  { label: 'Active', fieldName: 'isactive', type: 'boolean' },
]

const loadData = async () => {
  isLoading.value = true
  const resp = await axios.get('/frls/transaction-code/frls-vat')
  rows.value = resp?.data?.data ?? []
  isLoading.value = false
}

const openLink = async (row) => {
  action.value = 'Edit'
  isLoading.value = true
  try {
    const resp = await axios.get(`/frls/transaction-codes/${row.id}`)
    const data = resp?.data?.data || row
    form.id = data.id ?? row.id
    form.identitycode = data.identitycode ?? ''
    form.description = data.description ?? row.description ?? ''
    form.sortorder = data.sortorder ?? 0
    form.trans_value = data.trans_value ?? row.trans_value ?? 0
    form.isactive = true
    openDrawer.value = true
  } finally {
    isLoading.value = false
  }
}

const newItem = () => {
  action.value = 'Add'
  form.id = null
  form.identitycode = ''
  form.description = ''
  form.sortorder = 0
  form.trans_value = 0
  form.isactive = true
  openDrawer.value = true
}

const save = async () => {
  isLoading.value = true
  try {
    if (action.value === 'Edit' && form.id) {
      await new Promise((resolve, reject) => {
        form.put(`/frls/transaction-codes/${form.id}`, {
          preserveScroll: true,
          onSuccess: () => {
            toast.success('VAT updated')
            resolve()
          },
          onError: (errors) => {
            formErrors.value = Object.values(errors || {})
            toast.error('Failed to update VAT')
            reject(errors)
          }
        })
      })
    } else {
      await new Promise((resolve, reject) => {
        form.post('/frls/transaction-codes', {
          preserveScroll: true,
          onSuccess: () => {
            toast.success('VAT created')
            resolve()
          },
          onError: (errors) => {
            formErrors.value = Object.values(errors || {})
            toast.error('Failed to create VAT')
            reject(errors)
          }
        })
      })
    }
    await loadData()
    openDrawer.value = false
  } finally {
    isLoading.value = false
  }
}

const remove = async () => {
  if (!form.id) {
    openDrawer.value = false
    return
  }
  isLoading.value = true
  try {
    await new Promise((resolve, reject) => {
      router.delete(`/frls/transaction-codes/${form.id}`, {
        preserveScroll: true,
        onSuccess: () => {
          toast.success('VAT deleted')
          resolve()
        },
        onError: (errors) => {
          formErrors.value = Object.values(errors || {})
          toast.error('Failed to delete VAT')
          reject(errors)
        }
      })
    })
    await loadData()
    openDrawer.value = false
  } finally {
    isLoading.value = false
  }
}

onMounted(loadData)
</script>

<template>
  <CardBox class="flex-1 p-6" has-table>
    <CoreTable
      :table-rows="rows"
      :table-header="headers"
      table-name="FRMS VAT"
      searchable-fields="description,trans_value"
      :is-paginated="true"
      @openLink="openLink"
    >
      <template #table-action>
        <BaseButtons>
          <BaseButton :icon="'mdiFileDocumentPlus'" :disabled="isLoading"  color="info" rounded-full small label="Add" @click="newItem" />
        </BaseButtons>
      </template>
    </CoreTable>
  </CardBox>
  <AsideDrawer :title="drawerTitle" :is-open="openDrawer" @closeDrawer="openDrawer = false" class="shadow-lg shadow-blue-500/50">
    <div v-if="formErrors.length" class="mb-3 text-red-600 text-sm">
      <div v-for="(e,i) in formErrors" :key="i">{{ e }}</div>
    </div>
    <FormField label="VAT Code">
      <FormControl v-model="form.identitycode" />
    </FormField>
    <FormField label="Description">
      <FormControl v-model="form.description" />
    </FormField>
    <FormField label="Sort Order">
      <FormControl v-model="form.sortorder" />
    </FormField>
    <FormField label="VAT Value">
      <FormControl v-model="form.trans_value" />
    </FormField>
    <BaseButtons>
                <BaseButton :icon="'mdiContentSave'" :disabled="isLoading" color="info" rounded-full small label="Save" @click="save" />
      <BaseButton v-if="action === 'Edit'" :icon="'mdiDelete'" color="danger" rounded-full small label="Delete" @click="isConfirmOpen = true" />
      <BaseButton v-else :icon="'mdiClose'" color="error" rounded-full small label="Cancel" @click="openDrawer = false" />
    </BaseButtons>
    <SwalConfirm
      v-model="isConfirmOpen"
      type="warning"
      title="Delete Record"
      text="Are you sure you want to delete this record?"
      @confirm="remove"
    />
  </AsideDrawer>
</template>
