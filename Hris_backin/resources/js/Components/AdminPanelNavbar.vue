<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useForm, usePage, router } from '@inertiajs/vue3';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import { useGlobalStore } from '@/store/global-store'
import axios from 'axios';
import { mdiWarehouse } from '@mdi/js';
import Modal from '@/Components/Modal.vue'
import FormField from '@/Components/FormField.vue'
import FormControl from '@/Components/FormControl.vue'
import BaseButton from '@/Components/BaseButton.vue'
import Breadcrumb from '@/Components/Admin/Breadcrumb.vue'
import BaseIcon from '@/Components/BaseIcon.vue'

interface Props {
  sidebarCollapsed: boolean;
  homeRoute?: string;
}

interface Emits {
  (e: 'toggle-mobile-sidebar'): void;
  (e: 'toggle-collapse'): void;
}

interface Notification {
  id: number;
  title: string;
  message: string;
  type: string;
  is_read: boolean;
  created_at: string;
  action_url?: string;
}

const props = defineProps<Props>();
const emit = defineEmits<Emits>();

const page = usePage();
const user = computed(() => page.props.auth?.user);
const branchName = computed(() => page.props.branchName || 'Main Branch');
const userMenus = computed(() => page.props.userMenus || []);

// Theme toggle
const globalStore = useGlobalStore()
const isDark = computed(() => globalStore.currentTheme === 'dark')
const toggleTheme = () => {
  globalStore.toggleDarkMode()
}

const toggleMobileSidebar = () => {
  emit('toggle-mobile-sidebar');
};

const toggleCollapse = () => {
  emit('toggle-collapse')
}

// Notification state
const notifications = ref([]);
const unreadCount = ref(0);
const isLoading = ref(false);
const showModal = ref(false);
const selectedNotification = ref(null);

// Change Warehouse modal state
const showWarehouseModal = ref(false)
const warehouseOptions = ref<any[]>([])
const selectedBranch = ref<any | null>(null)
const savingWarehouse = ref(false)
const branchForm = useForm({ branch_id: null })

// Get current app ID based on route
const getCurrentAppId = () => {
  const currentRoute = route().current();

  if (currentRoute && currentRoute.startsWith('frls.')) {
    return 4; // FRMS app_id
  } else if (currentRoute && currentRoute.startsWith('admin.')) {
    return 1; // Admin app_id
  } else if (currentRoute && currentRoute.startsWith('hris.')) {
    return 2; // HRIS app_id
  } else if (currentRoute && currentRoute.startsWith('tracking.')) {
    return 3; // Tracking app_id
   } else if (currentRoute && currentRoute.startsWith('weekly-task-schedule.')) {
    return 6; // Dailytask app_id
  }

  return null; // No specific app context
};

// Fetch notifications
const fetchNotifications = async () => {
  try {
    isLoading.value = true;
    const appId = getCurrentAppId();
    const url = appId ? `/notifications/recent?app_id=${appId}` : '/notifications/recent';
    const response = await axios.get(url);
    notifications.value = response.data.notifications;
  } catch (error) {
    console.error('Error fetching notifications:', error);
  } finally {
    isLoading.value = false;
  }
};

// Fetch unread count
const fetchUnreadCount = async () => {
  try {
    const appId = getCurrentAppId();
    const url = appId ? `/notifications/unread-count?app_id=${appId}` : '/notifications/unread-count';
    const response = await axios.get(url);
    unreadCount.value = response.data.count;
  } catch (error) {
    console.error('Error fetching unread count:', error);
  }
};

// Mark all notifications as read
const markAllAsRead = async () => {
  try {
    const appId = getCurrentAppId();
    const url = appId ? `/notifications/mark-all-as-read?app_id=${appId}` : '/notifications/mark-all-as-read';
    await axios.patch(url);
    notifications.value.forEach(n => n.is_read = true);
    unreadCount.value = 0;
  } catch (error) {
    console.error('Error marking all as read:', error);
  }
};

