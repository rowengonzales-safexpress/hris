<script setup>
import Lists from "./List.vue";
import Manage from "./manage.vue";
import { ref } from "vue";
import TrackingLayout from '@/Layouts/TrackingLayout.vue';
const props = defineProps({
  masterlist: Array,
  clients: {
    type: Array,
    default: () => []
  },
  trackingTypes: {
    type: Array,
    default: () => []
  },
  trackingStatuses: {
    type: Array,
    default: () => []
  }
});

const action = ref('lists');
let rowdata = ref(null);

const changeWindow = (actionWindow, data) => {
    console.log('Changing window to:', actionWindow, 'with data:', data);
    rowdata.value = data ?? null;
    action.value = actionWindow;
};
</script>

<template>
  <Head title="Tracking Header Info" />
    <TrackingLayout>
    <div v-if="action === 'lists'">
        <Lists
            :masterlist="masterlist"
            @triggerTopRightButton="changeWindow"
        />
    </div>

    <div v-if="action === 'Manage' || action === 'Add'">
        <Manage
            :action="action"
            :formdata="rowdata"
            :clients="clients"
            :trackingTypes="trackingTypes"
            :trackingStatuses="trackingStatuses"
            @triggerTopRightButton="changeWindow"
        />
    </div>
</TrackingLayout>
</template>
