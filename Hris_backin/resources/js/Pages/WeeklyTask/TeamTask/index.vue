<script setup>
import WeeklyTaskLayout from "@/Layouts/WeeklyTaskLayout.vue";
import { Head, usePage } from "@inertiajs/vue3";
import { computed, onMounted, ref } from "vue";
import axios from "axios";
import moment from "moment";
import FormControl from "@/Components/FormControl.vue";

const page = usePage();

const branchName = ref("");

const users = ref([]);
const usersQuery = ref("");
const isLoadingUsers = ref(false);
const usersError = ref("");

const usersPage = ref(1);
const usersPerPage = 10;

const selectedUserId = ref(null);
const selectedUser = computed(() => users.value.find((u) => u.id === selectedUserId.value) || null);

const weekStart = ref("");
const weekEnd = ref("");
const tasks = ref([]);
const isLoadingTasks = ref(false);
const tasksError = ref("");

const expandedTaskId = ref(null);
const todosByTaskId = ref({});
const todosLoadingByTaskId = ref({});
const todosErrorByTaskId = ref({});

const filteredUsers = computed(() => {
    const q = String(usersQuery.value || "").toLowerCase().trim();
    if (!q) return users.value;
    return users.value.filter((u) => String(u.full_name || "").toLowerCase().includes(q));
});

const usersTotalPages = computed(() => {
    const total = filteredUsers.value.length;
    return Math.max(1, Math.ceil(total / usersPerPage));
});

const pagedUsers = computed(() => {
    const pageNum = Math.min(Math.max(usersPage.value, 1), usersTotalPages.value);
    const start = (pageNum - 1) * usersPerPage;
    return filteredUsers.value.slice(start, start + usersPerPage);
});

const groupedTasks = computed(() => {
    const groups = new Map();
    for (const t of tasks.value || []) {
        const key = t.taskdate ? moment(t.taskdate).format("YYYY-MM-DD") : "unknown";
        if (!groups.has(key)) groups.set(key, []);
        groups.get(key).push(t);
    }
    const keys = Array.from(groups.keys()).sort((a, b) => a.localeCompare(b));
    return keys.map((k) => ({
        key: k,
        label: k === "unknown" ? "No date" : moment(k, "YYYY-MM-DD").format("dddd, MMMM D, YYYY"),
        tasks: groups.get(k) || [],
    }));
});

const formatDateTime = (val) => {
    if (!val) return "";
    const m = moment(val);
    return m.isValid() ? m.format("MM/DD/YYYY hh:mm A") : String(val);
};

const isMiss = (task) => {
    const status = String(task?.status || "").toUpperCase().trim();
    const remarks = String(task?.remarks || "").toUpperCase().trim();
    return status === "MISS" || remarks === "MISS";
};

const isOnGoing = (task) => {
    const status = String(task?.status || "").toUpperCase().trim();
    return status === "ON GOING" || status === "ONGOING";
};

const taskBadgeClass = (task) => {
    if (isMiss(task)) return "bg-red-600 text-white border-red-700";
    if (isOnGoing(task)) return "bg-yellow-400 text-gray-900 border-yellow-500";
    return "bg-white dark:bg-gray-900 border-gray-200 dark:border-slate-700 text-gray-700 dark:text-gray-300";
};

const fetchUsers = async () => {
    isLoadingUsers.value = true;
    usersError.value = "";
    try {
        const res = await axios.get(route("weekly-task-schedule.teamtask.users"), {
            headers: { Accept: "application/json" },
        });
        branchName.value = res.data?.branch?.name || "";
        users.value = res.data?.users || [];
        usersPage.value = 1;
        if (!selectedUserId.value && users.value.length > 0) {
            await selectUser(users.value[0].id);
        }
    } catch (e) {
        users.value = [];
        usersError.value = "Failed to load team users.";
    } finally {
        isLoadingUsers.value = false;
    }
};

const fetchWeekTasks = async (userId) => {
    isLoadingTasks.value = true;
    tasksError.value = "";
    expandedTaskId.value = null;
    todosByTaskId.value = {};
    todosLoadingByTaskId.value = {};
    todosErrorByTaskId.value = {};
    try {
        const res = await axios.get(route("weekly-task-schedule.teamtask.week", { user: userId }), {
            headers: { Accept: "application/json" },
        });
        weekStart.value = res.data?.start || "";
        weekEnd.value = res.data?.end || "";
        tasks.value = res.data?.tasks || [];
    } catch (e) {
        tasks.value = [];
        tasksError.value = "Failed to load weekly tasks.";
    } finally {
        isLoadingTasks.value = false;
    }
};

