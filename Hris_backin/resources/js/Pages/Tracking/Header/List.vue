<template>
  <SectionTitleLineWithButton :icon="'mdiListBox'" title="Tracker" main>
    <BaseButton @click="emit('triggerTopRightButton', 'Add')" :icon="'mdiFileDocumentPlus'" label="Add Tracker" color="contrast" rounded-full small />
  </SectionTitleLineWithButton>


                <CardBox class="flex-1 p-6" has-table>
                  <CoreTable v-if="tableRows.length > 0"
                             :table-rows="tableRows"
                             :table-header="userHeader"
                             table-name="tracking-headers"
                             searchable-fields="tracking_number,reference_number,tracking_type,current_status,current_location,priority_level,estimated_delivery_date,created_at"
                             :is-paginated="true"
                             @openLink="openLink"
                  />
                  <div v-else class="text-gray-500">No tracking headers found</div>
                </CardBox>


            <AsideDrawer
                title="Tracking Header Details"
                :is-open="showDetails"
                @closeDrawer="showDetails = false"
                class="shadow-lg shadow-blue-500/50"
            >
                <Details
                    v-if="selectedDetails"
                    :data="selectedDetails"
                    :tracking-types="trackingTypes"
                    :tracking-statuses="trackingStatuses"
                    :is-new="selectedDetails.id === 0"
                    @close="showDetails = false"
                    @save="onSaveHeader"
                    @update="onUpdateHeader"
                    @triggerTopRightButton="onTriggerTopRightButton"
                />
            </AsideDrawer>


</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import SectionTitleLineWithButton from '@/Components/SectionTitleLineWithButton.vue'
import CardBox from '@/Components/CardBox.vue'
import CoreTable from '@/Components/CoreTable.vue'
import Details from './widgets/Details.vue';
import AsideDrawer from '@/Components/AsideDrawer.vue';
import BaseButton from '@/Components/BaseButton.vue'
import moment from 'moment';

// Define props and emits
const props = defineProps({
    masterlist: {
        type: [Array, Object],
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
            warehouse_id: item.warehouse_id || '',
            tracking_number: item.tracking_number || '',
            reference_number: item.reference_number || '',
            tracking_type: item.tracking_type?.type_name || '',
            current_status: item.current_status?.status_name || '',
            estimated_delivery_date: item.estimated_delivery_date || '',
            current_location: item.current_location || '',
            priority_level: item.priority_level || '',
            is_active: item.is_active || 0,
            created_at: item.created_at || ''
        };

        console.log('Transformed item:', transformedItem);
        return transformedItem;
    }).filter(item => item !== null); // Remove any null items

    console.log('Final transformed list:', transformed);
    return transformed;
});

// Status methods
const getStatusText = (is_active) => {
    const statusMap = {
        1: 'Active',
        true: 'Active',
        0: 'Inactive',
        false: 'Inactive'
    };
    return statusMap[is_active] || 'Unknown';
};

// Event handlers
const onDetailRowClick = (data) => {
    selectedDetails.value = data;
    showDetails.value = true;
};

const onManageHeader = (header) => {
    selectedDetails.value = header;
    showDetails.value = true;
};

const onRecordDelete = async (item) => {
    if (confirm(`Are you sure you want to delete the tracking header "${item.tracking_number}"?`)) {
        try {
            await router.delete(`/admin/tracking/header/${item.id}`, {
                preserveScroll: true,
                onSuccess: () => {
                    console.log('Tracking header deleted successfully');
                    showDetails.value = false;
                    selectedDetails.value = null;
                },
                onError: (errors) => {
                    console.error('Delete error:', errors);
                    alert('Error deleting tracking header. Please try again.');
                }
            });
        } catch (error) {
            console.error('Delete error:', error);
            alert('Error deleting tracking header. Please try again.');
        }
    }
};

const onSaveHeader = () => {
    // Handle save event from Details component
    showDetails.value = false;
    selectedDetails.value = null;
    // The page will refresh with updated data from Inertia
};

const onUpdateHeader = () => {
    // Handle update event from Details component
    showDetails.value = false;
    selectedDetails.value = null;
    // The page will refresh with updated data from Inertia
};

const onAddNew = () => {
    // Create a new tracking header object for the Add form
    selectedDetails.value = {
        id: 0,
        warehouse_id: '',
        tracking_number: '',
        reference_number: '',
        tracking_type_id: '',
        estimated_delivery_date: '',
        actual_delivery_date: '',
        current_status_id: '',
        current_location: '',
        priority_level: '',
        total_weight: '',
        total_volume: '',
        package_count: '',
        special_instructions: '',
        driver_id: '',
        helper_name: '',
        call_time: '',
        whse_in: '',
        loading_start: '',
        loading_end: '',
        whse_out: '',
        is_active: 1
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

const tableRows = computed(() => applist.value)
const openLink = (row) => {
  selectedDetails.value = row
  showDetails.value = true
}
const userHeader = [
  { label: 'Tracking #', fieldName: 'tracking_number', type: 'link' },
  { label: 'Reference #', fieldName: 'reference_number' },
  { label: 'Type', fieldName: 'tracking_type' },
  { label: 'Status', fieldName: 'current_status' },
  { label: 'Location', fieldName: 'current_location' },
  { label: 'Priority', fieldName: 'priority_level' },
  { label: 'Est. Delivery', fieldName: 'estimated_delivery_date', type: 'datetime' },
  { label: 'Active', fieldName: 'is_active', type: 'activeinactive' },
  { label: 'Created', fieldName: 'created_at', type: 'datetime' }
]

// Table columns
const columns = ref([
    { key: 'tracking_number', label: 'Tracking Number', sortable: true,clickable: true },
    { key: 'reference_number', label: 'Reference', sortable: true },
    { key: 'tracking_type', label: 'Type', sortable: true },
    { key: 'current_status', label: 'Status', sortable: true },
    { key: 'current_location', label: 'Location', sortable: true },
    { key: 'priority_level', label: 'Priority', sortable: true },
    { key: 'estimated_delivery_date', label: 'Est. Delivery', sortable: true },
    { key: 'is_active', label: 'Active', sortable: true },
    { key: 'created_at', label: 'Created At', sortable: true },
    { key: 'actions', label: 'Actions', sortable: false }
]);

</script>

<style scoped>
.header-list-container {
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
