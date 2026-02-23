<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import TrackingLayout from '@/Layouts/TrackingLayout.vue';
import { Head } from '@inertiajs/vue3';

interface Props {
    summary: {
        total_shipments: number;
        active_shipments: number;
        delivered_shipments: number;
        total_drivers: number;
        drivers_on_trip: number;
        total_vehicles: number;
        vehicles_in_use: number;
        total_clients: number;
        total_droptrips: number;
        completed_droptrips: number;
    };
    recentShipments?: Array<{
        tracking_number: string;
        client_name: string;
        destination: string;
        status: string;
        created_date: string;
    }>;
}

const props = withDefaults(defineProps<Props>(), {
    recentShipments: () => []
});
const currentTime = ref(new Date());
const animatedStats = ref({
    totalShipments: 0,
    activeDeliveries: 0,
    completedToday: 0,
    fleetUtilization: 0
});

// Compute fleet utilization percentage from summary
const fleetUtilizationTarget = computed(() => {
    const total = props.summary?.total_vehicles ?? 0;
    const inUse = props.summary?.vehicles_in_use ?? 0;
    if (total <= 0) return 0;
    return Math.round((inUse / total) * 100);
});

// Animate numbers on mount
onMounted(() => {
    // Update time every second
    setInterval(() => {
        currentTime.value = new Date();
    }, 1000);

    // Animate stats
    const duration = 2000;
    const steps = 60;
    const stepDuration = duration / steps;

    let step = 0;
    const interval = setInterval(() => {
        step++;
        const progress = step / steps;
        const easeOut = 1 - Math.pow(1 - progress, 3);

        animatedStats.value = {
            totalShipments: Math.floor((props.summary?.total_shipments ?? 0) * easeOut),
            activeDeliveries: Math.floor((props.summary?.active_shipments ?? 0) * easeOut),
            completedToday: Math.floor((props.summary?.delivered_shipments ?? 0) * easeOut),
            fleetUtilization: Math.floor(fleetUtilizationTarget.value * easeOut)
        };

        if (step >= steps) {
            clearInterval(interval);
            animatedStats.value = {
                totalShipments: props.summary?.total_shipments ?? 0,
                activeDeliveries: props.summary?.active_shipments ?? 0,
                completedToday: props.summary?.delivered_shipments ?? 0,
                fleetUtilization: fleetUtilizationTarget.value
            };
        }
    }, stepDuration);
});
const formatTime = computed(() => {
    return currentTime.value.toLocaleTimeString('en-US', {
        hour12: true,
        hour: 'numeric',
        minute: '2-digit',
        second: '2-digit'
    });
});