// Handle notification click
const handleNotificationClick = async (notification: Notification) => {
  try {
    // Mark as read if not already read
    if (!notification.is_read) {
      await axios.patch(`/notifications/${notification.id}/mark-as-read`);
      notification.is_read = true;
      unreadCount.value = Math.max(0, unreadCount.value - 1);
    }

    // Show modal with notification details
    selectedNotification.value = notification;
    showModal.value = true;
  } catch (error) {
    console.error('Error handling notification click:', error);
  }
};

// Show notification modal (legacy function for compatibility)
const showNotificationModal = (notification: Notification) => {
  selectedNotification.value = notification;
  showModal.value = true;
};

const openWarehouseModal = async () => {
  try {
    const resp = await axios.get(route('core.branches.my'))
    warehouseOptions.value = (resp.data?.data || []).map((b: any) => ({ id: b.id, label: `${b.branch_name} (${b.branch_code})` }))
    const currentId = (page.props.auth?.user?.branch_id ?? null) as number | null
    selectedBranch.value = warehouseOptions.value.find((o:any) => o.id === currentId) ?? null
  } catch (e) {
    console.error('Failed to load branches', e)
  } finally {
    showWarehouseModal.value = true
  }
}

const saveWarehouse = async () => {
  if (!selectedBranch.value?.id) return
  savingWarehouse.value = true
  branchForm.branch_id = selectedBranch.value.id
  branchForm.patch(route('core.users.me.branch'), {
    preserveScroll: true,
    onSuccess: () => {
      showWarehouseModal.value = false
    },
    onFinish: () => {
      savingWarehouse.value = false
      router.reload()
    },
    onError: () => {
      savingWarehouse.value = false
    }
  })
}

// Close modal
const closeModal = () => {
  showModal.value = false;
  selectedNotification.value = null;
};

// Search modal state
const showSearchModal = ref(false);
const searchQuery = ref('');

const getProperLink = (route: string) => {
  if (!route) return '#';
  if (route.startsWith('http')) return route;
  const cleanRoute = route.startsWith('/') ? route.substring(1) : route;
  return '/' + cleanRoute;
};

const flatMenus = computed(() => {
  const list: Array<{ id: number; name: string; route: string; icon?: string; group?: string }> = [];
  (userMenus.value || []).forEach((m: any) => {
    if (m?.submenus && Array.isArray(m.submenus) && m.submenus.length > 0) {
      m.submenus.forEach((s: any) => {
        list.push({
          id: s.id,
          name: s.name,
          route: s.route,
          icon: s.icon || m.icon,
          group: m.name
        });
      });
    } else {
      list.push({
        id: m.id,
        name: m.name,
        route: m.route,
        icon: m.icon
      });
    }
  });
  return list;
});

const filteredResults = computed(() => {
  const q = (searchQuery.value || '').trim().toLowerCase();
  if (!q) return flatMenus.value.slice(0, 5);
  return flatMenus.value
    .filter(item =>
      (item.name || '').toLowerCase().includes(q) ||
      (item.group || '').toLowerCase().includes(q)
    )
    .slice(0, 5);
});

const openSearchModal = () => {
  searchQuery.value = '';
  showSearchModal.value = true;
};

const closeSearch = () => {
  showSearchModal.value = false;
};

const goToRoute = (routePath: string) => {
  const link = getProperLink(routePath || '');
  if (link && link !== '#') {
    router.visit(link);
    closeSearch();
  }
};

const handleGlobalShortcut = (e: KeyboardEvent) => {
  if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
    e.preventDefault();
    openSearchModal();
  }
};

// Navigate to all notifications page based on current module
const viewAllNotifications = () => {
  const currentRoute = route().current();

  // Determine the module based on current route
  if (currentRoute && currentRoute.startsWith('frls.')) {
    router.visit('/frls/notifications');
  } else if (currentRoute && currentRoute.startsWith('admin.')) {
    router.visit('/admin/notifications');
  } else if (currentRoute && currentRoute.startsWith('hris.')) {
    router.visit('/hris/notifications');
  } else if (currentRoute && currentRoute.startsWith('tracking.')) {
    router.visit('/tracking/notifications');
  } else {
    // Default fallback to general notifications
    router.visit('/notifications');
  }
};

