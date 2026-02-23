<template>
  <AdminLayout>
    <div class="row justify-between flex gap-2">
            <h1 class="text-2xl font-bold mb-2">{{ action === 'Add' ? 'Add New Menu' : 'Edit Menu' }}</h1>

            <BaseButton
             @click="emit('triggerTopRightButton', 'lists')"
             color="secondary"
             :icon="'mdiArrowLeft'"
             label="Menu Master Lists"
             outline
            />

        </div>
    <div class="bg-white dark:bg-slate-900 rounded-md shadow p-6 mt-3">
      <form @submit.prevent="handleSubmit" ref="formRef">
        <div v-if="formErrors.length" class="mb-4 text-red-600">
          <ul>
            <li v-for="(err, i) in formErrors" :key="i">{{ err }}</li>
          </ul>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <FormField label="Application">
              <FormControl
                v-model="form.app_id"
                :options="appOptions"
                type="select"
                required
              />
            </FormField>
          </div>

          <div>
            <FormField label="Menu Name">
              <FormControl
                v-model="form.name"
                placeholder="Enter menu name"
                required
                :max="100"
              />
            </FormField>
          </div>

          <div>
            <FormField label="Route">
              <FormControl
                v-model="form.route"
                placeholder="Enter route"
                required
              />
            </FormField>
          </div>

          <div>
            <FormField label="Parent Menu">
              <FormControl
                v-model="form.parent_id"
                :options="parentMenuOptions"
                type="select"
              />
            </FormField>
          </div>

          <div>
            <FormField label="Sort Order">
              <FormControl
                v-model="form.sort_order"
                type="number"
                placeholder="Enter sort order"
              />
            </FormField>
          </div>

          <div>
            <FormField label="Icon">
              <FormControl
                v-model="form.icon"
                placeholder="Select or paste SVG path"
              />
              <BaseButton
                color="contrast"
                :icon="'mdiImage'"
                label="Pick Icon"
                small
                class="mt-2"
                @click="showIconModal = true"
              />
            </FormField>
          </div>

          <div class="flex items-center gap-2">
            <Checkbox v-model:checked="form.is_active" />
            <span>Active</span>
          </div>
        </div>

        <div class="mt-6 flex justify-end gap-2">
          <BaseButton
            color="contrast"
            label="Reset"
            outline
            @click="form.reset()"
          />
          <BaseButton
            type="submit"
            :disabled="form.processing"
            color="info"
            :label="action === 'Edit' ? 'Update' : 'Create'"
          />
        </div>
      </form>
    </div>

  <Modal :show="showIconModal" maxWidth="lg" @close="showIconModal = false">
    <div class="flex flex-col gap-4">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
        <FormControl v-model="iconSearch" placeholder="Search icon by name" />
        <FormControl v-model="selectedCategory" :options="categories" placeholder="Category" />
      </div>

      <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 xl:grid-cols-5 gap-4 max-h-[65vh] overflow-auto pr-1">
        <div
          v-for="(icon, idx) in filteredIcons"
          :key="icon.name + idx"
          class="relative p-3 rounded-lg border border-gray-200 hover:border-primary hover:shadow cursor-pointer bg-white"
          @click="selectIcon(icon)"
        >
          <div class="flex flex-col items-center justify-center gap-2">
            <svg viewBox="0 0 24 24" class="w-12 h-12 text-gray-800">
              <path :d="icon.path" />
            </svg>
            <div class="text-xs text-center line-clamp-2">{{ icon.name }}</div>
          </div>

          <div class="absolute top-2 right-2">
            <span v-if="selectedIconPath === icon.path" class="px-2 py-1 text-xs rounded bg-blue-600 text-white">Selected</span>
          </div>
        </div>
      </div>

      <div class="flex justify-end gap-2 mt-2">
        <BaseButton color="contrast" label="Cancel" @click="showIconModal = false" />
        <BaseButton :disabled="!selectedIconPath" color="info" label="OK" @click="confirmIcon" />
      </div>
    </div>
  </Modal>

  </AdminLayout>
</template>