const formatDate = computed(() => {
    return currentTime.value.toLocaleDateString('en-US', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
});

const getStatusColor = (status: string) => {
    const colors = {
        'Delivered': 'bg-green-100 text-green-800 border-green-200 dark:bg-green-900 dark:text-green-100 dark:border-green-800',
        'In Transit': 'bg-blue-100 text-blue-800 border-blue-200 dark:bg-blue-900 dark:text-blue-100 dark:border-blue-800',
        'Processing': 'bg-yellow-100 text-yellow-800 border-yellow-200 dark:bg-yellow-900 dark:text-yellow-100 dark:border-yellow-800',
        'Pending': 'bg-gray-100 text-gray-800 border-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600',
        'Cancelled': 'bg-red-100 text-red-800 border-red-200 dark:bg-red-900 dark:text-red-100 dark:border-red-800'
    };
    return colors[status as keyof typeof colors] || colors['Pending'];
};

const quickActions = [
    {
        name: 'New Shipment',
        icon: 'M12 4v16m8-8H4',
        color: 'from-blue-500 to-blue-600',
        description: 'Create a new shipment'
    },
    {
        name: 'Track Package',
        icon: 'M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z',
        color: 'from-green-500 to-green-600',
        description: 'Search and track packages'
    },
    {
        name: 'Generate Report',
        icon: 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
        color: 'from-purple-500 to-purple-600',
        description: 'Export analytics reports'
    },
    {
        name: 'Manage Fleet',
        icon: 'M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z',
        color: 'from-orange-500 to-orange-600',
        description: 'Monitor fleet status'
    }
];

const recentActivities = [
    { action: 'New shipment created', time: '2 minutes ago', type: 'shipment' },
    { action: 'Package delivered to customer', time: '15 minutes ago', type: 'delivery' },
    { action: 'Route optimization completed', time: '1 hour ago', type: 'system' },
    { action: 'Driver checked in', time: '2 hours ago', type: 'driver' },
    { action: 'Inventory updated', time: '3 hours ago', type: 'inventory' }
];

const getActivityIcon = (type: string) => {
    const icons = {
        shipment: 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10',
        delivery: 'M5 13l4 4L19 7',
        system: 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z',
        driver: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
        inventory: 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10'
    };
    return icons[type as keyof typeof icons] || icons.system;
};
</script>

<template>
    <Head title="Tracking Dashboard" />

    <TrackingLayout>
        <div class="space-y-8">
            <!-- Welcome Header with Time -->
            <div class="bg-gradient-to-r from-blue-600 via-purple-600 to-indigo-600 rounded-2xl p-8 text-white shadow-2xl">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <h1 class="text-4xl font-bold mb-2">Welcome to Tracking System</h1>
                        <p class="text-blue-100 text-lg">{{ formatDate }}</p>
                    </div>
                    <div class="mt-4 lg:mt-0 text-right">
                        <div class="text-3xl font-mono font-bold">{{ formatTime }}</div>
                        <p class="text-blue-100">Real-time Dashboard</p>
                    </div>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Total Shipments -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 border border-gray-100 dark:border-gray-700 hover:scale-105">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Total Shipments</p>
                            <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ animatedStats.totalShipments.toLocaleString() }}</p>
                            <p class="text-sm text-green-600 dark:text-green-400 mt-2 flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                </svg>
                                +12% from last month
                            </p>
                        </div>
                        <div class="w-16 h-16 bg-gradient-to-r from-blue-500 to-blue-600 rounded-2xl flex items-center justify-center shadow-lg">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Active Deliveries -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 border border-gray-100 dark:border-gray-700 hover:scale-105">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Active Shipments</p>
                            <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ animatedStats.activeDeliveries }}</p>
                            <p class="text-sm text-blue-600 dark:text-blue-400 mt-2 flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                In real-time
                            </p>
                        </div>
                        <div class="w-16 h-16 bg-gradient-to-r from-green-500 to-green-600 rounded-2xl flex items-center justify-center shadow-lg">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Completed Today -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 border border-gray-100 dark:border-gray-700 hover:scale-105">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Delivered Shipments</p>
                            <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ animatedStats.completedToday }}</p>
                            <p class="text-sm text-green-600 dark:text-green-400 mt-2 flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                +8% vs yesterday
                            </p>
                        </div>
                        <div class="w-16 h-16 bg-gradient-to-r from-purple-500 to-purple-600 rounded-2xl flex items-center justify-center shadow-lg">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Fleet Utilization -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 border border-gray-100 dark:border-gray-700 hover:scale-105">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Fleet Utilization</p>
                            <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ animatedStats.fleetUtilization }}%</p>
                            <div class="mt-2">
                                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                    <div
                                        class="bg-gradient-to-r from-orange-500 to-orange-600 h-2 rounded-full transition-all duration-1000"
                                        :style="{ width: `${animatedStats.fleetUtilization}%` }"
                                    ></div>
                                </div>
                            </div>
                        </div>
                        <div class="w-16 h-16 bg-gradient-to-r from-orange-500 to-orange-600 rounded-2xl flex items-center justify-center shadow-lg">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Recent Shipments -->
                <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700">
                    <div class="p-6 border-b border-gray-100 dark:border-gray-700">
                        <div class="flex items-center justify-between">
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white">Recent Shipments</h3>
                            <button class="text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 font-medium text-sm hover:bg-blue-50 dark:hover:bg-blue-900 px-3 py-1 rounded-lg transition-colors duration-200">
                                View All
                            </button>
                        </div>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                            <div
                                v-for="(shipment, index) in recentShipments"
                                :key="shipment.tracking_number"
                                class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors duration-200"
                                :style="{ animationDelay: `${index * 100}ms` }"
                            >
                                <div class="flex items-center space-x-4">
                                    <div class="w-12 h-12 bg-gradient-to-r from-blue-500 to-indigo-500 rounded-xl flex items-center justify-center text-white font-bold">
                                        {{ shipment.tracking_number.slice(-2) }}
                                    </div>
                                    <div>
                                        <p class="font-semibold text-gray-900 dark:text-white">{{ shipment.tracking_number }}</p>
                                        <p class="text-sm text-gray-600 dark:text-gray-300">{{ shipment.client_name }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ shipment.destination }}</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <span
                                        :class="['inline-flex items-center px-3 py-1 rounded-full text-xs font-medium border', getStatusColor(shipment.status)]"
                                    >
                                        {{ shipment.status }}
                                    </span>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ shipment.created_date }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions & Activity Feed -->
                <div class="space-y-6">
                    <!-- Quick Actions -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700">
                        <div class="p-6 border-b border-gray-100 dark:border-gray-700">
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white">Quick Actions</h3>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-2 gap-4">
                                <button
                                    v-for="action in quickActions"
                                    :key="action.name"
                                    class="group p-4 bg-gray-50 dark:bg-gray-700 rounded-xl hover:bg-gradient-to-r hover:text-white transition-all duration-300 hover:scale-105 hover:shadow-lg"
                                    :class="`hover:${action.color}`"
                                >
                                    <div class="flex flex-col items-center text-center space-y-2">
                                        <div class="w-10 h-10 bg-white dark:bg-gray-600 rounded-lg flex items-center justify-center group-hover:bg-white group-hover:bg-opacity-20 transition-colors duration-300">
                                            <svg class="w-5 h-5 text-gray-600 dark:text-gray-300 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="action.icon"></path>
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="font-semibold text-sm text-gray-900 dark:text-white group-hover:text-white">{{ action.name }}</p>
                                            <p class="text-xs opacity-75 text-gray-500 dark:text-gray-400 group-hover:text-white">{{ action.description }}</p>
                                        </div>
                                    </div>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Activity -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700">
                        <div class="p-6 border-b border-gray-100 dark:border-gray-700">
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white">Recent Activity</h3>
                        </div>
                        <div class="p-6">
                            <div class="space-y-4">
                                <div
                                    v-for="(activity, index) in recentActivities"
                                    :key="index"
                                    class="flex items-start space-x-3"
                                >
                                    <div class="w-8 h-8 bg-gradient-to-r from-gray-400 to-gray-500 rounded-full flex items-center justify-center flex-shrink-0">
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="getActivityIcon(activity.type)"></path>
                                        </svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ activity.action }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ activity.time }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Performance Chart Placeholder -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700">
                <div class="p-6 border-b border-gray-100 dark:border-gray-700">
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white">Performance Overview</h3>
                </div>
                <div class="p-6">
                    <div class="h-64 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900 dark:to-indigo-900 rounded-xl flex items-center justify-center">
                        <div class="text-center">
                            <svg class="w-16 h-16 text-blue-400 dark:text-blue-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                            <p class="text-lg font-semibold text-gray-700 dark:text-gray-200">Interactive Charts Coming Soon</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Advanced analytics and performance metrics</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </TrackingLayout>
</template>

<style scoped>
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>
