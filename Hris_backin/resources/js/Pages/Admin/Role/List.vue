<template>
    <Head title="Role Management" />
    <AdminLayout>
        <SectionTitleLineWithButton :icon="'mdiListBox'" title="Role Management" main>
          <BaseButtons>
            <BaseButton :icon="'mdiPlus'" label="Add Role" color="info" rounded-full small @click="onAddNewRole" />
          </BaseButtons>
        </SectionTitleLineWithButton>


                <CardBox class="flex-1 p-6" has-table>
                  <CoreTable v-if="rolelist.length > 0"
                             :table-rows="rolelist"
                             :table-header="roleHeader"
                             table-name="roles"
                             searchable-fields="name,description,is_active,created_at,updated_at"
                             :is-paginated="true"
                             @openLink="openLink"
                  >

                  </CoreTable>
                  <div v-else class="text-gray-500">Click Add Role button to create your first record</div>
                </CardBox>


        <!-- Right slide panel -->
        <AsideDrawer :title="drawerTitle" :is-open="showDetails" @closeDrawer="showDetails = false" class="shadow-lg shadow-blue-500/50">

            <Details
              v-if="selectedDetails"
              :data="selectedDetails"
              :is-new="selectedDetails.id === 0"
              @close="showDetails = false"
              @save="onSaveRole"
              @update="onUpdateRole"
              @triggerTopRightButton="onTriggerTopRightButton"
            />

        </AsideDrawer>
    </AdminLayout>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, router } from '@inertiajs/vue3';
// Removed vtable import
// import vtable from '@/Components/vtable.vue';
import Details from './widgets/Details.vue';
import moment from 'moment'
import CardBox from '@/Components/CardBox.vue'
import CoreTable from '@/Components/CoreTable.vue'
import AsideDrawer from '@/Components/AsideDrawer.vue'
import SectionTitleLineWithButton from '@/Components/SectionTitleLineWithButton.vue'
import BaseButton from '@/Components/BaseButton.vue'
import BaseButtons from '@/Components/BaseButtons.vue'
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
const drawerTitle = computed(() => (selectedDetails.value && selectedDetails.value.id === 0) ? 'Add Role' : 'Manage Role');

// Transform data to match what vtable expects
const rolelist = computed(() => {


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
            description: item.description || '',
            is_active: item.is_active ? 'A' : 'I',
            created_at: item.created_at || '',
            updated_at: item.updated_at || ''
        };


        return transformedItem;
    }).filter(item => item !== null); // Remove any null items


    return transformed;
});

// Event handlers
const onDetailRowClick = (data) => {
    selectedDetails.value = data;
    showDetails.value = true;
};

const onManageRole = (role) => {
    selectedDetails.value = role;
    showDetails.value = true;
};

const onRecordDelete = async (item) => {
    if (confirm(`Are you sure you want to delete the role "${item.name}"?`)) {
        try {
            await router.delete(`/admin/role/${item.id}`, {
                preserveScroll: true,
                onSuccess: () => {
                    console.log('Role deleted successfully');
                    showDetails.value = false;
                    selectedDetails.value = null;
                },
                onError: (errors) => {
                    console.error('Delete error:', errors);
                    alert('Error deleting role. Please try again.');
                }
            });
        } catch (error) {
            console.error('Delete error:', error);
            alert('Error deleting role. Please try again.');
        }
    }
};

const onSaveRole = () => {
    // Handle save event from Details component
    showDetails.value = false;
    selectedDetails.value = null;
    // The page will refresh with updated data from Inertia
};

const onUpdateRole = () => {
    // Handle update event from Details component
    showDetails.value = false;
    selectedDetails.value = null;
    // The page will refresh with updated data from Inertia
};

const onAddNewRole = () => {
    selectedDetails.value = {
        id: 0,
        name: '',
        description: '',
        is_active: 'A',
        created_at: new Date().toISOString(),
        updated_at: new Date().toISOString()
    };
    showDetails.value = true;
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

// CoreTable header
const roleHeader = [
  { label: 'Role Name', fieldName: 'name', type: 'link' },
  { label: 'Description', fieldName: 'description' },
  { label: 'Status', fieldName: 'is_active', type: 'activeinactive' },
  { label: 'Created', fieldName: 'created_at', type: 'datetime' },
  { label: 'Updated', fieldName: 'updated_at', type: 'datetime' }
];

// Link click handler from CoreTable
const openLink = (row) => {
  selectedDetails.value = row;
  showDetails.value = true;
};

</script>

<style scoped>
.role-list-container {
    position: relative;
    width: 100%;
    height: 100vh;
    overflow: hidden;
}

.main-content {
    width: 100%;
    height: 100%;
    overflow-y: auto;
    padding: 1rem;
}

.slide-panel {
    position: fixed !important;
    top: 0;
    right: 0;
    height: 100vh;
    z-index: 1000;
    box-shadow: -2px 0 8px rgba(0, 0, 0, 0.1);
    display: flex;
    flex-direction: column;
}
</style>
