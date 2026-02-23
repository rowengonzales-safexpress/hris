<script setup>
import FRMSLayout from '@/Layouts/FRMSLayout.vue'
import { Head } from '@inertiajs/vue3'
import { ref } from 'vue'
import Lists from './List.vue'
import Details from './Details.vue'

const props = defineProps({
  disbursementData: { type: Array, default: () => [] },
  filters: { type: Object, default: () => ({}) },
  summary: { type: Object, default: () => ({}) },
  sites: { type: Array, default: () => [] }
})

const action = ref('lists')
const rowdata = ref(null)

const changeWindow = (actionWindow, data) => {
  rowdata.value = data ?? null
  action.value = actionWindow
}
</script>

<template>
  <Head title="Finance Disbursement Report" />
  <FRMSLayout>
    <div v-if="action === 'lists'">
      <Lists
        :disbursementData="disbursementData"
        :filters="filters"
        :summary="summary"
        :sites="sites"
        @triggerTopRightButton="changeWindow"
      />
    </div>

    <div v-else-if="action === 'Details' || action === 'details'">
      <Details :formdata="rowdata" @triggerTopRightButton="changeWindow" />
    </div>
  </FRMSLayout>
</template>



<style scoped>
</style>
