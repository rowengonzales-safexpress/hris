<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';

interface Props {
    usersSummary: {
        total: number;
        active: number;
        inactive: number;
        admins: number;
        users: number;
    };
    recentUsers: Array<{
        id: number;
        name: string;
        email: string;
        user_type: string;
        status: string;
        created_at: string;
    }>;
}

const props = withDefaults(defineProps<Props>(), {
    usersSummary: () => ({ total: 0, active: 0, inactive: 0, admins: 0, users: 0 }),
    recentUsers: () => []
});

const currentTime = ref(new Date());
const animatedStats = ref({
    totalUsers: 0,
    activeUsers: 0,
    adminUsers: 0,
    standardUsers: 0
});

// Animate numbers on mount
onMounted(() => {
    // Update time every second
    setInterval(() => {
        currentTime.value = new Date();
    }, 1000);

    // Animate stats based on core_users
    const duration = 2000;
    const steps = 60;
    const stepDuration = duration / steps;

    let step = 0;
    const interval = setInterval(() => {
        step++;
        const progress = step / steps;
        const easeOut = 1 - Math.pow(1 - progress, 3);

        animatedStats.value = {
            totalUsers: Math.floor(props.usersSummary.total * easeOut),
            activeUsers: Math.floor(props.usersSummary.active * easeOut),
            adminUsers: Math.floor(props.usersSummary.admins * easeOut),
            standardUsers: Math.floor(props.usersSummary.users * easeOut)
        };

        if (step >= steps) {
            clearInterval(interval);
            animatedStats.value = {
                totalUsers: props.usersSummary.total,
                activeUsers: props.usersSummary.active,
                adminUsers: props.usersSummary.admins,
                standardUsers: props.usersSummary.users
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

const getUserStatusColor = (status: string) => {
    const map: Record<string, string> = {
        A: 'bg-green-100 text-green-800 border-green-200 dark:bg-green-900 dark:text-green-100 dark:border-green-800',
        I: 'bg-gray-100 text-gray-800 border-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600',
        C: 'bg-red-100 text-red-800 border-red-200 dark:bg-red-900 dark:text-red-100 dark:border-red-800',
        P: 'bg-yellow-100 text-yellow-800 border-yellow-200 dark:bg-yellow-900 dark:text-yellow-100 dark:border-yellow-800',
    };
    return map[status] ?? map.I;
};
</script>

<template>
    <Head title="SLI Admin Dashboard" />

    <AdminLayout>
        <div class="space-y-8 mt-5">
 

            <!-- Users Stats -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Total Users -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 border border-gray-100 dark:border-gray-700 hover:scale-105">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Total Users</p>
                            <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ animatedStats.totalUsers.toLocaleString() }}</p>
                            <p class="text-sm text-green-600 dark:text-green-400 mt-2 flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                </svg>
                                Users registered
                            </p>
                        </div>
                        <div class="w-16 h-16 bg-gradient-to-r from-blue-500 to-blue-600 rounded-2xl flex items-center justify-center shadow-lg">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Active Users -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 border border-gray-100 dark:border-gray-700 hover:scale-105">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Active Users</p>
                            <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ animatedStats.activeUsers }}</p>
                            <p class="text-sm text-blue-600 dark:text-blue-400 mt-2 flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Enabled accounts
                            </p>
                        </div>
                        <div class="w-16 h-16 bg-gradient-to-r from-green-500 to-green-600 rounded-2xl flex items-center justify-center shadow-lg">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Admin Users -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 border border-gray-100 dark:border-gray-700 hover:scale-105">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Admin Users</p>
                            <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ animatedStats.adminUsers }}</p>
                            <p class="text-sm text-green-600 dark:text-green-400 mt-2 flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Elevated privileges
                            </p>
                        </div>
                        <div class="w-16 h-16 bg-gradient-to-r from-purple-500 to-purple-600 rounded-2xl flex items-center justify-center shadow-lg">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Standard Users -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 border border-gray-100 dark:border-gray-700 hover:scale-105">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Standard Users</p>
                            <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ animatedStats.standardUsers }}</p>
                            <div class="mt-2 text-gray-600 dark:text-gray-400 text-sm">Regular accounts</div>
                        </div>
                        <div class="w-16 h-16 bg-gradient-to-r from-orange-500 to-orange-600 rounded-2xl flex items-center justify-center shadow-lg">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V7a2 2 0 00-2-2H6a2 2 0 00-2 2v6m16 0v4a2 2 0 01-2 2H6a2 2 0 01-2-2v-4m16 0H4"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="grid grid-cols-1 gap-8">
                <!-- Recent Users -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700">
                    <div class="p-6 border-b border-gray-100 dark:border-gray-700">
                        <div class="flex items-center justify-between">
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white">Recent Users</h3>
                            <button class="text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 font-medium text-sm hover:bg-blue-50 dark:hover:bg-blue-900 px-3 py-1 rounded-lg transition-colors duration-200">
                                Manage Users
                            </button>
                        </div>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                            <div
                                v-for="(user, index) in recentUsers"
                                :key="user.id"
                                class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors duration-200"
                                :style="{ animationDelay: `${index * 100}ms` }"
                            >
                                <div class="flex items-center space-x-4">
                                    <div class="w-12 h-12 bg-gradient-to-r from-blue-500 to-indigo-500 rounded-xl flex items-center justify-center text-white font-bold">
                                        {{ user.name ? user.name.charAt(0).toUpperCase() : 'U' }}
                                    </div>
                                    <div>
                                        <p class="font-semibold text-gray-900 dark:text-white">{{ user.name }}</p>
                                        <p class="text-sm text-gray-600 dark:text-gray-300">{{ user.email }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ user.user_type }}</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <span
                                        :class="['inline-flex items-center px-3 py-1 rounded-full text-xs font-medium border', getUserStatusColor(user.status)]"
                                    >
                                        {{ user.status === 'A' ? 'Active' : (user.status === 'I' ? 'Inactive' : (user.status === 'C' ? 'Cancelled' : 'Pending')) }}
                                    </span>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ new Date(user.created_at).toLocaleDateString() }}</p>
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
    </AdminLayout>
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

.space-y-4 > * {
    animation: fadeInUp 0.6s ease-out forwards;
}

/* Hover effects for cards */
.hover\:scale-105:hover {
    transform: scale(1.05);
}

/* Gradient text */
.bg-clip-text {
    -webkit-background-clip: text;
    background-clip: text;
}

/* Custom scrollbar */
::-webkit-scrollbar {
    width: 6px;
}
</style>
