<script setup>
import CardBox from '@/Components/CardBox.vue'
import BaseButton from '@/Components/BaseButton.vue'
import FormField from '@/Components/FormField.vue'
import FormControl from '@/Components/FormControl.vue'
import Checkbox from '@/Components/Checkbox.vue'
import service from '@/Components/Toast/service'
import { useForm, usePage } from "@inertiajs/vue3";
import { ref, watch, onMounted } from "vue";
import { router } from "@inertiajs/vue3";

const props = defineProps({
    action: String,
    formdata: Object,
    roles: Array,
    menus: Array,
});

const emit = defineEmits(["triggerTopRightButton"]);

// Form initialization
const page = usePage()
const form = useForm({
    _token: page.props.csrf_token,
    id: null,
    role_id: null,
    menu_id: null,
    permissions: [],
});
const formErrors = ref([])
const toast = service()

// Available permissions
const availablePermissions = [
    { text: "Create", value: "create" },
    { text: "Read", value: "read" },
    { text: "Update", value: "update" },
    { text: "Delete", value: "delete" },
];

// Role and Menu options
const roleOptions = ref([]);
const menuOptions = ref([]);

// Initialize options
onMounted(() => {
    if (props.roles) {
        roleOptions.value = props.roles.map(role => ({
            text: role.name,
            value: role.id
        }));
    }

    if (props.menus) {
        menuOptions.value = props.menus.map(menu => ({
            text: menu.name,
            value: menu.id
        }));
    }
});

// Watch for formdata changes
watch(
    () => props.formdata,
    (newData) => {
        if (newData) {
            form.id = newData.id || null;
            form.role_id = newData.role_id || null;
            form.menu_id = newData.menu_id || null;
            form.permissions = newData.permissions || [];
        }
    },
    { immediate: true }
);

// Submit function
const submit = () => {
    if (props.action === "Manage") {
        form.put(route("admin.menu-role.update", form.id), {
            onSuccess: () => {
                toast.success("Role menu assignment updated successfully!");
                emit("triggerTopRightButton", "lists");
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
                emit("triggerTopRightButton", "lists");
            },
            onError: (errors) => {
                formErrors.value = Object.values(errors || {});
                toast.error("Please check the form for errors.");
            },
        });
    }
};

// Reset form
const resetForm = () => {
    form.reset();
    form.clearErrors();
};

// Watch for changes in formdata prop
watch(
    () => props.formdata,
    (newData) => {
        if (newData) {
            form.id = newData.id || null;
            form.role_id = newData.role_id || null;
            form.menu_id = newData.menu_id || null;
            form.permissions = newData.permissions || [];
        }
    },
    { deep: true }
);
</script>

<template>
    <div class="row">
            <div class="flex md12">
                <CardBox>
                    <div class="p-4">
                        <h1 class="card-title text-secondary mb-4">
                            {{ props.action === "Add" ? "Add Role Menu Assignment" : "Edit Role Menu Assignment" }}
                        </h1>
                        <form @submit.prevent="submit">
                            <div v-if="formErrors.length" class="mb-4 text-danger">
                                <ul>
                                    <li v-for="(err, i) in formErrors" :key="i">{{ err }}</li>
                                </ul>
                            </div>
                            <div class="row">
                                <!-- Role Selection -->
                                <div class="flex md6 sm12">
                                    <FormField label="Role">
                                      <FormControl
                                        v-model="form.role_id"
                                        :options="roleOptions"
                                        type="select"
                                        placeholder="Select a role"
                                      />
                                    </FormField>
                                </div>

                                <!-- Menu Selection -->
                                <div class="flex md6 sm12">
                                    <FormField label="Menu">
                                      <FormControl
                                        v-model="form.menu_id"
                                        :options="menuOptions"
                                        type="select"
                                        placeholder="Select a menu"
                                      />
                                    </FormField>
                                </div>

                                <!-- Permissions -->
                                <div class="flex md12">
                                    <div class="permissions-section">
                                        <h4 class="mb-3">Permissions</h4>
                                        <div class="permissions-grid">
                                            <label
                                              v-for="permission in availablePermissions"
                                              :key="permission.value"
                                              class="flex items-center gap-2 permission-checkbox"
                                            >
                                              <Checkbox
                                                :checked="form.permissions"
                                                :value="permission.value"
                                                @update:checked="val => form.permissions = val"
                                              />
                                              <span>{{ permission.text }}</span>
                                            </label>
                                        </div>
                                        <div v-if="form.errors.permissions" class="error-message">
                                            {{ form.errors.permissions }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Form Actions -->
                            <div class="row">
                                <div class="flex">
                                    <BaseButton type="button" color="lightDark" @click="emit('triggerTopRightButton', 'lists')" class="mr-3" label="Back" />
                                    <BaseButton type="button" color="lightDark" @click="resetForm" class="mr-3" label="Reset" />
                                    <BaseButton type="submit" :loading="form.processing" :disabled="form.processing" :label="props.action === 'Add' ? 'Create Assignment' : 'Update Assignment'" />
                                </div>
                            </div>
                        </form>
                    </div>
                </CardBox>
            </div>
        </div>
</template>

<style scoped>
.card-title {
    font-size: 1.5rem;
    font-weight: bold;
}

.permissions-section {
    width: 100%;
    padding: 1rem;
    border: 1px solid #e0e0e0;
    border-radius: 4px;
    background-color: #f9f9f9;
}

.permissions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 1rem;
    margin-bottom: 1rem;
}

.permission-checkbox {
    margin-bottom: 0.5rem;
}

.error-message {
    color: #dc3545;
    font-size: 0.875rem;
    margin-top: 0.5rem;
}
</style>
