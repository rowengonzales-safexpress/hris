<template>
    <Head title="Droptrip Info" />
    <TrackingLayout>
        <div class="droptrip-list-container">
            <div class="main-content">
                <h1 class="text-2xl font-bold mb-4">Droptrip List</h1>

                <CardBox class="flex-1 p-6" has-table>
                  <CoreTable v-if="tableRows.length > 0"
                             :table-rows="tableRows"
                             :table-header="userHeader"
                             table-name="droptrips"
                             searchable-fields="drsino,sqno,receiver_name,store_time_in,store_time_out,delivery_status,created_at"
                             :is-paginated="true"
                             @openLink="openLink"
                  />
                  <div v-else class="text-gray-500">No droptrips found</div>
                </CardBox>
            </div>

            <AsideDrawer
                title="Droptrip Details"
                :is-open="showDetails"
                @closeDrawer="showDetails = false"
                class="shadow-lg shadow-blue-500/50"
            >
                <Details
                    v-if="selectedDetails"
                    :data="selectedDetails"
                    :is-new="selectedDetails.id === 0"
                    @close="showDetails = false"
                    @save="onSaveDroptrip"
                    @update="onUpdateDroptrip"
                    @triggerTopRightButton="onTriggerTopRightButton"
                />
            </AsideDrawer>
        </div>
    </TrackingLayout>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import TrackingLayout from '@/Layouts/TrackingLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import CardBox from '@/Components/CardBox.vue'
import CoreTable from '@/Components/CoreTable.vue';
import Details from './widgets/Details.vue';
import AsideDrawer from '@/Components/AsideDrawer.vue';
import moment from 'moment';

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
            trackingheader_id: item.trackingheader_id || '',
            trackingclient_id: item.trackingclient_id || '',
            trackingclient_store_id: item.trackingclient_store_id || '',
            sqno: item.sqno || '',
            drsino: item.drsino || '',
            store_time_in: item.store_time_in || '',
            unloading_start: item.unloading_start || '',
            unloading_end: item.unloading_end || '',
            store_time_out: item.store_time_out || '',
            receiver_name: item.receiver_name || '',
            delivery_status: item.delivery_status || 'PENDING',
            created_at: item.created_at || '',
            tracking_header: item.tracking_header || null,
            tracking_client: item.tracking_client || null,
            tracking_client_store: item.tracking_client_store || null
        };

        return transformedItem;
    }).filter(item => item !== null);

    console.log('Transformed data:', transformed);
    return transformed;
});

const tableRows = computed(() => applist.value)
const openLink = (row) => {
  selectedDetails.value = row
  showDetails.value = true
}
const userHeader = [
  { label: 'DRS No', fieldName: 'drsino', type: 'link' },
  { label: 'Sequence', fieldName: 'sqno' },
  { label: 'Receiver', fieldName: 'receiver_name' },
  { label: 'Time In', fieldName: 'store_time_in', type: 'datetime' },
  { label: 'Time Out', fieldName: 'store_time_out', type: 'datetime' },
  { label: 'Status', fieldName: 'delivery_status', type: 'status', enumerable: statusEnumerable },
  { label: 'Created', fieldName: 'created_at', type: 'datetime' }
]

const statusEnumerable = {
  COMPLETED: 'COMPLETED',
  IN_PROGRESS: 'IN PROGRESS',
  PENDING: 'PENDING',
  CANCELLED: 'CANCELLED'
}

// Event handlers
const onAddNew = () => {
    emit('triggerTopRightButton', 'Add');
};

const onDetailRowClick = (rowData) => {
    console.log('Row clicked:', rowData);
    selectedDetails.value = rowData;
    showDetails.value = true;
};

const onManageDroptrip = (rowData) => {
    console.log('Managing droptrip:', rowData);
    emit('triggerTopRightButton', 'Manage', rowData);
};

const onRecordDelete = (rowData) => {
    if (confirm('Are you sure you want to delete this droptrip?')) {
        router.delete(route('tracking.droptrip.destroy', rowData.id), {
            onSuccess: () => {
                console.log('Droptrip deleted successfully');
            },
            onError: (errors) => {
                console.error('Error deleting droptrip:', errors);
            }
        });
    }
};

const onPaginationChange = (page) => {
    console.log('Pagination changed to page:', page);
    // Handle pagination if needed
};

const onSaveDroptrip = (data) => {
    console.log('Saving droptrip:', data);
    showDetails.value = false;
    // Refresh data or handle save
};

const onUpdateDroptrip = (data) => {
    console.log('Updating droptrip:', data);
    showDetails.value = false;
    // Refresh data or handle update
};

const onTriggerTopRightButton = (action, data) => {
    console.log('Triggering top right button:', action, data);
    showDetails.value = false;
    emit('triggerTopRightButton', action, data);
};

onMounted(() => {
    console.log('Droptrip List component mounted');
});
</script>

<style scoped>
.droptrip-list-container {
    position: relative;
    height: 100vh;
}

.main-content {
    padding: 1rem;
}

.slide-panel {
    z-index: 1000;
}
</style>