// Format time ago
const formatTimeAgo = (dateString: string) => {
  const date = new Date(dateString);
  const now = new Date();
  const diffInSeconds = Math.floor((now.getTime() - date.getTime()) / 1000);

  if (diffInSeconds < 60) return 'Just now';
  if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)} min ago`;
  if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)} hour ago`;
  return `${Math.floor(diffInSeconds / 86400)} day ago`;
};

const getStatusBadgeClass = (status: string) => {
  const s = (status || '').toLowerCase();
  const map: Record<string, string> = {
    'fa': 'bg-yellow-100 text-yellow-800',
    'for approved': 'bg-yellow-100 text-yellow-800',
    'fd': 'bg-purple-100 text-purple-800',
    'for disbursement': 'bg-purple-100 text-purple-800',
    'fl': 'bg-blue-100 text-blue-800',
    'for liquidation': 'bg-blue-100 text-blue-800',
    'a': 'bg-green-100 text-green-800',
    'approved': 'bg-green-100 text-green-800',
    'c': 'bg-gray-100 text-gray-800',
    'closed': 'bg-gray-100 text-gray-800',
    'x': 'bg-red-100 text-red-800',
    'canceled': 'bg-red-100 text-red-800'
  };
  return map[s] || 'bg-gray-100 text-gray-800';
};

// Initialize notifications on component mount
onMounted(() => {
  fetchNotifications();
  fetchUnreadCount();

  // Set up periodic refresh for notifications
  const interval = setInterval(() => {
    fetchUnreadCount();
  }, 30000); // Refresh every 30 seconds

  // Cleanup interval on unmount
  return () => clearInterval(interval);
});

onMounted(() => {
  window.addEventListener('keydown', handleGlobalShortcut);
});

onUnmounted(() => {
  window.removeEventListener('keydown', handleGlobalShortcut);
});
</script>

