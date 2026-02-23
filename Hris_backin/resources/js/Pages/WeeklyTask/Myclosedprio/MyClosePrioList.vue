<script setup>
import axios from "axios";
import { ref, onMounted, reactive, watch, computed } from "vue";
import FormField from "@/Components/FormField.vue";
import FormControl from "@/Components/FormControl.vue";
import service from "@/Components/Toast/service";
import MyClosePrioListItem from "./MyClosePrioListItem.vue";
import { debounce } from "lodash";
import CoreTable from '@/Components/CoreTable.vue'
import CardBox from "@/Components/CardBox.vue";
import html2canvas from "html2canvas";
import moment from 'moment';
import WeeklyTaskLayout from "@/Layouts/WeeklyTaskLayout.vue";
import { Head, usePage } from "@inertiajs/vue3";
import SectionTitleLineWithButton from "@/Components/SectionTitleLineWithButton.vue"
import BaseButton from "@/Components/BaseButton.vue";


const isloading = ref(false);
let toast = service();
const lists = ref({ data: [] });
const allItems = ref([]);
const pageSize = ref(10);
const currentPage = ref(1);
const fromDate = ref("");
const toDate = ref("");

const page = usePage();
const user = computed(() => page.props.auth?.user || {});
const branchName = computed(() => page.props.branchName || 'Main Branch');
const fullName = computed(() => {
  const fn = user.value?.first_name ?? '';
  const ln = user.value?.last_name ?? '';
  return `${fn} ${ln}`.trim();
});

const setPage = (page = 1) => {
  currentPage.value = page;
  const start = (page - 1) * pageSize.value;
  const end = start + pageSize.value;
  lists.value = { data: allItems.value.slice(start, end) };
};

const fetchAll = () => {
  isloading.value = true;
  axios
    .get(`/weekly-task-schedule/mycloseprio`, {
      params: {
        query: searchQuery.value,
      },
    })
    .then((response) => {
      isloading.value = false;
      allItems.value = Array.isArray(response.data) ? response.data : (response?.data?.data ?? []);
      setPage(1);
    })
    .catch(() => {
      isloading.value = false;
      allItems.value = [];
      setPage(1);
    });
};

const capturemycloseprio = () => {
    const container = document.getElementById("capturePrioContainer");

    html2canvas(container).then((canvas) => {
        const dataURL = canvas.toDataURL();
        const link = document.createElement("a");
        link.href = dataURL;
        link.download = "MY CLOSED PRIO.png";
        link.click();
    });
};
//filter datae
const onFilterDate = () => {
    $("#FormModalfilterDate").modal("show");
};
const applyFilter = () => {
    isloading.value = true;
    axios
        .get("/weekly-task-schedule/filter-closeprio", {
           params:{
             start_date: fromDate.value,
             end_date: toDate.value,
           }
        })
        .then((response) => {
            isloading.value = false;
            allItems.value = Array.isArray(response.data) ? response.data : (response?.data?.data ?? []);
            setPage(1);
        })
        .catch((error) => {
            // Handle errors
            console.error(error);
        })
        .finally(() => {
            // Close the modal or perform any other actions
            $("#FormModalfilterDate").modal("hide");
        });
};
const searchQuery = ref(null);
// CoreTable headers for closed prio list
const tableHeader = [
  { label: 'Site', fieldName: 'sitename' },
  { label: 'Planned Date', fieldName: 'taskdate', type: 'date' },
  { label: 'Start Date', fieldName: 'startdate', type: 'datetime' },
  { label: 'End Date', fieldName: 'enddate', type: 'datetime' },
  { label: 'Task', fieldName: 'tasktype.listtask' },
  { label: 'Status', fieldName: 'status' },
  { label: 'Remark', fieldName: 'remarks' },
];

// Flatten rows for CoreTable display
const tableRows = computed(() => {
  const rows = lists.value?.data ?? [];
  return rows.map((r) => ({
    ...r,
    task: r?.tasktype?.listtask ?? ''
  }));
});

watch(
    searchQuery,
    debounce(() => {
        fetchAll();
    }, 300)
);

onMounted(() => {
    fetchAll();


});
</script>

<template>
    <Head title="Weekly Task Dashboard" />
    <WeeklyTaskLayout>
    <CardBox>
        <SectionTitleLineWithButton icon="mdiListBox" :title="`${fullName}
                            - My Closed Prio`">
<BaseButton :icon="'mdiCamera'"  color="danger" rounded-full small @click="capturemycloseprio" />
        </SectionTitleLineWithButton>
        <div class="container-fluid">
            <div class="card" id="capturePrioContainer">
               
                <div class="card-body">
                    <SkeletonLoader v-if="isloading" type="table" :item-count="8" />
                    <div v-else class="content">
                        <div class="container-fluid">
                            <div class="d-flex justify-content-between">
                                <div class="d-flex">
                                    <button
                                        @click="capturemycloseprio"
                                        type="button"
                                        class="mb-2 btn btn-sm btn-success"
                                    >
                                        <i class="fa fa-camera"></i>
                                    </button>
                                </div>

                            </div>

                            <div>
                                <CoreTable
                                  :table-rows="tableRows"
                                  :table-header="tableHeader"
                                  table-name="MyClosedPrio"
                                  :is-paginated="true"
                                  :rows-per-page="pageSize"
                                  :loading="isloading"
                                  :searchable-fields="'sitename,task,remarks,status'"
                                  :show-download-csv="true"
                                  :show-page-info="true"
                                  :show-total="true"
                                />

                             
                            </div>
                        </div>
                    </div>
                </div>
            </div>
  
        </div>
    </CardBox>
    </WeeklyTaskLayout>

    <div
        class="modal fade"
        id="FormModalfilterDate"
        data-backdrop="static"
        tabindex="-1"
        role="dialog"
        aria-labelledby="staticBackdropLabel"
        aria-hidden="true"
    >
        <div class="modal-dialog modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="staticBackdropLabel">
                        <span>My Close Prio</span>
                    </h5>
                    <button
                        type="button"
                        class="close"
                        data-dismiss="modal"
                        aria-label="Close"
                    >
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="fromtocenter">
                        <FormField label="From">
                            <FormControl v-model="fromDate" type="date" />
                        </FormField>
                        <FormField label="To">
                            <FormControl v-model="toDate" type="date" />
                        </FormField>
                    </div>
                </div>
                <div class="modal-footer">
                    <button
                        type="button"
                        class="btn btn-secondary"
                        data-dismiss="modal"
                    >
                        Cancel
                    </button>
                    <button
                        @click="applyFilter"
                        type="button"
                        class="btn btn-primary"
                    >
                        Generate
                    </button>
                </div>
            </div>
        </div>
    </div>
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
