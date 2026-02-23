<template>
    <Head title="Client Info" />
    <TrackingLayout>

                <SectionTitleLineWithButton :icon="'mdiListBox'" title="Clients" main>
  <BaseButton @click="openAddClient" :icon="'mdiFileDocumentPlus'" label="Add Client" color="contrast" rounded-full small />
</SectionTitleLineWithButton>

                <!-- Data loaded -->
                <CardBox class="flex-1 p-6" has-table>
                  <CoreTable v-if="tableRows.length > 0"
                             :table-rows="tableRows"
                             :table-header="userHeader"
                             table-name="clients"
                             searchable-fields="client_code,client_name,is_active,created_at"
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
                  <div v-else class="text-gray-500">Click Add Client button to create your first record</div>
                </CardBox>


            <AsideDrawer
                :title="drawerTitle"
                :is-open="showDetails"
                @closeDrawer="showDetails = false"
                class="shadow-lg shadow-blue-500/50"
            >
                <FormField label="Client Name">
                    <FormControl v-model="form.client_name" />
                </FormField>

                <FormField label="Client Code">
                    <FormControl v-model="form.client_code" />
                </FormField>

                <FormField label="Active">
                  <Checkbox v-model:checked="form.is_active" />
                </FormField>
         <BaseButtons>
        <BaseButton :icon="'mdiContentSave'" color="info" rounded-full small label="Save" @click="submit" />
        <BaseButton :icon="'mdiClose'" color="danger" rounded-full small label="Cancel" @click="closeDetails" />
      </BaseButtons>

            </AsideDrawer>

    </TrackingLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import TrackingLayout from '@/Layouts/TrackingLayout.vue';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import SectionTitleLineWithButton from '@/Components/SectionTitleLineWithButton.vue';
import BaseButton from '@/Components/BaseButton.vue';
import CardBox from '@/Components/CardBox.vue'
import CoreTable from '@/Components/CoreTable.vue'
import AsideDrawer from '@/Components/AsideDrawer.vue';
import FormField from '@/Components/FormField.vue'
import FormControl from '@/Components/FormControl.vue'
import BaseButtons from '@/Components/BaseButtons.vue'
import Checkbox from '@/Components/Checkbox.vue'
import BaseIcon from '@/Components/BaseIcon.vue'
import service from '@/Components/Toast/service'

// Define props and emits
const props = defineProps({
    masterlist: {
        type: [Array, Object],
        default: () => []
    }
});

const emit = defineEmits(['triggerTopRightButton']);

const showDetails = ref(false);
const loading = ref(false);
const page = usePage()
const form = useForm({
  _token: page.props.csrf_token,
  id: null,
  client_code: '',
  client_name: '',
  branch_id: null,
  is_active: true,
})

// Transform data to match what vtable expects

const normalizeStatus = (s) => {
  if (s === true || s === 1 || s === 'A' || s === 'ACTIVE') return 'A'
  if (s === false || s === 0 || s === 'I' || s === 'INACTIVE') return 'I'
  return s
}

const tableRows = computed(() => {
  const rows = Array.isArray(props.masterlist) ? props.masterlist : (props.masterlist?.data ?? [])
  return rows.map((r) => ({
    ...r,
    is_active: normalizeStatus(r?.is_active ?? r?.status)
  }))
})
const openLink = (row) => {
  form.id = row?.id ?? null
  form.client_code = row?.client_code ?? ''
  form.client_name = row?.client_name ?? ''
  const v = row?.is_active
  form.is_active = (v === true || v === 1 || v === 'ACTIVE' || v === 'A')
  form.branch_id = row?.branch_id ?? null
  showDetails.value = true
}
const userHeader = [
  { label: 'Code', fieldName: 'client_code', type: 'link' },
  { label: 'Name', fieldName: 'client_name' },
  { label: 'Status', fieldName: 'is_active', type: 'slot' },
  { label: 'Created At', fieldName: 'created_at', type: 'datetime' }
]

// Status methods
const drawerTitle = computed(() => {
  const isEditing = !!(form.id && form.id !== 0)
  return isEditing ? 'Edit Client' : 'Add Client'
})

// Event handlers
const openAddClient = () => {
  form.id = null
  form.client_code = ''
  form.client_name = ''
  form.is_active = true
  form.branch_id = null
  showDetails.value = true
}

const submit = () => {
  const url = form.id ? `/tracking/client/${form.id}` : '/tracking/client'
  const method = form.id ? 'put' : 'post'

  const toast = service()
  form[method](url, {
    preserveScroll: true,
    onSuccess: () => {
      toast.success(`Client successfully ${form.id ? 'updated' : 'created'}!`)
      showDetails.value = false
      router.visit('/tracking/client')
    },
    onError: (errors) => {
      const errs = Object.values(errors || {})
      if (errs.length) toast.error(errs[0])
      else toast.error('Failed to save client')
    },
    onFinish: () => {
      // Reset form after submission
      form.reset()
    }
  })
}

// Removed unused handlers for cleaner component

const closeDetails = () => {
  showDetails.value = false
  form.reset()
}


// Removed unused columns definition


</script>
