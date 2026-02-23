<script setup>
import { onMounted, ref } from 'vue';
import { usePage } from '@inertiajs/vue3'
import { Bar } from "vue-chartjs"
import {
  Chart as ChartJS,
  Title,
  Tooltip,
  Legend,
  BarElement,
  CategoryScale,
  LinearScale,
} from "chart.js";
import SectionTitleLineWithButton from '@/Components/SectionTitleLineWithButton.vue'
import CardBox from '@/Components/CardBox.vue'
import { mdiChartBar } from '@mdi/js'
import WeeklyTaskLayout from '@/Layouts/WeeklyTaskLayout.vue';
import { Head } from '@inertiajs/vue3';
ChartJS.register(
  Title,
  Tooltip,
  Legend,
  BarElement,
  CategoryScale,
  LinearScale
);

const year = ref(new Date().getFullYear());

const chartData = ref({
      labels: [],
      datasets: [
        {
          data: [],
        },
        {
          data: [],
        },
        {
          data: [],
        },
      ],
    });

const getChart = () => {
    // Assuming axios is available globally or we import it.
    // In inertia/laravel setup, axios is usually globally available.
      axios.get(route('weekly-task-schedule.dashboard.chart'))
        .then((response) => {
        const data = response.data;
        chartData.value = response.data;

        })
        .catch((error) => {
          console.error('Error fetching chart data:', error);
        });
    };

onMounted(() => {
    getChart();
});
</script>

<template>
     <Head title="Weekly Task Dashboard" />

    <WeeklyTaskLayout>
  <SectionTitleLineWithButton :icon="mdiChartBar" title="Dashboard" main>
  </SectionTitleLineWithButton>

    <div class="grid grid-cols-1 gap-6 mb-6">
        <CardBox>
            <div class="mb-4 text-xl font-bold">
                Yearly Progress - {{ year }}
            </div>
            <div class="h-96">
                <Bar :data="chartData" :options="{ responsive: true, maintainAspectRatio: false }" />
            </div>
        </CardBox>
    </div>
    </WeeklyTaskLayout>
</template>
