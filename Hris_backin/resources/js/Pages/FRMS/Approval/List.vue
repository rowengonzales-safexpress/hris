<script setup>
import { ref } from "vue";
import CoreTable from "@/Components/CoreTable.vue";
import SwalConfirm from "@/Components/SwalConfirm.vue";
import Tab from "@/Components/Tab.vue";

const props = defineProps({
    disbursementApprovals: {
        type: Array,
        default: () => [],
    },
    liquidationApprovals: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(["triggerTopRightButton"]);
const activetab = ref('disbursement-approval')

const tableRows = ref(
    (props.disbursementApprovals ?? []).map((f) => ({
        ...f,
        creator_name: `${f.user?.first_name ?? ""} ${f.user?.last_name ?? ""}`.trim(),
    }))
);
const liquidationRows = ref(
    (props.liquidationApprovals ?? []).map((f) => ({
        ...f,
        creator_name: `${f.user?.first_name ?? ""} ${f.user?.last_name ?? ""}`.trim(),
        disbursement_status: Array.isArray(f.disbursements) && f.disbursements.length > 0
          ? f.disbursements[f.disbursements.length - 1]?.status
          : null,
    }))
);
const fillIn = ref(false);

const toggleFillIn = () => {
    fillIn.value = !fillIn.value;
};

const openLink = (row) => {
    const type = activetab.value === 'disbursement-approval' ? 'disbursement' : 'liquidation'
    emit("triggerTopRightButton", "Manage", { row, approvalType: type });
};

const tableHeader = [
    { label: "Request No", fieldName: "frm_no", type: "link" },
    { label: "Request Date", fieldName: "request_date", type: "date" },
    { label: "Purpose", fieldName: "purpose" },
    { label: "Created By", fieldName: "creator_name" },
    { label: "Status", fieldName: "status_request", type: "slot" },
];
const liquidationHeader = [
    { label: "Request No", fieldName: "frm_no", type: "link" },
    { label: "Request Date", fieldName: "request_date", type: "date" },
    { label: "Purpose", fieldName: "purpose" },
    { label: "Created By", fieldName: "creator_name" },
    { label: "Status", fieldName: "disbursement_status", type: "slot" },
];
</script>

<template>
    <div
        class="bg-white dark:bg-gray-900 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 mt-2"
    >
        <div class="p-6 border-b border-gray-100 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div
                        class="w-10 h-10 bg-gradient-to-r from-green-500 to-green-600 rounded-xl flex items-center justify-center"
                    >
                        <svg
                            class="w-5 h-5 text-white"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                            ></path>
                        </svg>
                    </div>
                    <h3
                        class="text-lg font-bold text-gray-900 dark:text-gray-200"
                    >
                        Approval Center
                    </h3>
                </div>
            </div>
        </div>
        <ul
            class="flex flex-wrap -mb-px text-sm font-medium text-center text-gray-600 dark:text-gray-400"
        >
            <li class="mr-2">
                <Tab
                    label="Disbursement Approvals"
                    :icon="'mdiFileDocumentPlus'"
                    name="disbursement-approval"
                    :active-tab="activetab"
                    @click="activetab = 'disbursement-approval'"
                />
            </li>
            <li class="mr-2">
                <Tab
                    label="Liquidation Approvals"
                    :icon="'mdiFileDocument'"
                    name="liquidation-approvals"
                    :active-tab="activetab"
                    @click="activetab = 'liquidation-approvals'"
                />
            </li>
        </ul>
        <div class="mt-4">
            <div v-show="activetab === 'disbursement-approval'">
                <div class="p-8 text-gray-800 dark:text-gray-200">
      <div v-if="tableRows.length > 0">
        <CoreTable
          :table-rows="tableRows"
          :table-header="tableHeader"
          table-name="frms-approval"
          :is-paginated="true"
          @openLink="openLink"
        >
    <template #row-action="scope">
                 <span :class="{
                   'text-yellow-500': scope.slotProp.status_request === 'FA',
                   'text-blue-500': scope.slotProp.status_request === 'FD',
                   'text-red-500': scope.slotProp.status_request === 'FL',
                   'text-green-500': scope.slotProp.status_request === 'A'
                 }">
                    {{
                     scope.slotProp.status_request === 'FA' ? 'Approval' :
                     scope.slotProp.status_request === 'FD' ? 'Disbursement' :
                     scope.slotProp.status_request === 'FL' ? 'Liquidation' :
                     scope.slotProp.status_request === 'A' ? 'Approved' :
                     scope.slotProp.status_request === 'C' ? 'Close' :
                     scope.slotProp.status_request === 'X' ? 'Cancel' :
                     scope.slotProp.status_request === 'FR' ? 'For Review' :
                     scope.slotProp.status_request
                   }}
                 </span>
               </template>
    </CoreTable>
      </div>
      <div v-else class="text-center py-16">
        <svg class="mx-auto h-24 w-24 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M3 7h18v10a2 2 0 01-2 2H5a2 2 0 01-2-2V7zm3-4h6l2 2h6"></path>
        </svg>
        <h3 class="mt-6 text-lg font-medium text-gray-900 dark:text-gray-200">You have no pending approvals.</h3>
      </div>
    </div>
            </div>
            <div v-show="activetab === 'liquidation-approvals'">
                <div class="p-8 text-gray-800 dark:text-gray-200">
                    <div v-if="liquidationRows.length > 0">
                        <CoreTable
                            :table-rows="liquidationRows"
                            :table-header="liquidationHeader"
                            table-name="frms-liquidation-approvals"
                            :is-paginated="true"
                            @openLink="openLink"
                        >
                            <template #row-action="scope">
                                <span :class="{
                                    'text-blue-500': scope.slotProp.disbursement_status === 'P',
                                    'text-yellow-500': scope.slotProp.disbursement_status === 'FS',
                                    'text-purple-500': scope.slotProp.disbursement_status === 'FM',
                                }">
                                    {{
                                        scope.slotProp.disbursement_status === 'P' ? 'Pending' :
                                        scope.slotProp.disbursement_status === 'FS' ? 'Settlement' :
                                        scope.slotProp.disbursement_status === 'FM' ? 'Monitoring' :
                                        scope.slotProp.disbursement_status
                                    }}
                                </span>
                            </template>
                        </CoreTable>
                    </div>
                    <div v-else class="text-center py-16">
                        <svg class="mx-auto h-24 w-24 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M3 7h18v10a2 2 0 01-2 2H5a2 2 0 01-2-2V7zm3-4h6l2 2h6"></path>
                        </svg>
                        <h3 class="mt-6 text-lg font-medium text-gray-900 dark:text-gray-200">No liquidation approvals.</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
