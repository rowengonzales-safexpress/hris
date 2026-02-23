<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { useForm, usePage } from "@inertiajs/vue3";
import { ref, watch, onMounted, computed } from "vue";
import { router } from "@inertiajs/vue3";
import BaseButton from '@/Components/BaseButton.vue'
import BaseIcon from '@/Components/BaseIcon.vue'
import CardBox from '@/Components/CardBox.vue'
import FormControl from '@/Components/FormControl.vue'
import FormField from '@/Components/FormField.vue'
import SectionTitleLineWithButton from '@/Components/SectionTitleLineWithButton.vue'
import FormWizard from '@/Components/FormWizard.vue'
import TabContent from '@/Components/TabContent.vue'
import Checkbox from '@/Components/Checkbox.vue'
import axios from "axios";
import service from '@/Components/Toast/service'

const toast = service()

const props = defineProps({
    action: String,
    formdata: Object,
    roles: Array,
    applications: Array,
    userMenus: Array,
});

const emit = defineEmits(["triggerTopRightButton"]);

// Expandable sections state
const userDetailsExpanded = ref(true);
const menuRightsExpanded = ref(false);

// Form initialization
const page = usePage()
const form = useForm({
  _token: page.props.csrf_token,
  name: "",
  first_name: "",
  last_name: "",
  email: "",
  user_type: "",
  password: "",
  password_confirmation: "",
  member_role: null,
  status: false,
  app_ids: [],
  menu_permissions_by_app: {},
});
const formErrors = ref([])
const showPasswordField = ref(false)

const togglePasswordField = () => {
    showPasswordField.value = !showPasswordField.value
    if (!showPasswordField.value) {
        form.password = ''
        form.password_confirmation = ''
    }
}

// Role options
const allRoles = ref([]);
const roleOptions = computed(() => {
    return allRoles.value.map(role => ({
        id: role.id,
        label: role.code ? `${role.name} (${role.code})` : role.name,
        app_id: role.app_id
    }));
});

// Filtered role options for Menu Rights tab
const filteredRoleOptions = computed(() => {
    if (!selectedApplication.value) return [];

    return roleOptions.value.filter(role => role.app_id === selectedApplication.value.id);
});

// Applications and menus data
const selectedApplication = ref(null);
const coreApplications = ref([]);
const selectedApplicationMenus = ref([]);
const isLoadingApps = ref(false);
const isLoadingMenus = ref(false);
const selectedRole = ref(null);
const userApplications = ref([]);
const saving = ref(false);

// Store menu selections per application
const menuSelections = ref({}); // Structure: { [appId]: { [menuId]: { selected: boolean, manage: boolean, view: boolean } } }

// Computed property for selected application menus
const selectedAppMenus = computed(() => {
    if (!selectedApplication.value) return [];
    return selectedApplicationMenus.value;
});

