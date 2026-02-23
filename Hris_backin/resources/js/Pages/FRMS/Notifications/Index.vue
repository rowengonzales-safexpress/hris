<template>
  <FRMSLayout>
    <div class="max-w-7xl mx-auto">
      <!-- Header -->
      <div class="mb-6">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Notifications</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">
              Manage your notifications and stay updated
            </p>
          </div>
          <div class="flex items-center space-x-3">
            <button
              v-if="unreadCount > 0"
              @click="markAllAsRead"
              :disabled="isMarkingAllRead"
              class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
            >
              <span v-if="isMarkingAllRead">Marking...</span>
              <span v-else>Mark All Read ({{ unreadCount }})</span>
            </button>
            <button
              @click="refreshNotifications"
              :disabled="isLoading"
              class="p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors"
            >
              <svg class="w-5 h-5" :class="{ 'animate-spin': isLoading }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
              </svg>
            </button>
          </div>
        </div>
      </div>

      <!-- Filters -->
      <div class="mb-6 flex flex-wrap items-center gap-4">
        <div class="flex items-center space-x-2">
          <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Filter:</label>
          <select
            v-model="selectedFilter"
            @change="applyFilter"
            class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
          >
            <option value="all">All Notifications</option>
            <option value="unread">Unread Only</option>
            <option value="read">Read Only</option>
          </select>
        </div>
        <div class="flex items-center space-x-2">
          <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Type:</label>
          <select
            v-model="selectedType"
            @change="applyFilter"
            class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
          >
            <option value="">All Types</option>
            <option value="info">Info</option>
            <option value="success">Success</option>
            <option value="warning">Warning</option>
            <option value="error">Error</option>
          </select>
        </div>
      </div>

      <!-- Notifications List -->
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
        <div v-if="isLoading && notifications.data.length === 0" class="p-8 text-center">
          <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
          <p class="text-gray-500 mt-2">Loading notifications...</p>
        </div>

        <div v-else-if="notifications.data.length === 0" class="p-8 text-center">
          <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM10.5 3.75a6 6 0 0 1 6 6v2.25a2.25 2.25 0 0 0 2.25 2.25H21a.75.75 0 0 1 0 1.5H3a.75.75 0 0 1 0-1.5h2.25A2.25 2.25 0 0 0 7.5 12V9.75a6 6 0 0 1 6-6Z" />
          </svg>
          <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No notifications</h3>
          <p class="text-gray-500 dark:text-gray-400">You're all caught up! No notifications to display.</p>
        </div>

        <div v-else class="divide-y divide-gray-200 dark:divide-gray-700">
          <div
            v-for="notification in notifications.data"
            :key="notification.id"
            :class="[
              'p-6 hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer transition-colors',
              !notification.is_read ? 'bg-blue-50 dark:bg-blue-900/20 border-l-4 border-blue-500' : ''
            ]"
            @click="handleNotificationClick(notification)"
          >
            <div class="flex items-start space-x-4">
              <!-- Notification Icon -->
              <div :class="[
                'flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center',
                getNotificationIconClass(notification.type)
              ]">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                  <path v-if="notification.type === 'success'" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"/>
                  <path v-else-if="notification.type === 'warning'" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"/>
                  <path v-else-if="notification.type === 'error'" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"/>
                  <path v-else d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"/>
                </svg>
              </div>

              <!-- Notification Content -->
              <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between">
                  <h3 class="text-sm font-medium text-gray-900 dark:text-white">
                    {{ notification.title }}
                  </h3>
                  <div class="flex items-center space-x-2">
                    <span :class="[
                      'px-2 py-1 text-xs font-medium rounded-full',
                      getNotificationTypeClass(notification.type)
                    ]">
                      {{ notification.type.charAt(0).toUpperCase() + notification.type.slice(1) }}
                    </span>
                    <div v-if="!notification.is_read" class="w-2 h-2 bg-blue-500 rounded-full"></div>
                  </div>
                </div>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                  {{ notification.message }}
                </p>
                <div class="flex items-center justify-between mt-3">
                  <p class="text-xs text-gray-500 dark:text-gray-400">
                    {{ formatDateTime(notification.created_at) }}
                  </p>
                  <div class="flex items-center space-x-2">
                    <button
                      v-if="!notification.is_read"
                      @click.stop="markAsRead(notification)"
                      class="text-xs text-blue-600 hover:text-blue-800 font-medium"
                    >
                      Mark as read
                    </button>
                    <button
                      @click.stop="deleteNotification(notification)"
                      class="text-xs text-red-600 hover:text-red-800 font-medium"
                    >
                      Delete
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Pagination -->
        <div v-if="notifications.data.length > 0" class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
          <div class="flex items-center justify-between">
            <div class="text-sm text-gray-700 dark:text-gray-300">
              Showing {{ notifications.from }} to {{ notifications.to }} of {{ notifications.total }} notifications
            </div>
            <div class="flex items-center space-x-2">
              <button
                v-for="link in notifications.links"
                :key="link.label"
                @click="changePage(link.url)"
                :disabled="!link.url"
                :class="[
                  'px-3 py-2 text-sm rounded-lg transition-colors',
                  link.active
                    ? 'bg-blue-600 text-white'
                    : link.url
                      ? 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-600'
                      : 'text-gray-400 cursor-not-allowed'
                ]"
                v-html="link.label"
              ></button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div v-if="showModal && selectedNotification" class="fixed inset-0 z-50">
      <div class="fixed inset-0 bg-black/40" @click="closeModal"></div>
      <div class="relative mx-auto my-10 max-w-2xl bg-white dark:bg-gray-800 rounded-lg shadow-xl border border-gray-200 dark:border-gray-700">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
          <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ selectedNotification?.title }}</h3>
            <button class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200" @click="closeModal">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
          </div>
          <div class="mt-2 text-xs text-gray-500 dark:text-gray-400">{{ formatDateTime(selectedNotification?.created_at || '') }}</div>
        </div>
        <div class="px-6 py-5 space-y-4">
          <div class="text-sm text-gray-700 dark:text-gray-300">{{ selectedNotification?.message }}</div>
          <div v-if="selectedNotification?.data && Object.keys(selectedNotification.data).length" class="bg-gray-50 dark:bg-gray-900/40 border border-gray-200 dark:border-gray-700 rounded-lg p-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
              <div v-for="(value, key) in selectedNotification?.data" :key="key" class="flex items-center justify-between">
                <div class="text-xs text-gray-500 dark:text-gray-400 capitalize">{{ key.replace('_',' ') }}</div>
                <div class="text-sm font-medium text-gray-900 dark:text-white" v-if="!(Array.isArray(value) || typeof value === 'object')">{{ value }}</div>
              </div>
            </div>
          </div>
          <div v-if="previewImages.length" class="space-y-3">
            <div class="text-sm font-medium text-gray-900 dark:text-white">Attachments</div>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
              <img v-for="(src, i) in previewImages" :key="i" :src="src" class="w-full h-32 object-cover rounded-md border border-gray-200 dark:border-gray-700" />
            </div>
          </div>
        </div>
        <div class="px-6 pb-6">
          <BaseButtons>
            <BaseButton :label="selectedNotification?.is_read ? 'Read' : 'Mark as Read'" color="info" @click="selectedNotification && markAsRead(selectedNotification)" />
            <BaseButton label="Delete" color="danger" @click="selectedNotification && deleteNotification(selectedNotification)" />
         
          </BaseButtons>
        </div>
      </div>
    </div>
  </FRMSLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import FRMSLayout from '@/Layouts/FRMSLayout.vue';
