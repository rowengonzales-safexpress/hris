<script setup>
import FRMSLayout from '@/Layouts/FRMSLayout.vue'
import { Head } from '@inertiajs/vue3'
import { ref } from 'vue'
import Lists from './List.vue'
import Manage from './manage.vue'

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
  <Head title="FRMS For Liquadation" />
  <FRMSLayout>
    <div v-if="action === 'lists'">
      <Lists :masterlist="masterlist" @triggerTopRightButton="changeWindow" />
    </div>

    <div v-if="action === 'Manage' || action === 'Add'">
      <Manage :action="action" :formdata="rowdata" @triggerTopRightButton="changeWindow" />
    </div>

  </FRMSLayout>
</template>
