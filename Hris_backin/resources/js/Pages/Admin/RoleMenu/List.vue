<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import CardBox from '@/Components/CardBox.vue'
import CoreTable from '@/Components/CoreTable.vue'
import FormControl from '@/Components/FormControl.vue'
import FormField from '@/Components/FormField.vue'
import BaseIcon from '@/Components/BaseIcon.vue';
import AsideDrawer from '@/Components/AsideDrawer.vue';
import SectionTitleLineWithButton from '@/Components/SectionTitleLineWithButton.vue';
import BaseButtons from '@/Components/BaseButtons.vue';
import BaseButton from '@/Components/BaseButton.vue';
import service from '@/Components/Toast/service'

// Define props and emits
const props = defineProps({
    masterlist: {
        type: [Array, Object],
        default: () => []
    },
    roles: {
        type: Array,
        default: () => []
    },
    menus: {
        type: Array,
        default: () => []
    },
    apps: {
        type: Array,
        default: () => []
    }
});

const emit = defineEmits(['triggerTopRightButton']);

const showDetails = ref(false);
const selectedDetails = ref(null);
const loading = ref(false);
const selectedItems = ref([]);
const drawerTitle = computed(() => (selectedDetails.value && selectedDetails.value.id === 0) ? 'Add Menu Role' : 'Edit Menu Role');

const toast = service()
const formErrors = ref([])
const page = usePage()

onMounted(() => {
    // console.log('List.vue mounted');
});

const permissionType = ref('view'); // 'view' or 'manage'

const roleOptions = computed(() => {
    return props.roles.map(role => ({
        text: role.name,
        value: role.id
    }));
});

const formApplicationOptions = computed(() => {
    return props.apps.map(app => ({
        text: app.name,
        value: app.id
    }));
});

const form = useForm({
    _token: page.props.csrf_token,
    id: null,
    role_id: null,
    app_id: null,
    menu_id: null,
    role_name: "",
    menu_name: "",
    permission: "", // Changed from permissions to permission (single value)
    created_at: "",
    updated_at: "",
});

const menuOptions = computed(() => {
    let filteredMenus = props.menus;

    if (form.app_id) {
        filteredMenus = props.menus.filter(menu => menu.app_id === form.app_id);
    }

    return filteredMenus.map(menu => ({
        text: menu.name,
        value: menu.id
    }));
});

const submitForm = () => {
    formErrors.value = [];

    // Set permission value from permissionType
    form.permission = permissionType.value;

    if (form.id && form.id !== 0) {
        form.put(route("admin.menu-role.update", form.id), {
            onSuccess: () => {
                toast.success("Role menu assignment updated successfully!");
                showDetails.value = false;
                selectedDetails.value = null;
                 // Reload the page to get fresh data
                router.reload({ only: ['masterlist'], preserveScroll: true });
            },
            onError: (errors) => {
                formErrors.value = Object.values(errors || {});
                toast.error("Please check the form for errors.");
            },
        });
    } else {
        form.post(route("admin.menu-role.store"), {
            onSuccess: () => {
                toast.success("Role menu assignment created successfully!");
                showDetails.value = false;
                selectedDetails.value = null;
                 // Reload the page to get fresh data
                router.reload({ only: ['masterlist'], preserveScroll: true });
            },
            onError: (errors) => {
                formErrors.value = Object.values(errors || {});
                toast.error("Please check the form for errors.");
            },
        });
    }
};