// Password Validation Computed Properties
const validations = computed(() => {
  const pwd = form.password || ''
  return {
    length: pwd.length >= 8,
    upper: /[A-Z]/.test(pwd),
    lower: /[a-z]/.test(pwd),
    number: /[0-9]/.test(pwd),
    special: /[!@#$%^&*]/.test(pwd)
  }
})

const strengthScore = computed(() => {
  const v = validations.value
  let score = 0
  if (v.length) score++
  if (v.upper) score++
  if (v.lower) score++
  if (v.number) score++
  if (v.special) score++
  return score
})

const strengthLabel = computed(() => {
  const score = strengthScore.value
  if (score <= 1) return 'Very Weak'
  if (score === 2) return 'Weak'
  if (score === 3) return 'Medium'
  if (score === 4) return 'Strong'
  return 'Very Strong'
})

const strengthColor = computed(() => {
  const score = strengthScore.value
  if (score <= 1) return 'text-red-500'
  if (score === 2) return 'text-orange-500'
  if (score === 3) return 'text-yellow-500'
  if (score === 4) return 'text-blue-500'
  return 'text-green-600'
})

const strengthBarColor = computed(() => {
  const score = strengthScore.value
  if (score <= 1) return 'bg-red-500'
  if (score === 2) return 'bg-orange-500'
  if (score === 3) return 'bg-yellow-500'
  if (score === 4) return 'bg-blue-500'
  return 'bg-green-600'
})

const strengthWidth = computed(() => {
  return `${(strengthScore.value / 5) * 100}%`
})

const passwordMatch = computed(() => {
  return form.password && form.password_confirmation && form.password === form.password_confirmation
})


// Computed property for "Select All" checkbox state
const allManageChecked = computed({
    get() {
        const rows = selectedApplicationMenus.value || [];
        if (!rows.length) return false;
        return rows.every(m => !!m.manage);
    },
    set(val) {
        const rows = selectedApplicationMenus.value || [];
        rows.forEach(m => {
            m.manage = !!val;
            m.view = false; // If manage is checked, uncheck view
            m.selected = !!val; // If manage is checked, select the menu
        });

        // Save to menu selections
        if (selectedApplication.value) {
            saveCurrentAppSelections();
        }
    }
});

const groupedMenus = computed(() => {
    const groups = {};
    (selectedApplicationMenus.value || []).forEach(m => {
        const key = m.group || 'Others';
        if (!groups[key]) groups[key] = [];
        groups[key].push(m);
    });
    return Object.entries(groups).map(([group, items]) => ({ group, items }));
});

// Check if all menus in a group are selected
const isGroupAllSelected = (grp) => {
    return (grp.items || []).every(m => m.selected);
};

// Check if all manage are selected in a group
const isGroupAllManage = (grp) => {
    return (grp.items || []).every(m => m.manage);
};

const selectAllInGroup = (grp) => {
    const allSelected = isGroupAllSelected(grp);

    (grp.items || []).forEach(m => {
        if (allSelected) {
            // Clear all
            m.selected = false;
            m.manage = false;
            m.view = false;
        } else {
            // Select all with manage permission
            m.selected = true;
            m.manage = true;
            m.view = false;
        }
    });

    // Save to menu selections
    if (selectedApplication.value) {
        saveCurrentAppSelections();
    }
};

const clearAllInGroup = (grp) => {
    (grp.items || []).forEach(m => {
        m.selected = false;
        m.manage = false;
        m.view = false;
    });

    // Save to menu selections
    if (selectedApplication.value) {
        saveCurrentAppSelections();
    }
};

// Handle manage checkbox change
const handleManageChange = (menu, value) => {
    menu.manage = value;
    if (value) {
        // If manage is checked, uncheck view
        menu.view = false;
    }
};

// Handle view checkbox change
const handleViewChange = (menu, value) => {
    menu.view = value;
    if (value) {
        // If view is checked, uncheck manage
        menu.manage = false;
    }
};

// Handle menu selection change
const handleMenuSelection = (menu, value) => {
    menu.selected = value;
    if (!value) {
        // If menu is unselected, clear both permissions
        menu.manage = false;
        menu.view = false;
    }
};

// Save all application menu selections
const saveAllAppSelections = () => {
    // Make sure we save selections for ALL applications, not just selected ones
    coreApplications.value.forEach(app => {
        // If this app has been viewed/selected before, save its current state
        if (menuSelections.value[app.id]) {
            // Already saved, no need to save again
        } else if (selectedApplication.value && selectedApplication.value.id === app.id) {
            // This is the currently selected app, save its selections
            saveCurrentAppSelections();
        } else {
            // For apps that haven't been viewed yet, initialize with empty selections
            // But we need to load their existing permissions first
            if (props.formdata && props.formdata.id) {
                // For existing user, we should load their permissions for this app
                // This will be handled when the app is selected
            }
        }
    });
};

// Methods
const selectApplication = async (app) => {
    // Save current application's menu selections before switching
    if (selectedApplication.value) {
        saveCurrentAppSelections();
    }

    selectedApplication.value = app;

    // Mark all apps as unselected first
    coreApplications.value.forEach(a => a.selected = false);

    // Mark the clicked app as selected
    app.selected = true;

    // Load menus for the selected application
    await loadMenusForApplication(app.id);
    if (props.formdata && props.formdata.id) {
        await loadUserMenuPermissions(props.formdata.id, app.id);
    }
};

// Handle role selection change
const onRoleChange = (roleId) => {
    selectedRole.value = roleId;

    // If an application is selected, reload its menus with role permissions
    if (selectedApplication.value) {
        loadRoleMenuPermissions(roleId, selectedApplication.value.id, true);
    }
};

// Handle application checkbox change
const onApplicationCheckboxChange = (app) => {
    app.checked = !app.checked;
};

// Save current application's menu selections to memory
const saveCurrentAppSelections = () => {
    if (!selectedApplication.value || !selectedApplicationMenus.value.length) return;

    const appId = selectedApplication.value.id;
    const selections = {};

    selectedApplicationMenus.value.forEach(menu => {
        selections[menu.id] = {
            selected: menu.selected || false,
            manage: menu.manage || false,
            view: menu.view || false
        };
    });

    menuSelections.value[appId] = selections;
};

// Load saved menu selections for application
const loadSavedAppSelections = (appId) => {
    if (!menuSelections.value[appId]) return;

    const savedSelections = menuSelections.value[appId];

    selectedApplicationMenus.value.forEach(menu => {
        if (savedSelections[menu.id]) {
            const saved = savedSelections[menu.id];
            menu.selected = saved.selected;
            menu.manage = saved.manage;
            menu.view = saved.view;
        }
    });
};

// Save user applications
const saveUserApplications = async () => {
    if (!props.formdata || !props.formdata.id) return;

    saving.value = true;
    try {
        // First save user applications
        const checkedAppIds = coreApplications.value
            .filter(app => app.checked)
            .map(app => app.id);

        await axios.post(`/admin/user/${props.formdata.id}/applications`, {
            app_ids: checkedAppIds
        });

        // Then save menu selections for each application that has been viewed
        for (const appId in menuSelections.value) {
            const appSelections = menuSelections.value[appId];
            const menuPermissions = [];

            for (const menuId in appSelections) {
                const menu = appSelections[menuId];
                menuPermissions.push({
                    menu_id: parseInt(menuId),
                    permission: menu.selected ? (menu.manage ? 'manage' : (menu.view ? 'view' : 'none')) : 'none'
                });
            }

            if (menuPermissions.length > 0) {
                await axios.post(`/admin/user/${props.formdata.id}/menus/app/${appId}`, {
                    menu_permissions: menuPermissions
                });
            }
        }

        // Show success message
        console.log('User applications and menu permissions saved successfully');
        toast.add({
            message: "User applications and menu permissions saved successfully!",
            color: "success",
        });
    } catch (error) {
        console.error('Error saving user applications:', error);
        toast.add({
            message: "Failed to save user applications.",
            color: "danger",
        });
    } finally {
        saving.value = false;
    }
};

// Save role menu permissions
const saveRoleMenuPermissions = async () => {
    if (!selectedRole.value || !selectedApplication.value) return;

    try {
        const menuPermissions = selectedApplicationMenus.value
            .filter(menu => menu.selected)
            .map(menu => ({
                menu_id: menu.id,
                permission: menu.manage ? 'manage' : (menu.view ? 'view' : 'none')
            }));

        await axios.post(`/admin/role/${selectedRole.value}/menus/app/${selectedApplication.value.id}`, {
            menu_permissions: menuPermissions
        });

        // Show success message
        console.log('Role menu permissions saved successfully');
        toast.add({
            message: "Role menu permissions saved successfully!",
            color: "success",
        });
    } catch (error) {
        console.error('Error saving role menu permissions:', error);
        toast.add({
            message: "Failed to save role menu permissions.",
            color: "danger",
        });
    }
};

const loadCoreApplications = async () => {
    try {
        isLoadingApps.value = true;
        const response = await axios.get('/admin/core-apps');
        coreApplications.value = response.data.map(app => ({
            ...app,
            selected: false,
            checked: false // For checkbox in application list
        }));

        // If editing a user, load their applications
        if (props.formdata && props.formdata.id) {
            await loadUserApplications(props.formdata.id);
        }
    } catch (error) {
        console.error('Error loading core applications:', error);
    } finally {
        isLoadingApps.value = false;
    }
};

// Load all user menu permissions for all applications
const loadAllUserMenuPermissions = async (userId) => {
    try {
        // Load user's applications first
        await loadUserApplications(userId);

        // For each application the user has, load menu permissions
        for (const app of coreApplications.value) {
            if (app.checked) {
                // Load permissions for this app
                await loadUserMenuPermissions(userId, app.id);

                // Also make sure selections are saved
                if (selectedApplication.value && selectedApplication.value.id === app.id) {
                    saveCurrentAppSelections();
                }
            }
        }
    } catch (error) {
        console.error('Error loading all user menu permissions:', error);
    }
};

// Load user applications (for checkboxes)
const loadUserApplications = async (userId) => {
    try {
        const response = await axios.get(`/admin/user/${userId}/applications`);
        userApplications.value = response.data;

        // Mark applications as checked if user has them
        coreApplications.value.forEach(app => {
            app.checked = userApplications.value.some(userApp => userApp.id === app.id);
        });
    } catch (error) {
        console.error('Error loading user applications:', error);
    }
};

const loadMenusForApplication = async (appId) => {
    try {
        isLoadingMenus.value = true;
        const response = await axios.get(`/admin/menus/app/${appId}`);
        selectedApplicationMenus.value = response.data.map(menu => ({
            ...menu,
            selected: false,
            manage: false,
            view: false,
            hasPermission: false // Will be set based on core_rolemenu
        }));

        // Load saved selections first (if any)
        loadSavedAppSelections(appId);

        // If a role is selected, load role menu permissions
        if (selectedRole.value) {
            await loadRoleMenuPermissions(selectedRole.value, appId);
        }
    } catch (error) {
        console.error('Error loading menus for application:', error);
        selectedApplicationMenus.value = [];
    } finally {
        isLoadingMenus.value = false;
    }
};

// Load role menu permissions
const loadRoleMenuPermissions = async (roleId, appId, force = false) => {
    try {
        const response = await axios.get(`/admin/role/${roleId}/menus/app/${appId}`);
        const rolePermissions = response.data;

        // Only apply role permissions if we don't have saved user selections or if forced
        if (force || !menuSelections.value[appId]) {
            selectedApplicationMenus.value.forEach(menu => {
                const permission = rolePermissions.find(p => p.id === menu.id);
                if (permission) {
                    menu.hasPermission = true;
                    menu.selected = true;
                    if (permission.permission === 'manage') {
                        menu.manage = true;
                        menu.view = false;
                    } else if (permission.permission === 'view') {
                        menu.manage = false;
                        menu.view = true;
                    }
                } else {
                    menu.hasPermission = false;
                    menu.selected = false;
                    menu.manage = false;
                    menu.view = false;
                }
            });

            // If forced, save the selections immediately
            if (force) {
                saveCurrentAppSelections();
            }
        }
    } catch (error) {
        console.error('Error loading role menu permissions:', error);
    }
};

const loadUserMenuPermissions = async (userId, appId) => {
    try {
        const response = await axios.get(`/admin/user/${userId}/menus/app/${appId}`);
        const userPermissions = response.data;

        // Update menu permissions based on user's existing permissions
        selectedApplicationMenus.value.forEach(menu => {
            const permission = userPermissions.find(p => p.id === menu.id);
            if (permission) {
                menu.selected = true;
                if (permission.permission === 'manage') {
                    menu.manage = true;
                    menu.view = false;
                } else if (permission.permission === 'view') {
                    menu.manage = false;
                    menu.view = true;
                }
            } else {
                // If no user permission exists, keep current selection or default to false
                if (!menuSelections.value[appId]) {
                    menu.selected = false;
                    menu.manage = false;
                    menu.view = false;
                }
            }
        });

        // Save these selections
        saveCurrentAppSelections();
    } catch (error) {
        console.error('Error loading user menu permissions:', error);
    }
};

// Debug function to check what's being sent
const debugFormData = () => {
    console.log('Form Data:', form);
    console.log('Menu Selections:', menuSelections.value);
    console.log('Checked Apps:', coreApplications.value.filter(app => app.checked).map(app => app.id));

    // Check if all apps have menu selections
    coreApplications.value.forEach(app => {
        if (menuSelections.value[app.id]) {
            console.log(`App ${app.name} has ${Object.keys(menuSelections.value[app.id]).length} menu selections`);
        } else {
            console.log(`App ${app.name} has NO menu selections`);
        }
    });
};

// Watch for menu selection changes and save them
watch(selectedApplicationMenus, () => {
    if (selectedApplication.value) {
        // Debounce the save to prevent too many calls
        clearTimeout(saveTimeout);
        saveTimeout = setTimeout(() => {
            saveCurrentAppSelections();
        }, 500);
    }
}, { deep: true });

let saveTimeout = null;

// Applications list
const applicationsList = ref([]);

// Access rights for selected applications
const accessRights = ref([
    { name: "Administrator", active: true },
    { name: "iTrak", active: true },
]);

// Initialize role options
onMounted(() => {
    if (props.roles && Array.isArray(props.roles)) {
        allRoles.value = props.roles;

        // Normalize member_role option object during mount
        if (form.member_role && typeof form.member_role !== 'object') {
            const match = roleOptions.value.find(r => r.id === form.member_role)
            if (match) form.member_role = match
        }
    }

    // Load core applications when component mounts
    loadCoreApplications().then(() => {
        // If editing a user, load all their menu permissions
        if (props.formdata && props.formdata.id) {
            loadAllUserMenuPermissions(props.formdata.id);
            if (!form.user_type) {
                loadFullUserDetails(props.formdata.id);
            }
        }
    });

    // Initialize applications list from props or use empty array
    if (props.applications && props.applications.length > 0) {
        applicationsList.value = props.applications.map(app => ({
            id: app.id,
            name: app.name,
            code: app.code,
            selected: false
        }));

        // Select first application by default
        if (applicationsList.value.length > 0) {
            selectedApplication.value = applicationsList.value[0];
            applicationsList.value[0].selected = true;
        }
    }
});

const loadFullUserDetails = async (userId) => {
  try {
    const resp = await axios.get(`/admin/user/${userId}`)
    const u = resp.data || {}
    form.user_type = u.user_type ?? form.user_type
    form.member_role = u.member_role ?? form.member_role
    form.first_name = u.first_name ?? form.first_name
    form.last_name = u.last_name ?? form.last_name
  } catch (e) {
    console.error('Failed to load user details', e)
  }
}

const required = (value) => !!value || "This field is required";

// Submit function
const submit = () => {
    // Save all selections first
    saveAllAppSelections();

    // Debug
    debugFormData();

    formErrors.value = []
    if (!form.user_type || (typeof form.user_type === 'string' && !form.user_type.trim())) {
        formErrors.value.push('User type is required')
        return
    }
    form.member_role = typeof form.member_role === 'object' ? (form.member_role?.id ?? null) : form.member_role

    // Get checked application IDs
    const checkedAppIds = coreApplications.value
        .filter(app => app.checked)
        .map(app => app.id)

    const menuPermissionsByApp = {}

    coreApplications.value.forEach(app => {
        const appId = app.id
        const appSelections = menuSelections.value[appId]

        if (!app.checked) {
            const menuPermissions = []
            if (selectedApplicationMenus.value.length > 0 && selectedApplication.value?.id === appId) {
                selectedApplicationMenus.value.forEach(menu => {
                    menuPermissions.push({ menu_id: menu.id, permission: 'none' })
                })
            }
            menuPermissionsByApp[appId] = menuPermissions
            return
        }

        if (appSelections) {
            const menuPermissions = []
            for (const menuId in appSelections) {
                const menu = appSelections[menuId]
                menuPermissions.push({
                    menu_id: parseInt(menuId),
                    permission: menu.selected ? (menu.manage ? 'manage' : (menu.view ? 'view' : 'none')) : 'none'
                })
            }
            menuPermissionsByApp[appId] = menuPermissions
        }
    })

    // Add to form data
    form.app_ids = checkedAppIds
    form.menu_permissions_by_app = menuPermissionsByApp

    if (props.formdata && props.formdata.id) {
        const originalStatus = !!form.status;
        form.status = originalStatus ? 'A' : 'I';
        form.put(route("admin.user.update", props.formdata.id), {
            onSuccess: () => {
                router.visit(route("admin.user.index"));
            },
            onError: (errors) => {
                formErrors.value = Object.values(errors || {})
                toast.error("Failed to update user.");
            },
            onFinish: () => { form.status = originalStatus },
        });
    } else {
        const originalStatus = !!form.status;
        form.status = originalStatus ? 'A' : 'I';
        form.post(route("admin.user.store"), {
            onSuccess: () => {
                router.visit(route("admin.user.index"));
            },
            onError: (errors) => {
                formErrors.value = Object.values(errors || {})
                toast.error("Failed to create user.");
            },
            onFinish: () => { form.status = originalStatus },
        });
    }
};

// Reset form
const resetForm = () => {
    form.reset();
    form.clearErrors();
};

// Watch for formdata changes
watch(
    () => props.formdata,
    (newData) => {
      if (newData) {
        form.name = newData.name || "";
        form.first_name = newData.first_name || "";
        form.last_name = newData.last_name || "";
        form.email = newData.email || "";
        form.user_type = newData.user_type || newData.type || "";
        form.member_role = newData.member_role || form.member_role || null;
        form.status = ((newData.status !== undefined ? newData.status : 'I') === 'A');
        // Don't populate password fields for editing
        form.password = "";
        form.password_confirmation = "";
        showPasswordField.value = false;
        } else {
            resetForm();
            showPasswordField.value = true;
        }
    },
    { immediate: true }
);

// Sync selectedRole when role field changes via FormControl
watch(
    () => form.member_role,
    (val) => {
        const id = typeof val === 'object' ? val?.id : val
        selectedRole.value = id || null
        if (selectedApplication.value && id) {
            loadRoleMenuPermissions(id, selectedApplication.value.id)
        }
    },
    { immediate: true }
)
</script>

<template>
    <AdminLayout>
        <SectionTitleLineWithButton :icon="'mdiFileDocumentPlus'" :title="(props.action === 'Add' ? 'Add' : 'Update') + ' User'" main>
            <BaseButton @click="emit('triggerTopRightButton', 'Lists')" :icon="'mdiViewList'" label="User Lists" color="contrast" rounded-full small />
        </SectionTitleLineWithButton>

        <CardBox>
            <FormWizard
                @on-complete="submit"
                color="#2563eb"
                step-size="xs"
            >
                <TabContent title="User Details" icon="mdiAccount">

                    <div v-if="formErrors.length" class="flex md12 mb-4 text-danger">
                        <ul>
                            <li v-for="(err, i) in formErrors" :key="i">{{ err }}</li>
                        </ul>
                    </div>

                            <FormField label="User Name" :error="!!form.errors.name" :error-messages="form.errors.name" required class="mb-4">
                                <FormControl v-model="form.name" type="text" />
                            </FormField>
                              <FormField label="First Name" :error="!!form.errors.first_name" :error-messages="form.errors.first_name" required class="mb-4">
                                <FormControl v-model="form.first_name" type="text" />
                            </FormField>
                              <FormField label="Last Name" :error="!!form.errors.last_name" :error-messages="form.errors.last_name" required class="mb-4">
                                <FormControl v-model="form.last_name" type="text" />
                            </FormField>
                            <FormField label="Email" :error="!!form.errors.email" :error-messages="form.errors.email" required class="mb-4">
                                <FormControl v-model="form.email" type="email" />
                            </FormField>
   <FormField label="User Type" :error="!!form.errors.user_type" :error-messages="form.errors.user_type" required class="mb-4">
                                <FormControl v-if="props.action === 'Add'" v-model="form.user_type" type="text" />
                                <FormControl v-else :model-value="form.user_type" type="text" disabled />
                            </FormField>
                            <FormField label="Role" :error="!!form.errors.member_role" :error-messages="form.errors.member_role" class="mb-4">
                                <FormControl v-model="form.member_role" :options="roleOptions" />
                            </FormField>



                            <!-- Password Requirements Info -->
                            <div v-if="props.formdata && props.formdata.id" class="mb-4">
                                <div class="flex justify-between items-center mb-2">

                                    <BaseButton
                                        type="button"
                                        small
                                        color="info"
                                        :label="showPasswordField ? 'Cancel' : 'Change Password'"
                                        @click="togglePasswordField"
                                    />
                                </div>
                                <p v-if="!showPasswordField" class="text-gray-500 text-sm italic">Leave blank to keep current password</p>
                            </div>

                            <div v-if="showPasswordField">
                                <div class="mb-4 bg-blue-50 dark:bg-blue-900/20 p-3 rounded-lg border border-blue-100 dark:border-blue-800">
                                    <div class="flex items-start">
                                        <BaseIcon path="mdiInformation" class="text-blue-500 mt-0.5 mr-2" w="w-5" h="h-5" />
                                        <div class="text-sm text-gray-600 dark:text-gray-300">
                                            <p class="font-semibold mb-1">Password Requirements</p>
                                            <ul class="list-disc pl-4 space-y-0.5 text-xs">
                                                <li>Must be at least 8 characters long</li>
                                                <li>Must meet at least 3 of the following criteria:</li>
                                                <ul class="list-disc pl-4 mt-0.5">
                                                    <li>At least one uppercase letter (A-Z)</li>
                                                    <li>At least one lowercase letter (a-z)</li>
                                                    <li>At least one number (0-9)</li>
                                                    <li>At least one special character (!@#$%^&*)</li>
                                                </ul>
                                            </ul>
                                        </div>
                                    </div>
                                </div>

                                <FormField label="Password" :error="!!form.errors.password" :error-messages="form.errors.password" :required="!props.formdata || !props.formdata.id" class="mb-4">
                                    <FormControl v-model="form.password" type="password" />
                                </FormField>

                                <!-- Strength Meter -->
                                <div v-if="(!props.formdata || !props.formdata.id || form.password) && form.password" class="mb-6 -mt-2 px-1">
                                    <div class="flex justify-between items-center mb-1">
                                        <span class="text-xs text-gray-500 dark:text-gray-400">Password strength: <span :class="['font-bold', strengthColor]">{{ strengthLabel }}</span></span>
                                    </div>
                                    <div class="h-1.5 w-full bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden mb-2">
                                        <div class="h-full transition-all duration-300" :class="strengthBarColor" :style="{ width: strengthWidth }"></div>
                                    </div>

                                    <div class="grid grid-cols-1 gap-1">
                                        <div class="flex items-center text-xs" :class="validations.length ? 'text-green-600' : 'text-gray-400'">
                                            <BaseIcon :path="validations.length ? 'mdiCheck' : 'mdiCircleSmall'" w="w-3.5" h="h-3.5" class="mr-1" />
                                            8+ characters
                                        </div>
                                        <div class="flex items-center text-xs" :class="validations.upper ? 'text-green-600' : 'text-gray-400'">
                                            <BaseIcon :path="validations.upper ? 'mdiCheck' : 'mdiCircleSmall'" w="w-3.5" h="h-3.5" class="mr-1" />
                                            Uppercase letter
                                        </div>
                                        <div class="flex items-center text-xs" :class="validations.lower ? 'text-green-600' : 'text-gray-400'">
                                            <BaseIcon :path="validations.lower ? 'mdiCheck' : 'mdiCircleSmall'" w="w-3.5" h="h-3.5" class="mr-1" />
                                            Lowercase letter
                                        </div>
                                        <div class="flex items-center text-xs" :class="validations.number ? 'text-green-600' : 'text-gray-400'">
                                            <BaseIcon :path="validations.number ? 'mdiCheck' : 'mdiCircleSmall'" w="w-3.5" h="h-3.5" class="mr-1" />
                                            Number
                                        </div>
                                        <div class="flex items-center text-xs" :class="validations.special ? 'text-green-600' : 'text-gray-400'">
                                            <BaseIcon :path="validations.special ? 'mdiCheck' : 'mdiCircleSmall'" w="w-3.5" h="h-3.5" class="mr-1" />
                                            Special character
                                        </div>
                                    </div>
                                </div>

                                <FormField label="Confirm Password" :error="!!form.errors.password_confirmation" :error-messages="form.errors.password_confirmation" :required="!props.formdata || !props.formdata.id" class="mb-4">
                                    <div>
                                        <FormControl v-model="form.password_confirmation" type="password" />
                                        <div v-if="form.password_confirmation" class="flex items-center text-xs mt-1" :class="passwordMatch ? 'text-green-500' : 'text-red-500'">
                                            <BaseIcon :path="passwordMatch ? 'mdiCheck' : 'mdiClose'" w="w-3.5" h="h-3.5" class="mr-1" />
                                            {{ passwordMatch ? 'Passwords match' : 'Passwords do not match' }}
                                        </div>
                                    </div>
                                </FormField>
                            </div>


                            <Checkbox v-model:checked="form.status" label="Active" class="mb-4" />


                </TabContent>

                <TabContent title="Menu Rights" icon="mdiSecurity">
                    <div class="menu-rights-content">
                        <div class="menu-rights-layout">
                            <div class="application-list-section">
                                <div class="application-list-header">
                                    <BaseIcon path="mdiApps" class="mr-2" />
                                    Application List
                                </div>
                                <div class="application-list-content">
                                    <div class="applications-container">
                                        <div
                                            v-for="app in coreApplications"
                                            :key="app.id"
                                            class="application-row"
                                            :class="{ 'selected': app.selected }"
                                            @click="selectApplication(app)"
                                        >
                                            <div class="flex items-center">
                                                <Checkbox v-model:checked="app.checked" :value="app.id" @click.stop />
                                                <span class="ml-2">{{ app.name }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="access-rights-section">
                                <div class="access-rights-header"><BaseIcon path="mdiSecurity" class="mr-2" />Access Rights</div>
                                <div class="access-rights-content">


                                    <div class="role-selection-container mb-4">
                                        <FormField label="Role">
                                            <FormControl v-model="selectedRole" label="Select Role for Menu Permissions" :options="filteredRoleOptions" class="role-select" @update:model-value="onRoleChange" />
                                        </FormField>

                                    </div>

                                    <div class="groups-grid">
                                        <div v-for="grp in groupedMenus" :key="grp.group" class="group-card">
                                            <div class="group-header">
                                                <div class="group-title">{{ selectedApplication?.name || 'Select an Application' }}</div>
                                                <div class="group-actions">
                                                    <button class="link-btn" @click="selectAllInGroup(grp)">
                                                        {{ isGroupAllSelected(grp) ? 'Clear All' : 'Select All' }}
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="group-body">
                                                <div v-for="menu in grp.items" :key="menu.id" class="group-row">
                                                    <div class="row-left">
                                                        <Checkbox
                                                            v-model:checked="menu.selected"
                                                            :value="menu.id"
                                                            @update:checked="handleMenuSelection(menu, $event)"
                                                        />
                                                        <span class="ml-2">{{ menu.name }}</span>
                                                    </div>
                                                    <div class="row-right">
                                                        <div class="perm-item">
                                                            <Checkbox
                                                                v-model:checked="menu.manage"
                                                                :disabled="!menu.selected"
                                                                @update:checked="handleManageChange(menu, $event)"
                                                            />
                                                            <span class="ml-2">manage</span>
                                                        </div>
                                                        <div class="perm-item">
                                                            <Checkbox
                                                                v-model:checked="menu.view"
                                                                :disabled="!menu.selected"
                                                                @update:checked="handleViewChange(menu, $event)"
                                                            />
                                                            <span class="ml-2">view</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>


                                </div>
                            </div>
                        </div>
                    </div>
                </TabContent>
            </FormWizard>
        </CardBox>
    </AdminLayout>
</template>

<style scoped>
.menu-rights-content {
    padding: 1rem;
}

.menu-rights-layout {
    display: flex;
    gap: 2rem;
    min-height: 500px;
}

/* Application List Section */
.application-list-section {
    flex: 0 0 300px;
    background-color: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
}
.dark .application-list-section {
    background-color: #1e293b;
    border-color: #334155;
}

.application-list-header {
    background-color: #2563eb;
    color: #ffffff;
    padding: 12px 16px;
    border-radius: 8px 8px 0 0;
    font-weight: 600;
    display: flex;
    align-items: center;
}

.application-list-content {
    padding: 16px;
}

.applications-container {
    max-height: 400px;
    overflow-y: auto;
}

.application-row {
    padding: 8px 12px;
    margin-bottom: 4px;
    border-radius: 4px;
    cursor: pointer;
    transition: background-color 0.2s;
}

.application-row:hover {
    background-color: #eff6ff;
}
.dark .application-row:hover {
    background-color: #1e3a8a;
}

.application-row.selected {
    background-color: #eff6ff;
    border-left: 4px solid #2563eb;
}
.dark .application-row.selected {
    background-color: #1e3a8a;
    border-left-color: #3b82f6;
}

/* Access Rights Section */
.access-rights-section {
    flex: 1;
    background-color: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
}
.dark .access-rights-section {
    background-color: #1e293b;
    border-color: #334155;
}

.access-rights-header {
    background-color: #2563eb;
    color: #ffffff;
    padding: 12px 16px;
    border-radius: 8px 8px 0 0;
    font-weight: 600;
    display: flex;
    align-items: center;
}

.access-rights-content {
    padding: 16px;
}

/* Grouped Menu Cards */
.groups-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: 1rem;
}

.group-card {
    background-color: #ffffff;
    color: inherit;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    overflow: hidden;
}
.dark .group-card {
    background-color: #1f2937;
    border-color: #374151;
}

.group-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 16px;
    background-color: #f8fafc;
}
.dark .group-header {
    background-color: #1e293b;
}

.group-title {
    font-weight: 600;
    font-size: 0.9rem;
}

.group-actions {
    display: flex;
    gap: 12px;
}

.link-btn {
    color: #3b82f6;
    font-size: 0.875rem;
    cursor: pointer;
    background: none;
    border: none;
    padding: 0;
    text-decoration: underline;
}

.link-btn:hover {
    color: #2563eb;
}

.group-body {
    padding: 12px 16px;
    max-height: 360px;
    overflow-y: auto;
}

.group-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 0;
    border-bottom: 1px solid #e2e8f0;
}
.dark .group-row {
    border-bottom-color: #374151;
}

.group-row:last-child {
    border-bottom: none;
}

.row-left {
    display: flex;
    align-items: center;
    flex: 1;
    min-width: 0;
}

.row-right {
    display: flex;
    align-items: center;
    gap: 24px;
    white-space: nowrap;
}

.perm-item {
    display: flex;
    align-items: center;
}

.perm-item span {
    font-size: 0.875rem;
    color: #64748b;
}
.dark .perm-item span {
    color: #94a3b8;
}

.role-selection-container {
    margin-bottom: 1rem;
}

.role-select {
    width: 100%;
}

.mb-4 {
    margin-bottom: 1rem;
}

.mr-2 {
    margin-right: 0.5rem;
}

.ml-2 {
    margin-left: 0.5rem;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .menu-rights-layout {
        flex-direction: column;
    }

    .application-list-section {
        flex: none;
        width: 100%;
    }

    .groups-grid {
        grid-template-columns: 1fr;
    }

    .group-row {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }

    .row-right {
        width: 100%;
        justify-content: space-between;
    }
}
</style>
