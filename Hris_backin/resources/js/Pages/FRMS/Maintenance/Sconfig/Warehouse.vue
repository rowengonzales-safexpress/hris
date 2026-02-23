<script setup>
import { ref, onMounted, computed } from 'vue'
import { useForm, router, usePage } from '@inertiajs/vue3'
import service from '@/Components/Toast/service'
import CardBox from '@/Components/CardBox.vue'
import CoreTable from '@/Components/CoreTable.vue'
import AsideDrawer from '@/Components/AsideDrawer.vue'
import FormField from '@/Components/FormField.vue'
import FormControl from '@/Components/FormControl.vue'
import BaseButtons from '@/Components/BaseButtons.vue'
import BaseButton from '@/Components/BaseButton.vue'

import axios from 'axios'
import SwalConfirm from '@/Components/SwalConfirm.vue'

const rows = ref([])
const openDrawer = ref(false)
const isLoading = ref(false)
const page = usePage()
const form = useForm({
  _token: page.props.csrf_token,
  id: null,
  branch_code: '',
  branch_name: '',
  fulladdress: '',
  status: 'A'
})
const formErrors = ref([])
let toast = service()
const isConfirmOpen = ref(false)
const action = ref('')
const drawerTitle = computed(() => action.value === 'Add' ? 'Add Warehouse' : action.value === 'Edit' ? 'Edit Warehouse' : 'Warehouse')

const headers = [
  { label: 'Code', fieldName: 'branch_code', type: 'link' },
  { label: 'Name', fieldName: 'branch_name' },
{ label: 'Address', fieldName: 'fulladdress' },
  { label: 'Status', fieldName: 'status', type: 'activeinactive' },
]

const loadData = async () => {
  isLoading.value = true
  const resp = await axios.get('/frls/core-branch/list')
  rows.value = resp?.data?.data ?? []
  isLoading.value = false
}

const openLink = async (row) => {
  action.value = 'Edit'
  isLoading.value = true
  try {
    const resp = await axios.get(`/frls/core-branch/${row.id}`)
    const data = resp?.data || row
    form.id = data.id ?? row.id
    form.branch_code = data.branch_code ?? row.branch_code ?? ''
    form.branch_name = data.branch_name ?? row.branch_name ?? ''
    form.fulladdress = data.fulladdress ?? row.fulladdress ?? ''
    form.status = data.status ?? 'A'
    openDrawer.value = true
  } finally {
    isLoading.value = false
  }
}

const newItem = () => {
  action.value = 'Add'
  form.id = null
  form.branch_code = ''
  form.branch_name = ''
  form.fulladdress = ''
  form.status = 'A'
  openDrawer.value = true
}

const save = async () => {
  isLoading.value = true
  try {
    if (action.value === 'Edit' && form.id) {
      await new Promise((resolve, reject) => {
        form.put(`/frls/core-branch/${form.id}`, {
          preserveScroll: true,
          onSuccess: () => {
            toast.success('Warehouse updated')
            resolve()
          },
          onError: (errors) => {
            formErrors.value = Object.values(errors || {})
            const firstError = formErrors.value.length > 0 ? formErrors.value[0] : 'Failed to update warehouse'
            toast.error(firstError)
            reject(errors)
          }
        })
      })
    } else {
      await new Promise((resolve, reject) => {
        form.post('/frls/core-branch', {
          preserveScroll: true,
          onSuccess: () => {
            toast.success('Warehouse created')
            resolve()
          },
          onError: (errors) => {
            formErrors.value = Object.values(errors || {})
            const firstError = formErrors.value.length > 0 ? formErrors.value[0] : 'Failed to create warehouse'
            toast.error(firstError)
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
      router.delete(`/frls/core-branch/${form.id}`, {
        preserveScroll: true,
        onSuccess: () => {
          toast.success('Warehouse deleted')
          resolve()
        },
        onError: (errors) => {
          formErrors.value = Object.values(errors || {})
          toast.error('Failed to delete warehouse')
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
      table-name="Warehouses"
      searchable-fields="code,name,description"
      :is-paginated="true"
      :loading="isLoading"
      @openLink="openLink"
    >
      <template #table-action>
        <BaseButtons>
          <BaseButton :icon="'mdiFileDocumentPlus'" :disabled="isLoading" color="info" rounded-full small label="Add" @click="newItem" />
        </BaseButtons>
      </template>
    </CoreTable>
      </CardBox>
    <AsideDrawer :title="drawerTitle" :is-open="openDrawer" @closeDrawer="openDrawer = false" class="shadow-lg shadow-blue-500/50">
      <div v-if="formErrors.length" class="mb-3 text-red-600 text-sm">
        <div v-for="(e,i) in formErrors" :key="i">{{ e }}</div>
      </div>
      <FormField label="Code">
        <FormControl v-model="form.branch_code" />
      </FormField>
      <FormField label="Name">
        <FormControl v-model="form.branch_name" />
      </FormField>
      <FormField label="Address">
        <FormControl v-model="form.fulladdress" />
      </FormField>
      <FormField label="Status">
         <select v-model="form.status" class="w-full rounded border-gray-300">
           <option value="A">Active</option>
           <option value="I">Inactive</option>
         </select>
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
