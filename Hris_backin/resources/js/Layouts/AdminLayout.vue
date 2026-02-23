<script setup >
import { ref, computed, onMounted } from "vue";
import { mdiForwardburger, mdiBackburger, mdiMenu } from '@mdi/js'
import { usePage } from "@inertiajs/vue3";
import AdminPanelSidebar from "@/Components/AdminPanelSidebar.vue";
import AdminPanelNavbar from "@/Components/AdminPanelNavbar.vue";
import Footer from "@/Components/Footer.vue";
const sidebarOpen = ref(false);
const sidebarCollapsed = ref(false);
const page = usePage();

const user = computed(() => page.props.auth?.user);
const userMenus = computed(() => page.props.userMenus || []);

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
    // Add some initial animations
    const sidebar = document.querySelector(".admin-sidebar");
    if (sidebar) {
        sidebar.classList.add("animate-slide-in");
    }
});
</script>

<template>
    <div class="min-h-screen bg-gray-100 dark:bg-gray-900 font-sans text-gray-900 dark:text-gray-100">
        <!-- Sidebar Component -->
        <AdminPanelSidebar
            :sidebar-open="sidebarOpen"
            :sidebar-collapsed="sidebarCollapsed"
            :title="'SLI'"
            :subtitle="'SLI management System'"
            @toggle-sidebar="toggleSidebar"
            @close-sidebar="closeSidebar"
            class="admin-sidebar"
        />

        <!-- Main Content -->
        <div
            :class="[
                'transition-all duration-300 ease-in-out min-h-screen flex flex-col',
                sidebarCollapsed ? 'lg:ml-20' : 'lg:ml-72',
            ]"
        >
            <!-- Navbar Component -->
            <AdminPanelNavbar
                :sidebar-collapsed="sidebarCollapsed"
                @toggle-mobile-sidebar="toggleMobileSidebar"
                @toggle-collapse="toggleSidebar"
            />

            <!-- Page Content -->

            <main class="flex-1 px-4 sm:px-6 lg:px-8 py-4 overflow-x-hidden">
                <div class="w-full">
                    <slot />
                </div>
            </main>

            <!-- Footer  -->
             <Footer/>

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