<script setup lang="ts">
import { ref, onMounted, watch, computed } from 'vue'
import { Head, router, useForm, usePage } from '@inertiajs/vue3'
import service from '@/Components/Toast/service'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import FormField from '@/Components/FormField.vue'
import FormControl from '@/Components/FormControl.vue'
import BaseButton from '@/Components/BaseButton.vue'
import Checkbox from '@/Components/Checkbox.vue'
import Modal from '@/Components/Modal.vue'

interface Props {
  action: string
  data?: any
  apps: any[]
  parentMenus: any[]
  formdata: any
  errors?: any
}

const props = defineProps<Props>()
const toast = service()
const page = usePage()
const formRef = ref()
const action = computed(() => props.formdata?.id ? 'Edit' : 'Add')
// Initialize form
const form = useForm({
  _token: page.props.csrf_token,
  id: props.formdata?.id || null,
  app_id: props.formdata?.app_id || null,
  name: props.formdata?.name || '',
  group: props.formdata?.group || '',
  route: props.formdata?.route || '',
  parent_id: props.formdata?.parent_id || null,
  sort_order: props.formdata?.sort_order || 0,
  is_active: props.formdata?.is_active ?? true,
  icon: props.formdata?.icon || '',
})
const formErrors = ref<string[]>([])

const emit = defineEmits(["triggerTopRightButton"]);

// Watch for changes in formdata prop to update form
watch(() => props.formdata, (newData) => {
  if (newData) {
    form.id = newData.id || null
    form.app_id = newData.app_id || null
    form.name = newData.name || ''
    form.group = newData.group || ''
    form.route = newData.route || ''
    form.parent_id = newData.parent_id || null
    form.sort_order = newData.sort_order || 0
    form.is_active = newData.is_active ?? true
    form.icon = newData.icon || ''
  }
}, { immediate: true })

// Icon picker state
const showIconModal = ref(false)
const iconSearch = ref('')
const selectedCategory = ref<string | null>(null)
const selectedIconPath = ref<string | null>(null)

const appOptions = computed(() => (props.apps || []).map((a: any) => ({ id: a.id, label: a.name })))
const parentMenuOptions = computed(() => (props.parentMenus || []).map((m: any) => ({ id: m.id, label: m.name })))

