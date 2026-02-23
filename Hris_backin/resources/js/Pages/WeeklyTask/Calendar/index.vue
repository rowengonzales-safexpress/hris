<script setup>
import WeeklyTaskLayout from "@/Layouts/WeeklyTaskLayout.vue";
import { Head } from "@inertiajs/vue3";
import axios from "axios";
import moment from "moment";
import { computed, onMounted, ref, watch } from "vue";

const monthCursor = ref(moment().startOf("month"));
const isLoading = ref(false);
const tasksByDate = ref({});

const monthLabel = computed(() => monthCursor.value.format("MMMM YYYY"));

const gridStart = computed(() => monthCursor.value.clone().startOf("isoWeek").startOf("day"));
const gridEnd = computed(() => monthCursor.value.clone().endOf("month").endOf("isoWeek").startOf("day"));

const weekDayLabels = computed(() => ["Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun"]);

const calendarDays = computed(() => {
    const days = [];
    const cur = gridStart.value.clone();
    while (cur.isSameOrBefore(gridEnd.value, "day")) {
        const key = cur.format("YYYY-MM-DD");
        const meta = tasksByDate.value[key] || { total: 0, hit: 0, miss: 0 };
        days.push({
            key,
            date: cur.clone(),
            inMonth: cur.isSame(monthCursor.value, "month"),
            meta,
        });
        cur.add(1, "day");
    }
    return days;
});

const statusMetaForTask = (task) => {
    const status = String(task?.status || "").toUpperCase().trim();
    const remarks = String(task?.remarks || "").toUpperCase().trim();
    const isMiss = status === "MISS" || remarks === "MISS";
    const isHit = status === "HIT" || remarks === "HIT";
    return { isMiss, isHit };
};

const buildTasksByDate = (tasks) => {
    const map = {};
    for (const task of tasks || []) {
        const m = moment(task?.taskdate);
        if (!m.isValid()) continue;
        const key = m.format("YYYY-MM-DD");
        if (!map[key]) map[key] = { total: 0, hit: 0, miss: 0 };
        map[key].total += 1;
        const meta = statusMetaForTask(task);
        if (meta.isMiss) map[key].miss += 1;
        if (meta.isHit) map[key].hit += 1;
    }
    return map;
};

const fetchMonthTasks = async () => {
    isLoading.value = true;
    try {
        const startDate = monthCursor.value.clone().startOf("month").format("YYYY-MM-DD");
        const endDate = monthCursor.value.clone().endOf("month").format("YYYY-MM-DD");
        const res = await axios.get("/weekly-task-schedule/api/filter-vsc", {
            params: { start_date: startDate, end_date: endDate },
            headers: { Accept: "application/json" },
        });
        const dailyTasks = res?.data?.dailyTasks || [];
        tasksByDate.value = buildTasksByDate(dailyTasks);
    } catch {
        tasksByDate.value = {};
    } finally {
        isLoading.value = false;
    }
};

const goPrevMonth = () => {
    monthCursor.value = monthCursor.value.clone().subtract(1, "month").startOf("month");
};

const goNextMonth = () => {
    monthCursor.value = monthCursor.value.clone().add(1, "month").startOf("month");
};

const isToday = (day) => day?.date?.isSame(moment(), "day");

const dayCellClass = (day) => {
    const base = `relative w-full aspect-square rounded-lg border p-2 flex flex-col ${
        isToday(day) ? "ring-2 ring-indigo-500 ring-offset-2 ring-offset-white dark:ring-offset-gray-800" : ""
    }`;
    if (!day.inMonth) return `${base} opacity-40 dark:opacity-30 border-gray-200 dark:border-slate-700`;

    if (day.meta.miss > 0) return `${base} border-red-300 bg-red-50 dark:bg-red-900/20 dark:border-red-900/40`;
    if (day.meta.hit > 0) return `${base} border-green-300 bg-green-50 dark:bg-green-900/20 dark:border-green-900/40`;
    if (day.meta.total > 0) return `${base} border-gray-300 bg-gray-50 dark:bg-slate-800/40 dark:border-slate-700`;

    return `${base} border-gray-200 dark:border-slate-700`;
};

const markerClass = (day) => {
    if (day.meta.miss > 0) return "bg-red-600";
    if (day.meta.hit > 0) return "bg-green-600";
    if (day.meta.total > 0) return "bg-gray-400";
    return "bg-transparent";
};

watch(
    () => monthCursor.value.format("YYYY-MM"),
    () => fetchMonthTasks()
);

onMounted(() => {
    fetchMonthTasks();
});
</script>

<template>
    <Head title="Calendar" />
    <WeeklyTaskLayout>
        <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-6">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">
                        Weekly Task Calendar
                    </h2>
                    <div class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        <span class="font-medium">{{ monthLabel }}</span>
                        <span v-if="isLoading" class="ml-2">Loading…</span>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        class="px-3 py-1.5 text-sm rounded border border-gray-300 dark:border-slate-700 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-slate-700"
                        @click="goPrevMonth"
                    >
                        Prev
                    </button>
                    <button
                        type="button"
                        class="px-3 py-1.5 text-sm rounded border border-gray-300 dark:border-slate-700 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-slate-700"
                        @click="goNextMonth"
                    >
                        Next
                    </button>
                </div>
            </div>

            <div class="mt-4 flex flex-wrap items-center gap-4 text-sm">
                <div class="flex items-center gap-2 text-gray-700 dark:text-gray-200">
                    <span class="h-2.5 w-2.5 rounded-full bg-green-600"></span>
                    <span>Hit</span>
                </div>
                <div class="flex items-center gap-2 text-gray-700 dark:text-gray-200">
                    <span class="h-2.5 w-2.5 rounded-full bg-red-600"></span>
                    <span>Miss</span>
                </div>
                <div class="flex items-center gap-2 text-gray-700 dark:text-gray-200">
                    <span class="h-2.5 w-2.5 rounded-full bg-gray-400"></span>
                    <span>Task</span>
                </div>
            </div>

            <div class="mt-4">
                <div class="grid grid-cols-7 gap-2 text-xs font-medium text-gray-600 dark:text-gray-300">
                    <div v-for="d in weekDayLabels" :key="d" class="px-2 py-1">
                        {{ d }}
                    </div>
                </div>

                <div class="mt-2 grid grid-cols-7 gap-2">
                    <div v-for="day in calendarDays" :key="day.key" :class="dayCellClass(day)">
                        <div class="flex items-start justify-between">
                            <div class="text-sm font-semibold text-gray-800 dark:text-gray-100">
                                {{ day.date.date() }}
                            </div>

                            <div
                                v-if="day.meta.total > 0"
                                class="text-[11px] px-1.5 py-0.5 rounded-full border border-gray-200 dark:border-slate-700 text-gray-700 dark:text-gray-200 bg-white/70 dark:bg-slate-900/40"
                            >
                                {{ day.meta.total }}
                            </div>
                        </div>

                        <div class="mt-auto flex items-center justify-end">
                            <span class="h-2.5 w-2.5 rounded-full" :class="markerClass(day)"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </WeeklyTaskLayout>
</template>
