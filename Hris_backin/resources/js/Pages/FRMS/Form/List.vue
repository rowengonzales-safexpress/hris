<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import SectionTitleLineWithButton from '@/Components/SectionTitleLineWithButton.vue'
import BaseButton from '@/Components/BaseButton.vue'
import CardBox from '@/Components/CardBox.vue'
import AsideDrawer from '@/Components/AsideDrawer.vue'
import FormField from '@/Components/FormField.vue'
import FormControl from '@/Components/FormControl.vue'
import BaseButtons from '@/Components/BaseButtons.vue'
import CoreTable from '@/Components/CoreTable.vue'
import service from "@/Components/Toast/service";
import SwalConfirm from '@/Components/SwalConfirm.vue'
import axios from 'axios'

let toast = service();

const props = defineProps({
  masterlist: {
    type: Array,
    default: () => []
  }
})

const emit = defineEmits(["triggerTopRightButton"])
const isLoading = ref(false)
const tableRows = ref(props.masterlist ?? [])
const openDrawer = ref(false)
const formdata = ref({})

const statusLabel = computed(() => {
  const status = formdata.value.status_request
  switch (status) {
    case 'FA': return 'Approved'
    case 'FD': return 'Disbursement'
    case 'FL': return 'Liquidation'
    case 'A': return 'Approved'
    case 'C': return 'Closed'
    case 'X': return 'Canceled'
    default: return status
  }
})

const openLink = (row) => {
  formdata.value = row
  openDrawer.value = true
}

const actionClicked = (action) => {
  openDrawer.value = false
  emit('triggerTopRightButton', action, formdata.value)
}
const remove = async () => {
  if (!formdata.value.id) {
    openDrawer.value = false
    return
  }
  isLoading.value = true
  try {
    await axios.delete(`/frls/form/destroy/${formdata.value.id}`)
    tableRows.value = (tableRows.value || []).filter(r => r.id !== formdata.value.id)
    openDrawer.value = false
    toast.success('Form record successfully deleted!')
    await getData()
  } finally {
    isLoading.value = false
  }
}


const showPop = ref(false);

const popShow = () =>{
  showPop.value = true
  openDrawer.value = false;
}

const popAction = async () => {
  if (!id) {
    openDrawer.value = false
    return
  }

  isLoading.value = true
  openDrawer.value = false

  try {
    await router.delete(route('frls.form.destroy', { id }), {
      preserveScroll: true,
      onSuccess: () => {
        toast.success('Form record successfully deleted!')
        tableRows.value = (tableRows.value || []).filter(r => r.id !== id)
      },
      onError: (error) => {
        toast.error('Failed to delete form record')
      }
    })
  } catch (error) {
    toast.error('An unexpected error occurred')
  } finally {
    isLoading.value = false
  }
}

const popCancel = () => {
  showPop.value = false
}
const userHeader = [

  { label: 'Requestno', fieldName: 'frm_no', type: 'link' },
  { label: 'Request Date', fieldName: 'request_date', type: 'date' },
  { label: 'Expected Liquidation', fieldName: 'expectedliquidation_date', type: 'date' },
  { label: 'Status', fieldName: 'status_request', type: 'slot' }
]

const getData = async () => {
  isLoading.value = true
  try {
    await router.visit(route('frls.form.index'), {
      preserveScroll: true,
      replace: true,
      onSuccess: (page) => {
        tableRows.value = page?.props?.masterlist ?? []
      }
    })
  } finally {
    isLoading.value = false
  }
}
</script>

<template>
  <SectionTitleLineWithButton :icon="'mdiListBox'" title="Fund Requests" main>
    <BaseButton @click="emit('triggerTopRightButton', 'Add')" :icon="'mdiFileDocumentPlus'" label="Add Request" color="contrast" rounded-full small />
  </SectionTitleLineWithButton>

  <CardBox class="flex-1 p-6" has-table>
    <CoreTable v-if="tableRows.length > 0"
               :table-rows="tableRows"
               :table-header="userHeader"
               table-name="fundrequests"
               searchable-fields="frm_no,request_date,expectedliquidation_date,status_request"
               :is-paginated="true"
               @openLink="openLink"
    >
<template #table-action>
        <BaseButtons>
          <BaseButton :icon="'mdiCloudDownloadOutline'" title="Retrieve Records" color="whiteDark" @click="getData" />
        </BaseButtons>
      </template>

<template #row-action="scope">
                 <span :class="{
                   'text-yellow-500': scope.slotProp.status_request === 'FA',
                   'text-blue-500': scope.slotProp.status_request === 'FD',
                   'text-red-500': scope.slotProp.status_request === 'FL',
                   'text-green-500': scope.slotProp.status_request === 'A'
                 }">
                   {{
                     scope.slotProp.status_request === 'FA' ? 'Approval' :
                     scope.slotProp.status_request === 'FD' ? 'Disbursement' :
                     scope.slotProp.status_request === 'FL' ? 'Liquidation' :
                     scope.slotProp.status_request === 'A' ? 'Approved' :
                     scope.slotProp.status_request === 'C' ? 'Close' :
                     scope.slotProp.status_request === 'X' ? 'Cancel' :
                     scope.slotProp.status_request === 'FR' ? 'Review' :
                     scope.slotProp.status_request
                   }}
                 </span>
               </template>
</CoreTable>
    <div v-else class="text-gray-500">Click Add Request button to create your first record</div>
  </CardBox>

  <AsideDrawer title="Request Info" :is-open="openDrawer" @closeDrawer="openDrawer = false" class="shadow-lg shadow-blue-500/50">
     <FormField label="Request No">
      <FormControl v-model="formdata.frm_no" name="frm_no" :disabled="true" />
    </FormField>
    <FormField label="Request Date">
      <FormControl v-model="formdata.request_date" name="request_date" :disabled="true" />
    </FormField>
    <FormField label="Expected Liquidation">
      <FormControl v-model="formdata.expectedliquidation_date" name="expectedliquidation_date" :disabled="true" />
    </FormField>
    <FormField label="Status">
      <FormControl v-model="statusLabel" name="status_request" :disabled="true" />
    </FormField>

    <BaseButtons class="mt-2">
      <BaseButton v-if="formdata.status_request === 'FA'" color="info" @click="actionClicked('Manage')" label="Manage" icon="mdiAccountBoxEditOutline"  />
        <BaseButton v-if="formdata.status_request === 'FA'" color="danger" label="Delete" @click="popShow" icon="mdiClose" />
    </BaseButtons>
  </AsideDrawer>

  <!-- Popup Dialog Confirmation-->
 <SwalConfirm
      v-model="showPop"
      type="warning"
      title="Are you sure?"
      :text="`You want to delete ${formdata.frm_no} ? Once submitted, you cannot undo this action!`"
      confirmText="Submit"
      cancelText="Cancel"
      @confirm="remove"
      @cancel="popCancel"
    />
</template>