const selectUser = async (userId) => {
    selectedUserId.value = userId;
    await fetchWeekTasks(userId);
};

const goPrevUsersPage = () => {
    usersPage.value = Math.max(1, usersPage.value - 1);
};

const goNextUsersPage = () => {
    usersPage.value = Math.min(usersTotalPages.value, usersPage.value + 1);
};

const onUsersQueryInput = () => {
    usersPage.value = 1;
};

const toggleTask = async (task) => {
    const id = task?.dailytask_id;
    if (!id) return;

    expandedTaskId.value = expandedTaskId.value === id ? null : id;
    if (expandedTaskId.value !== id) return;

    if (Array.isArray(todosByTaskId.value[id])) return;

    todosLoadingByTaskId.value = { ...todosLoadingByTaskId.value, [id]: true };
    todosErrorByTaskId.value = { ...todosErrorByTaskId.value, [id]: "" };
    try {
        const res = await axios.get(route("weekly-task-schedule.getTask", id), {
            headers: { Accept: "application/json" },
        });
        todosByTaskId.value = { ...todosByTaskId.value, [id]: res.data || [] };
    } catch (e) {
        todosByTaskId.value = { ...todosByTaskId.value, [id]: [] };
        todosErrorByTaskId.value = { ...todosErrorByTaskId.value, [id]: "Failed to load todos." };
    } finally {
        todosLoadingByTaskId.value = { ...todosLoadingByTaskId.value, [id]: false };
    }
};

onMounted(() => {
    fetchUsers();
});
</script>