// Icon data (copied from index.php)
interface IconItem { name: string; path: string; category: string }
const icons = ref<IconItem[]>([
  { name: "Dashboard", path: "M13,3V9H21V3M13,21H21V11H13M3,21H11V15H3M3,13H11V3H3V13Z", category: "dashboard" },
  { name: "Analytics", path: "M5,9.5L7.5,14H2.5L5,9.5M3,4H7L8.5,7L10,4H14L15.5,7L17,4H21L18.5,13H15.5L14,10L12.5,13H9.5L8,10L6.5,13H3.5L1,4H3M5,19.5H9V18.5H5V19.5M11,19.5H15V18.5H11V19.5M17,19.5H21V18.5H17V19.5Z", category: "dashboard" },
  { name: "Charts", path: "M3,22V8H7V22H3M10,22V2H14V22H10M17,22V14H21V22H17Z", category: "dashboard" },
  { name: "Graph", path: "M16,11.78L20.24,4.45L21.97,5.45L16.74,14.5L10.23,10.75L5.46,19H22V21H2V3H4V17.54L9.5,8L16,11.78Z", category: "dashboard" },
  { name: "Metrics", path: "M16,11.78L20.24,4.45L21.97,5.45L16.74,14.5L10.23,10.75L5.46,19H22V21H2V3H4V17.54L9.5,8L16,11.78Z", category: "dashboard" },
  { name: "Speedometer", path: "M12,2A10,10 0 0,0 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12A10,10 0 0,0 12,2M13,4.07C16.07,4.45 18.57,6.59 19.54,9.42L13,11.58V4.07M11,4.07V11.58L4.46,9.42C5.43,6.59 7.93,4.45 11,4.07M4,12C4,12.5 4.07,13 4.19,13.46L11,15.58V19.93C7.6,19.72 4.86,16.73 4.05,13H4M13,19.93V15.58L19.81,13.46C19.93,13 20,12.5 20,12H19.95C19.14,16.73 16.4,19.72 13,19.93Z", category: "dashboard" },
  { name: "User Stats", path: "M12,4A4,4 0 0,1 16,8A4,4 0 0,1 12,12A4,4 0 0,1 8,8A4,4 0 0,1 12,4M12,14C16.42,14 20,15.79 20,18V20H4V18C4,15.79 7.58,14 12,14Z", category: "dashboard" },
  { name: "Data Table", path: "M5,4H19A2,2 0 0,1 21,6V18A2,2 0 0,1 19,20H5A2,2 0 0,1 3,18V6A2,2 0 0,1 5,4M5,8V12H11V8H5M13,8V12H19V8H13M5,14V18H11V14H5M13,14V18H19V14H13Z", category: "dashboard" },
  { name: "Reports", path: "M13,9H18.5L13,3.5V9M6,2H14L20,8V20A2,2 0 0,1 18,22H6C4.89,22 4,21.1 4,20V4C4,2.89 4.89,2 6,2M7,20H9V14H7V20M11,20H13V12H11V20M15,20H17V16H15V20Z", category: "dashboard" },
  { name: "Dashboard Layout", path: "M19,5V7H15V5H19M9,5V11H5V5H9M19,13V19H15V13H19M9,17V19H5V17H9M21,3H13V9H21V3M11,3H3V13H11V3M21,11H13V21H21V11M11,15H3V21H11V15Z", category: "dashboard" },
  { name: "Facebook", path: "M12 2.04C6.5 2.04 2 6.53 2 12.06C2 17.06 5.66 21.21 10.44 21.96V14.96H7.9V12.06H10.44V9.85C10.44 7.34 11.93 5.96 14.22 5.96C15.31 5.96 16.45 6.15 16.45 6.15V8.62H15.19C13.95 8.62 13.56 9.39 13.56 10.18V12.06H16.34L15.89 14.96H13.56V21.96A10 10 0 0 0 22 12.06C22 6.53 17.5 2.04 12 2.04Z", category: "social" },
  { name: "Twitter", path: "M22.46,6C21.69,6.35 20.86,6.58 20,6.69C20.88,6.16 21.56,5.32 21.88,4.31C21.05,4.81 20.13,5.16 19.16,5.36C18.37,4.5 17.26,4 16,4C13.65,4 11.73,5.92 11.73,8.29C11.73,8.63 11.77,8.96 11.84,9.27C8.28,9.09 5.11,7.38 3,4.79C2.63,5.42 2.42,6.16 2.42,6.94C2.42,8.43 3.17,9.75 4.33,10.5C3.62,10.5 2.96,10.3 2.38,10C2.38,10 2.38,10 2.38,10.03C2.38,12.11 3.86,13.85 5.82,14.24C5.46,14.34 5.08,14.39 4.69,14.39C4.42,14.39 4.15,14.36 3.89,14.31C4.43,16 6,17.26 7.89,17.29C6.43,18.45 4.58,19.13 2.56,19.13C2.22,19.13 1.88,19.11 1.54,19.07C3.44,20.29 5.7,21 8.12,21C16,21 20.33,14.46 20.33,8.79C20.33,8.6 20.33,8.42 20.32,8.23C21.16,7.63 21.88,6.87 22.46,6Z", category: "social" },
  { name: "Instagram", path: "M7.8,2H16.2C19.4,2 22,4.6 22,7.8V16.2A5.8,5.8 0 0,1 16.2,22H7.8C4.6,22 2,19.4 2,16.2V7.8A5.8,5.8 0 0,1 7.8,2M7.6,4A3.6,3.6 0 0,0 4,7.6V16.4C4,18.39 5.61,20 7.6,20H16.4A3.6,3.6 0 0,0 20,16.4V7.6C20,5.61 18.39,4 16.4,4H7.6M17.25,5.5A1.25,1.25 0 0,1 18.5,6.75A1.25,1.25 0 0,1 17.25,8A1.25,1.25 0 0,1 16,6.75A1.25,1.25 0 0,1 17.25,5.5M12,7A5,5 0 0,1 17,12A5,5 0 0,1 12,17A5,5 0 0,1 7,12A5,5 0 0,1 12,7M12,9A3,3 0 0,0 9,12A3,3 0 0,0 12,15A3,3 0 0,0 15,12A3,3 0 0,0 12,9Z", category: "social" },
  { name: "LinkedIn", path: "M19,3A2,2 0 0,1 21,5V19A2,2 0 0,1 19,21H5A2,2 0 0,1 3,19V5A2,2 0 0,1 5,3H19M18.5,18.5V13.2A3.26,3.26 0 0,0 15.24,9.94C14.39,9.94 13.4,10.46 12.92,11.24V10.13H10.13V18.5H12.92V13.57C12.92,12.8 13.54,12.17 14.31,12.17A1.4,1.4 0 0,1 15.71,13.57V18.5H18.5M6.88,8.56A1.68,1.68 0 0,0 8.56,6.88C8.56,5.95 7.81,5.19 6.88,5.19A1.69,1.69 0 0,0 5.19,6.88C5.19,7.81 5.95,8.56 6.88,8.56M8.27,18.5V10.13H5.5V18.5H8.27Z", category: "social" },
  { name: "YouTube", path: "M10,15L15.19,12L10,9V15M21.56,7.17C21.69,7.64 21.78,8.27 21.84,9.07C21.91,9.87 21.94,10.56 21.94,11.16L22,12C22,14.19 21.84,15.8 21.56,16.83C21.31,17.73 20.73,18.31 19.83,18.56C19.36,18.69 18.5,18.78 17.18,18.84C15.88,18.91 14.69,18.94 13.59,18.94L12,19C7.81,19 5.2,18.84 4.17,18.56C3.27,18.31 2.69,17.73 2.44,16.83C2.31,16.36 2.22,15.73 2.16,14.93C2.09,14.13 2.06,13.44 2.06,12.84L2,12C2,9.81 2.16,8.2 2.44,7.17C2.69,6.27 3.27,5.69 4.17,5.44C4.64,5.31 5.5,5.22 6.82,5.16C8.12,5.09 9.31,5.06 10.41,5.06L12,5C16.19,5 18.8,5.16 19.83,5.44C20.73,5.69 21.31,6.27 21.56,7.17Z", category: "social" },
  { name: "GitHub", path: "M12,2A10,10 0 0,0 2,12C2,16.42 4.87,20.17 8.84,21.5C9.34,21.58 9.5,21.27 9.5,21C9.5,20.77 9.5,20.14 9.5,19.31C6.73,19.91 6.14,17.97 6.14,17.97C5.68,16.81 5.03,16.5 5.03,16.5C4.12,15.88 5.1,15.9 5.1,15.9C6.1,15.97 6.63,16.93 6.63,16.93C7.5,18.45 8.97,18 9.54,17.76C9.63,17.11 9.89,16.67 10.17,16.42C7.95,16.17 5.62,15.31 5.62,11.5C5.62,10.39 6,9.5 6.65,8.79C6.55,8.54 6.2,7.5 6.75,6.15C6.75,6.15 7.59,5.88 9.5,7.17C10.29,6.95 11.15,6.84 12,6.84C12.85,6.84 13.71,6.95 14.5,7.17C16.41,5.88 17.25,6.15 17.25,6.15C17.8,7.5 17.45,8.54 17.35,8.79C18,9.5 18.38,10.39 18.38,11.5C18.38,15.32 16.04,16.16 13.81,16.41C14.17,16.72 14.5,17.33 14.5,18.26C14.5,19.6 14.5,20.68 14.5,21C14.5,21.27 14.66,21.59 15.17,21.5C19.14,20.16 22,16.42 22,12A10,10 0 0,0 12,2Z", category: "social" },
  { name: "Reddit", path: "M22,11.5C22,10.1 20.9,9 19.5,9C18.9,9 18.3,9.2 17.8,9.6C16.4,8.7 14.6,8 12.5,8C10.4,8 8.6,8.7 7.2,9.6C6.7,9.2 6.1,9 5.5,9C4.1,9 3,10.1 3,11.5C3,12.4 3.5,13.2 4.2,13.7C4.2,13.8 4.3,13.9 4.3,14V17C4.3,20 7.6,22 12.5,22C17.4,22 20.7,20 20.7,17V14C20.7,13.9 20.8,13.8 20.8,13.7C21.5,13.2 22,12.4 22,11.5M12.5,11.5C13.6,11.5 14.5,12.4 14.5,13.5C14.5,14.6 13.6,15.5 12.5,15.5C11.4,15.5 10.5,14.6 10.5,13.5C10.5,12.4 11.4,11.5 12.5,11.5M6.5,12.5C7.6,12.5 8.5,13.4 8.5,14.5C8.5,15.6 7.6,16.5 6.5,16.5C5.4,16.5 4.5,15.6 4.5,14.5C4.5,13.4 5.4,12.5 6.5,12.5M18.5,12.5C19.6,12.5 20.5,13.4 20.5,14.5C20.5,15.6 19.6,16.5 18.5,16.5C17.4,16.5 16.5,15.6 16.5,14.5C16.5,13.4 17.4,12.5 18.5,12.5Z", category: "social" },
  { name: "Discord", path: "M22,24L16.75,19L17.38,21H4.5A2.5,2.5 0 0,1 2,18.5V3.5A2.5,2.5 0 0,1 4.5,1H19.5A2.5,2.5 0 0,1 22,3.5V24M12,6.8C9.32,6.8 7.44,7.95 7.44,7.95C8.47,7.03 10.27,6.5 10.27,6.5L10.1,6.33C8.41,6.36 6.88,7.53 6.88,7.53C5.38,11.12 5.27,15.93 5.27,15.93C6.19,17.56 8.19,17.61 8.19,17.61L8.48,17.41C7.5,16.71 6.73,15.61 6.73,15.61C7.78,15.33 8.5,15.04 8.5,15.04C9.42,14.84 10.18,14.5 10.18,14.5L10.38,14.55C10.32,14.55 10.27,14.55 10.22,14.55C8.78,14.55 7.61,13.62 7.61,12.45C7.61,11.28 8.78,10.35 10.22,10.35C11.66,10.35 12.83,11.28 12.83,12.45C12.83,13.62 11.66,14.55 10.22,14.55C10.17,14.55 10.12,14.55 10.07,14.55L10.27,14.5C10.27,14.5 9.51,14.84 8.59,15.04C8.59,15.04 7.87,15.33 6.82,15.61C6.82,15.61 7.59,16.71 8.57,17.41L8.86,17.61C8.86,17.61 10.86,17.56 11.78,15.93C11.78,15.93 11.67,11.12 10.17,7.53C10.17,7.53 8.64,6.36 6.95,6.33L6.78,6.5C6.78,6.5 8.58,7.03 9.61,7.95C9.61,7.95 7.73,6.8 5.05,6.8V10.45H5.12C5.12,10.45 5.83,8.05 8.48,7.28C8.48,7.28 9.74,8.83 12,8.83C14.26,8.83 15.52,7.28 15.52,7.28C18.17,8.05 18.88,10.45 18.88,10.45H18.95V6.8H16.27C16.27,6.8 14.39,7.95 14.39,7.95C15.42,7.03 17.22,6.5 17.22,6.5L17.05,6.33C15.36,6.36 13.83,7.53 13.83,7.53C12.33,11.12 12.22,15.93 12.22,15.93C13.14,17.56 15.14,17.61 15.14,17.61L15.43,17.41C14.45,16.71 13.68,15.61 13.68,15.61C14.73,15.33 15.45,15.04 15.45,15.04C16.37,14.84 17.13,14.5 17.13,14.5L17.33,14.55C17.28,14.55 17.23,14.55 17.18,14.55C15.74,14.55 14.57,13.62 14.57,12.45C14.57,11.28 15.74,10.35 17.18,10.35C18.62,10.35 19.79,11.28 19.79,12.45C19.79,13.62 18.62,14.55 17.18,14.55C17.13,14.55 17.08,14.55 17.03,14.55L17.23,14.5C17.23,14.5 16.47,14.84 15.55,15.04C15.55,15.04 14.83,15.33 13.78,15.61C13.78,15.61 14.55,16.71 15.53,17.41L15.82,17.61C15.82,17.61 17.82,17.56 18.74,15.93C18.74,15.93 18.63,11.12 17.13,7.53C17.13,7.53 15.6,6.36 13.91,6.33L13.74,6.5C13.74,6.5 15.54,7.03 16.57,7.95C16.57,7.95 14.69,6.8 12.01,6.8H12Z", category: "social" },
  { name: "TikTok", path: "M16.6,5.82S16.26,6,15.83,6C14.27,6 13,4.73 13,3.17V2H11V3.17C11,4.73 9.73,6 8.17,6C7.74,6 7.4,5.82 7.4,5.82S7.06,6 6.63,6C5.07,6 3.8,4.73 3.8,3.17V2H1.8V3.17C1.8,5.86 3.94,8 6.63,8C7.06,8 7.4,7.82 7.4,7.82S7.74,8 8.17,8C9.73,8 11,9.27 11,10.83V22H13V10.83C13,9.27 14.27,8 15.83,8C16.26,8 16.6,7.82 16.6,7.82S16.94,8 17.37,8C20.06,8 22.2,5.86 22.2,3.17V2H20.2V3.17C20.2,4.73 18.93,6 17.37,6C16.94,6 16.6,5.82 16.6,5.82Z", category: "social" },
  { name: "Pallet", path: "M3 12H5V15H10V12H14V15H19V12H21V20H19V17H14V20H10V17H5V20H3V12Z", category: "warehouse" },
  { name: "Forklift", path: "M6,4V11H4C2.89,11 2,11.89 2,13V17A3,3 0 0,0 5,20A3,3 0 0,0 8,17H10A3,3 0 0,0 13,20A3,3 0 0,0 16,17V13L12.74,9.3C12.39,9.09 12,9 11.63,9H9C8.45,9 8,9.45 8,10V11H6V10C6,9.45 5.55,9 5,9C4.45,9 4,9.45 4,10V17H5A1,1 0 0,1 6,18A1,1 0 0,1 5,19A1,1 0 0,1 4,18V13C4,12.45 4.45,12 5,12H6V15H8V12H10V15H12V13C12,12.45 12.45,12 13,12H14V15H16V13C16,12.45 16.45,12 17,12H18V15H20V13C20,12.45 20.45,12 21,12H22V10H20V4H18V10H16V4H14V10H12V4H10V10H8V4H6Z", category: "warehouse" },
  { name: "Package", path: "M5.12,5H18.87L17.93,4H5.93L5.12,5M20.54,5.23C20.83,5.57 21,6 21,6.5V19A2,2 0 0,1 19,21H5A2,2 0 0,1 3,19V6.5C3,6 3.17,5.57 3.46,5.23L4.84,3.55C5.12,3.21 5.53,3 6,3H18C18.47,3 18.88,3.21 19.15,3.55L20.54,5.23M6,18H12V15H6V18Z", category: "warehouse" },
  { name: "Permision", path: "M12,12H19C18.47,16.11 15.72,19.78 12,20.92V12H5V6.3L12,3.19M12,1L3,5V11C3,16.55 6.84,21.73 12,23C17.16,21.73 21,16.55 21,11V5L12,1Z", category: "warehouse" },
  { name: "Role", path: "M12,5.5A3.5,3.5 0 0,1 15.5,9A3.5,3.5 0 0,1 12,12.5A3.5,3.5 0 0,1 8.5,9A3.5,3.5 0 0,1 12,5.5M5,8C5.56,8 6.08,8.15 6.53,8.42C6.38,9.85 6.8,11.27 7.66,12.38C7.16,13.34 6.16,14 5,14A3,3 0 0,1 2,11A3,3 0 0,1 5,8M19,8A3,3 0 0,1 22,11A3,3 0 0,1 19,14C17.84,14 16.84,13.34 16.34,12.38C17.2,11.27 17.62,9.85 17.47,8.42C17.92,8.15 18.44,8 19,8M5.5,18.25C5.5,16.18 8.41,14.5 12,14.5C15.59,14.5 18.5,16.18 18.5,18.25V20H5.5V18.25M0,20V18.5C0,17.11 1.89,15.94 4.45,15.6C3.86,16.28 3.5,17.22 3.5,18.25V20H0M24,20H20.5V18.25C20.5,17.22 20.14,16.28 19.55,15.6C22.11,15.94 24,17.11 24,18.5V20Z", category: "warehouse" },
  { name: "Printer", path: "M18,3H6V7H18M19,12A1,1 0 0,1 18,11A1,1 0 0,1 19,10A1,1 0 0,1 20,11A1,1 0 0,1 19,12M16,19H8V14H16M19,8H5A3,3 0 0,0 2,11V17H6V21H18V17H22V11A3,3 0 0,0 19,8Z", category: "office" },
  { name: "Desk", path: "M3,6H21V4H3V6M2,2H22V8H2V2M3,9H8V22H3V9M10,9H14V22H10V9M15,9H21V22H15V9Z", category: "office" },
  { name: "Wrench", path: "M22.7,19L13.6,9.9C14.5,7.6 14,4.9 12.1,3C10.1,1 7.1,0.6 4.7,1.7L9,6L6,9L1.6,4.7C0.4,7.1 0.9,10.1 2.9,12.1C4.8,14 7.5,14.5 9.8,13.6L18.9,22.7C19.3,23.1 19.9,23.1 20.3,22.7L22.6,20.4C23.1,20 23.1,19.3 22.7,19Z", category: "utilities" },
  { name: "Hammer", path: "M2,19.63L13.43,8.2L12.72,7.5L14.14,6.07L12,3.89C13.2,2.7 15.09,2.7 16.27,3.89L19.5,7.1C20.68,8.28 20.68,10.17 19.5,11.35L17.33,9.18L15.92,10.6L15.21,9.9L3.76,21.34V19.63M4.06,15.83L12,7.9L13.58,9.46L13.75,9.63L15.16,8.21L14.45,7.5L16,5.93C15.09,4 12.88,3.56 11.45,4.97L7.68,8.74L4.06,12.36V15.83Z", category: "utilities" },
  { name: "Server", path: "M4,1H20A1,1 0 0,1 21,2V6A1,1 0 0,1 20,7H4A1,1 0 0,1 3,6V2A1,1 0 0,1 4,1M4,9H20A1,1 0 0,1 21,10V14A1,1 0 0,1 20,15H4A1,1 0 0,1 3,14V10A1,1 0 0,1 4,9M4,17H20A1,1 0 0,1 21,18V22A1,1 0 0,1 20,23H4A1,1 0 0,1 3,22V18A1,1 0 0,1 4,17M9,5H10V3H9V5M9,13H10V11H9V13M9,21H10V19H9V21M5,3V5H7V3H5M5,11V13H7V11H5M5,19V21H7V19H5Z", category: "technology" },
  { name: "Database", path: "M12,3C7.58,3 4,4.79 4,7C4,9.21 7.58,11 12,11C16.42,11 20,9.21 20,7C20,4.79 16.42,3 12,3M4,9V12C4,14.21 7.58,16 12,16C16.42,16 20,14.21 20,12V9C20,11.21 16.42,13 12,13C7.58,13 4,11.21 4,9M4,14V17C4,19.21 7.58,21 12,21C16.42,21 20,19.21 20,17V14C20,16.21 16.42,18 12,18C7.58,18 4,16.21 4,14Z", category: "technology" },
  { name: "Car", path: "M5,11L6.5,6.5H17.5L19,11M17.5,16A1.5,1.5 0 0,1 16,14.5A1.5,1.5 0 0,1 17.5,13A1.5,1.5 0 0,1 19,14.5A1.5,1.5 0 0,1 17.5,16M6.5,16A1.5,1.5 0 0,1 5,14.5A1.5,1.5 0 0,1 6.5,13A1.5,1.5 0 0,1 8,14.5A1.5,1.5 0 0,1 6.5,16M18.92,6C18.72,5.42 18.16,5 17.5,5H6.5C5.84,5 5.28,5.42 5.08,6L3,12V20A1,1 0 0,0 4,21H5A1,1 0 0,0 6,20V19H18V20A1,1 0 0,0 19,21H20A1,1 0 0,0 21,20V12L18.92,6Z", category: "transportation" },
  { name: "Truck", path: "M18,18.5A1.5,1.5 0 0,1 16.5,17A1.5,1.5 0 0,1 18,15.5A1.5,1.5 0 0,1 19.5,17A1.5,1.5 0 0,1 18,18.5M19.5,9.5L21.46,12H17V9.5M6,18.5A1.5,1.5 0 0,1 4.5,17A1.5,1.5 0 0,1 6,15.5A1.5,1.5 0 0,1 7.5,17A1.5,1.5 0 0,1 6,18.5M20,8H17V4H3C1.89,4 1,4.89 1,6V17H3A3,3 0 0,0 6,20A3,3 0 0,0 9,17H15A3,3 0 0,0 18,20A3,3 0 0,0 21,17H23V12L20,8Z", category: "transportation" },
  { name: "Pizza", path: "M12,15A2,2 0 0,1 10,13C10,11.89 10.9,11 12,11A2,2 0 0,1 14,13A2,2 0 0,1 12,15M7,7C7,5.89 7.89,5 9,5A2,2 0 0,1 11,7A2,2 0 0,1 9,9C7.89,9 7,8.1 7,7M12,2C8.43,2 5.23,3.54 3,6L12,22L21,6C18.78,3.54 15.57,2 12,2Z", category: "food" },
  { name: "Hamburger", path: "M2,16H22V18C22,19.11 21.11,20 20,20H4C2.89,20 2,19.11 2,18V16M6,4H18C20.22,4 22,5.78 22,8V10H2V8C2,5.78 3.78,4 6,4M4,11H15L17,13H19L20,11H20.5C21.33,11 22,11.67 22,12.5C22,13.33 21.33,14 20.5,14H3.5C2.67,14 2,13.33 2,12.5C2,11.67 2.67,11 3.5,11H4Z", category: "food" },
  { name: "Hospital", path: "M18,14H14V18H10V14H6V10H10V6H14V10H18M19,3H5C3.89,3 3,3.89 3,5V19A2,2 0 0,0 5,21H19A2,2 0 0,0 21,19V5C21,3.89 20.1,3 19,3Z", category: "medical" },
  { name: "Pill", path: "M4.22,11.29L11.29,4.22C13.64,1.88 17.43,1.88 19.78,4.22C22.12,6.56 22.12,10.36 19.78,12.71L12.71,19.78C10.36,22.12 6.56,22.12 4.22,19.78C1.88,17.43 1.88,13.64 4.22,11.29M5.64,12.71C4.59,13.75 4.24,15.24 4.6,16.57L10.59,10.59L14.83,14.83L18.36,11.29C19.93,9.73 19.93,7.2 18.36,5.64C16.8,4.07 14.27,4.07 12.71,5.64L5.64,12.71Z", category: "medical" },
  { name: "List", path: "M19 3H18V1H16V3H8V1H6V3H5C3.9 3 3 3.9 3 5V19C3 20.11 3.9 21 5 21H19C20.11 21 21 20.11 21 19V5C21 3.9 20.11 3 19 3M19 19H5V9H19V19M5 7V5H19V7H5M7 11H17V13H7V11M7 15H14V17H7V15Z", category: "technology" },
  { name: "Menu", path: "M3,6H21V8H3V6M3,11H21V13H3V11M3,16H21V18H3V16Z", category: "technology" },
])

const categories = computed(() => {
  const cats = Array.from(new Set(icons.value.map(i => i.category)))
  return cats
})

const filteredIcons = computed(() => {
  const q = iconSearch.value.trim().toLowerCase()
  return icons.value.filter(i => {
    const nameMatch = i.name.toLowerCase().includes(q)
    const catMatch = selectedCategory.value ? i.category === selectedCategory.value : true
    return nameMatch && catMatch
  })
})

function selectIcon(icon: IconItem) {
  selectedIconPath.value = icon.path
}

function confirmIcon() {
  if (selectedIconPath.value) {
    form.icon = selectedIconPath.value
    showIconModal.value = false
  }
}

// Handle form submission
const handleSubmit = () => {
  let data = { ...form };
  form.post('/admin/menu', {
    preserveScroll: true,
    onSuccess: () => {
      toast.success(`Menu successfully saved!`);
      router.visit('/admin/menu')
    },
    onError: (errors) => {
      formErrors.value = Object.values(errors || {}) as string[]
      toast.error('Error saving menu. Please check the form.')
    },
  })
}
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-box-orient: vertical;
  -webkit-line-clamp: 2;
  overflow: hidden;
}
</style>
