<template>
  <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <!-- Background overlay -->
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
      <div
        class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
        aria-hidden="true"
        @click="$emit('close')"
      ></div>

      <!-- This element is to trick the browser into centering the modal contents. -->
      <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

      <!-- Modal panel -->
      <div class="relative inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
        <!-- Close button -->
        <div class="absolute top-0 right-0 pt-4 pr-4">
          <button
            type="button"
            class="bg-white dark:bg-gray-800 rounded-md text-gray-400 hover:text-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
            @click="$emit('close')"
          >
            <span class="sr-only">Close</span>
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <!-- Modal content -->
        <div class="sm:flex sm:items-start">
          <!-- Icon -->
          <div :class="[
            'mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full sm:mx-0 sm:h-10 sm:w-10',
            getNotificationIconClass(notification.type)
          ]">
            <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
              <path v-if="notification.type === 'success'" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"/>
              <path v-else-if="notification.type === 'warning'" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"/>
              <path v-else-if="notification.type === 'error'" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"/>
              <path v-else d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"/>
            </svg>
          </div>

          <!-- Content -->
          <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left flex-1">
            <!-- Header -->
            <div class="flex items-center justify-between mb-4">
              <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white" id="modal-title">
                {{ notification.title }}
              </h3>
              <div class="flex items-center space-x-2">
                <span :class="[
                  'px-2 py-1 text-xs font-medium rounded-full',
                  getNotificationTypeClass(notification.type)
                ]">
                  {{ notification.type.charAt(0).toUpperCase() + notification.type.slice(1) }}
                </span>
                <div v-if="!notification.is_read" class="w-2 h-2 bg-blue-500 rounded-full" title="Unread"></div>
              </div>
            </div>

            <!-- Message -->
            <div class="mt-2">
              <p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed">
                {{ notification.message }}
              </p>
            </div>

            <!-- Additional Data -->
            <div v-if="notification.data && Object.keys(notification.data).length > 0" class="mt-4">
              <!-- FRMS List Display -->
              <div v-if="isFrmsList" class="space-y-4">
                <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-3">FRMS Request Details:</h4>

                <!-- FRMS Items List -->
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-lg overflow-hidden">
                  <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 border-b border-gray-200 dark:border-gray-600">
                    <div class="grid grid-cols-3 gap-4 text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                      <div>Description</div>
                      <div class="text-right">Amount</div>
                      <div class="text-right">Status</div>
                    </div>
                  </div>
                  <div class="divide-y divide-gray-200 dark:divide-gray-600">
                    <div
                      v-for="(item, index) in frmsListItems"
                      :key="index"
                      class="px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                    >
                      <div class="grid grid-cols-3 gap-4 items-center">
                        <div class="text-sm text-gray-900 dark:text-white">
                          {{ item.description || 'No description' }}
                        </div>
                        <div class="text-sm text-right font-medium text-gray-900 dark:text-white">
                          ₱{{ formatCurrency(item.amount) }}
                        </div>
                        <div class="text-right">
                          <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                            Active
                          </span>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Total Amount -->
                  <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 border-t border-gray-200 dark:border-gray-600">
                    <div class="flex justify-between items-center">
                      <span class="text-sm font-medium text-gray-900 dark:text-white">Total Amount:</span>
                      <span class="text-lg font-bold text-blue-600 dark:text-blue-400">
                        ₱{{ formatCurrency(totalAmount) }}
                      </span>
                    </div>
                  </div>
                </div>

                <!-- Additional FRMS Information -->
                <div v-if="frmsAdditionalInfo && Object.keys(frmsAdditionalInfo).length > 0" class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                  <h5 class="text-xs font-medium text-gray-700 dark:text-gray-300 mb-2 uppercase tracking-wider">Additional Information</h5>
                  <div class="space-y-2">
                    <div v-for="(value, key) in frmsAdditionalInfo" :key="key" class="flex justify-between items-center">
                      <span class="text-sm text-gray-600 dark:text-gray-400 capitalize">{{ formatKey(key) }}:</span>
                      <span v-if="key === 'new_status' || key === 'old_status'" :class="['inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium', getStatusBadgeClass(String(value))]">{{ value }}</span>
                      <span v-else class="text-sm font-medium text-gray-900 dark:text-white">{{ value }}</span>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Regular Additional Data (non-FRMS) -->
              <div v-else>
                <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-2">Additional Information:</h4>
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3">
                  <pre class="text-xs text-gray-600 dark:text-gray-400 whitespace-pre-wrap">{{ formatData(notification.data) }}</pre>
                </div>
              </div>
            </div>

            <!-- Metadata -->
            <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-600">
              <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                <span>{{ formatDateTime(notification.created_at) }}</span>
                <span v-if="notification.is_read" class="text-green-600 dark:text-green-400">
                  ✓ Read
                </span>
                <span v-else class="text-blue-600 dark:text-blue-400">
                  ● Unread
                </span>
              </div>
            </div>

            <!-- Action URL -->
            <div v-if="notification.action_url" class="mt-4">
              <a
                :href="notification.action_url"
                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-blue-700 bg-blue-100 hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
              >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                </svg>
                View Related Item
              </a>
            </div>
          </div>
        </div>

        <!-- Actions -->
        <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
          <button
            v-if="!notification.is_read"
            type="button"
            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm transition-colors"
            @click="handleMarkAsRead"
          >
            Mark as Read
          </button>

          <button
            type="button"
            class="mt-3 w-full inline-flex justify-center rounded-md border border-red-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-red-700 hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:mt-0 sm:w-auto sm:text-sm transition-colors"
            @click="handleDelete"
          >
            Delete
          </button>

          <button
            type="button"
            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition-colors"
            @click="$emit('close')"
          >
            Close
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';

