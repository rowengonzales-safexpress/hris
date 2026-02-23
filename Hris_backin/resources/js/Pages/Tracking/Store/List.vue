<template>
    <Head title="Store Management" />
    <AdminLayout>
        <div class="store-list-container">
            <div class="main-content">
                <SectionTitleLineWithButton :icon="'mdiListBox'" title="Stores" main>
  <BaseButton @click="emit('triggerTopRightButton', 'Add')" :icon="'mdiFileDocumentPlus'" label="Add Store" color="contrast" rounded-full small />
</SectionTitleLineWithButton>

                <!-- Data loaded -->
<!-- vtable removed: replaced with CardBox + CoreTable -->
                <CardBox class="flex-1 p-6" has-table>
                  <CoreTable v-if="tableRows.length > 0"
                             :table-rows="tableRows"
                             :table-header="userHeader"
                             table-name="stores"
                             searchable-fields="store_code,store_name,client_name,city,phone,email,address,state_province,zip_code,status"
                             :is-paginated="true"
                             @openLink="openLink"
                  />
                  <div v-else class="text-gray-500">Click Add Store button to create your first record</div>
                </CardBox>
            </div>

            <AsideDrawer
                title="Store Details"
                :is-open="showDetails"
                @closeDrawer="showDetails = false"
                class="shadow-lg shadow-blue-500/50"
            >
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
        </div>
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
import CardBox from '@/Components/CardBox.vue'
import CoreTable from '@/Components/CoreTable.vue'

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
            store_code: item.store_code || '',
            store_name: item.store_name || '',
            client_name: item.client?.client_name || item.client_name || '',
            city: item.city || '',
            phone: item.phone || '',
            email: item.email || '',
            address: item.address || '',
            state_province: item.state_province || '',
            zip_code: item.zip_code || '',
            status: item.status || 'A',
            client_id: item.client_id || item.client?.id || '',
            client: item.client || null
        };

        console.log('Transformed store item:', transformedItem);
        return transformedItem;
    }).filter(item => item !== null); // Remove any null items

    console.log('Final transformed list:', transformed);
    return transformed;
});

// Status methods
const getStatusText = (status) => {
    const statusMap = {
        'A': 'Active',
        'ACTIVE': 'Active',
        'I': 'Inactive',
        'INACTIVE': 'Inactive',
        'M': 'Maintenance',
        'MAINTENANCE': 'Maintenance',
        'D': 'Development',
        'DEVELOPMENT': 'Development'
    };
    return statusMap[status] || 'Unknown';
};

// Event handlers
const onDetailRowClick = (data) => {
    selectedDetails.value = data;
    showDetails.value = true;
};

const onManageClient = (client) => {
    selectedDetails.value = client;
    showDetails.value = true;
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
                    alert('Error deleting store. Please try again.');
                }
            });
        } catch (error) {
            console.error('Delete error:', error);
            alert('Error deleting store. Please try again.');
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
    // Create a new store object for the Add form
    selectedDetails.value = {
        id: 0,
        store_code: '',
        store_name: '',
        client_id: null,
        client_name: '',
        email: '',
        phone: '',
        address: '',
        city: '',
        state_province: '',
        zip_code: '',
        status: 'A'
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

// Table columns
const columns = ref([
    { key: 'store_code', label: 'Store Code', sortable: true },
    { key: 'store_name', label: 'Store Name', sortable: true },
    { key: 'client_name', label: 'Client', sortable: true },
    { key: 'city', label: 'City', sortable: true },
    { key: 'phone', label: 'Phone', sortable: true },
    { key: 'status', label: 'Status', sortable: true },
    { key: 'actions', label: 'Actions', sortable: false }
]);
const tableRows = computed(() => Array.isArray(props.masterlist) ? props.masterlist : (props.masterlist?.data ?? []))
const openLink = (row) => {
  selectedDetails.value = row
  showDetails.value = true
}
const userHeader = [
  { label: 'Code', fieldName: 'store_code', type: 'link' },
  { label: 'Name', fieldName: 'store_name' },
  { label: 'Client', fieldName: 'client_name' },
  { label: 'City', fieldName: 'city' },
  { label: 'Phone', fieldName: 'phone' },
  { label: 'Email', fieldName: 'email' },
  { label: 'Address', fieldName: 'address' },
  { label: 'State', fieldName: 'state_province' },
  { label: 'Zip', fieldName: 'zip_code' },
  { label: 'Status', fieldName: 'status', type: 'activeinactive' }
]
</script>

<style scoped>
.store-list-container {
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
