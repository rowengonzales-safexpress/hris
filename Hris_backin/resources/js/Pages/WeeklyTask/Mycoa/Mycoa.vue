<script setup>
import { onMounted, ref, watch, computed } from "vue";
import { Head, usePage, router } from "@inertiajs/vue3";
import moment from "moment";
import html2canvas from "html2canvas";
// Use globally registered SkeletonLoader component
import FormControl from "@/Components/FormControl.vue";
import CardBox from "@/Components/CardBox.vue";
import CoreTable from "@/Components/CoreTable.vue";
import Modal from "@/Components/Modal.vue";
import WeeklyTaskLayout from "@/Layouts/WeeklyTaskLayout.vue";
import SectionTitleLineWithButton from "@/Components/SectionTitleLineWithButton.vue"
import BaseButton from "@/Components/BaseButton.vue";
const pageTitle = "My Coa";

const page = usePage();
const user = computed(() => page.props.auth?.user || {});

const fullName = computed(() => {
  const fn = user.value?.first_name ?? '';
  const ln = user.value?.last_name ?? '';
  return `${fn} ${ln}`.trim();
});

const isloading = ref(false);
// Variables to store the total counts
const totalTodos = ref(0);
const totalCompleted = ref(0);
const totalPercentage = ref(0);

const themcolor = ref([
    {
        background: "#B98D65",
        active_background: "#72461F",
        font_background: "#F8F9FA",
    },
    {
        background: "#2196F3",
        active_background: "#1769AA",
        font_background: "#F8F9FA",
    },
    {
        background: "#FFC400",
        active_background: "#B28900",
        font_background: "#F8F9FA",
    },
    {
        background: "#9C27B0",
        active_background: "#6D1B7B",
        font_background: "#F8F9FA",
    },

    {
        background: "#009688",
        active_background: "#00695F",
        font_background: "#F8F9FA",
    },
    {
        background: "#607d8b",
        active_background: "#37474f",
        font_background: "#F8F9FA",
    },
]);
//format date
const getFormattedDate = () => {
    const options = { month: "long", day: "numeric", year: "numeric" };
    return new Date().toLocaleString("en-US", options);
};
const formattedDate = ref(getFormattedDate());
//end format date

const isCurrentDate = (taskDate) => {
    const currentDate = new Date().toISOString().split("T")[0];
    return taskDate === currentDate;
};

const getTaskColor = (task) => {
    const isToday = moment(task.taskdate).format('MMMM D, YYYY') === formattedDate.value;
    if (isToday) {
        return task.active_background == '' ? '#72461F' : task.active_background;
    }
    return task.background == '' ? '#B98D65' : task.background;
};

//capture function
const capturevsc = () => {
    const container = document.getElementById("captureVSCContainer");

    html2canvas(container).then((canvas) => {
        const dataURL = canvas.toDataURL();
        const link = document.createElement("a");
        link.href = dataURL;
        link.download = `${fullName.value} - ${pageTitle}.png`;
        link.click();
    });
};

const capturehitrate = () => {
    const container = document.getElementById("captureHitRateContainer");

    html2canvas(container).then((canvas) => {
        const dataURL = canvas.toDataURL();
        const link = document.createElement("a");
        link.href = dataURL;
        link.download = `${fullName.value} - My HIT RATE.png`;
        link.click();
    });
};

const containercapture = ref(null);
const selectedDateRange = ref("today");
const lists = ref({ data: [] });
const listscount = ref({ data: [] });
const showFilterModal = ref(false);
const showThemeModal = ref(false);
const getItems = () => {
    isloading.value = true;
    axios.get(`/weekly-task-schedule/api/myvsc`).then((response) => {
        isloading.value = false;
        lists.value = response.data.dailyTasks;
        listscount.value = response.data.TaskList;
        totalTodos.value = response.data.totaltasklist;
        totalCompleted.value = response.data.totalcomplettask;
        totalPercentage.value = response.data.totalpercentcomplete;
    });
};

const onFilterDate = () => {
    showFilterModal.value = true;
};

