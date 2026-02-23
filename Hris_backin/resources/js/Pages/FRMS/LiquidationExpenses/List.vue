<script setup>
import { ref, computed } from 'vue'
import SectionTitleLineWithButton from '@/Components/SectionTitleLineWithButton.vue'
import BaseButton from '@/Components/BaseButton.vue'
import CardBox from '@/Components/CardBox.vue'
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

const tableRows = computed(() => (props.masterlist ?? []).filter(r => r.status_request !== 'FA'))

const statusLabel = computed(() => '')

const openLink = (row) => {
  emit('triggerTopRightButton', 'Manage', row)
}

const actionClicked = (action) => {}

const deleteRecord =  (id) =>{}

const showPop = ref(false);

const popShow = () =>{}

const popAction = (action) =>{}

const popCancel = () => {}
const userHeader = [
  { label: 'Requestno', fieldName: 'frm_no', type: 'link'},
  { label: 'Request Date', fieldName: 'request_date', type: 'date' },
  { label: 'Expected Liquidation', fieldName: 'expectedliquidation_date', type: 'date' },
  { label: 'Status', fieldName: 'status_request',type: 'slot' }
]
</script>

<template>
  <SectionTitleLineWithButton :icon="'mdiListBox'" title="Liquidation Expenses" main/>


  <CardBox class="flex-1 p-6" has-table>
    <CoreTable v-if="tableRows.length > 0"
               :table-rows="tableRows"
               :table-header="userHeader"
               table-name="fundrequests"
               searchable-fields="request_date,expectedliquidation_date,status_request"
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
    <div v-else class="text-gray-500">No Liquidation Expenses found.</div>
  </CardBox>




</template>
