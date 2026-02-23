<script setup>
import { ref, watch } from "vue";
import { router } from "@inertiajs/vue3";
import { useForm } from "@inertiajs/vue3";
import FormField from '@/Components/FormField.vue'
import FormControl from '@/Components/FormControl.vue'

const props = defineProps({
    data: {
        type: Object,
        required: true
    },
    isNew: {
        type: Boolean,
        default: false
    }
});

const emit = defineEmits(['close', 'save', 'update', 'Manage', 'triggerTopRightButton']);

// Form for handling role data
const form = useForm({
    id: null,
    name: "",
    description: "",
    is_active: false,
    created_at: "",
    updated_at: "",
});

const localRole = ref({
    id: null,
    name: "",
    description: "",
    is_active: false,
    created_at: "",
    updated_at: "",
});

// Watch for changes in props.data
watch(() => props.data, (newData) => {
    if (newData) {
        localRole.value = { ...newData };

        // Update form data
        form.id = newData.id || null;
        form.name = newData.name || "";
        form.description = newData.description || "";
        form.is_active = newData.is_active || false;
        form.created_at = newData.created_at || "";
        form.updated_at = newData.updated_at || "";
    }
}, { immediate: true });

// Handle manage role button click
const handleManageRole = () => {
    emit("triggerTopRightButton", "Manage", localRole.value);
};

// Handle close sidebar
const handleClose = () => {
    emit("close");
};
</script>

<template>

      <FormField label="Role Name">
        <FormControl v-model="form.name" :disabled="true" />
      </FormField>
      <FormField label="Description">
        <FormControl v-model="form.description" type="textarea" :disabled="true" />
      </FormField>
      <FormField label="Active">
        <FormControl v-model="form.is_active" type="checkbox" :disabled="true" />
      </FormField>
      <FormField v-if="form.created_at" label="Created At">
        <FormControl :model-value="new Date(form.created_at).toLocaleString()" :disabled="true" />
      </FormField>
      <FormField v-if="form.updated_at" label="Updated At">
        <FormControl :model-value="new Date(form.updated_at).toLocaleString()" :disabled="true" />
      </FormField>

    <BaseButtons class="mt-2">
    <BaseButton color="info" @click="handleManageUser('Manage')" label="Manage" icon="mdiAccountBoxEditOutline" />
    <BaseButton color="danger" @click="handleClose" label="Close" icon="mdiClose" />
</BaseButtons>

</template>

<style scoped>
.role-details {
    padding: 1rem;
    height: 100%;
    display: flex;
    flex-direction: column;
}

.details-content {
    flex: 1;
    overflow-y: auto;
}

.details-actions {
    border-top: 1px solid #e0e0e0;
    padding-top: 1rem;
    margin-top: 1rem;
}

.w-full {
    width: 100%;
}
.action-button { padding: 0.5rem 1rem; border: 1px solid #e5e7eb; border-radius: 0.375rem; }
</style>
