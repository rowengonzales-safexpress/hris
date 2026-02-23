<script setup>
import { ref, onMounted, computed, inject, watch } from "vue";
import { useForm, usePage, router, Head } from "@inertiajs/vue3";
import WeeklyTaskLayout from "@/Layouts/WeeklyTaskLayout.vue";
import Modal from "@/Components/Modal.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import DangerButton from "@/Components/DangerButton.vue";
import FormField from '@/Components/FormField.vue'
import FormControl from '@/Components/FormControl.vue'
import InputError from "@/Components/InputError.vue";
import BaseIcon from "@/Components/BaseIcon.vue";
import BaseButton from "@/Components/BaseButton.vue";
import CardBox from "@/Components/CardBox.vue";
import axios from "axios";
import moment from "moment";
import { debounce } from "lodash";
import { mdiAbTesting, mdiTrashCan } from "@mdi/js";

const props = defineProps({
    tasks: Object,
    filters: Object,
    sites: Array,
});

const page = usePage();
const swal = inject("$swal");
const toastr = inject("$toastr"); // Assuming toastr is provided or imported globally, if not I'll use page flash
const swalFire = (a, b, c) => {
    if (swal && typeof swal.fire === "function") {
        return typeof a === "string" ? swal.fire(a, b, c) : swal.fire(a);
    }
    const w = typeof window !== "undefined" ? window.Swal : null;
    if (w && typeof w.fire === "function") {
        return typeof a === "string" ? w.fire(a, b, c) : w.fire(a);
    }
    if (typeof a === "string") {
        alert([a, b].filter(Boolean).join("\n"));
        return Promise.resolve({ isConfirmed: true });
    } else {
        const ok = confirm(a.text || a.title || "Are you sure?");
        return Promise.resolve({ isConfirmed: ok });
    }
};

// State
const params = ref({
    query: props.filters.query || "",
    start_date: props.filters.start_date || "",
    end_date: props.filters.end_date || "",
});

const showingCreateModal = ref(false);
const showingTaskModal = ref(false);
const showingRemarksModal = ref(false);
const isloading = ref(false);
const isloadingTask = ref(false);
const subTasks = ref([]);
const selectedTask = ref(null);
const editing = ref(false);
const editTitleDate = ref('');

// Task Form (DailyTask)
const form = useForm({
    dailytask_id: null,
    site: "",
    user_id: page.props.auth.user.id,
    tasktype: "",
    plandate: "",
    planenddate: "",
    enddates: "",
    startdate: "",
    enddate: "",
    remarks: "",
    status: "",
    project: "",
    taskdate: "",
    status_task: 0
});

// SubTask Form
const subTaskForm = useForm({
    id: null,
    dailytask_id: null,
    task_name: "",
    status: 0,
});

// Remarks Form (for completing task)
const remarksForm = useForm({
    dailytask_id: null,
    startdate: "",
    remarks: "",
    status: "",
});


// Options
const taskTypes = ref([]);
const taskTypeLabel = (id) => {
    const t = taskTypes.value.find(x => x.id == id);
    return t ? t.name : id;
};

const showCompleted = ref(false);

// Computed
const pendingSubTasks = computed(() => {
    return subTasks.value.filter((item) => item.status === 0);
});

const completedSubTasks = computed(() => {
    return subTasks.value.filter((item) => item.status === 1);
});

// Watchers
watch(
    params,
    debounce((value) => {
        router.get(
            route("weekly-task-schedule.index"),
            value,
            { preserveState: true, preserveScroll: true }
        );
    }, 300),
    { deep: true }
);

// Methods

const openCreateModal = () => {
    editing.value = false;
    form.reset();
    form.clearErrors();
    showingCreateModal.value = true;
};

const editTask = (task) => {
    editing.value = true;
    form.reset();
    form.clearErrors();

    // Populate form
    form.dailytask_id = task.dailytask_id;
    form.site = task.site_name; // Note: Controller expects 'site' ID but frontend shows Name?
    // Wait, controller store method uses 'site' => $request->site.
    // If it's a select, it should be the ID.
    // The original code set form.site = value.site_name for edit.
    // I should check if the select options use ID or Name.
    // sites prop has id and branch_name.
    // I'll try to find the site ID based on name if needed, or just bind correctly.
    const siteObj = props.sites.find(s => s.branch_name === task.site_name);
    form.site = siteObj ? siteObj.id : task.site; // Fallback

    form.tasktype = task.tasktype;
    form.plandate = moment(task.plandate).format("YYYY-MM-DDTHH:mm");
    form.planenddate = moment(task.planenddate).format("YYYY-MM-DDTHH:mm");
    form.project = task.project;
    editTitleDate.value = moment(task.taskdate).format('MMMM D, YYYY');

    showingCreateModal.value = true;
};

