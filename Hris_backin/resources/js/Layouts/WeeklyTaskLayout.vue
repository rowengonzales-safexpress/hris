<script setup lang="ts">
import { ref, computed, onMounted } from "vue";
import { usePage } from "@inertiajs/vue3";
import SessionIdle from '@/Components/SessionIdle.vue'
import AdminPanelSidebar from "@/Components/AdminPanelSidebar.vue";
import AdminPanelNavbar from "@/Components/AdminPanelNavbar.vue";

const sidebarOpen = ref(false);
const sidebarCollapsed = ref(false);
const page = usePage();

const toggleSidebar = () => {
    sidebarCollapsed.value = !sidebarCollapsed.value;
};

const toggleMobileSidebar = () => {
    sidebarOpen.value = !sidebarOpen.value;
};

const closeSidebar = () => {
    sidebarOpen.value = false;
};

onMounted(() => {
    const sidebar = document.querySelector(".admin-sidebar");
    if (sidebar) {
        sidebar.classList.add("animate-slide-in");
    }
});
</script>

<template>
    <div class="min-h-screen bg-backgroundPrimary dark:bg-backgroundPrimary">
        <SessionIdle :lifetime="Number(page.props.session?.lifetime ?? 15)" :warning-seconds="30" />
        <!-- Sidebar Component -->
        <AdminPanelSidebar
            :sidebar-open="sidebarOpen"
            :sidebar-collapsed="sidebarCollapsed"
            :title="'Daily Task'"
            :subtitle="'Daily Task Management'"
            :home-route="route('weekly-task-schedule.index')"
            @toggle-sidebar="toggleSidebar"
            @close-sidebar="closeSidebar"
            class="admin-sidebar"
        />

        <!-- Main Content -->
        <div
            :class="[
                'transition-all duration-300 ease-in-out',
                sidebarCollapsed ? 'lg:ml-20' : 'lg:ml-72',
            ]"
        >
            <!-- Navbar Component -->
            <AdminPanelNavbar
                :sidebar-collapsed="sidebarCollapsed"
                :home-route="route('weekly-task-schedule.index')"
                @toggle-mobile-sidebar="toggleMobileSidebar"
                @toggle-collapse="toggleSidebar"
            />

            <!-- Page Content -->
            <main class="flex-1 p-6 overflow-auto">
                <slot />
            </main>
        </div>
    </div>
</template>

<style scoped>
@keyframes slide-in {
    from {
        transform: translateX(-100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

.animate-slide-in {
    animation: slide-in 0.3s ease-out;
}
</style>
