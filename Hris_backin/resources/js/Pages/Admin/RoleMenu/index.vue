<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import Lists from "./List.vue";
import Manage from "./manage.vue";
import { ref } from "vue";

const props = defineProps({
    masterlist: Array,
    roles: Array,
    menus: Array,
    apps: Array
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
                :roles="roles"
                :menus="menus"
                :apps="apps"
                @triggerTopRightButton="changeWindow"
            />
        </div>

        <div v-if="action === 'Manage' || action === 'Add'">
            <Manage
                :action="action"
                :formdata="rowdata"
                :roles="roles"
                :menus="menus"
                @triggerTopRightButton="changeWindow"
            />
        </div>

</template>