<template>
    <Head title="Team Task" />
    <WeeklyTaskLayout>
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-6">
                <div class="flex items-center justify-between gap-3">
                    <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">
                        Team
                    </h2>
                    <div class="text-sm text-gray-500 dark:text-gray-400">
                        Branch: {{ branchName || "-" }}
                    </div>
                </div>

                <div class="mt-4">
                    <FormControl v-model="usersQuery" placeholder="Search user..." class="w-full" :clearable="true" @update:modelValue="onUsersQueryInput" />
                </div>

                <div v-if="usersError" class="mt-4 text-sm text-red-600 dark:text-red-400">
                    {{ usersError }}
                </div>

                <div v-if="isLoadingUsers" class="mt-4">
                    <SkeletonLoader type="side-menu" :item-count="8" />
                </div>

                <div v-else class="mt-4">
                    <div class="space-y-2">
                    <button
                        v-for="u in pagedUsers"
                        :key="u.id"
                        type="button"
                        class="w-full text-left px-3 py-2 rounded border dark:border-slate-700 hover:bg-gray-50 dark:hover:bg-slate-700/40 transition"
                        :class="selectedUserId === u.id ? 'bg-blue-50 dark:bg-slate-700 border-blue-200 dark:border-slate-600' : 'bg-white dark:bg-gray-800'"
                        @click="selectUser(u.id)"
                    >
                        <div class="font-medium text-gray-800 dark:text-gray-200">
                            {{ u.full_name || (u.first_name + " " + u.last_name) }}
                        </div>
                    </button>

                    <div v-if="filteredUsers.length === 0" class="text-sm text-gray-600 dark:text-gray-400">
                        No users found.
                    </div>
                    </div>

                    <div v-if="filteredUsers.length > 0" class="mt-4 flex items-center justify-between">
                        <button
                            type="button"
                            class="px-3 py-1 text-sm rounded border dark:border-slate-700 text-gray-700 dark:text-gray-200 disabled:opacity-50"
                            :disabled="usersPage <= 1"
                            @click="goPrevUsersPage"
                        >
                            Prev
                        </button>
                        <div class="text-xs text-gray-500 dark:text-gray-400">
                            Page {{ usersPage }} / {{ usersTotalPages }}
                        </div>
                        <button
                            type="button"
                            class="px-3 py-1 text-sm rounded border dark:border-slate-700 text-gray-700 dark:text-gray-200 disabled:opacity-50"
                            :disabled="usersPage >= usersTotalPages"
                            @click="goNextUsersPage"
                        >
                            Next
                        </button>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-6 lg:col-span-2">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">
                            Weekly Tasks
                        </h2>
                        <div class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            <span v-if="selectedUser">{{ selectedUser.full_name }}</span>
                            <span v-else>Select a user</span>
                            <span v-if="weekStart && weekEnd"> • {{ weekStart }} to {{ weekEnd }}</span>
                        </div>
                    </div>
                </div>

                <div v-if="tasksError" class="mt-4 text-sm text-red-600 dark:text-red-400">
                    {{ tasksError }}
                </div>

                <div v-if="isLoadingTasks" class="mt-6">
                    <SkeletonLoader type="card" :item-count="6" :columns="1" />
                </div>

                <div v-else class="mt-6 max-h-[70vh] overflow-y-auto pr-2 space-y-6">
                    <div v-if="tasks.length === 0" class="flex justify-center items-center p-10 bg-gray-50 dark:bg-slate-900/30 rounded-lg">
                        <div class="text-center">
                            <p class="text-gray-500 dark:text-gray-400">No tasks found for this week.</p>
                        </div>
                    </div>

                    <div v-for="group in groupedTasks" :key="group.key" class="space-y-3">
                        <div class="flex items-center justify-between">
                            <div class="text-sm font-semibold text-gray-700 dark:text-gray-300">
                                {{ group.label }}
                            </div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                {{ group.tasks.length }} task(s)
                            </div>
                        </div>

                        <div v-for="task in group.tasks" :key="task.dailytask_id" class="border rounded-lg dark:border-slate-700 overflow-hidden">
                            <button
                                type="button"
                                class="w-full flex items-center justify-between gap-4 p-4 bg-gray-50 dark:bg-slate-800/60 hover:bg-gray-100 dark:hover:bg-slate-800 transition"
                                @click="toggleTask(task)"
                            >
                                <div class="min-w-0 text-left">
                                    <div class="font-semibold text-gray-800 dark:text-gray-200 truncate">
                                        {{ task.project || "Untitled task" }}
                                    </div>
                                    <div class="mt-1 text-xs text-gray-600 dark:text-gray-400">
                                        <span v-if="task.site_name">{{ task.site_name }}</span>
                                        <span v-if="task.plandate"> • Plan: {{ formatDateTime(task.plandate) }}</span>
                                        <span v-if="task.planenddate"> - {{ formatDateTime(task.planenddate) }}</span>
                                    </div>
                                </div>

                                <div class="flex items-center gap-3 shrink-0">
                                    <span class="text-xs px-2 py-1 rounded border" :class="taskBadgeClass(task)">
                                        {{ task.status || "Pending" }}
                                    </span>
                                    <span class="text-gray-500 dark:text-gray-400">
                                        {{ expandedTaskId === task.dailytask_id ? "▲" : "▼" }}
                                    </span>
                                </div>
                            </button>

                            <div v-if="expandedTaskId === task.dailytask_id" class="p-4 bg-white dark:bg-gray-800">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                                    <div class="text-gray-700 dark:text-gray-300">
                                        <span class="font-semibold">Start:</span> {{ formatDateTime(task.startdate) || "-" }}
                                    </div>
                                    <div class="text-gray-700 dark:text-gray-300">
                                        <span class="font-semibold">End:</span> {{ formatDateTime(task.enddate) || "-" }}
                                    </div>
                                    <div class="text-gray-700 dark:text-gray-300">
                                        <span class="font-semibold">Remarks:</span> {{ task.remarks || "-" }}
                                    </div>
                                    <div class="text-gray-700 dark:text-gray-300">
                                        <span class="font-semibold">Task ID:</span> {{ task.dailytask_id }}
                                    </div>
                                </div>

                                <div class="mt-4">
                                    <div class="text-sm font-semibold text-gray-800 dark:text-gray-200">
                                        Todos
                                    </div>

                                    <div v-if="todosErrorByTaskId[task.dailytask_id]" class="mt-2 text-sm text-red-600 dark:text-red-400">
                                        {{ todosErrorByTaskId[task.dailytask_id] }}
                                    </div>

                                    <div v-if="todosLoadingByTaskId[task.dailytask_id]" class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                        Loading todos...
                                    </div>

                                    <div v-else class="mt-2">
                                        <div
                                            v-if="(todosByTaskId[task.dailytask_id] || []).length === 0"
                                            class="text-sm text-gray-600 dark:text-gray-400"
                                        >
                                            No todos found.
                                        </div>

                                        <div v-else class="space-y-2">
                                            <div
                                                v-for="todo in todosByTaskId[task.dailytask_id]"
                                                :key="todo.id"
                                                class="flex items-center justify-between gap-3 p-3 rounded border dark:border-slate-700 bg-gray-50 dark:bg-slate-900/30"
                                            >
                                                <div class="text-sm text-gray-800 dark:text-gray-200">
                                                    {{ todo.task_name }}
                                                </div>
                                                <div class="text-xs text-gray-600 dark:text-gray-400">
                                                    {{ todo.status || "-" }}
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
        </div>
    </WeeklyTaskLayout>
</template>