// Create a reactive form object

const form = ref({
    start_date: "",
    end_date: "",
});

const fromDate = ref("");
const toDate = ref("");

// Watch for changes in Sdate and StrHours and update plandate
watch([fromDate], () => {
    const originalDate = new Date(fromDate.value);
    const year = originalDate.getFullYear();
    const month = String(originalDate.getMonth() + 1).padStart(2, "0");
    const day = String(originalDate.getDate()).padStart(2, "0");
    form.value.start_date = `${year}-${month}-${day}`;
});

// Watch for changes in Edate and EndHours and update planenddate
watch([toDate], () => {
    const originalDate = new Date(toDate.value);
    const year = originalDate.getFullYear();
    const month = String(originalDate.getMonth() + 1).padStart(2, "0");
    const day = String(originalDate.getDate()).padStart(2, "0");
    form.value.end_date = `${year}-${month}-${day}`;
});

const fillMissingDates = (tasks, startDate, endDate) => {
    const start = moment(startDate);
    const end = moment(endDate);
    const filled = [];

    let defaultTheme = {
        background: themcolor.value[0].background,
        active_background: themcolor.value[0].active_background,
        font_background: themcolor.value[0].font_background
    };

    if (tasks && tasks.length > 0) {
        defaultTheme = {
            background: tasks[0].background,
            active_background: tasks[0].active_background,
            font_background: tasks[0].font_background
        };
    }

    const taskMap = {};
    if (tasks) {
        tasks.forEach(task => {
            const d = moment(task.taskdate).format('YYYY-MM-DD');
            taskMap[d] = task;
        });
    }

    for (let m = moment(start); m.isSameOrBefore(end); m.add(1, 'days')) {
        const dateStr = m.format('YYYY-MM-DD');
        if (taskMap[dateStr]) {
            filled.push(taskMap[dateStr]);
        } else {
            filled.push({
                id: 'placeholder_' + dateStr,
                taskdate: dateStr,
                site_name: 'No Schedule',
                tasktype: null,
                task_lists: [],
                ...defaultTheme
            });
        }
    }
    return filled;
};

const applyFilter = () => {
    isloading.value = true;

    axios
        .get("/weekly-task-schedule/api/filter-vsc", {
            params: {
                start_date: form.value.start_date,
                end_date: form.value.end_date,
            },
        })
        .then((response) => {
            isloading.value = false;
            lists.value = fillMissingDates(response.data.dailyTasks, form.value.start_date, form.value.end_date);
            listscount.value = response.data.TaskList;
        })
        .catch((error) => {
            // Handle errors
            console.error(error);
        })
        .finally(() => {
            showFilterModal.value = false;
        });
};

const onPickThemes = () => {
    showThemeModal.value = true;
};

const handleThemeClick = (item) => {
    if (item) {
        const userId = user.value.id;

        axios
            .post("/weekly-task-schedule/api/changethemes", {
                userid: userId,
                background: item.background,
                active_background: item.active_background,
                font_background: item.font_background,
            })
            .then((response) => {
                showThemeModal.value = false;
                isloading.value = true;
                axios.get(`/weekly-task-schedule/api/myvsc`).then((response) => {
                    isloading.value = false;
                    lists.value = response.data.dailyTasks;
                    listscount.value = response.data.TaskList;
                });
            })
            .catch((error) => {
                console.error(error);
            });
    } else {
        console.error("Invalid item:", item);
    }
};

const getPercentageColor = (percentage) => {
    const p = parseFloat(percentage);
    if (p >= 100) return 'bg-green-600';
    if (p >= 51) return 'bg-orange-500';
    return 'bg-red-600';
};

const tableHeader = [
  { label: "Planed Date", fieldName: "taskdate_text", type: "html" },
  { label: "Total Todos", fieldName: "task_lists_count" },
  { label: "Complete", fieldName: "completed_task_count", type: "slot", slotName: "complete" },
  { label: "Remarks", fieldName: "remarks" },
  { label: "Status", fieldName: "status_text", type: "slot", slotName: "status" },
  { label: "Percentage Todos", fieldName: "percentage_completed", type: "slot", slotName: "percentage" },
];

