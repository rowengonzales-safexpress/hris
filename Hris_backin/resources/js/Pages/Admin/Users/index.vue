<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import Lists from "./List.vue";
import Manage from "./manage.vue";
import { ref } from "vue";

const props = defineProps({
    masterlist: Array,
    roles: Array,
    applications: Array,
});

// Reactive data
const action = ref("Lists");
const rowdata = ref(null);

// Methods
const changeWindow = (newAction, data = null) => {
    console.log("Changing window:", newAction, data);
    action.value = newAction;
    rowdata.value = data;
};
</script>

<template>

        <Lists
            v-if="action === 'Lists'"
            :masterlist="masterlist"
            @triggerTopRightButton="changeWindow"
        />
        <Manage
            v-else-if="action === 'Add' || action === 'Manage'"
            :action="action"
            :formdata="rowdata"
            :roles="roles"
            :applications="applications"
            @triggerTopRightButton="changeWindow"
        />

</template>

<style scoped>
/* Add any specific styles if needed */
</style>
