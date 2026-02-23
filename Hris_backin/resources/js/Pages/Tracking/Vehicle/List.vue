<template>
    <Head title="Vehicle Info" />
    <TrackingLayout>
        <div class="application-list-container">
            <div class="main-content">
                <SectionTitleLineWithButton :icon="'mdiListBox'" title="Vehicles" main>
  <BaseButton @click="emit('triggerTopRightButton', 'Add')" :icon="'mdiFileDocumentPlus'" label="Add Vehicle" color="contrast" rounded-full small />
</SectionTitleLineWithButton>

                <!-- Data loaded -->

               <CardBox class="flex-1 p-6" has-table>
                  <CoreTable v-if="tableRows.length > 0"
                             :table-rows="tableRows"
                             :table-header="userHeader"
                             table-name="vehicles"
                             searchable-fields="vehicle_code,plate_no,vehicle_type,current_status,is_active,created_at"
                             :is-paginated="true"
                             @openLink="openLink"
                 >
                 <template #row-action="scope">
                      <BaseIcon
                        v-if="scope.slotProp.is_active === 'A' || scope.slotProp.is_active === true || scope.slotProp.is_active === 1"
                        :path="'mdiCheckCircle'"
                        class="text-green-500"
                        size="20"
                        title="Active"
                      />
                      <BaseIcon
                        v-else
                        :path="'mdiCloseCircle'"
                        class="text-red-500"
                        size="20"
                        title="Inactive"
                      />
                    </template>
                </CoreTable>
                <div v-else class="text-gray-500">Click Add Vehicle button to create your first record</div>
               </CardBox>
            </div>


        </div>
    </TrackingLayout>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import TrackingLayout from '@/Layouts/TrackingLayout.vue';
import { Head, router } from '@inertiajs/vue3';
// import vtable from '@/Components/vtable.vue';

import moment from 'moment';
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
            branch_id: item.branch_id || '',
            vehicle_code: item.vehicle_code || '',
            plate_no: item.plate_no || '',
            vehicle_type: item.vehicle_type || '',
            current_status: item.current_status || '',
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
        'ACTIVE': 'Active',
        0: 'Inactive',
        'INACTIVE': 'Inactive',
    };
    return statusMap[is_active] || 'Unknown';
};

// Event handlers
const onDetailRowClick = (data) => {
    selectedDetails.value = data;
    emit('triggerTopRightButton', 'Manage', data)
};

const onManageVehicle = (vehicle) => {
    selectedDetails.value = vehicle;
    emit('triggerTopRightButton', 'Manage', vehicle)
};

const onRecordDelete = async (item) => {
    if (confirm(`Are you sure you want to delete the vehicle "${item.plate_no}"?`)) {
        try {
            await router.delete(`/tracking/vehicle/${item.id}`, {
                preserveScroll: true,
                onSuccess: () => {
                    console.log('Vehicle deleted successfully');
                    selectedDetails.value = null;
                },
                onError: (errors) => {
                    console.error('Delete error:', errors);
                    alert('Error deleting vehicle. Please try again.');
                }
            });
        } catch (error) {
            console.error('Delete error:', error);
            alert('Error deleting vehicle. Please try again.');
        }
    }
};

const onSaveVehicle = () => {
    selectedDetails.value = null;
};

const onUpdateVehicle = () => {
    selectedDetails.value = null;
};

const onAddNew = () => {
    // Create a new vehicle object for the Add form
    selectedDetails.value = {
        id: 0,
        branch_id: '',
        vehicle_code: '',
        plate_no: '',
        vehicle_type: '',
        current_status: '',
        is_active: 1
    };
    emit('triggerTopRightButton', 'Add');
};

const onTriggerTopRightButton = (action, data) => {
    selectedDetails.value = null;
    emit('triggerTopRightButton', action, data);
};

const onPaginationChange = (page) => {
    // Handle pagination changes if needed
    console.log('Pagination changed to page:', page);
};


const tableRows = computed(() => applist.value)
const openLink = (row) => {
  emit('triggerTopRightButton', 'Manage', row)
}
const userHeader = [
  { label: 'Code', fieldName: 'vehicle_code', type: 'link' },
  { label: 'Plate No', fieldName: 'plate_no' },
  { label: 'Type', fieldName: 'vehicle_type' },
  { label: 'Status', fieldName: 'current_status' },
  { label: 'Active', fieldName: 'is_active', type: 'slot' },
  { label: 'Created', fieldName: 'created_at', type: 'datetime' }
]
</script>
