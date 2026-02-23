<template>
    <Head title="Application Info" />
    <AdminLayout>

                <SectionTitleLineWithButton :icon="'mdiListBox'" title="Applications" main>
  <BaseButton @click="onAddNewApp" :icon="'mdiFileDocumentPlus'" label="Add App" color="contrast" rounded-full small />
</SectionTitleLineWithButton>

                <!-- Data loaded -->
                <CardBox class="flex-1 p-6" has-table>
                  <CoreTable v-if="tableRows.length > 0"
                             :table-rows="tableRows"
                             :table-header="userHeader"
                             table-name="applications"
                             searchable-fields="code,name,status,created_at"
                             :is-paginated="true"
                             @openLink="openLink"
                  >
                <template #row-action="{ slotProp }">
                  <BaseButton @click="onManageClient(slotProp)" :icon="'mdiMenu'" color="contrast" rounded-full small />
                </template>
                </CoreTable>
                  <div v-else class="text-gray-500">Click Add App button to create your first record</div>
                </CardBox>


            <!-- Right slide panel -->
            <AsideDrawer :title="drawerTitle" :is-open="showDetails" @closeDrawer="showDetails = false" class="shadow-lg shadow-blue-500/50">

                <Details
                  v-if="selectedDetails"
                  :data="selectedDetails"
                  :is-new="selectedDetails.id === 0"
                  @close="showDetails = false"
                  @save="onSaveClient"
                  @update="onUpdateClient"
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
const drawerTitle = computed(() => (selectedDetails.value && selectedDetails.value.id === 0) ? 'Add Application' : 'Manage Application');

// Transform data to match what vtable expects
const applist = computed(() => {
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
            code: item.code || '',
            name: item.name || '',
            status: item.status || 'I',
            description: item.description || '',
            status_message: item.status_message || '',
            route: item.route || '',
            logo: item.logo || ''
        };

        console.log('Transformed item:', transformedItem);
        return transformedItem;
    }).filter(item => item !== null); // Remove any null items

    console.log('Final transformed list:', transformed);
    return transformed;
});

// Table rows and header for CoreTable
const tableRows = computed(() => Array.isArray(props.masterlist) ? props.masterlist : (props.masterlist?.data ?? []))
const openLink = (row) => {
  selectedDetails.value = row
  showDetails.value = true
}
const userHeader = [
  { label: 'Code', fieldName: 'code', type: 'link' },
  { label: 'Name', fieldName: 'name' },
  { label: 'Status', fieldName: 'status' },
  { label: 'Action', fieldName: 'action', type: 'slot' }
]

// Event handlers
const onDetailRowClick = (data) => {
    selectedDetails.value = data;
    showDetails.value = true;
};

const onManageClient = (client) => {
    emit('triggerTopRightButton', 'Menu', client)
};

const onRecordDelete = async (item) => {
    if (confirm(`Are you sure you want to delete the application "${item.name}"?`)) {
        try {
            await router.delete(`/admin/application/${item.id}`, {
                preserveScroll: true,
                onSuccess: () => {
                    console.log('Application deleted successfully');
                    showDetails.value = false;
                    selectedDetails.value = null;
                },
                onError: (errors) => {
                    console.error('Delete error:', errors);
                    alert('Error deleting application. Please try again.');
                }
            });
        } catch (error) {
            console.error('Delete error:', error);
            alert('Error deleting application. Please try again.');
        }
    }
};

const onSaveClient = () => {
    // Handle save event from Details component
    showDetails.value = false;
    selectedDetails.value = null;
    // The page will refresh with updated data from Inertia
};

const onUpdateClient = () => {
    // Handle update event from Details component
    showDetails.value = false;
    selectedDetails.value = null;
    // The page will refresh with updated data from Inertia
};

const onAddNewApp = () => {
    // Create a new application object for the Add form
    selectedDetails.value = {
        id: 0,
        code: '',
        name: '',
        status: 'A',
        description: '',
        status_message: '',
        route: '',
        logo: ''
    };
    showDetails.value = true;
    // Also emit to parent for navigation
    emit('triggerTopRightButton', 'Add');
};

const onTriggerTopRightButton = (action, data) => {
    // Handle the trigger event from Details component
    showDetails.value = false;
    selectedDetails.value = null;
    // Emit the event to parent (index.vue)
    emit('triggerTopRightButton', action, data);
};

const onPaginationChange = (page) => {
    // Handle pagination changes if needed
    console.log('Pagination changed to page:', page);
};

// Removed legacy vtable columns; using CoreTable header above.


</script>


