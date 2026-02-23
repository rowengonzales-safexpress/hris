<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import Snowfall from 'vue3-snowfall';

import {ref, computed,onMounted } from "vue";

const applist = ref([]);
const hoveredApp = ref(null);
const props = defineProps({userApps:Array});
const isDecember = new Date().getMonth() === 11;

const userApps = () => {
  const apps = props.userApps || [];
  applist.value = apps;
  return apps;
};

onMounted(() => {
    userApps();
})

</script>

<template>
     <Head title="Safexpress Management System" />



        <!-- Applications Grid -->
        <div class="py-16 bg-gray-100 dark:bg-gray-900 relative overflow-hidden">
            <!-- Background Elements -->
            <div class="absolute inset-0 opacity-5">
                <!-- Warehouse Icons Background -->
                <div class="absolute top-10 left-10">
                    <svg class="w-32 h-32 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"/>
                    </svg>
                </div>
                <div class="absolute top-32 right-20">
                    <svg class="w-24 h-24 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M8 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM15 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z"/>
                        <path d="M3 4a1 1 0 00-1 1v10a1 1 0 001 1h1.05a2.5 2.5 0 014.9 0H10a1 1 0 001-1V4a1 1 0 00-1-1H3zM14 7a1 1 0 00-1 1v6.05A2.5 2.5 0 0115.95 16H17a1 1 0 001-1v-5a1 1 0 00-.293-.707L16 7.586A1 1 0 0015.414 7H14z"/>
                    </svg>
                </div>
                <div class="absolute bottom-20 left-1/4">
                    <svg class="w-28 h-28 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="absolute bottom-10 right-10">
                    <svg class="w-20 h-20 text-indigo-500" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"/>
                    </svg>
                </div>
            </div>

            <div class="mx-auto max-w-7xl px-6 lg:px-8 relative z-10">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
                    <div
                        v-for="app in applist"
                        :key="app.id"
                        class="relative group"
                        @mouseenter="hoveredApp = app.id"
                        @mouseleave="hoveredApp = null"
                    >
                        <Link

                            :href="app.is_active ? app.href : '#'"
                            :class="[
                                'block relative overflow-hidden rounded-2xl shadow-lg transition-all duration-300 ease-in-out transform',
                                app.is_active
                                    ? 'bg-backgroundSecondary hover:shadow-2xl hover:scale-105 hover:-translate-y-2'
                                    : 'bg-backgroundPrimary cursor-not-allowed opacity-60'
                            ]"
                           :target="app.is_active ? '_blank' : '_self'"
                           :rel="app.is_active ? 'noopener noreferrer' : ''"
                        >
                            <!-- Card Header with Icon -->
                            <div class="relative p-6 pb-4">
                                <div class="flex items-center justify-between mb-4">
                                    <div class="flex items-center space-x-3">
                                        <!-- Dynamic Icon based on app type -->
                                        <div :class="[
                                            'w-12 h-12 rounded-xl flex items-center justify-center text-white transition-all duration-300',
                                            app.is_active
                                                ? 'bg-gradient-to-br from-blue-500 to-indigo-600 group-hover:from-blue-600 group-hover:to-indigo-700'
                                                : 'bg-gray-400'
                                        ]">
                                            <!-- Warehouse Icon -->
                                            <svg v-if="app.name.toLowerCase().includes('warehouse') || app.name.toLowerCase().includes('inventory')"
                                                 class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"/>
                                            </svg>
                                            <!-- Truck Icon -->
                                            <svg v-else-if="app.name.toLowerCase().includes('truck') || app.name.toLowerCase().includes('transport') || app.name.toLowerCase().includes('delivery')"
                                                 class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M8 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM15 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z"/>
                                                <path d="M3 4a1 1 0 00-1 1v10a1 1 0 001 1h1.05a2.5 2.5 0 014.9 0H10a1 1 0 001-1V4a1 1 0 00-1-1H3zM14 7a1 1 0 00-1 1v6.05A2.5 2.5 0 0115.95 16H17a1 1 0 001-1v-5a1 1 0 00-.293-.707L16 7.586A1 1 0 0015.414 7H14z"/>
                                            </svg>
                                            <!-- Default App Icon -->
                                            <svg v-else class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M3 5a2 2 0 012-2h10a2 2 0 012 2v8a2 2 0 01-2 2h-2.22l.123.489.804.804A1 1 0 0113 18H7a1 1 0 01-.707-1.707l.804-.804L7.22 15H5a2 2 0 01-2-2V5zm5.771 7H5V5h10v7H8.771z" clip-rule="evenodd"/>
                                            </svg>
                                        </div>
                                        <div v-if="!app.is_active" class="px-2 py-1 bg-red-100 text-red-600 text-xs font-medium rounded-full">
                                            Inactive
                                        </div>
                                        <div v-else class="px-2 py-1 bg-green-100 text-green-600 text-xs font-medium rounded-full">
                                            Active
                                        </div>
                                    </div>
                                </div>

                                <h3 class="text-xl font-bold text-regularLarge mb-2 group-hover:text-blue-600 transition-colors duration-300">
                                    {{ app.name }}
                                </h3>

                                <p class="text-regularMedium text-sm line-clamp-2 leading-relaxed">
                                    {{ app.description }}
                                </p>
                            </div>

                            <!-- Card Footer -->
                            <div class="px-6 pb-6">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-regularMedium font-medium">
                                        Click to access
                                    </span>
                                    <svg class="w-5 h-5 text-regularMedium group-hover:text-blue-500 transition-colors duration-300"
                                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </div>
                            </div>

                            <!-- Hover Overlay -->
                            <div class="absolute inset-0 bg-gradient-to-t from-blue-600/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 rounded-2xl"></div>
                        </Link>


                    </div>
                </div>

                <!-- Empty State -->
                <div v-if="applist.length === 0" class="text-center py-16">
                    <svg class="mx-auto h-24 w-24 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                    <h3 class="mt-6 text-lg font-medium text-gray-900">No applications available</h3>
                    <p class="mt-2 text-gray-500">Contact your administrator to get access to warehouse and trucking applications.</p>
                </div>
            </div>
        </div>
        <Snowfall v-if="isDecember" />

</template>

<style scoped>
@keyframes fade-in {
    from {
        opacity: 0;
        transform: translateY(-10px) translateX(-50%);
    }
    to {
        opacity: 1;
        transform: translateY(0) translateX(-50%);
    }
}

.animate-fade-in {
    animation: fade-in 0.2s ease-out;
}

.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
