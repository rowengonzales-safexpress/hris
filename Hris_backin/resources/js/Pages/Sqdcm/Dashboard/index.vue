<script setup>
import SqdcmLayout from '@/Layouts/SqdcmLayout.vue'
import { Head } from '@inertiajs/vue3'
import { ref, onMounted, computed } from 'vue'
import axios from 'axios'
import KpiUtilizationChart from '@/Components/Sqdcm/KpiUtilizationChart.vue'
import KpiStandingChart from '@/Components/Sqdcm/KpiStandingChart.vue'
import AttendancePerformanceTable from '@/Components/Sqdcm/AttendancePerformanceTable.vue'
import SiteSelector from '@/Components/Sqdcm/SiteSelector.vue'
import DateRangePicker from '@/Components/Sqdcm/DateRangePicker.vue'

const props = defineProps({
  sites: {
    type: Array,
    default: () => []
  }
})

// Reactive data
const loading = ref(false)
const selectedSite = ref('')
const startDate = ref('')
const endDate = ref('')
const dashboardData = ref({})
const attendanceData = ref([])
const kpiData = ref([])

// Computed properties
const kpiUtilization = computed(() => dashboardData.value?.kpi_utilization || 0)
const totalEntries = computed(() => dashboardData.value?.total_entries || {})
const attendanceSummary = computed(() => dashboardData.value?.attendance_summary || {})
const performanceSummary = computed(() => dashboardData.value?.performance_summary || {})
const kpiUtilizationChart = computed(() => dashboardData.value?.kpi_utilization_chart || [])
const kpiStandingChart = computed(() => dashboardData.value?.kpi_standing_chart || [])

// Methods
const fetchDashboardData = async () => {
  loading.value = true
  try {
    const params = {
      site_code: selectedSite.value,
      start_date: startDate.value,
      end_date: endDate.value
    }

    const response = await axios.get('sqdcm/api/dashboard', { params })
    dashboardData.value = response.data.data
  } catch (error) {
    console.error('Error fetching dashboard data:', error)
  } finally {
    loading.value = false
  }
}

const fetchAttendanceData = async () => {
  try {
    const params = {
      site_code: selectedSite.value,
      start_date: startDate.value,
      end_date: endDate.value,
      level: 'site'
    }

    const response = await axios.get('sqdcm/api/attendance-performance', { params })
    attendanceData.value = response.data.data
  } catch (error) {
    console.error('Error fetching attendance data:', error)
  }
}

const fetchKpiData = async () => {
  try {
    const params = {
      site_code: selectedSite.value,
      start_date: startDate.value,
      end_date: endDate.value
    }

    const response = await axios.get('sqdcm/api/kpi-values', { params })
    kpiData.value = response.data.data
  } catch (error) {
    console.error('Error fetching KPI data:', error)
  }
}

const onSiteChange = (site) => {
  selectedSite.value = site
  fetchAllData()
}

const onDateRangeChange = (dates) => {
  startDate.value = dates.start
  endDate.value = dates.end
  fetchAllData()
}

const fetchAllData = () => {
  fetchDashboardData()
  fetchAttendanceData()
  fetchKpiData()
}

// Initialize dates
const initializeDates = () => {
  const today = new Date()
  const thirtyDaysAgo = new Date(today.getTime() - (30 * 24 * 60 * 60 * 1000))

  endDate.value = today.toISOString().split('T')[0]
  startDate.value = thirtyDaysAgo.toISOString().split('T')[0]
}

onMounted(() => {
  initializeDates()
  if (props.sites.length > 0) {
    selectedSite.value = props.sites[0].site_code
  }
  fetchAllData()
})
</script>