// Watch for changes in selectedDetails
watch(
    selectedDetails,
    (newData) => {
        if (newData) {
            form.id = newData.id || null;
            form.role_id = newData.role_id || null;
            form.menu_id = newData.menu_id || null;
            form.role_name = newData.role_name || "";
            form.menu_name = newData.menu_name || "";
            form.permission = newData.permission || ""; // Single value
            form.created_at = newData.created_at || "";
            form.updated_at = newData.updated_at || "";

            // Initialize application selection if editing
            if (newData.menu_id) {
                const menu = props.menus.find(m => m.id === newData.menu_id);
                if (menu && menu.app_id) {
                     form.app_id = menu.app_id;
                } else {
                     form.app_id = null;
                }
            } else {
                form.app_id = null;
            }

            // Set permissionType based on permission value
            if (newData.permission === 'manage') {
                permissionType.value = 'manage';
            } else {
                permissionType.value = 'view';
            }

            console.log('Final permissionType:', permissionType.value);
            console.log('Final form.permission:', form.permission);
        }
    },
    { deep: true }
);

// Watch for changes in permissionType to update form.permission
watch(permissionType, (newType) => {
    console.log('permissionType changed to:', newType);
    form.permission = newType;
    console.log('Updated form.permission to:', form.permission);
});

// Transform data to match what vtable expects
const applist = computed(() => {
    if (!props.masterlist) {
        return [];
    }

    // Check if we have paginated data structure or plain array
    let data = [];

    if (Array.isArray(props.masterlist)) {
        data = props.masterlist;
    } else if (props.masterlist && typeof props.masterlist === 'object') {
        // Handle Laravel pagination structure
        if (props.masterlist.data && Array.isArray(props.masterlist.data)) {
            data = props.masterlist.data;
        } else {
            // Handle other object structures
            data = Object.values(props.masterlist);
        }
    }

    // Transform the data to match vtable expectations
    return data.map(item => ({
        id: item.id,
        role_id: item.role_id,
        menu_id: item.menu_id,
        role_name: item.role && item.role.name ? (item.role.code ? `${item.role.name} (${item.role.code})` : item.role.name) : 'N/A',
        application_name: item.menu?.app?.name || 'N/A',
        menu_name: item.menu?.name || 'N/A',
        permission: item.permission || "", // Single value
        created_at: item.created_at,
        updated_at: item.updated_at,
        actions: item.id,
    }));
});

const onManageRoleMenu = (roleMenu) => {
    selectedDetails.value = roleMenu;
    showDetails.value = true;
};

const onRecordDelete = async (item) => {
    if (confirm(`Are you sure you want to delete this role menu assignment?`)) {
        try {
            await router.delete(route("admin.menu-role.destroy", item.id), {
                preserveScroll: true,
                onSuccess: () => {
                    showDetails.value = false;
                    selectedDetails.value = null;
                },
                onError: (errors) => {
                    alert('Error deleting role menu assignment. Please try again.');
                }
            });
        } catch (error) {
            alert('Error deleting role menu assignment. Please try again.');
        }
    }
};

const onAddNewRoleMenu = () => {
    // Create a new role menu assignment object for the Add form
    selectedDetails.value = {
        id: 0,
        role_id: null,
        menu_id: null,
        permission: "" // Single value
    };
    showDetails.value = true;
    permissionType.value = 'view';
    form.app_id = null;
};

const onTriggerTopRightButton = (action, data) => {
    // Handle the trigger event from Details component
    showDetails.value = false;
    selectedDetails.value = null;
    // Emit the event to parent (index.vue)
    emit('triggerTopRightButton', action, data);
};

const onCheckRows = (items) => {
    selectedItems.value = items;
};

const onBulkDelete = async () => {
    if (confirm(`Are you sure you want to delete ${selectedItems.value.length} selected items?`)) {
        try {
            await router.delete(route("admin.menu-role.bulk-destroy"), {
                data: { ids: selectedItems.value.map(item => item.id) },
                preserveScroll: true,
                onSuccess: () => {
                    selectedItems.value = []; // Clear selection
                    // Data will be automatically refreshed by Inertia
                },
                onError: (errors) => {
                    alert('Error deleting items. Please try again.');
                }
            });
        } catch (error) {
            alert('Error deleting items. Please try again.');
        }
    }
};