import BaseButton from '@/Components/BaseButton.vue'
import BaseButtons from '@/Components/BaseButtons.vue'
import axios from 'axios';

interface Notification {
  id: number;
  title: string;
  message: string;
  type: string;
  is_read: boolean;
  created_at: string;
  action_url?: string;
}

interface NotificationResponse {
  data: Notification[];
  current_page: number;
  from: number;
  to: number;
  total: number;
  links: Array<{
    url: string | null;
    label: string;
    active: boolean;
  }>;
}

// Props from Inertia
const props = defineProps<{
  notifications: NotificationResponse;
}>();

// State
const notifications = ref<NotificationResponse>(props.notifications);
const selectedNotification = ref<Notification | null>(null);
const showModal = ref(false);
const isLoading = ref(false);
const isMarkingAllRead = ref(false);
const selectedFilter = ref('all');
const selectedType = ref('');

// Computed
const unreadCount = computed(() => {
  if (!notifications.value?.data) return 0;
  return notifications.value.data.filter((n: Notification) => !n.is_read).length;
});

// Methods
const refreshNotifications = async () => {
  isLoading.value = true;
  try {
    router.reload({ only: ['notifications'] });
  } finally {
    isLoading.value = false;
  }
};

const markAllAsRead = async () => {
  isMarkingAllRead.value = true;
  try {
    await axios.patch('/frls/notifications/mark-all-as-read');
    notifications.value.data.forEach(n => n.is_read = true);
  } catch (error) {
    console.error('Error marking all as read:', error);
  } finally {
    isMarkingAllRead.value = false;
  }
};

