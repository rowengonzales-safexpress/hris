<template>
    <Head title="Menu Management" />
    <AdminLayout>
        <div class="menu-list-container">
            <div class="main-content">
                <SectionTitleLineWithButton :icon="'mdiListBox'" title="Menus" main>
  <BaseButton @click="onAddNewMenu" :icon="'mdiFileDocumentPlus'" label="Add Menu" color="contrast" rounded-full small />
</SectionTitleLineWithButton>

                <!-- Data loaded -->
                <CardBox class="flex-1 p-6" has-table>
                  <CoreTable v-if="tableRows.length > 0"
                             :table-rows="tableRows"
                             :table-header="userHeader"
                             table-name="menus"
                             searchable-fields="name,route,app_name,parent_name,sort_order,is_active"
                             :is-paginated="true"
                             @openLink="openLink"
                  />
                  <div v-else class="text-gray-500">Click Add Menu button to create your first record</div>
                </CardBox>
            </div>
        </div>

        <!-- Right slide panel -->
        <AsideDrawer :title="drawerTitle" :is-open="showDetails" @closeDrawer="showDetails = false" class="shadow-lg shadow-blue-500/50">
    
            <Details
              v-if="selectedDetails"
              :data="selectedDetails"
              :is-new="selectedDetails.id === 0"
              @close="showDetails = false"
              @save="onSaveMenu"
              @triggerTopRightButton="onTriggerTopRightButton"
            />

        </AsideDrawer>
    </AdminLayout>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, router } from '@inertiajs/vue3';
// import vtable from '@/Components/vtable.vue';
import Details from './widgets/Details.vue';
import AsideDrawer from '@/Components/AsideDrawer.vue';
import SectionTitleLineWithButton from '@/Components/SectionTitleLineWithButton.vue';
import BaseButton from '@/Components/BaseButton.vue';
import CardBox from '@/Components/CardBox.vue';
import CoreTable from '@/Components/CoreTable.vue';

// Define props and emits
const props = defineProps({
    masterlist: {
        type: [Array, Object],
        default: () => []
    }
});

const emit = defineEmits(['triggerTopRightButton']);

const showDetails = ref(false);
const selectedDetails = ref(null);
const loading = ref(false);
const drawerTitle = computed(() => (selectedDetails.value && selectedDetails.value.id === 0) ? 'Add Menu' : 'Manage Menu');

// Transform data to match what vtable expects
const menulist = computed(() => {
    console.log('Raw masterlist:', props.masterlist);

    if (!props.masterlist) {
        console.error('Masterlist is undefined or null');
        return [];
    }

    // Check if we have paginated data structure or plain array
    let data = [];

    if (Array.isArray(props.masterlist)) {
        data = props.masterlist;
    } else if (props.masterlist && typeof props.masterlist === 'object') {
        // Handle Laravel paginated response
        data = props.masterlist.data || [];
    }

    if (!Array.isArray(data)) {
        console.error('Data is not an array:', data);
        return [];
    }

    const transformed = data.map(item => {
        if (!item) return null;

        // Handle both paginated response and direct array
        const transformedItem = {
            id: item.id || '',
            name: item.name || '',
            route: item.route || '',
            icon: item.icon || '',
            sort_order: item.sort_order || 0,
            is_active: item.is_active || false,
            app_id: item.app_id || null,
            parent_id: item.parent_id || null,
            app_name: item.app?.name || '',
            parent_name: item.parent?.name || 'Root'
        };

        console.log('Transformed item:', transformedItem);
        return transformedItem;
    }).filter(item => item !== null); // Remove any null items

    console.log('Final transformed list:', transformed);
    return transformed;
});

// Event handlers
const onDetailRowClick = (data) => {
    selectedDetails.value = data;
    showDetails.value = true;
};

const tableRows = computed(() => menulist.value)
const openLink = (row) => {
  selectedDetails.value = row
  showDetails.value = true
}
const userHeader = [
  { label: 'Name', fieldName: 'name', type: 'link' },
  { label: 'Route', fieldName: 'route' },
  { label: 'Application', fieldName: 'app_name' },
  { label: 'Parent', fieldName: 'parent_name' },
  { label: 'Order', fieldName: 'sort_order' },
  { label: 'Status', fieldName: 'is_active', type: 'activeinactive' }
]

const onAddNewMenu = () => {
    selectedDetails.value = {
        id: 0,
        name: '',
        route: '',
        icon: '',
        sort_order: 0,
        is_active: true,
        app_id: null,
        parent_id: null
    };
    showDetails.value = true;
};

const onManageMenu = (menu) => {
    selectedDetails.value = menu;
    showDetails.value = true;

};

const onRecordDelete = async (item) => {
    if (confirm(`Are you sure you want to delete the menu "${item.name}"?`)) {
        try {
            await router.delete(`/admin/menu/${item.id}`, {
                preserveScroll: true,
                onSuccess: () => {
                    console.log('Menu deleted successfully');
                    showDetails.value = false;
                    selectedDetails.value = null;
                },
                onError: (errors) => {
                    console.error('Delete error:', errors);
                    alert('Error deleting menu. Please try again.');
                }
            });
        } catch (error) {
            console.error('Delete error:', error);
            alert('Error deleting menu. Please try again.');
        }
    }
};

const onSaveMenu = () => {
    // Handle save event from Details component
    showDetails.value = false;
    selectedDetails.value = null;
    // The page will refresh with updated data from Inertia
};

const onUpdateMenu = () => {
    // Handle update event from Details component
    showDetails.value = false;
    selectedDetails.value = null;
    // The page will refresh with updated data from Inertia
};

const onTriggerTopRightButton = (action, data) => {
    // Handle triggerTopRightButton event from Details component
    showDetails.value = false;
    selectedDetails.value = null;
    emit('triggerTopRightButton', action, data);
};

const onPaginationChange = (page) => {
    // Handle pagination changes if needed
    console.log('Pagination changed to page:', page);
};

// Removed legacy vtable columns; using CoreTable header above.

</script>

<style scoped>
.menu-list-container {
    position: relative;
    display: flex;
    height: 100vh;
}

.main-content {
    flex: 1;
    padding-right: 1rem;
    overflow-y: auto;
}

.slide-panel {
    position: fixed;
    top: 0;
    right: 0;
    height: 100vh;
    z-index: 1000;
    overflow-y: auto;
    box-shadow: -2px 0 8px rgba(0, 0, 0, 0.1);
}
</style>
