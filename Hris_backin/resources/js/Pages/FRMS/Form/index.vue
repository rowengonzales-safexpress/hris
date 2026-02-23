<script setup>
import FRMSLayout from '@/Layouts/FRMSLayout.vue'
import { Head } from '@inertiajs/vue3'
import { ref } from 'vue'
import Lists from './List.vue'
import Manage from './manage.vue'
import Document from './Document.vue'

const props = defineProps({
  masterlist: {
    type: Array,
    default: () => []
  }
})

const action = ref('lists')
let rowdata = ref(null)

const changeWindow = (actionWindow, data) => {
  rowdata.value = data ?? null
  action.value = actionWindow
}
</script>

<template>
  <Head title="FRMS Requests" />
  <FRMSLayout>
    <div v-if="action === 'lists'">
      <Lists :masterlist="masterlist" @triggerTopRightButton="changeWindow" />
    </div>

    <div v-if="action === 'Manage' || action === 'Add'">
      <Manage :action="action" :formdata="rowdata" @triggerTopRightButton="changeWindow" />
    </div>

    <div v-if="action === 'UploadDocument'">
      <Document :form="rowdata" @triggerTopRightButton="changeWindow" />
    </div>
  </FRMSLayout>
</template>
