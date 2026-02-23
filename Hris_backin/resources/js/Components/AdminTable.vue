<template>
  <div class="bg-white dark:bg-slate-900 rounded-lg shadow-sm border border-gray-100 dark:border-gray-800">
    <div class="p-4">
      <!-- Search and Filter Section -->
      <div class="flex flex-col sm:flex-row gap-4 mb-4 justify-between items-center">
        <div class="flex flex-col md:flex-row gap-2 w-full sm:w-auto">
          <!-- Optional Input -->
          <div class="flex gap-1 relative w-full sm:w-32" v-if="showOptionalInput">
             <FormControl
                v-model="optionalInputValue"
                :placeholder="optionalInputPlaceholder"
                input-class="pr-8 h-9 text-sm"
             />
             <button
                class="absolute right-2 top-1/2 transform -translate-y-1/2 text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300"
                @click="$emit('data-retrieve-item', optionalInputValue)"
                type="button"
             >
                <BaseIcon :path="mdiPencil" size="16" />
             </button>
          </div>

          <!-- Search Input -->
          <div class="relative w-full sm:w-64">
            <FormControl
              v-model="searchQuery"
              :placeholder="searchPlaceholder"
              @keyup.enter="handleSearch"
              :icon="mdiMagnify"
              input-class="h-9 text-sm"
            />
             <button
                v-if="searchQuery"
                class="absolute right-2 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300"
                @click="clearSearch"
                type="button"
              >
                <BaseIcon :path="mdiClose" size="18" />
             </button>
          </div>
        </div>
        
        <!-- Action Button -->
        <BaseButton 
          v-if="btnLabel" 
          :label="btnLabel"
          :color="btnColor"
          :icon="btnIcon"
          small
          @click="$emit('add-item')" 
        />
      </div>

      <!-- Desktop Table View -->
      <div class="hidden md:block overflow-x-auto">
        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
          <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
            <tr>
              <th 
                v-for="column in processedColumns" 
                :key="column.key"
                scope="col" 
                class="px-6 py-3 transition-colors"
                :class="{ 'cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600': column.sortable }"
                @click="column.sortable ? handleSort(column.key) : null"
              >
                <div class="flex items-center gap-1">
                  {{ column.label }}
                  <span v-if="column.sortable && sortBy === column.key">
                    <BaseIcon :path="sortingOrder === 'asc' ? mdiArrowUp : mdiArrowDown" size="14" />
                  </span>
                </div>
              </th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="loading">
                <td :colspan="columns.length" class="px-6 py-8 text-center">
                    <div class="flex justify-center items-center">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-gray-900 dark:border-white"></div>
                        <span class="ml-2">Loading...</span>
                    </div>
                </td>
            </tr>
            <tr v-else-if="paginatedItems.length === 0">
                <td :colspan="columns.length" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                    {{ noDataText }}
                </td>
            </tr>
            <tr 
                v-else
                v-for="(row, index) in paginatedItems" 
                :key="index"
                class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 cursor-pointer transition-colors"
                @click="handleRowClick(row)"
            >
              <td v-for="column in columns" :key="column.key" class="px-6 py-4">
                 <slot :name="`cell(${column.key})`" :rowData="row">
                  {{ row[column.key] }}
                </slot>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Mobile Table View -->
      <div class="md:hidden">
        <div v-if="loading" class="text-center py-8">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-gray-900 dark:border-white mx-auto"></div>
            <span class="mt-2 block">Loading...</span>
        </div>
        <div v-else-if="paginatedItems.length === 0" class="text-center py-8 text-gray-500">
            {{ noDataText }}
        </div>
        <MobileTableCard
          v-else
          :items="paginatedItems"
          :columns="processedColumns"
          :clickable-columns="clickableColumns"
          @row-click="handleRowClick"
        >
          <template v-for="column in columns" :key="column.key" #[`mobile-cell(${column.key})`]="{ item }">
            <slot :name="`mobile-cell(${column.key})`" :item="item">
              {{ item[column.key] }}
            </slot>
          </template>
        </MobileTableCard>
      </div>

      <!-- Pagination Controls -->
      <div class="flex flex-col sm:flex-row justify-between items-center mt-4 gap-4 border-t border-gray-100 dark:border-gray-800 pt-4">
        <!-- Pagination Info -->
        <div class="text-sm text-gray-600 dark:text-gray-400">
          Showing {{ startItem }} to {{ endItem }} of {{ totalItems }} entries
          <span v-if="isFiltered">(filtered from {{ originalTotal }} total entries)</span>
        </div>

        <!-- Pagination Controls -->
        <div class="flex items-center gap-2">
          <BaseButton
            :icon="mdiChevronLeft"
            small
            color="contrast"
            outline
            :disabled="currentPage === 1"
            @click="goToPage(currentPage - 1)"
          />
          
          <div class="flex items-center gap-1 hidden sm:flex">
             <button 
                v-for="page in visiblePages" 
                :key="page"
                class="w-8 h-8 flex items-center justify-center rounded text-sm transition-colors"
                :class="page === currentPage ? 'bg-blue-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600'"
                @click="goToPage(page)"
                type="button"
             >
                {{ page }}
             </button>
          </div>
          <div class="sm:hidden flex items-center">
            <span class="text-sm text-gray-700 dark:text-gray-300">Page {{ currentPage }} of {{ totalPages }}</span>
          </div>
          
          <BaseButton
            :icon="mdiChevronRight"
            small
            color="contrast"
            outline
            :disabled="currentPage === totalPages"
            @click="goToPage(currentPage + 1)"
          />
        </div>

        <!-- Per Page Selector -->
        <div class="flex items-center gap-2">
          <span class="text-sm text-gray-600 dark:text-gray-400">Per page:</span>
           <select 
            v-model="perPage" 
            @change="handlePerPageChange"
            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-20 p-2 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white"
           >
            <option v-for="opt in perPageOptions" :key="opt.value" :value="opt.value">{{ opt.text }}</option>
           </select>
        </div>
      </div>

      <!-- Export Button -->
      <div class="flex justify-end mt-4" v-if="showExport">
        <BaseButton 
            label="Export CSV" 
            color="info" 
            small 
            :icon="mdiDownload" 
            @click="exportToCSV" 
        />
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, computed, ref, watch, PropType } from 'vue'
import MobileTableCard from './MobileTableCard.vue'
import BaseButton from '@/Components/BaseButton.vue'
import FormControl from '@/Components/FormControl.vue'
import BaseIcon from '@/Components/BaseIcon.vue'
import { mdiMagnify, mdiClose, mdiPencil, mdiDownload, mdiChevronLeft, mdiChevronRight, mdiArrowUp, mdiArrowDown } from '@mdi/js'