const onPaginationChange = (page) => {
    router.get(route("admin.menu-role.index"), { page }, { preserveState: true });
};

const selectedApplication = ref('All Applications');

const applicationOptions = computed(() => {
    const apps = new Set();
    applist.value.forEach(item => {
        if (item.application_name) {
            apps.add(item.application_name);
        }
    });
    return ['All Applications', ...Array.from(apps)];
});

const tableRows = computed(() => {
    if (selectedApplication.value === 'All Applications') {
        return applist.value;
    }
    return applist.value.filter(item => item.application_name === selectedApplication.value);
});

const openLink = (row) => {
  selectedDetails.value = row
  showDetails.value = true
}

const userHeader = [
  { label: 'ID', fieldName: 'id' },
  { label: 'Role', fieldName: 'role_name', type: 'link' },
  { label: 'Application', fieldName: 'application_name' },
  { label: 'Menu', fieldName: 'menu_name' },
  { label: 'Permission', fieldName: 'permission', type: 'slot', slotName: 'permissions' },
  { label: 'Actions', fieldName: 'actions', type: 'slot', slotName: 'actions' }
]

const getPermissionBadge = (permission) => {
    if (!permission) {
        return { label: 'No permission', color: 'warning' };
    }
    if (permission === 'manage') {
        return { label: 'Full Management', color: 'info' };
    } else if (permission === 'view') {
        return { label: 'View Only', color: 'success' };
    }
    return { label: permission, color: 'warning' };
};
const getPermissionBadgeClass = (color) => {
  const map = {
    info: 'inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 border border-blue-200 dark:bg-blue-900 dark:text-blue-100 dark:border-blue-800',
    success: 'inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 border border-green-200 dark:bg-green-900 dark:text-green-100 dark:border-green-800',
    warning: 'inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 border border-yellow-200 dark:bg-yellow-900 dark:text-yellow-100 dark:border-yellow-800',
    danger: 'inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 border border-red-200 dark:bg-red-900 dark:text-red-100 dark:border-red-800',
  }
  return map[color] ?? map.warning
}
</script>