const tableRows = computed(() => {
  const rows = Array.isArray(listscount.value) ? listscount.value : (listscount.value?.data ?? []);
  return rows.map((r) => ({
    ...r,
    taskdate_text: moment(r.taskdate).format("MMMM D, YYYY"),
    status_text: r.status === null || r.status === "" ? "On Going!" : r.status,
  }));
});

onMounted(() => {
    getItems();



});
</script>
<template>
    <Head title="Weekly Task Dashboard" />
    <WeeklyTaskLayout>

                <CardBox id="captureVSCContainer">
                     <SectionTitleLineWithButton icon="mdiListBox" :title="`${fullName}
                            - My COA`">
<BaseButton :icon="'mdiPalette'"  rounded-full small @click="onPickThemes" />
        </SectionTitleLineWithButton>

                        <div>
                      <div class="flex justify-between items-center mb-2">
                                <BaseButton
                                    :icon="'mdiCamera'"
                                    rounded-full
                                    small
                                    @click="capturevsc"
                                />
                                <BaseButton
                                    :icon="'mdiFilter'"
                                    rounded-full
                                    small
                                    @click="onFilterDate"
                                />
                        </div>

                        <SkeletonLoader v-if="isloading" type="card" :item-count="8" :columns="4" />
                        <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                            <div
                                class="h-full"
                                v-for="task in lists"
                                :key="task.id"
                            >

                                <div class="rounded-lg shadow-md overflow-hidden h-full flex flex-col" :style="{ backgroundColor: getTaskColor(task) }">
                                    <!-- Header -->
                                    <div class="text-center py-2 border-b-2 border-gray-200">
                                        <div class="text-black text-xl font-bold flex justify-center items-center">
                                            {{ moment(task.taskdate).format("dddd") }}
                                            <img src="/img/calindar_logo.png" class="w-6 h-6 ml-2 opacity-80" draggable="false" />
                                        </div>
                                        <div class="bg-white text-black text-sm border-t border-b border-gray-300 mx-4 mt-1 py-1 font-semibold">
                                            {{ moment(task.taskdate).format("MMMM D, YYYY") }}
                                        </div>
                                    </div>

                                    <!-- Body -->
                                    <div class="p-3 flex-grow flex flex-col text-white">
                                        <div class="mb-3 font-medium">
                                            <span v-if="task.tasktype == 5">WORK FROM HOME</span>
                                            <span v-else-if="task.tasktype == 6">Holiday</span>
                                            <span v-else>{{ task.site_name }}</span>
                                        </div>

                                        <div v-if="task.task_lists && task.task_lists.length > 0" class="bg-white text-gray-800 rounded p-3 shadow-sm mt-auto">
                                            <div class="text-xs font-bold text-gray-500 uppercase mb-2">Todos</div>
                                            <ul class="list-none m-0 p-0 space-y-2">
                                                <li v-for="taskList in task.task_lists" :key="taskList.id" class="flex items-center">
                                                    <input type="checkbox" :checked="taskList.status == 1" disabled class="h-4 w-4 mr-2 accent-black border-gray-400 rounded" />
                                                    <span class="text-sm leading-snug">{{ taskList.task_name }}</span>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="flex justify-end mt-4 text-gray-500">{{ formattedDate }}</div>
                    </div>
                </CardBox>

                <!-- Hit rate -->
                <CardBox id="captureHitRateContainer" class="mt-6">
                    <SectionTitleLineWithButton icon="mdiListBox" :title="`${fullName} - My HIT RATE`">
                        <BaseButton :icon="'mdiCamera'"  rounded-full small @click="capturehitrate" />
                    </SectionTitleLineWithButton>

                    <div class="overflow-x-auto">
                        <CoreTable :table-header="tableHeader" :table-rows="tableRows" tableName="hitRateTable" :is-paginated="true">
                            <template #status="{ slotProp }">
                                <div class="p-1 text-center rounded" :class="{'bg-red-600 text-white': slotProp.status_text === 'MISS', 'bg-green-600 text-white': slotProp.status_text === 'HIT', 'text-gray-700': slotProp.status_text !== 'MISS' && slotProp.status_text !== 'HIT'}">
                                    {{ slotProp.status_text }}
                                </div>
                            </template>
                            <template #complete="{ slotProp }">
                                <div class="p-1 text-center rounded" :class="{'bg-green-600 text-white': slotProp.completed_task_count > 0 && slotProp.completed_task_count === slotProp.task_lists_count}">
                                    {{ slotProp.completed_task_count }}
                                </div>
                            </template>
                            <template #percentage="{ slotProp }">
                                <div class="p-1 text-center text-white font-bold rounded" :class="getPercentageColor(slotProp.percentage_completed)">
                                    {{ slotProp.percentage_completed }}%
                                </div>
                            </template>
                        </CoreTable>

                        <!-- Overall Summary -->
                        <div class="mt-2 border-t-2 border-gray-300 pt-4 px-4 bg-gray-50 dark:bg-slate-800">
                            <div class="grid grid-cols-2 md:grid-cols-6 gap-4 font-bold text-sm items-center">
                                <div class="md:text-left text-gray-700 dark:text-gray-300">OVERALL:</div>
                                <div class="md:text-center text-gray-700 dark:text-gray-300">Total Todos: {{ totalTodos }}</div>
                                <div class="md:text-center text-gray-700 dark:text-gray-300">Complete: {{ totalCompleted }}</div>
                                <div class="hidden md:block"></div>
                                <div class="hidden md:block"></div>
                                <div class="md:text-center text-white p-2 rounded shadow-sm" :class="getPercentageColor(totalPercentage)">
                                    {{ totalPercentage }}%
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="flex justify-end mt-4 text-gray-500">{{ formattedDate }}</div>
                </CardBox>

    </WeeklyTaskLayout>
    <Modal :show="showFilterModal" @close="showFilterModal = false" maxWidth="sm">
        <div class="p-6">
            <h5 class="text-lg font-semibold mb-4">{{ pageTitle }} Summary Report</h5>
            <div class="fromtocenter space-y-4">
                <div>
                    <label for="fromDate" class="block text-sm font-medium text-gray-700">From:</label>
                    <FormControl type="date" v-model="fromDate" />
                </div>
                <div>
                    <label for="toDate" class="block text-sm font-medium text-gray-700">To:</label>
                    <FormControl type="date" v-model="toDate" />
                </div>
            </div>
            <div class="flex justify-end gap-2 mt-6">
                <button type="button" class="px-3 py-2 rounded bg-gray-200 hover:bg-gray-300 text-gray-800" @click="showFilterModal = false">Cancel</button>
                <button @click="applyFilter" type="button" class="px-3 py-2 rounded bg-blue-600 hover:bg-blue-700 text-white">Generate</button>
            </div>
        </div>
    </Modal>

    <Modal :show="showThemeModal" @close="showThemeModal = false" maxWidth="md">
        <div class="p-6">
            <h5 class="text-lg font-semibold mb-4">Card Themes Picker</h5>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                <div v-for="item in themcolor" :key="item.background" @click="handleThemeClick(item)" class="cursor-pointer">
                    <div class="flex rounded overflow-hidden shadow border">
                        <div class="h-8 w-1/2" :style="{ backgroundColor: item.background }"></div>
                        <div class="h-8 w-1/2" :style="{ backgroundColor: item.active_background }"></div>
                    </div>
                </div>
            </div>
            <div class="flex justify-end mt-6">
                <button type="button" class="px-3 py-2 rounded bg-gray-200 hover:bg-gray-300 text-gray-800" @click="showThemeModal = false">Cancel</button>
            </div>
        </div>
    </Modal>
</template>

<style scoped>
.fromtocenter {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    /* Optional: Add additional styling if needed */
    margin-top: 5px; /* Adjust as needed */
}
</style>
