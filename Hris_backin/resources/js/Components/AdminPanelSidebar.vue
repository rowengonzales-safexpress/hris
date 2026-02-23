<script setup lang="ts">
import { ref, computed } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import AppMenu from '@/Components/AppMenu.vue';

interface Props {
  sidebarOpen: boolean;
  sidebarCollapsed: boolean;
  title?: string;
  subtitle?: string;
  homeRoute?: string;
}

interface Emits {
  (e: 'toggle-sidebar'): void;
  (e: 'close-sidebar'): void;
}

const props = defineProps<Props>();
const emit = defineEmits<Emits>();

const page = usePage();
const user = computed(() => page.props.auth?.user);
const userMenus = computed(() => page.props.userMenus || []);

const toggleSidebar = () => {
  emit('toggle-sidebar');
};

const closeSidebar = () => {
  emit('close-sidebar');
};

const logout = () => {
  router.post(route('logout'));
};
</script>

<template>
  <!-- Mobile sidebar overlay -->
  <div v-show="sidebarOpen" class="fixed inset-0 z-40 lg:hidden">
    <div
      class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity"
      @click="closeSidebar"
    ></div>
  </div>

  <!-- Desktop Sidebar -->
  <div
    :class="[
      'fixed inset-y-0 left-0 z-50 transition-all duration-300 ease-in-out overflow-hidden flex flex-col',
      sidebarCollapsed ? 'w-20' : 'w-72',
      'bg-slate-900 text-white shadow-xl border-r border-slate-800',
      'hidden lg:flex'
    ]"
  >
    <!-- Sidebar Header -->
    <div
      class="flex items-center h-16 px-6 border-b border-slate-800 bg-slate-900 shrink-0"
    >
      <Link
        :href="props.homeRoute || route('admin.dashboard')"
        class="flex items-center gap-3 w-full"
      >
        <div class="flex-shrink-0">
          <div
            class="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center shadow-lg shadow-blue-600/20"
          >
            <span class="text-white font-bold text-xl">
              {{ (props.title || 'S').charAt(0) }}
            </span>
          </div>
        </div>
        <div
          v-show="!sidebarCollapsed"
          class="flex flex-col transition-opacity duration-200 overflow-hidden"
        >
          <span class="text-lg font-bold text-white leading-none">{{ props.title || 'SafExpress' }}</span>
          <span class="text-xs text-slate-400 mt-1 truncate dark:text-slate-400">
            {{ props.subtitle || 'Admin Panel' }}
          </span>
        </div>
      </Link>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 px-4 py-6 overflow-y-auto space-y-1 custom-scrollbar">
      <AppMenu :menus="userMenus" :collapsed="sidebarCollapsed" />
    </nav>

    <!-- Sidebar Footer -->
    <div class="p-4 border-t border-slate-800 bg-slate-900 shrink-0">

      <!-- Sign Out Button -->
      <!-- <button
        @click="logout"
        class="w-full flex items-center gap-3 px-3 py-2.5 mb-4 text-red-400 hover:bg-red-400/10 hover:text-red-300 rounded-lg transition-colors group"
        :class="{ 'justify-center': sidebarCollapsed }"
      >
        <svg
          xmlns="http://www.w3.org/2000/svg"
          viewBox="0 0 24 24"
          fill="currentColor"
          class="w-5 h-5 transition-transform group-hover:-translate-x-1"
        >
          <path d="M16 17v-3H9v-4h7V7l5 5-5 5M14 2a2 2 0 0 1 2 2v2h-2V4H5v16h9v-2h2v2a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9Z"/>
        </svg>
        <span v-show="!sidebarCollapsed" class="font-medium text-sm">Sign out</span>
      </button> -->

      <!-- User Profile -->
      <div
        class="flex items-center gap-3 p-3 rounded-xl bg-slate-800/50 border border-slate-800"
        :class="{ 'justify-center p-2': sidebarCollapsed }"
      >
        <div class="flex-shrink-0 relative">
          <div
            class="w-10 h-10 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-full flex items-center justify-center text-white font-bold text-sm shadow-inner"
          >
            {{ user?.name?.charAt(0).toUpperCase() || "A" }}
          </div>
          <div class="absolute bottom-0 right-0 w-2.5 h-2.5 bg-green-500 border-2 border-slate-900 rounded-full"></div>
        </div>
        <div v-show="!sidebarCollapsed" class="flex-1 min-w-0 overflow-hidden">
          <p class="text-sm font-semibold text-white truncate">
            {{ user?.name || "Admin User" }}
          </p>
          <p class="text-xs text-slate-400 truncate">
            {{ user?.email || "admin@example.com" }}
          </p>
        </div>
      </div>
    </div>
  </div>

  <!-- Mobile sidebar -->
  <div
    :class="[
      'fixed inset-y-0 left-0 z-50 w-72 bg-slate-900 shadow-2xl transform transition-transform duration-300 ease-in-out lg:hidden flex flex-col',
      sidebarOpen ? 'translate-x-0' : '-translate-x-full',
    ]"
  >
    <!-- Mobile Header -->
    <div
      class="flex items-center justify-between h-16 px-6 border-b border-slate-800 bg-slate-900 shrink-0"
    >
      <Link
        :href="route('admin.dashboard')"
        class="flex items-center gap-3"
      >
        <div
          class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center shadow-lg"
        >
           <span class="text-white font-bold text-lg">
              {{ (props.title || 'S').charAt(0) }}
            </span>
        </div>
        <span class="text-lg font-bold text-white">{{ props.title || 'SafExpress' }}</span>
      </Link>
      <button
        @click="closeSidebar"
        class="p-2 rounded-lg text-slate-400 hover:bg-slate-800 hover:text-white transition-colors"
      >
        <svg
          class="w-6 h-6"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M6 18L18 6M6 6l12 12"
          ></path>
        </svg>
      </button>
    </div>

    <!-- Mobile Navigation -->
    <nav class="flex-1 px-4 py-6 overflow-y-auto space-y-1 custom-scrollbar">
      <AppMenu :menus="userMenus" @menu-click="closeSidebar" />
    </nav>

    <!-- Mobile Footer -->
    <div class="p-4 border-t border-slate-800 bg-slate-900 shrink-0">
       <!-- <button
        @click="logout"
        class="w-full flex items-center gap-3 px-3 py-2.5 mb-4 text-red-400 hover:bg-red-400/10 hover:text-red-300 rounded-lg transition-colors"
      >
        <svg
          xmlns="http://www.w3.org/2000/svg"
          viewBox="0 0 24 24"
          fill="currentColor"
          class="w-5 h-5"
        >
          <path d="M16 17v-3H9v-4h7V7l5 5-5 5M14 2a2 2 0 0 1 2 2v2h-2V4H5v16h9v-2h2v2a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9Z"/>
        </svg>
        <span class="font-medium text-sm">Sign out</span>
      </button> -->

      <div class="flex items-center gap-3 p-3 rounded-xl bg-slate-800/50 border border-slate-800">
        <div class="flex-shrink-0 relative">
          <div
            class="w-10 h-10 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-full flex items-center justify-center text-white font-bold text-sm shadow-inner"
          >
            {{ user?.name?.charAt(0).toUpperCase() || "A" }}
          </div>
           <div class="absolute bottom-0 right-0 w-2.5 h-2.5 bg-green-500 border-2 border-slate-900 rounded-full"></div>
        </div>
        <div class="flex-1 min-w-0">
          <p class="text-sm font-semibold text-white truncate">
            {{ user?.name || "Admin User" }}
          </p>
          <p class="text-xs text-slate-400 truncate">
            {{ user?.email || "admin@example.com" }}
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.custom-scrollbar {
  scrollbar-width: thin;
  scrollbar-color: #728198 #0f172a;
}

.custom-scrollbar::-webkit-scrollbar {
  width: 10px;
}

.custom-scrollbar::-webkit-scrollbar-track {
  background: #0f172a;
  border-radius: 12px;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
  background: linear-gradient(180deg, #7b8a9f, #5c6676);
  border-radius: 12px;
  border: 2px solid #0f172a;
  min-height: 36px;
  box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.06);
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: linear-gradient(180deg, #8a97aa, #6a7483);
}

.custom-scrollbar::-webkit-scrollbar-corner {
  background: transparent;
}
</style>