const submitTask = () => {
    if (editing.value) {
        form.put(route('weekly-task-schedule.update', form.dailytask_id), {
            onSuccess: () => {
                showingCreateModal.value = false;
                // Toast handled by flash message or global watcher
            },
        });
    } else {
        form.post(route('weekly-task-schedule.store'), {
            onSuccess: () => {
                showingCreateModal.value = false;
            },
        });
    }
};

const dropTask = (id) => {
    swal.fire({
        title: "Are you sure?",
        text: "You wanna Drop this Todo?",
        icon: "warning",
        showCancelButton: true,
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(route('weekly-task-schedule.destroy', id)); // Using destroy route name
        }
    });
};

// SubTask Methods
const fetchSubTasks = async (dailytask_id) => {
    isloadingTask.value = true;
    try {
        const response = await axios.get(route('weekly-task-schedule.getTask', dailytask_id));
        subTasks.value = response.data;
    } catch (error) {
        console.error("Error fetching tasks:", error);
    } finally {
        isloadingTask.value = false;
    }
};

const openSubTaskModal = (task) => {
    selectedTask.value = task;
    subTaskForm.dailytask_id = task.dailytask_id;
    subTaskForm.task_name = "";
    subTaskForm.id = null;

    showingTaskModal.value = true;
    fetchSubTasks(task.dailytask_id);
};

const editSubTask = (item) => {
    subTaskForm.id = item.id;
    subTaskForm.dailytask_id = item.dailytask_id;
    subTaskForm.task_name = item.task_name;
    subTaskForm.status = item.status;
    subTaskForm.clearErrors();
};

const submitSubTask = () => {
    const name = (subTaskForm.task_name || '').trim();
    if (!name) {
        swalFire("Todo name is required", "", "warning");
        return;
    }
    const currentDailyTaskId = subTaskForm.dailytask_id;
    subTaskForm.post(route('weekly-task-schedule.addTask'), {
        onSuccess: () => {
            subTaskForm.reset('task_name', 'id', 'status');
            // Ensure status is 0 for new tasks
            subTaskForm.status = 0;
            fetchSubTasks(currentDailyTaskId);
        },
        preserveScroll: true,
    });
};

const deleteSubTask = (id) => {
    router.delete(route('weekly-task-schedule.deleteTask', id), {
        preserveScroll: true,
    });
};

const toggleSubTaskCompletion = (item) => {
    subTaskForm.id = item.id;
    subTaskForm.dailytask_id = item.dailytask_id;
    subTaskForm.task_name = item.task_name;
    subTaskForm.status = item.status === 1 ? 0 : 1;

    submitSubTask();
};

// Start/End Task
const startTask = (task) => {
    // Check if subtasks exist
    axios.get(route('weekly-task-schedule.getTask', task.dailytask_id)).then(res => {
        if (!res.data || res.data.length === 0) {
            swalFire("Warning!", "No todos found. Please add todo before starting your task.", "warning");
            return;
        }

        swalFire({
            title: "Are you sure?",
            text: "You want to start your todo now?",
            icon: "warning",
            showCancelButton: true,
        }).then((result) => {
            if (result.isConfirmed) {
                router.put(route('weekly-task-schedule.onhandler', task.dailytask_id), {
                    status: 'On Going' // Logic handled in controller
                });
            }
        });
    });
};

const endTask = (task) => {
    remarksForm.dailytask_id = task.dailytask_id;
    remarksForm.startdate = task.startdate;
    remarksForm.remarks = "";
    showingRemarksModal.value = true;
};

const submitRemarks = () => {
    if (!remarksForm.remarks) {
        // Show error
        return;
    }

    // Determine status logic (HIT/MISS) - simplistic replication of original logic
    // Original: check if taskdate is today
    // I'll just pass remarks and let controller or backend handle it, or do logic here.
    // The original logic was in frontend:
    // if (moment(taskdate).format(...) === moment().format(...)) status = HIT else MISS
    // I'll replicate:
    const task = props.tasks.data.find(t => t.dailytask_id == remarksForm.dailytask_id);
    const isHit = moment(task.taskdate).isSame(moment(), 'day');
    remarksForm.status = isHit ? 'HIT' : 'MISS';

    remarksForm.put(route('weekly-task-schedule.onhandler', remarksForm.dailytask_id), {
        onSuccess: () => {
            showingRemarksModal.value = false;
        }
    });
};

