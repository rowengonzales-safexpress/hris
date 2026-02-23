<template>
    <Head title="Driver Info" />
    <TrackingLayout>
        <div class="application-list-container">
            <div class="main-content">
                <SectionTitleLineWithButton :icon="'mdiListBox'" title="Drivers" main>
  <BaseButton @click="emit('triggerTopRightButton', 'Add')" :icon="'mdiFileDocumentPlus'" label="Add Driver" color="contrast" rounded-full small />
</SectionTitleLineWithButton>

                <!-- Data loaded -->
                <CardBox class="flex-1 p-6" has-table>
                  <CoreTable v-if="tableRows.length > 0"
                             :table-rows="tableRows"
                             :table-header="userHeader"
                             table-name="drivers"
                             searchable-fields="driver_code,full_name,license_no,mobile_no,is_active,created_at"
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
                  <div v-else class="text-gray-500">Click Add Driver button to create your first record</div>
                </CardBox>
            </div>


        </div>
    </TrackingLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import TrackingLayout from '@/Layouts/TrackingLayout.vue';
import { Head } from '@inertiajs/vue3';
import BaseIcon from '@/Components/BaseIcon.vue'
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

const showDetails = ref(false);
const loading = ref(false);

// Transform data to match CoreTable expectations
const normalizeStatus = (s) => {
  if (s === true || s === 1 || s === 'A' || s === 'ACTIVE') return 'A'
  if (s === false || s === 0 || s === 'I' || s === 'INACTIVE') return 'I'
  return s
}

const tableRows = computed(() => {
  const rows = Array.isArray(props.masterlist) ? props.masterlist : (props.masterlist?.data ?? [])
  return rows.map((r) => ({
    ...r,
    full_name: r?.full_name ?? [r?.first_name, r?.last_name].filter(Boolean).join(' '),
    is_active: normalizeStatus(r?.is_active ?? r?.status)
  }))
})

// Event handlers
const openLink = (row) => {
  emit('triggerTopRightButton', 'Manage', row)
}


const userHeader = [
  { label: 'Code', fieldName: 'driver_code', type: 'link' },
  { label: 'Name', fieldName: 'full_name' },
  { label: 'License', fieldName: 'license_no' },
  { label: 'Mobile', fieldName: 'mobile_no' },
  { label: 'Status', fieldName: 'is_active', type: 'slot' },
  { label: 'Created At', fieldName: 'created_at', type: 'datetime' }
]
</script>


