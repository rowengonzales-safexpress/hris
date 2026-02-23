<template>
    <FRMSLayout>
        <SectionTitleLineWithButton :icon="'mdiChartLine'" title="Finance Liquidation Review" main/>
<CardBox class="flex-1 p-6" has-table>
    <CoreTable v-if="tableRows.length > 0"
               :table-rows="tableRows"
               :table-header="liquidationHeader"
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

                    'text-blue-500': scope.slotProp.status_request === 'FR'
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

        <AsideDrawer title="Disbursement Details" :is-open="openDrawer" @closeDrawer="openDrawer = false" class="shadow-lg shadow-blue-500/50">
            <FormField label="Form No.">
                <FormControl v-model="formdata.frm_no" :disabled="true" />
            </FormField>
            <FormField label="Request Date">
                <FormControl :model-value="formatDate(formdata.request_date)" :disabled="true" />
            </FormField>
            <FormField label="Expected Liquidation">
                <FormControl :model-value="formatDate(formdata.expectedliquidation_date)" :disabled="true" />
            </FormField>
            <FormField label="Status">
                <FormControl :model-value="statusLabel" :disabled="true" />
            </FormField>
            <FormField label="Purpose">
                <FormControl v-model="formdata.purpose" :disabled="true" />
            </FormField>

            <BaseButtons class="mt-4" />
        </AsideDrawer>
    </FRMSLayout>
</template>

<script setup>
import FRMSLayout from '@/Layouts/FRMSLayout.vue'
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

const tableRows = ref(props.masterlist ?? [])
const openDrawer = ref(false)
const formdata = ref({})

const statusLabel = computed(() => {
  const status = formdata.value.status_request
  switch (status) {
    case 'FA': return 'For Approved'
    case 'FD': return 'For Disbursement'
    case 'FL': return 'For Liquidation'
    case 'A': return 'Approved'
    case 'C': return 'Closed'
    case 'X': return 'Canceled'
    default: return status
  }
})

const openLink = (row) => {
  router.visit(route('frls.review.show', { form: row.id }))
}

const actionClicked = (action) => {
  openDrawer.value = false
  emit('triggerTopRightButton', action, formdata.value)
}

const deleteRecord =  (id) =>{
   axios.delete('/frms/form/destroy/'+id)
    .then(response => {
      toast.success(`Form record successfully deleted!`);
      // Refresh the page data instead of full page reload
      window.location.reload();
      }).catch(err =>{
        console.log(err)
        toast.error('Failed to delete form record');
    });
};

const showPop = ref(false);

const popShow = () =>{
  showPop.value = true
  openDrawer.value = false;
}

const popAction = (action) =>{
  showPop.value = false
  if (action === 'ok') {
    deleteRecord(formdata.value.id)
    openDrawer.value = false;
  }
}

const popCancel = () => {
  showPop.value = false
}
const liquidationHeader = [
  { label: 'Requestno', fieldName: 'frm_no', type: 'link'},
  { label: 'Request Date', fieldName: 'request_date', type: 'date' },
  { label: 'Expected Liquidation', fieldName: 'expectedliquidation_date', type: 'date' },
  { label: 'Status', fieldName: 'status_request', type: 'slot' }
]

const formatDate = (dateString) => {
  if (!dateString) return ''
  return new Date(dateString).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: '2-digit' })
}
</script>
