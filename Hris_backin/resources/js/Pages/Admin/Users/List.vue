<template>
    <Head title="User List" />
    <AdminLayout>
 <SectionTitleLineWithButton :icon="'mdiListBox'" title="User List" main>
    <BaseButton @click="emit('triggerTopRightButton', 'Add')" :icon="'mdiFileDocumentPlus'" label="Add User" color="contrast" rounded-full small />
 </SectionTitleLineWithButton>

                <!-- Data loaded -->
                <CardBox class="flex-1 p-6" has-table>
                  <CoreTable v-if="tableRows.length > 0"
                             :table-rows="tableRows"
                             :table-header="userHeader"
                             table-name="users"
                             searchable-fields="name,email,role.name,status,created_at,updated_at"
                             :is-paginated="true"
                             @openLink="openLink"
                  />
                  <div v-else class="text-gray-500">Click Add User button to create your first record</div>
                </CardBox>


            <!-- Right slide panel -->
            <AsideDrawer :title="drawerTitle" :is-open="showDetails" @closeDrawer="showDetails = false" class="shadow-lg shadow-blue-500/50">

                <Details
                  v-if="selectedDetails"
                  :data="selectedDetails"
                  :is-new="selectedDetails.id === 0"
                  @close="showDetails = false"
                  @save="onSaveUser"
                  @update="onUpdateUser"
                  @triggerTopRightButton="onTriggerTopRightButton"
                />

            </AsideDrawer>

    </AdminLayout>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import BaseButton from '@/Components/BaseButton.vue'
import SectionTitleLineWithButton from '@/Components/SectionTitleLineWithButton.vue'
import Details from './widgets/Details.vue';
import AsideDrawer from '@/Components/AsideDrawer.vue';
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
const drawerTitle = computed(() => (selectedDetails.value && selectedDetails.value.id === 0) ? 'Add User' : 'Manage User');

// Event handlers
const onDetailRowClick = (data) => {
    selectedDetails.value = data;
    showDetails.value = true;
};

const onManageUser = (user) => {
    selectedDetails.value = user;
    showDetails.value = true;
};

const onRecordDelete = async (item) => {
    if (confirm(`Are you sure you want to delete the user "${item.name}"?`)) {
        try {
            await router.delete(route("admin.user.destroy", item.id), {
                preserveScroll: true,
                onSuccess: () => {
                    console.log('User deleted successfully');
                    showDetails.value = false;
                    selectedDetails.value = null;
                },
                onError: (errors) => {
                    console.error('Delete error:', errors);
                    alert('Error deleting user. Please try again.');
                }
            });
        } catch (error) {
            console.error('Delete error:', error);
            alert('Error deleting user. Please try again.');
        }
    }
};

const onSaveUser = () => {
    // Handle save event from Details component
    showDetails.value = false;
    selectedDetails.value = null;
    // The page will refresh with updated data from Inertia
};

const onUpdateUser = () => {
    // Handle update event from Details component
    showDetails.value = false;
    selectedDetails.value = null;
    // The page will refresh with updated data from Inertia
};



const onTriggerTopRightButton = (action, data) => {
    // Handle the trigger event from Details component
    showDetails.value = false;
    selectedDetails.value = null;
    // Emit the event to parent (index.vue)
    emit('triggerTopRightButton', action, data);
};

const onPaginationChange = (page) => {
    router.get(route("admin.user.index"), { page }, { preserveState: true });
};

// Table columns
const tableRows = computed(() => Array.isArray(props.masterlist) ? props.masterlist : (props.masterlist?.data ?? []))
const openLink = (row) => {
  selectedDetails.value = row
  showDetails.value = true
}
const userHeader = [
  { label: 'Name', fieldName: 'name', type: 'link' },
  { label: 'First Name', fieldName: 'first_name' },
  { label: 'Last Name', fieldName: 'last_name' },
  { label: 'Email', fieldName: 'email' },
  { label: 'Role', fieldName: 'role.name' },
  { label: 'Status', fieldName: 'status', type: 'activeinactive' },
  { label: 'Created At', fieldName: 'created_at', type: 'datetime' },
  { label: 'Updated At', fieldName: 'updated_at', type: 'datetime' }
]
const columns = ref([
    { key: 'name', label: 'Name', sortable: true,clickable: true },
    { key: 'email', label: 'Email', sortable: true },
    { key: 'role', label: 'Role', sortable: true },
    { key: 'status', label: 'Status', sortable: true },
    { key: 'actions', label: 'Actions', sortable: false }
]);


</script>

<style scoped>
.user-list-container {
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