const markAsRead = async (notification: Notification) => {
  if (notification.is_read) return;

  try {
    await axios.patch(`/frls/notifications/${notification.id}/mark-as-read`);
    notification.is_read = true;
  } catch (error) {
    console.error('Error marking as read:', error);
  }
};

const deleteNotification = async (notification: Notification) => {
  if (!confirm('Are you sure you want to delete this notification?')) return;

  try {
    await axios.delete(`/frls/notifications/${notification.id}`);
    const index = notifications.value.data.findIndex(n => n.id === notification.id);
    if (index > -1) {
      notifications.value.data.splice(index, 1);
    }
    closeModal();
  } catch (error) {
    console.error('Error deleting notification:', error);
  }
};

const handleNotificationClick = async (notification: Notification) => {
  if (!notification.is_read) {
    await markAsRead(notification);
  }
  selectedNotification.value = notification;
  showModal.value = true;
};

const closeModal = () => {
  showModal.value = false;
  selectedNotification.value = null;
};

const applyFilter = () => {
  const params = new URLSearchParams();
  if (selectedFilter.value !== 'all') {
    params.append('filter', selectedFilter.value);
  }
  if (selectedType.value) {
    params.append('type', selectedType.value);
  }

  const url = params.toString() ? `/frls/notifications?${params.toString()}` : '/frls/notifications';
  router.visit(url);
};

const changePage = (url: string | null) => {
  if (url) {
    router.visit(url);
  }
};

const getNotificationIconClass = (type: string) => {
  const classes = {
    success: 'bg-green-100 text-green-600',
    warning: 'bg-yellow-100 text-yellow-600',
    error: 'bg-red-100 text-red-600',
    info: 'bg-blue-100 text-blue-600'
  };
  return classes[type as keyof typeof classes] || classes.info;
};

const getNotificationTypeClass = (type: string) => {
  const classes = {
    success: 'bg-green-100 text-green-800',
    warning: 'bg-yellow-100 text-yellow-800',
    error: 'bg-red-100 text-red-800',
    info: 'bg-blue-100 text-blue-800'
  };
  return classes[type as keyof typeof classes] || classes.info;
};

const formatDateTime = (dateString: string) => {
  const date = new Date(dateString);
  return date.toLocaleString();
};

const previewImages = computed<string[]>(() => {
  const d: any = selectedNotification.value?.data || {};
  const images: string[] = [];
  if (typeof d?.image_url === 'string') images.push(d.image_url);
  if (typeof d?.document_url === 'string') images.push(d.document_url);
  if (Array.isArray(d?.images)) images.push(...d.images);
  if (Array.isArray(d?.files)) images.push(...d.files);
  return images.filter((u) => typeof u === 'string');
});
</script>