const completeHolidayTask = (task) => {
     swalFire({
        title: "Are you sure?",
        text: "You want to complete your task?",
        icon: "warning",
        showCancelButton: true,
    }).then((result) => {
        if (result.isConfirmed) {
            router.put(route('weekly-task-schedule.onTashHoliday', task.dailytask_id));
        }
    });
};

// Helper for status colors
const getStatusColor = (status) => {
    switch (status) {
        case 'On Going': return 'text-blue-600';
        case 'HIT': return 'text-green-600';
        case 'MISS': return 'text-red-600';
        case 'HOLIDAY': return 'text-purple-600';
        default: return 'text-gray-600';
    }
};

onMounted(() => {
    const flash = page.props && page.props.flash ? page.props.flash : {};
    if (flash.open_task_id) {
        const task = props.tasks && props.tasks.data
            ? props.tasks.data.find(t => t.dailytask_id == flash.open_task_id)
            : null;
        if (task) {
            openSubTaskModal(task);
        }
    }

    // Toastr handling if flash messages exist
    if (flash.success) {
        // toastr.success(page.props.flash.success);
        // Assuming user has a toastr setup, otherwise rely on UI feedback
    }
    axios.get(route('weekly-task-schedule.tasktypes'), { headers: { Accept: 'application/json' } })
        .then(res => {
            taskTypes.value = res.data.tasktypes || [];
        })
        .catch(() => {
            taskTypes.value = [];
        });
});

</script>