interface Column {
  key: string
  label: string
  sortable?: boolean
  clickable?: boolean
  numeric?: boolean
  width?: string
}

interface PaginationData {
  current_page: number
  last_page: number
  per_page: number
  total: number
  data?: any[]
}

export default defineComponent({
  name: 'AdminTable',
  components: {
    MobileTableCard,
    BaseButton,
    FormControl,
    BaseIcon
  },
  props: {
    columns: {
      type: Array as PropType<Column[]>,
      required: true,
    },
    items: {
      type: [Array, Object] as PropType<any[] | PaginationData>,
      required: true,
    },
    loading: {
      type: Boolean,
      default: false,
    },
    btnLabel: {
      type: String,
      default: null,
    },
    btnIcon: {
      type: String,
      default: null,
    },
    btnColor: {
      type: String,
      default: 'info',
    },
    searchPlaceholder: {
      type: String,
      default: 'Search...',
    },
    noDataText: {
      type: String,
      default: 'No data available',
    },
    showOptionalInput: {
      type: Boolean,
      default: false,
    },
    optionalInputPlaceholder: {
      type: String,
      default: 'Enter value...',
    },
    clickableColumns: {
      type: Array as PropType<string[]>,
      default: () => [],
    },
    showExport: {
      type: Boolean,
      default: true,
    },
    serverSide: {
      type: Boolean,
      default: false,
    },
  },
  emits: [
    'add-item',
    'row-click',
    'data-retrieve-item',
    'search',
    'pagination-change',
    'sort-change',
  ],
  setup(props, { emit }) {
    // Reactive data
    const searchQuery = ref('')
    const optionalInputValue = ref('')
    const sortBy = ref('')
    const sortingOrder = ref<'asc' | 'desc' | null>(null)
    const currentPage = ref(1)
    const perPage = ref(10)

    // Per page options
    const perPageOptions = [
      { text: '10', value: 10 },
      { text: '25', value: 25 },
      { text: '50', value: 50 },
      { text: '100', value: 100 },
    ]

    // Process columns to add sorting capability
    const processedColumns = computed(() => {
      return props.columns.map(column => ({
        ...column,
        sortable: column.sortable !== false, // Default to sortable unless explicitly disabled
      }))
    })

    // Handle different data structures (array vs Laravel pagination)
    const processedItems = computed(() => {
      if (Array.isArray(props.items)) {
        return props.items
      } else if (props.items && typeof props.items === 'object' && props.items.data) {
        // Laravel pagination structure
        return props.items.data
      }
      return []
    })

    // Pagination info from Laravel or computed locally
    const paginationInfo = computed(() => {
      if (!Array.isArray(props.items) && props.items && typeof props.items === 'object') {
        return {
          current_page: props.items.current_page || 1,
          last_page: props.items.last_page || 1,
          per_page: props.items.per_page || 10,
          total: props.items.total || 0,
        }
      }
      return null
    })

    // Filter items based on search
    const filteredItems = computed(() => {
      if (!searchQuery.value || props.serverSide) {
        return processedItems.value
      }

      const query = searchQuery.value.toLowerCase()
      return processedItems.value.filter(item => {
        return Object.values(item).some(value => 
          String(value).toLowerCase().includes(query)
        )
      })
    })

    // Sort items
    const sortedItems = computed(() => {
      if (!sortBy.value || !sortingOrder.value || props.serverSide) {
        return filteredItems.value
      }

      const sorted = [...filteredItems.value].sort((a, b) => {
        const aVal = a[sortBy.value]
        const bVal = b[sortBy.value]
        
        if (aVal === bVal) return 0
        
        const result = aVal < bVal ? -1 : 1
        return sortingOrder.value === 'desc' ? -result : result
      })

      return sorted
    })

    // Paginate items (client-side only)
    const paginatedItems = computed(() => {
      if (props.serverSide || paginationInfo.value) {
        return sortedItems.value
      }

      const start = (currentPage.value - 1) * perPage.value
      const end = start + perPage.value
      return sortedItems.value.slice(start, end)
    })

    // Pagination calculations
    const totalItems = computed(() => {
      if (paginationInfo.value) {
        return paginationInfo.value.total
      }
      return filteredItems.value.length
    })

    const originalTotal = computed(() => {
      if (paginationInfo.value) {
        return paginationInfo.value.total
      }
      return processedItems.value.length
    })

    const totalPages = computed(() => {
      if (paginationInfo.value) {
        return paginationInfo.value.last_page
      }
      return Math.ceil(totalItems.value / perPage.value)
    })

    const startItem = computed(() => {
      if (totalItems.value === 0) return 0
      if (paginationInfo.value) {
        return (paginationInfo.value.current_page - 1) * paginationInfo.value.per_page + 1
      }
      return (currentPage.value - 1) * perPage.value + 1
    })

    const endItem = computed(() => {
      if (paginationInfo.value) {
        return Math.min(
          paginationInfo.value.current_page * paginationInfo.value.per_page,
          paginationInfo.value.total
        )
      }
      return Math.min(currentPage.value * perPage.value, totalItems.value)
    })

    const isFiltered = computed(() => {
      return searchQuery.value.length > 0 && totalItems.value !== originalTotal.value
    })
    
    // Visible pages logic
    const visiblePages = computed(() => {
        const pages = [];
        const maxVisible = 5;
        let start = Math.max(1, currentPage.value - Math.floor(maxVisible / 2));
        let end = Math.min(totalPages.value, start + maxVisible - 1);

        if (end - start + 1 < maxVisible) {
            start = Math.max(1, end - maxVisible + 1);
        }

        for (let i = start; i <= end; i++) {
            pages.push(i);
        }
        return pages;
    });

    // Update pagination info from props
    watch(() => paginationInfo.value, (newPagination) => {
      if (newPagination) {
        currentPage.value = newPagination.current_page
        perPage.value = newPagination.per_page
      }
    }, { immediate: true })

    // Methods
    const handleSearch = () => {
      if (props.serverSide) {
        emit('search', searchQuery.value)
      } else {
        currentPage.value = 1 // Reset to first page when searching
      }
    }

    const clearSearch = () => {
      searchQuery.value = ''
      handleSearch()
    }

    const handleRowClick = (row: any) => {
      emit('row-click', row)
    }

    const goToPage = (page: number) => {
      if (page < 1 || page > totalPages.value) return;
      
      currentPage.value = page
      if (props.serverSide) {
        emit('pagination-change', {
          page: currentPage.value,
          perPage: perPage.value,
        })
      }
    }

    const handlePerPageChange = () => {
      // perPage is already updated via v-model
      currentPage.value = 1 // Reset to first page
      if (props.serverSide) {
        emit('pagination-change', {
          page: currentPage.value,
          perPage: perPage.value,
        })
      }
    }
    
    const handleSort = (key: string) => {
        if (sortBy.value === key) {
            // Toggle order: asc -> desc -> null (reset)
            if (sortingOrder.value === 'asc') {
                sortingOrder.value = 'desc';
            } else if (sortingOrder.value === 'desc') {
                sortBy.value = '';
                sortingOrder.value = null;
            } else {
                sortingOrder.value = 'asc';
            }
        } else {
            sortBy.value = key;
            sortingOrder.value = 'asc';
        }
        
        if (props.serverSide) {
            emit('sort-change', {
              sortBy: sortBy.value,
              sortingOrder: sortingOrder.value,
            })
        }
    }

    const exportToCSV = () => {
      const csvContent = [
        // Header row
        props.columns.map(col => col.label).join(','),
        // Data rows
        ...processedItems.value.map(item =>
          props.columns.map(col => {
            const value = item[col.key]
            // Escape commas and quotes in CSV
            return typeof value === 'string' && (value.includes(',') || value.includes('"'))
              ? `"${value.replace(/"/g, '""')}"`
              : value
          }).join(',')
        )
      ].join('\n')

      const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' })
      const link = document.createElement('a')
      const url = URL.createObjectURL(blob)
      link.setAttribute('href', url)
      link.setAttribute('download', 'table-data.csv')
      link.style.visibility = 'hidden'
      document.body.appendChild(link)
      link.click()
      document.body.removeChild(link)
    }

    return {
      searchQuery,
      optionalInputValue,
      sortBy,
      sortingOrder,
      currentPage,
      perPage,
      perPageOptions,
      processedColumns,
      paginatedItems,
      totalItems,
      originalTotal,
      totalPages,
      startItem,
      endItem,
      isFiltered,
      visiblePages,
      handleSearch,
      clearSearch,
      handleRowClick,
      goToPage,
      handlePerPageChange,
      handleSort,
      exportToCSV,
      mdiMagnify, 
      mdiClose, 
      mdiPencil, 
      mdiDownload, 
      mdiChevronLeft, 
      mdiChevronRight, 
      mdiArrowUp, 
      mdiArrowDown
    }
  },
})
</script>

<style scoped>
/* Scoped styles can be minimal as we rely on Tailwind */
</style>
