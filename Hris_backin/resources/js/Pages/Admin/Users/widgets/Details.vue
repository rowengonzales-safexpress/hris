<script setup>
import { ref, watch } from "vue";
import { router } from "@inertiajs/vue3";
import { useForm } from "@inertiajs/vue3";
import FormField from '@/Components/FormField.vue'
import FormControl from '@/Components/FormControl.vue'
import BaseButton from '@/Components/BaseButton.vue'
import BaseButtons from '@/Components/BaseButtons.vue'

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

// Form for display purposes
const form = useForm({
    name: "",
    email: "",
    username: "",
    role_name: "",
    is_active: false,

});

const localUser = ref({
    name: "",
    email: "",
    username: "",
    role_name: "",
    is_active: false,

});

// Watch for changes in props.data
watch(() => props.data, (newData) => {
    if (newData) {
        localUser.value = { ...newData };

        // Update form data
        form.name = newData.name || "";
        form.email = newData.email || "";
        form.username = newData.username || "";
        form.role_name = newData.role || "No Role";
        form.is_active = newData.is_active || false;
        form.email_verified_at = newData.email_verified_at || "";

    }
}, { immediate: true });

// Methods
const handleManageUser = () => {
    emit("triggerTopRightButton", "Manage", localUser.value);
};

const handleClose = () => {
    emit("close");
};

const formatDate = (dateString) => {
    if (!dateString) return "N/A";
    return new Date(dateString).toLocaleDateString();
};

const formatDateTime = (dateString) => {
    if (!dateString) return "N/A";
    return new Date(dateString).toLocaleString();
};
</script>

<template>

      <FormField label="Full Name">
        <FormControl v-model="form.name" :disabled="true" />
      </FormField>
      <FormField label="Email">
        <FormControl v-model="form.email" :disabled="true" />
      </FormField>
      <FormField label="Username">
        <FormControl v-model="form.username" :disabled="true" />
      </FormField>
      <FormField label="Role">
        <FormControl v-model="form.role_name" :disabled="true" />
      </FormField>
      <FormField label="Account Active">
        <FormControl v-model="form.is_active" type="checkbox" :disabled="true" />
      </FormField>


<BaseButtons class="mt-2">
    <BaseButton color="info" @click="handleManageUser('Manage')" label="Manage" icon="mdiAccountBoxEditOutline" />
    <BaseButton color="danger" @click="handleClose" label="Close" icon="mdiClose" />
</BaseButtons>

</template>

<style scoped>
.details-container {
    padding: 1rem;
    height: 100%;
    display: flex;
    flex-direction: column;
}


.details-content { flex: 1; overflow-y: auto; }

.details-actions {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    padding-top: 1rem;
    border-top: 1px solid #e2e8f0;
}
.dark .details-actions {
    border-top-color: #374151;
}

.action-button { width: 100%; padding: 0.5rem 1rem; border: 1px solid #e5e7eb; border-radius: 0.375rem; margin-top: 0.5rem; }
</style>
