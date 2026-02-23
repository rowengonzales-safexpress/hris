<script setup>
import WeeklyTaskLayout from "@/Layouts/WeeklyTaskLayout.vue";
import { Head } from "@inertiajs/vue3";
import CoreTable from "@/Components/CoreTable.vue";

const props = defineProps({
    tasks: {
        type: Array,
        default: () => [],
    },
});

const tableHeader = [
    { label: "Date", fieldName: "taskdate", type: "date" },
    { label: "User", fieldName: "user_name" },
    { label: "Project", fieldName: "project" },
    { label: "Total Tasks", fieldName: "total_tasks" },
    { label: "Status", fieldName: "status", type: "slot", slotName: "status-col" },
];
</script>

<template>
    <Head title="Reports" />
    <WeeklyTaskLayout>
        <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-6">
            <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200 mb-4">
                Weekly Task Reports
            </h2>
            <div class="mt-4">
                <CoreTable
                    :table-header="tableHeader"
                    :table-rows="tasks"
                    table-name="WeeklyTaskReport"
                    :is-paginated="true"
                    :rows-per-page="10"
                    :searchable-fields="'user_name,project,status'"
                    :show-download-csv="true"
                >
                    <template #status-col="{ slotProp }">
                        <span
                            :class="{
                                'text-green-600 font-bold': slotProp.status === 'HIT',
                                'text-red-600 font-bold': slotProp.status === 'MISS',
                                'text-gray-600': !['HIT', 'MISS'].includes(slotProp.status),
                            }"
                        >
                            {{ slotProp.status || 'N/A' }}
                        </span>
                    </template>
                </CoreTable>
            </div>
        </div>
    </WeeklyTaskLayout>
</template>