<template>
  <Head title="SQDCM Dashboard" />
  <SqdcmLayout>
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex justify-between items-center">
        <h1 class="text-3xl font-bold text-gray-900">SQDCM Dashboard</h1>
        <div class="flex space-x-4">
          <SiteSelector
            :sites="sites"
            :selected="selectedSite"
            @change="onSiteChange"
          />
          <DateRangePicker
            :start-date="startDate"
            :end-date="endDate"
            @change="onDateRangeChange"
          />
        </div>
      </div>

      <!-- Loading Indicator -->
      <div v-if="loading" class="flex justify-center items-center py-8">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
      </div>

      <!-- KPI Summary Cards -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-blue-500">
          <div class="flex items-center">
            <div class="flex-1">
              <p class="text-sm font-medium text-gray-600">KPI Utilization</p>
              <p class="text-3xl font-bold text-blue-600">{{ kpiUtilization }}%</p>
            </div>
            <div class="p-3 bg-blue-100 rounded-full">
              <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
              </svg>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-green-500">
          <div class="flex items-center">
            <div class="flex-1">
              <p class="text-sm font-medium text-gray-600">Average Attendance</p>
              <p class="text-3xl font-bold text-green-600">{{ attendanceSummary?.average_attendance || 0 }}%</p>
            </div>
            <div class="p-3 bg-green-100 rounded-full">
              <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
              </svg>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-purple-500">
          <div class="flex items-center">
            <div class="flex-1">
              <p class="text-sm font-medium text-gray-600">Average Performance</p>
              <p class="text-3xl font-bold text-purple-600">{{ performanceSummary?.average_performance || 0 }}%</p>
            </div>
            <div class="p-3 bg-purple-100 rounded-full">
              <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
              </svg>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-orange-500">
          <div class="flex items-center">
            <div class="flex-1">
              <p class="text-sm font-medium text-gray-600">Total Entries</p>
              <p class="text-3xl font-bold text-orange-600">{{ totalEntries.total || 0 }}</p>
            </div>
            <div class="p-3 bg-orange-100 rounded-full">
              <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
              </svg>
            </div>
          </div>
        </div>
      </div>

      <!-- Charts Section -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl shadow-lg p-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">KPI Utilization Trend</h3>
          <KpiUtilizationChart :data="kpiUtilizationChart" />
        </div>

        <div class="bg-white rounded-xl shadow-lg p-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">KPI Standing by Category</h3>
          <KpiStandingChart :data="kpiStandingChart" />
        </div>
      </div>

      <!-- Detailed Tables Section -->
      <div class="grid grid-cols-1 gap-6">
        <div class="bg-white rounded-xl shadow-lg p-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Attendance & Performance Overview</h3>
          <AttendancePerformanceTable :data="attendanceData" />
        </div>
      </div>

      <!-- Additional Stats -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-xl shadow-lg p-6">
          <h4 class="text-md font-semibold text-gray-900 mb-3">Attendance Statistics</h4>
          <div class="space-y-2">
            <div class="flex justify-between">
              <span class="text-sm text-gray-600">Max Attendance:</span>
              <span class="text-sm font-medium">{{ attendanceSummary?.max_attendance || 0 }}%</span>
            </div>
            <div class="flex justify-between">
              <span class="text-sm text-gray-600">Min Attendance:</span>
              <span class="text-sm font-medium">{{ attendanceSummary?.min_attendance || 0 }}%</span>
            </div>
            <div class="flex justify-between">
              <span class="text-sm text-gray-600">Avg Present Employees:</span>
              <span class="text-sm font-medium">{{ attendanceSummary?.average_present_employees || 0 }}</span>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-xl shadow-lg p-6">
          <h4 class="text-md font-semibold text-gray-900 mb-3">Performance Statistics</h4>
          <div class="space-y-2">
            <div class="flex justify-between">
              <span class="text-sm text-gray-600">Max Performance:</span>
              <span class="text-sm font-medium">{{ performanceSummary?.max_performance || 0 }}%</span>
            </div>
            <div class="flex justify-between">
              <span class="text-sm text-gray-600">Min Performance:</span>
              <span class="text-sm font-medium">{{ performanceSummary?.min_performance || 0 }}%</span>
            </div>
            <div class="flex justify-between">
              <span class="text-sm text-gray-600">Overall Achievement:</span>
              <span class="text-sm font-medium">{{ performanceSummary?.overall_achievement || 0 }}%</span>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-xl shadow-lg p-6">
          <h4 class="text-md font-semibold text-gray-900 mb-3">Entry Breakdown</h4>
          <div class="space-y-2">
            <div class="flex justify-between">
              <span class="text-sm text-gray-600">Site Entries:</span>
              <span class="text-sm font-medium">{{ totalEntries?.site_entries || 0 }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-sm text-gray-600">Department Entries:</span>
              <span class="text-sm font-medium">{{ totalEntries?.department_entries || 0 }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-sm text-gray-600">Employee Entries:</span>
              <span class="text-sm font-medium">{{ totalEntries?.employee_entries || 0 }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </SqdcmLayout>
</template>