<template>
  <header class="bg-white dark:bg-gray-900 shadow-sm border-b border-gray-100 dark:border-gray-800 sticky top-0 z-30">
    <div
      :class="[
        'flex items-center justify-between h-16 px-4 sm:px-6 lg:px-8 transition-all duration-300',
      ]"
    >
      <!-- Left side -->
      <div class="flex items-center">
        <!-- Mobile hamburger menu -->
        <button
          @click="toggleMobileSidebar"
          class="lg:hidden p-2 rounded-lg text-gray-500 hover:text-gray-700 hover:bg-gray-100 transition-colors duration-200"
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
              d="M4 6h16M4 12h16M4 18h16"
            ></path>
          </svg>
        </button>

        <!-- Sidebar toggle for desktop -->
          <button
            @click="toggleCollapse"
            class="hidden lg:flex p-2   rounded-lg text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800"
          >
            <svg
              class="w-5 h-5"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M4 6h16M4 12h8m-8 6h16"
              />
            </svg>
          </button>
           <!-- Search trigger -->
        <button
          @click="openSearchModal"
          class="hidden md:flex items-center gap-3 px-3 py-2 rounded-lg bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors"
          title="Search (Ctrl/Cmd + K)"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
          </svg>
          <span class="text-sm">Search...</span>
          <span class="ml-2 px-2 py-0.5 text-xs rounded border border-gray-300 dark:border-gray-600 text-gray-500 dark:text-gray-400">Ctrl K</span>
        </button>

      </div>

      <!-- Right side -->
      <div class="flex items-center space-x-4">

           <!-- Welcome message (desktop only) -->
        <div class="hidden lg:block ml-4">
          <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-400">
             {{ branchName }}
          </h1>
        </div>

        <!-- Mobile title -->
        <div class="lg:hidden ml-4">
          <h1 class="text-lg font-semibold text-gray-900 dark:text-gray-400">
             {{ branchName }}
          </h1>
          <p class="text-sm text-gray-600 mt-1">

          </p>
        </div>
        <!-- Theme toggle -->
        <button
          @click="toggleTheme"
          :title="isDark ? 'Switch to light mode' : 'Switch to dark mode'"
          class="p-2 rounded-lg text-gray-500 hover:text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:text-white dark:hover:bg-gray-800 transition-colors duration-200"
        >
          <svg v-if="!isDark" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v2m0 14v2m9-9h-2M5 12H3m15.364 6.364l-1.414-1.414M7.05 7.05L5.636 5.636m12.728 0l-1.414 1.414M7.05 16.95l-1.414 1.414M12 8a4 4 0 100 8 4 4 0 000-8z" />
          </svg>
          <svg v-else class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12.79A9 9 0 1111.21 3a7 7 0 009.79 9.79z" />
          </svg>
        </button>



        <!-- Notifications -->
        <Dropdown align="right" width="80">
          <template #trigger>
            <button
              class="relative p-2 rounded-lg text-gray-500 hover:text-gray-700 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-gray-200 dark:hover:bg-gray-700 transition-colors duration-200"
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
                  d="M15 17h5l-5 5v-5zM10.5 3.75a6 6 0 0 1 6 6v2.25a2.25 2.25 0 0 0 2.25 2.25H21a.75.75 0 0 1 0 1.5H3a.75.75 0 0 1 0-1.5h2.25A2.25 2.25 0 0 0 7.5 12V9.75a6 6 0 0 1 6-6Z"
                />
              </svg>
              <div
                v-if="unreadCount > 0"
                class="absolute -top-1 -right-1 w-5 h-5 bg-red-500 rounded-full flex items-center justify-center text-xs text-white font-medium animate-pulse"
              >
                {{ unreadCount > 99 ? '99+' : unreadCount }}
              </div>
            </button>
          </template>
          <template #content>
            <div class="py-2">
              <div class="px-4 py-2 border-b border-gray-100 dark:border-gray-800">
                <div class="flex items-center justify-between">
                  <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Notifications</h3>
                  <button
                    v-if="unreadCount > 0"
                    @click="markAllAsRead"
                    class="text-xs text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300"
                  >
                    Mark all read
                  </button>
                </div>
              </div>
              <div class="max-h-80 overflow-y-auto notification-list">
                <div v-if="isLoading" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                  <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500 mx-auto mb-2"></div>
                  Loading notifications...
                </div>
                <div v-else-if="notifications.length === 0" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                  <svg class="w-12 h-12 mx-auto mb-2 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM10.5 3.75a6 6 0 0 1 6 6v2.25a2.25 2.25 0 0 0 2.25 2.25H21a.75.75 0 0 1 0 1.5H3a.75.75 0 0 1 0-1.5h2.25A2.25 2.25 0 0 0 7.5 12V9.75a6 6 0 0 1 6-6Z" />
                  </svg>
                  <p class="text-sm dark:text-gray-200">No notifications yet</p>
                  <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">You'll see notifications here when they arrive</p>
                </div>
                <div
                  v-else
                  v-for="notification in notifications"
                  :key="notification.id"
                  :class="[
                    'px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-800 cursor-pointer border-l-4 transition-colors relative notification-card',
                    !notification.is_read ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20' : 'border-transparent'
                  ]"
                  @click="handleNotificationClick(notification)"
                >
                  <div class="flex items-start">
                    <div class="flex-shrink-0 mr-3">
                      <div :class="[
                        'w-8 h-8 rounded-full flex items-center justify-center text-white text-sm',
                        notification.type === 'form_created' ? 'bg-green-500' :
                        notification.type === 'form_status_changed' ? 'bg-blue-500' :
                        notification.type === 'document_uploaded' ? 'bg-purple-500' :
                        notification.type === 'disbursement_created' ? 'bg-orange-500' :
                        'bg-gray-500'
                      ]">
                        <svg v-if="notification.type === 'form_created'" class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <svg v-else-if="notification.type === 'form_status_changed'" class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                          <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd" />
                        </svg>
                        <svg v-else-if="notification.type === 'document_uploaded'" class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                          <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                        <svg v-else-if="notification.type === 'disbursement_created'" class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                          <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z" />
                          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd" />
                        </svg>
                        <svg v-else class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                          <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                        </svg>
                      </div>
                    </div>
                    <div class="flex-1 min-w-0">
                      <div class="flex items-center justify-between">
                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">
                          {{ notification.title }}
                        </p>
                        <div
                          v-if="!notification.is_read"
                          class="w-2 h-2 bg-blue-500 rounded-full flex-shrink-0"
                        ></div>
                      </div>
                      <p class="text-xs text-gray-600 dark:text-gray-400 mt-1 line-clamp-2" v-if="notification.message">
                        {{ notification.message.length > 60 ? notification.message.substring(0, 60) + '...' : notification.message }}
                      </p>
                      <div class="flex items-center justify-between mt-2">
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                          {{ formatTimeAgo(notification.created_at) }}
                        </p>
                        <span :class="[
                          'px-2 py-1 text-xs rounded-full font-medium',
                          notification.type === 'form_created' ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300' :
                          notification.type === 'form_status_changed' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300' :
                          notification.type === 'document_uploaded' ? 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300' :
                          notification.type === 'disbursement_created' ? 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-300' :
                          'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300'
                        ]">
                          {{ notification.type.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase()) }}
                        </span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="px-4 py-2 border-t border-gray-100 dark:border-gray-800">
                <button
                  @click="viewAllNotifications"
                  class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 w-full text-center"
                >
                  View all notifications
                </button>
              </div>
            </div>
          </template>
        </Dropdown>

        <!-- User Menu -->
        <Dropdown align="right" width="64">
          <template #trigger>
            <button
              class="flex items-center space-x-3 p-2 rounded-lg  transition-colors duration-200"
            >
              <div
                class="w-8 h-8 bg-gradient-to-r from-blue-500 to-indigo-500 rounded-full flex items-center justify-center text-white font-semibold text-sm"
              >
                {{ user?.name?.charAt(0).toUpperCase() || "A" }}
              </div>
              <div class="text-left hidden sm:block">
                <p class="text-sm font-medium text-gray-900 dark:text-white">
                  {{ user?.name || "Admin User" }}
                </p>
                <p class="text-xs text-gray-500 dark:text-gray-300">
                  Administrator
                </p>
              </div>
              <svg
                class="w-4 h-4 text-gray-400 hidden sm:block"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M19 9l-7 7-7-7"
                ></path>
              </svg>
            </button>
          </template>
          <template #content>
            <div class="py-2 dark:bg-gray-900 dark:text-white">


              <DropdownLink :href="route('profile.edit')">
                <div class="flex items-center">
                  <svg class="w-4 h-4 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                  </svg>
                  Your Profile
                </div>
              </DropdownLink>

              <DropdownLink href="#">
                <div class="flex items-center">
                  <svg class="w-4 h-4 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                  </svg>
                  Settings
                </div>
              </DropdownLink>

              <DropdownLink href="#">
                <div class="flex items-center">
                  <svg class="w-4 h-4 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2 7a2 2 0 012-2h16a2 2 0 012 2v2H2V7zm0 6h20v4a2 2 0 01-2 2H4a2 2 0 01-2-2v-4z" />
                  </svg>
                  Billing
                </div>
              </DropdownLink>

              <DropdownLink @click.prevent="openWarehouseModal">
                <div class="flex items-center">
                  <svg class="w-4 h-4 mr-3 text-gray-400" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                    <path :d="mdiWarehouse" />
                  </svg>
                  Change Warehouse
                </div>
              </DropdownLink>

              <div class="border-t border-gray-100 dark:border-gray-800 my-1"></div>

              <DropdownLink
                :href="route('logout')"
                method="post"
                as="button"
              >
                <div class="flex items-center text-red-600">
                  <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                  </svg>
                  Sign out
                </div>
              </DropdownLink>
            </div>
          </template>
        </Dropdown>
      </div>
    </div>
    <div class="border-t border-gray-100 dark:border-gray-800 py-2 bg-gray-50 dark:bg-gray-900">
        <Breadcrumb />
    </div>
  </header>

  <!-- Search Modal -->
  <Modal :show="showSearchModal" maxWidth="xl" @close="closeSearch">
    <div class="p-6">
      <div class="flex items-center gap-3 mb-4">
        <svg class="w-5 h-5 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
        </svg>
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Search pages, actions, and more..."
          class="flex-1 px-3 py-2 rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500"
        />
        <button @click="closeSearch" class="p-2 rounded text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
      </div>
      <div class="space-y-2">
        <div
          v-for="item in filteredResults"
          :key="item.id"
          @click="goToRoute(item.route)"
          class="flex items-center justify-between p-3 rounded-lg cursor-pointer bg-gray-50 dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
        >
          <div class="flex items-center gap-3">
            <BaseIcon v-if="item.icon" :path="item.icon" :size="18" class="text-gray-500 dark:text-gray-300" />
            <div>
              <div class="text-gray-900 dark:text-gray-100 font-medium">{{ item.name }}</div>
              <div v-if="item.group" class="text-xs text-gray-500 dark:text-gray-400">{{ item.group }}</div>
            </div>
          </div>
          <div class="text-xs text-gray-400">{{ item.route }}</div>
        </div>
        <div v-if="filteredResults.length === 0" class="text-sm text-gray-500 dark:text-gray-400 p-3">
          No results
        </div>
      </div>
      <div class="mt-4 flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
        <div class="flex items-center gap-2">
          <span class="px-2 py-0.5 rounded border border-gray-300 dark:border-gray-600">↑↓</span>
          Navigate
          <span class="ml-3 px-2 py-0.5 rounded border border-gray-300 dark:border-gray-600">Enter</span>
          Select
        </div>
        <div class="flex items-center gap-2">
          <span class="px-2 py-0.5 rounded border border-gray-300 dark:border-gray-600">ESC</span>
          Close
        </div>
      </div>
    </div>
  </Modal>

  <!-- Notification Modal -->
  <div
    v-if="showModal && selectedNotification"
    class="fixed inset-0 z-50 overflow-y-auto modal-overlay"
    @click="closeModal"
  >
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
      <!-- Background overlay -->
      <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>

      <!-- Modal panel -->
      <div
        class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full modal-panel"
        @click.stop
      >
        <!-- Modal Header -->
        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
          <div class="flex items-start">
            <div class="flex-shrink-0 mr-4">
              <!-- Notification type icon -->
              <div :class="[
                'w-10 h-10 rounded-full flex items-center justify-center text-white',
                selectedNotification?.type === 'form_created' ? 'bg-green-500' :
                selectedNotification?.type === 'form_status_changed' ? 'bg-blue-500' :
                selectedNotification?.type === 'document_uploaded' ? 'bg-purple-500' :
                selectedNotification?.type === 'disbursement_created' ? 'bg-orange-500' :
                'bg-gray-500'
              ]">
                <svg v-if="selectedNotification?.type === 'form_created'" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <svg v-else-if="selectedNotification?.type === 'form_status_changed'" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd" />
                </svg>
                <svg v-else-if="selectedNotification?.type === 'document_uploaded'" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
                <svg v-else-if="selectedNotification?.type === 'disbursement_created'" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z" />
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd" />
                </svg>
                <svg v-else class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                </svg>
              </div>
            </div>
            <div class="flex-1 min-w-0">
              <div class="flex items-center justify-between">
                <h3 class="text-lg leading-6 font-medium text-gray-900">
                  {{ selectedNotification?.title }}
                </h3>
                <button
                  @click="closeModal"
                  class="ml-4 text-gray-400 hover:text-gray-600 focus:outline-none focus:text-gray-600 transition ease-in-out duration-150"
                >
                  <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                </button>
              </div>
              <div class="mt-2">
                <span :class="[
                  'inline-flex px-2 py-1 text-xs font-semibold rounded-full',
                  selectedNotification?.type === 'form_created' ? 'bg-green-100 text-green-800' :
                  selectedNotification?.type === 'form_status_changed' ? 'bg-blue-100 text-blue-800' :
                  selectedNotification?.type === 'document_uploaded' ? 'bg-purple-100 text-purple-800' :
                  selectedNotification?.type === 'disbursement_created' ? 'bg-orange-100 text-orange-800' :
                  'bg-gray-100 text-gray-800'
                ]">
                  {{ selectedNotification?.type?.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase()) }}
                </span>
                <span class="ml-2 text-sm text-gray-500">
                  {{ selectedNotification?.created_at ? formatTimeAgo(selectedNotification.created_at) : '' }}
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- Modal Content -->
        <div class="bg-gray-50 px-4 py-5 sm:p-6 modal-content">
          <div class="text-sm text-gray-700">
            <p class="mb-4">{{ selectedNotification?.message }}</p>

            <!-- Additional notification data if available -->
            <div v-if="selectedNotification?.data" class="bg-white rounded-lg p-4 border border-gray-200">
              <h4 class="text-sm font-medium text-gray-900 mb-2">Details</h4>
              <div class="space-y-2">
                <div v-for="(value, key) in selectedNotification?.data" :key="key" class="flex justify-between">
                  <span class="text-sm text-gray-600 capitalize">{{ key.replace('_', ' ') }}:</span>
                  <span v-if="key === 'new_status' || key === 'old_status'" :class="['inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium', getStatusBadgeClass(String(value))]">{{ value }}</span>
                  <span v-else class="text-sm font-medium text-gray-900">{{ value }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Modal Actions -->
        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
          <button
            v-if="selectedNotification?.action_url"
            @click="() => { if (selectedNotification?.action_url) { window.location.href = selectedNotification.action_url; closeModal(); } }"
            type="button"
            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm transition-colors duration-200"
          >
            View Details
          </button>
          <button
            @click="closeModal"
            type="button"
            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition-colors duration-200"
          >
            Close
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Change Warehouse Modal -->
  <Modal :show="showWarehouseModal" maxWidth="md" @close="showWarehouseModal=false">
    <div class="p-6">
      <div class="text-lg font-semibold mb-4">Change Warehouse</div>
      <FormField label="Select Warehouse">
        <FormControl v-model="selectedBranch" :options="warehouseOptions" />
      </FormField>
      <div class="mt-6 flex justify-end gap-3">
        <BaseButton label="Cancel" color="whiteDark" @click="showWarehouseModal=false" />
        <BaseButton label="OK" color="info" :disabled="savingWarehouse || !selectedBranch" @click="saveWarehouse" />
      </div>
    </div>
  </Modal>
</template>

<style scoped>
/* Custom animations */
@keyframes pulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: .5;
  }
}

.animate-pulse {
  animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

/* Modal animations */
@keyframes fadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}

@keyframes slideIn {
  from {
    opacity: 0;
    transform: translateY(-10px) scale(0.95);
  }
  to {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}

.modal-overlay {
  animation: fadeIn 0.3s ease-out;
}

.modal-panel {
  animation: slideIn 0.3s ease-out;
}

/* Responsive modal adjustments */
@media (max-width: 640px) {
  .modal-panel {
    margin: 1rem;
    width: calc(100% - 2rem);
  }
}

/* Enhanced notification card hover effects */
.notification-card {
  transition: all 0.2s ease-in-out;
}

.notification-card:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

/* Smooth transitions for modal elements */
.modal-content {
  transition: all 0.2s ease-in-out;
}

/* Custom scrollbar for notification list */
.notification-list::-webkit-scrollbar {
  width: 6px;
}

.notification-list::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 3px;
}

.notification-list::-webkit-scrollbar-thumb {
  background: #c1c1c1;
  border-radius: 3px;
}

.notification-list::-webkit-scrollbar-thumb:hover {
  background: #a8a8a8;
}
</style>
