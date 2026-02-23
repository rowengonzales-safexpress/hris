<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { useForm, usePage } from "@inertiajs/vue3";
import { ref, watch } from "vue";
import { router } from "@inertiajs/vue3";
import CardBox from '@/Components/CardBox.vue';
import FormField from '@/Components/FormField.vue';
import FormControl from '@/Components/FormControl.vue';
import Checkbox from '@/Components/Checkbox.vue';
import BaseButton from '@/Components/BaseButton.vue';
import service from '@/Components/Toast/service';

const props = defineProps({
    action: String,
    formdata: Object,
});

const emit = defineEmits(["triggerTopRightButton"]);

// Form initialization
const page = usePage()
const toast = service()
const form = useForm({
    _token: page.props.csrf_token,
    id: null,
    name: "",
    description: "",
    status: true,
});
const formErrors = ref([])

// Initialize form data
if (props.formdata) {
    form.id = props.formdata.id || null;
    form.name = props.formdata.name || "";
    form.description = props.formdata.description || "";
    form.status = props.formdata.status || "";
}

// Watch for changes in formdata prop
watch(() => props.formdata, (newFormdata) => {
    if (newFormdata) {
        form.id = newFormdata.id || null;
        form.name = newFormdata.name || "";
        form.description = newFormdata.description || "";
        form.status = newFormdata.status || "";
    }
}, { immediate: true });

// Submit function
const handleSubmit = () => {
  form.post('/admin/role', {
    preserveScroll: true,
    onSuccess: () => {
      toast.success('Role successfully saved!');
      router.visit('/admin/role')
    },
    onError: (errors) => {
      formErrors.value = Object.values(errors || {})
      toast.error('Error saving role. Please check the form.')
    },
  })
}

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
            form.name = newData.name || "";
            form.description = newData.description || "";
            form.status = newData.status !== undefined ? newData.status : true;
            form.permissions = newData.permissions || [];
        }
    },
    { deep: true }
);
</script>

<template>
    <AdminLayout>
        <div class="w-full max-w-4xl mx-auto">
            <CardBox>
                <div class="mb-6">
                    <h1 class="text-2xl font-bold text-gray-700 dark:text-gray-200">
                        {{ props.action === "Add" ? "Add New Role" : "Edit Role" }}
                    </h1>
                </div>

                <form @submit.prevent="handleSubmit">
                    <div v-if="formErrors.length" class="mb-4 text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/50 p-3 rounded">
                        <ul class="list-disc list-inside">
                            <li v-for="(err, i) in formErrors" :key="i">{{ err }}</li>
                        </ul>
                    </div>

                    <div class="grid grid-cols-1 gap-6 mb-6">
                        <!-- Role Name -->
                        <FormField label="Role Name" :error="form.errors.name">
                            <FormControl
                                v-model="form.name"
                                placeholder="Enter role name"
                            />
                        </FormField>

                        <!-- Description -->
                        <FormField label="Description" :error="form.errors.description">
                            <FormControl
                                v-model="form.description"
                                type="textarea"
                                placeholder="Enter role description"
                                class="h-32"
                            />
                        </FormField>

                        <!-- Active Status Checkbox -->
                        <FormField>
                            <div class="flex items-center">
                                <Checkbox
                                    v-model:checked="form.status"
                                />
                                <span class="ml-2 text-gray-700 dark:text-gray-300">Active</span>
                            </div>
                        </FormField>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex items-center justify-start space-x-4 mt-8">
                        <BaseButton
                            type="button"
                            color="info"
                            outline
                            label="Back"
                            @click="emit('triggerTopRightButton', 'lists')"
                        />
                        <BaseButton
                            type="button"
                            color="warning"
                            outline
                            label="Reset"
                            @click="resetForm"
                        />
                        <BaseButton
                            type="submit"
                            color="blue"
                            :disabled="form.processing"
                            :label="props.action === 'Add' ? 'Create Role' : 'Update Role'"
                        />
                    </div>
                </form>
            </CardBox>
        </div>
    </AdminLayout>
</template>
