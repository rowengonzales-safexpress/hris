<script setup>
import Lists from "./List.vue";
import Manage from "./manage.vue";
import Menu from "./Menu.vue";
import { ref } from "vue";
import axios from "axios";

const props = defineProps({ masterlist: Array });
const action = ref('lists');
let rowdata = ref(null);
const menusForApp = ref([]);
const loadingMenus = ref(false);

const changeWindow = async (actionWindow, data) => {
    console.log('Changing window to:', actionWindow, 'with data:', data);
    rowdata.value = data ?? null;
    action.value = actionWindow;
    if (actionWindow === 'Menu' && data?.id) {
        loadingMenus.value = true;
        try {
            const res = await axios.get(`/admin/menus/app/${data.id}`);
            menusForApp.value = Array.isArray(res.data) ? res.data : [];
        } catch (e) {
            menusForApp.value = [];
            console.error('Failed to load menus for app', data.id, e);
        } finally {
            loadingMenus.value = false;
        }
    }
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
            @triggerTopRightButton="changeWindow"
        />
    </div>
    <div v-if="action === 'Menu'">
        <Menu :masterlist="menusForApp" :app="rowdata" @triggerTopRightButton="changeWindow" />
    </div>
</template>