<template>
    <Head title="Weekly Task Dashboard" />

    <WeeklyTaskLayout>

             <CardBox class="flex-1 p-6" has-table>
                        <div class="mb-4">
                            <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-100">
                                {{ page.props.auth.user.first_name }} {{ page.props.auth.user.last_name }} - My Prio
                            </h2>
                            <div class="mt-2 border-b border-gray-200 dark:border-slate-700"></div>
                        </div>
                        <div class="flex justify-between items-center mb-6">
                            <BaseButton @click="openCreateModal" color="info" icon="mdiPlus" label="New Task"/>

                            <FormControl v-model="params.query" placeholder="Search..." class="w-64" :clearable="true" />
                        </div>

                        <!-- Task List -->
                        <div v-if="tasks.data.length === 0" class="flex justify-center items-center p-10 bg-gray-50 rounded-lg">
                            <div class="text-center">
                                <img src="/img/no task.jpg" alt="No Task" class="mx-auto h-48 opacity-50 mb-4" />
                                <p class="text-gray-500">No tasks found.</p>
                            </div>
                        </div>

                        <div v-else class="space-y-4">
                            <div v-for="task in tasks.data" :key="task.dailytask_id" class="border rounded-lg shadow-sm dark:border-slate-700">
                                <div class="bg-gray-50 dark:bg-slate-800 dark:text-gray-100 p-4 rounded-t-lg flex justify-between items-center border-b dark:border-slate-700">
                                    <div class="flex items-center space-x-3 cursor-pointer" @click="selectedTask = selectedTask === task.dailytask_id ? null : task.dailytask_id">
                                        <i class="fa fa-chevron-right transition-transform duration-200 text-gray-400" :class="{ 'rotate-90': selectedTask === task.dailytask_id }"></i>
                                        <BaseIcon path="mdiCalendar" class="text-red-500" :size="20" />
                                        <div class="font-semibold text-gray-800 dark:text-gray-100">
                                            {{ moment(task.taskdate).format('MMMM D, YYYY') }} - {{ task.site_name }}
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <BaseButton icon="mdiWatch" label="Start" v-if="!task.startdate && task.tasktype !== 'HOLIDAY'" @click.stop="startTask(task)" :border="false" color="" class="bg-green-600 hover:bg-green-700 text-white dark:bg-green-600"/>
                                        <BaseButton icon="mdiWatch" label="End" v-if="task.startdate && !task.enddate && task.tasktype !== 'HOLIDAY'" @click.stop="endTask(task)" :border="false" color="" class="bg-red-600 hover:bg-red-700 text-white dark:bg-red-600"/>
                                        <BaseButton icon="mdiFormatListBulleted" @click.stop="openSubTaskModal(task)" :border="false" color="" class="px-3 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 dark:bg-blue-600"/>
                                    </div>
                                </div>
                                <div class="p-4 dark:bg-slate-900 dark:text-gray-100" v-show="selectedTask === task.dailytask_id">
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                        <div>
                                            <p class="font-bold">Site: <span class="font-normal text-gray-700 dark:text-gray-200">{{ task.site_name }}</span></p>
                                            <p class="font-bold">Planned Date: <span class="font-normal text-gray-700 dark:text-gray-200">{{ task.plandate }}</span></p>
                                            <p class="font-bold">Planned End Date: <span class="font-normal text-gray-700 dark:text-gray-200">{{ task.planenddate }}</span></p>

                                            <BaseButton label="Edit" icon="mdiBookEditOutline" @click.stop="editTask(task)" color="info"/>
                                        </div>
                                        <div>
                                            <p class="font-bold">Start Date: <span class="font-normal text-gray-700 dark:text-gray-200">{{ moment(task.startdate).format('MMMM D, YYYY , HH:mm A') }} </span></p>
                                            <p class="font-bold">Accomplished Date: <span class="font-normal text-gray-700 dark:text-gray-200">{{ task.enddate }}</span></p>
                                            <p class="font-bold">Type: <span class="font-normal text-gray-700 dark:text-gray-200">{{ taskTypeLabel(task.tasktype) }}</span></p>
                                        </div>
                                        <div>
                                            <p class="font-bold">Status: <span class="font-normal text-gray-700 dark:text-gray-200">{{ task.status }}</span></p>
                                            <p class="font-bold">Attachment: <span class="font-normal text-gray-700 dark:text-gray-200">{{ task.attachment }}</span></p>
                                            <p class="font-bold">Remarks: <span class="font-normal text-gray-700 dark:text-gray-200">{{ task.remarks }}</span></p>

                                             <BaseButton v-if="!task.startdate && !task.enddate" label="Drop" icon="mdiDeleteOutline" @click.stop="dropTask(task.dailytask_id)" color="danger"/>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Pagination -->
                         <div class="mt-6">
                            <!-- Implement pagination links if needed, usually passed in meta -->
                             <!-- <Pagination :links="tasks.links" /> -->
                        </div>
             </CardBox>


        <!-- Create/Edit Modal -->
        <Modal :show="showingCreateModal" :closeable="false" @close="showingCreateModal = false">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4 ">
                    {{ editing ? `Edit My Task on ${editTitleDate}` : 'Create New Task' }}
                </h2>

                <form @submit.prevent="submitTask" class="space-y-4">
                    <template v-if="editing">
                        <FormField label="Select a Site:">
                         
                            <select v-model="form.site" id="site" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full">
                                <option value="">Select Site</option>
                                <option v-for="site in sites" :key="site.id" :value="site.id">{{ site.branch_name }}</option>
                            </select>
                            <InputError :message="form.errors.site" class="mt-2" />
                        </FormField>
                        <FormField label="Type">
                            <select v-model="form.tasktype" id="tasktype" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full">
                                <option value="">Select Type</option>
                                <option v-for="type in taskTypes" :key="type.id" :value="type.id">{{ type.name }}</option>
                            </select>
                            <InputError :message="form.errors.tasktype" class="mt-2" />
                        </FormField>
                    </template>
                    <template v-else>
                        <FormField label="Site">
                            <select v-model="form.site" id="site" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full">
                                <option value="">Select Site</option>
                                <option v-for="site in sites" :key="site.id" :value="site.id">{{ site.branch_name }}</option>
                            </select>
                            <InputError :message="form.errors.site" class="mt-2" />
                        </FormField>
                        <FormField label="Task Type">
                            <select v-model="form.tasktype" id="tasktype" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full">
                                <option value="">Select Type</option>
                                <option v-for="type in taskTypes" :key="type.id" :value="type.id">{{ type.name }}</option>
                            </select>
                            <InputError :message="form.errors.tasktype" class="mt-2" />
                        </FormField>
                        <FormField label="Project / Description">
                            <FormControl v-model="form.project" required />
                            <InputError :message="form.errors.project" class="mt-2" />
                        </FormField>
                        <div class="grid grid-cols-2 gap-4">
                            <FormField label="Plan Start Date">
                                <FormControl v-model="form.plandate" type="datetime-local" required />
                                <InputError :message="form.errors.plandate" class="mt-2" />
                            </FormField>
                            <FormField label="Plan End Date">
                                <FormControl v-model="form.planenddate" type="datetime-local" required />
                                <InputError :message="form.errors.planenddate" class="mt-2" />
                            </FormField>
                        </div>
                    </template>

                    <!-- Hidden field for enddates logic if needed, but handled by controller logic using planenddate -->

                    <div class="flex justify-end mt-6">
                        <SecondaryButton @click="showingCreateModal = false" class="mr-3">Cancel</SecondaryButton>
                        <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                            {{ editing ? 'submit' : 'Save' }}
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- SubTask Modal -->
        <Modal :show="showingTaskModal" :closeable="false" @close="showingTaskModal = false">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">
                    Todo List
                </h2>

                        <div class="flex space-x-2 mb-4">
                            <FormControl v-model="subTaskForm.task_name" placeholder="New Todo..." class="w-full" :clearable="true" />
                            <BaseButton :icon="subTaskForm.id ? 'mdiPen' : 'mdiPlus'" @click="submitSubTask" :disabled="subTaskForm.processing">
                                {{ subTaskForm.id ? 'Edit' : 'Add' }}
                            </BaseButton>
                        </div>

                <div v-if="isloadingTask" class="text-center py-4">Loading...</div>

                <ul v-else class="space-y-2">
                     <li v-for="item in pendingSubTasks" :key="item.id" class="flex justify-between items-center p-2 bg-gray-50 dark:bg-slate-800 rounded">
                        <div class="flex items-center flex-1">
                            <input v-if="selectedTask && selectedTask.startdate" type="checkbox" :checked="item.status" @change="toggleSubTaskCompletion(item)" class="mr-2 rounded text-blue-600 focus:ring-blue-500" />
                            <span @click="editSubTask(item)" class="cursor-pointer hover:text-blue-600 dark:hover:text-blue-400" :class="{ 'line-through text-gray-400': item.status }">{{ item.task_name }}</span>
                        </div>
                        <button @click="deleteSubTask(item.id)" class="text-red-500 hover:text-red-700">
                            <i class="fa fa-times"></i>
                        </button>
                    </li>

                    <div v-if="completedSubTasks.length > 0" class="mt-4">
                        <button @click="showCompleted = !showCompleted" class="flex items-center text-sm font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                             <i class="fa mr-2" :class="showCompleted ? 'fa-chevron-down' : 'fa-chevron-right'"></i>
                             Completed ({{ completedSubTasks.length }})
                        </button>
                    </div>

                    <div v-show="showCompleted && completedSubTasks.length > 0" class="space-y-2 mt-2 pl-4 border-l-2 border-gray-200 dark:border-gray-700">
                         <li v-for="item in completedSubTasks" :key="item.id" class="flex justify-between items-center p-2 bg-gray-50 dark:bg-slate-800 rounded opacity-75">
                            <div class="flex items-center flex-1">
                                <input v-if="selectedTask && selectedTask.startdate" type="checkbox" :checked="item.status" @change="toggleSubTaskCompletion(item)" class="mr-2 rounded text-blue-600 focus:ring-blue-500" />
                                 <span @click="editSubTask(item)" class="cursor-pointer hover:text-blue-600 dark:hover:text-blue-400 line-through text-gray-400">{{ item.task_name }}</span>
                            </div>
                             <button @click="deleteSubTask(item.id)" class="text-red-500 hover:text-red-700">
                                <i class="fa fa-times"></i>
                            </button>
                         </li>
                    </div>
                </ul>

                <div class="mt-6 flex justify-end">
                    <SecondaryButton @click="showingTaskModal = false">Close</SecondaryButton>
                </div>
            </div>
        </Modal>

        <!-- Remarks Modal -->
        <Modal :show="showingRemarksModal" :closeable="false" @close="showingRemarksModal = false">
             <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">
                    End Task Remarks
                </h2>
                        <FormField label="Remarks">
                            <FormControl v-model="remarksForm.remarks" required />
                            <InputError :message="remarksForm.errors.remarks" class="mt-2" />
                        </FormField>
                <div class="flex justify-end">
                    <SecondaryButton @click="showingRemarksModal = false" class="mr-3">Cancel</SecondaryButton>
                    <PrimaryButton @click="submitRemarks" :disabled="remarksForm.processing">Submit</PrimaryButton>
                </div>
            </div>
        </Modal>

    </WeeklyTaskLayout>
</template>