<template>
    <Head title="Role Menu" />
    <AdminLayout>

         <SectionTitleLineWithButton :icon="'mdiListBox'" title="Role Menu" main>
          <BaseButtons>
            <BaseButton v-if="selectedItems.length > 0 && selectedItems.length < tableRows.length"
                       :icon="'mdiDelete'"
                       label="Remove"
                       color="danger"
                       rounded-full
                       small
                       @click="onBulkDelete" />
            <BaseButton :icon="'mdiPlus'"
                       label="Add Role Menu"
                       color="info"
                       rounded-full
                       small
                       @click="onAddNewRoleMenu" />
          </BaseButtons>
        </SectionTitleLineWithButton>


                <CardBox class="flex-1 p-6" has-table>
                  <CoreTable v-if="tableRows.length > 0"
                             :table-rows="tableRows"
                             :table-header="userHeader"
                             table-name="role-menu-assignments"
                             searchable-fields="role_name,menu_name,permission,application_name,created_at"
                             :is-paginated="true"
                             :is-show-row-checkbox="true"
                             @checkRows="onCheckRows"
                             @openLink="openLink"
                  >


                    <template #permissions="{ slotProp }">
                      <div class="permissions-list">
                        <span
                          v-if="slotProp.permission"
                          :class="getPermissionBadgeClass(getPermissionBadge(slotProp.permission).color)"
                          class="mr-1 mb-1"
                        >{{ getPermissionBadge(slotProp.permission).label }}</span>
                        <span v-else class="text-muted">
                          No permission
                        </span>
                      </div>
                    </template>

                    <template #actions="{ slotProp }">
                        <div class="flex gap-2">
                            <BaseButton small color="warning" @click="onManageRoleMenu(slotProp.raw_item || slotProp)" label="Edit" />
                            <BaseButton small color="danger" @click="onRecordDelete(slotProp.raw_item || slotProp)" label="Delete" />
                        </div>
                    </template>
                  </CoreTable>
                  <div v-else class="text-gray-500">No role menu assignments found</div>
                </CardBox>


            <!-- Right slide panel -->
            <AsideDrawer :title="drawerTitle" :is-open="showDetails" @closeDrawer="showDetails = false" class="shadow-lg shadow-blue-500/50">

                  <!-- Debug information (optional - remove in production) -->
                  <div v-if="false" class="debug-info p-2 bg-gray-100 rounded mb-4 text-xs">
                      <p>Debug Info:</p>
                      <p>permissionType: {{ permissionType }}</p>
                      <p>form.permission: {{ form.permission }}</p>
                      <p>selectedDetails permission: {{ selectedDetails?.permission }}</p>
                  </div>

                  <form @submit.prevent="submitForm">
                    <div v-if="formErrors.length" class="mb-4 text-red-500">
                        <ul>
                            <li v-for="(err, i) in formErrors" :key="i">{{ err }}</li>
                        </ul>
                    </div>

                    <div class="mb-4">
                        <label class="font-bold dark:text-gray-200 mb-2 block">Role</label>
                        <FormControl
                            v-model="form.role_id"
                            :options="roleOptions"
                            type="select"
                            placeholder="Select a role"
                            class="w-full"
                        />
                        <div v-if="form.errors.role_id" class="text-red-500 text-sm mt-1">{{ form.errors.role_id }}</div>
                    </div>

                    <div class="mb-4">
                        <label class="font-bold dark:text-gray-200 mb-2 block">Application</label>
                        <FormControl
                            v-model="form.app_id"
                            :options="formApplicationOptions"
                            type="select"
                            placeholder="Select an application"
                            class="w-full"
                        />
                    </div>

                    <div class="mb-4">
                        <label class="font-bold dark:text-gray-200 mb-2 block">Menu</label>
                        <FormControl
                            v-model="form.menu_id"
                            :options="menuOptions"
                            type="select"
                            placeholder="Select a menu"
                            class="w-full"
                            :disabled="!form.app_id"
                        />
                        <div v-if="form.errors.menu_id" class="text-red-500 text-sm mt-1">{{ form.errors.menu_id }}</div>
                    </div>

                    <div class="mb-4 permissions-section p-4 border border-gray-200 rounded bg-gray-50 dark:bg-gray-800 dark:border-gray-700">
                        <h4 class="font-bold dark:text-gray-200 mb-3">Permission Type</h4>
                        <div class="flex gap-4 items-center">
                          <label class="flex items-center gap-2">
                            <input type="radio" value="view" v-model="permissionType" class="text-blue-600 focus:ring-blue-500" />
                            <span class="dark:text-gray-200">View Only</span>
                          </label>
                          <label class="flex items-center gap-2">
                            <input type="radio" value="manage" v-model="permissionType" class="text-blue-600 focus:ring-blue-500" />
                            <span class="dark:text-gray-200">Full Management</span>
                          </label>
                        </div>
                        <div v-if="form.errors.permission" class="text-red-500 text-sm mt-2">
                            {{ form.errors.permission }}
                        </div>
                    </div>

                    <div class="mt-4 flex flex-col gap-3">
                        <BaseButton type="submit" :label="form.id === 0 || !form.id ? 'Create Assignment' : 'Update Assignment'" color="info" :loading="form.processing" />
                        <BaseButton label="Close" color="danger" outline @click="showDetails = false" />
                    </div>
                  </form>

            </AsideDrawer>

    </AdminLayout>
</template>

<style>
</style>

<style scoped>
.rolemenu-list-container {
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

.card-title {
    font-size: 1.5rem;
    font-weight: bold;
}

.permissions-list {
    max-width: 200px;
}

.text-muted {
    color: #6c757d;
    font-style: italic;
}
</style>