interface Notification {
  id: number;
  title: string;
  message: string;
  type: string;
  is_read: boolean;
  created_at: string;
  action_url?: string;
  data?: any;
}

const props = defineProps<{
  notification: Notification;
}>();

const emit = defineEmits<{
  close: [];
  'mark-as-read': [notification: Notification];
  delete: [notification: Notification];
}>();

// Computed properties for FRMS list detection and processing
const isFrmsList = computed(() => {
  if (!props.notification.data) return false;

  // Check if data contains FRMS-related fields
  const data = props.notification.data;

  // Check for various FRMS data structures
  return (
    data.frms_list ||
    data.items ||
    data.form?.items ||
    data.finance_disbursement ||
    (Array.isArray(data) && data.some(item => item.description && item.amount)) ||
    (data.description && data.amount) ||
    (data.total_amount !== undefined)
  );
});

const frmsListItems = computed(() => {
  if (!props.notification.data) return [];

  const data = props.notification.data;
  let items = [];

  // Try different data structures
  if (data.frms_list && Array.isArray(data.frms_list)) {
    items = data.frms_list;
  } else if (data.items && Array.isArray(data.items)) {
    items = data.items;
  } else if (data.form?.items && Array.isArray(data.form.items)) {
    items = data.form.items;
  } else if (Array.isArray(data)) {
    items = data.filter(item => item.description && item.amount);
  } else if (data.description && data.amount) {
    // Single item
    items = [data];
  }

  return items.map(item => ({
    description: item.description || item.name || 'No description',
    amount: parseFloat(item.amount || 0)
  }));
});

const totalAmount = computed(() => {
  const data = props.notification.data;

  // Check if total_amount is directly provided
  if (data?.total_amount !== undefined) {
    return parseFloat(data.total_amount);
  }

  // Calculate from items
  return frmsListItems.value.reduce((sum, item) => sum + item.amount, 0);
});

const frmsAdditionalInfo = computed(() => {
  if (!props.notification.data) return {};

  const data = props.notification.data;
  const excludeKeys = ['frms_list', 'items', 'description', 'amount', 'total_amount'];

  const additionalInfo: Record<string, any> = {};

  Object.keys(data).forEach(key => {
    if (!excludeKeys.includes(key) && data[key] !== null && data[key] !== undefined) {
      additionalInfo[key] = data[key];
    }
  });

  return additionalInfo;
});

const handleMarkAsRead = () => {
  emit('mark-as-read', props.notification);
};

const handleDelete = () => {
  if (confirm('Are you sure you want to delete this notification?')) {
    emit('delete', props.notification);
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

const formatData = (data: any) => {
  if (typeof data === 'string') {
    try {
      return JSON.stringify(JSON.parse(data), null, 2);
    } catch {
      return data;
    }
  }
  return JSON.stringify(data, null, 2);
};

const formatCurrency = (amount: number) => {
  return new Intl.NumberFormat('en-PH', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(amount);
};

const formatKey = (key: string) => {
  return key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
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
</script>
