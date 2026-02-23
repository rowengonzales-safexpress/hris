<script setup>
import Lists from "./List.vue";
import Manage from "./manage.vue";
import { ref } from "vue";

const props = defineProps({ 
    masterlist: Array,
    apps: Array,
    parentMenus: Array
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
            :apps="apps"
            :parentMenus="parentMenus"
            @triggerTopRightButton="changeWindow"
        />
    </div>
</template>
