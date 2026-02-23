<script setup>
import FRMSLayout from '@/Layouts/FRMSLayout.vue'
import { Head } from '@inertiajs/vue3'
import { ref } from 'vue'
import Lists from './List.vue'
import Manage from './manage.vue'

const props = defineProps({
  disbursementApprovals: {
    type: Array,
    default: () => []
  },
  liquidationApprovals: {
    type: Array,
    default: () => []
  }
})

const action = ref('lists')
let rowdata = ref(null)
const approvalType = ref('disbursement')

const changeWindow = (actionWindow, data) => {
  rowdata.value = (data && data.row) ? data.row : (data ?? null)
  approvalType.value = (data && data.approvalType) ? data.approvalType : approvalType.value
  action.value = actionWindow
}
</script>

<template>
  <Head title="Approval Center" />
  <FRMSLayout>
    <div v-if="action === 'lists'">
      <Lists :disbursement-approvals="disbursementApprovals" :liquidation-approvals="liquidationApprovals" @triggerTopRightButton="changeWindow" />
    </div>

    <div v-if="action === 'Manage' || action === 'Add'">
      <Manage :action="action" :formdata="rowdata" :approval-type="approvalType" @triggerTopRightButton="changeWindow" />
    </div>
  </FRMSLayout>
  </template>
