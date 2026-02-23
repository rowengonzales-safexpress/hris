<template>
    <Head :title="`Menus - ${app?.name || ''}`" />
    <AdminLayout>
        <div class="menu-list-container">
            <div class="main-content">
                <SectionTitleLineWithButton :icon="'mdiListBox'" :title="`Menus for ${app?.name || ''}`" main>
                    <div>
                        <BaseButton @click="onAddNewMenu" :icon="'mdiFileDocumentPlus'" label="Add Menu" color="contrast" rounded-full small  class="mr-1"/>
                <BaseButton @click="emit('triggerTopRightButton', 'lists')" :icon="'mdiViewList'" label="Back to List" color="contrast" rounded-full small />
                    </div>

                </SectionTitleLineWithButton>

                <CardBox class="flex-1 p-6" has-table>
                  <CoreTable v-if="tableRows.length > 0"
                             :table-rows="tableRows"
                             :table-header="userHeader"
                             table-name="menus"
                             searchable-fields="name,route,app_name,parent_name,sort_order,is_active"
                             :is-paginated="true"
                             @openLink="openLink"
                  />
                  <div v-else class="text-gray-500">No menus found for this application</div>
                </CardBox>
            </div>
        </div>

        <AsideDrawer :title="drawerTitle" :is-open="showDetails" @closeDrawer="showDetails = false" class="shadow-lg shadow-blue-500/50">
          <div class="space-y-4">
            <FormField label="Menu Name">
              <FormControl v-model="form.name" />
            </FormField>
            <FormField label="Route">
              <FormControl v-model="form.route" />
            </FormField>
            <FormField label="Icon">
              <FormControl v-model="form.icon" />
            </FormField>
            <FormField label="Sort Order">
              <FormControl v-model="form.sort_order" type="number" />
            </FormField>
            <FormField label="Parent Menu">
              <FormControl v-model="form.parent_id" :options="parentMenuOptions" />
            </FormField>
            <FormField label="Active">
                <Checkbox v-model="form.is_active" />
            </FormField>
            <BaseButtons class="pt-2 justify-end">
<BaseButton
  :icon="mode === 'Add' ? 'mdiFileDocumentPlus' : 'mdiAccountBoxEditOutline'"
  :label="mode === 'Add' ? 'Save' : 'Update'"
  color="info"
  @click="saveMenu"
/>
              <BaseButton label="Cancel" color="danger"  @click="showDetails = false" icon="mdiClose"/>

            </BaseButtons>
          </div>
        </AsideDrawer>
    </AdminLayout>
  </template>

  <script setup lang="ts">
import { ref, computed, watch } from 'vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Head } from '@inertiajs/vue3'
import BaseButtons from '@/Components/BaseButtons.vue'
import FormField from '@/Components/FormField.vue'
import FormControl from '@/Components/FormControl.vue'
import axios from 'axios'
import { useForm } from '@inertiajs/vue3'
  import AsideDrawer from '@/Components/AsideDrawer.vue'
  import SectionTitleLineWithButton from '@/Components/SectionTitleLineWithButton.vue'
  import BaseButton from '@/Components/BaseButton.vue'
  import CardBox from '@/Components/CardBox.vue'
  import CoreTable from '@/Components/CoreTable.vue'
  import Checkbox from '@/Components/Checkbox.vue'

  const props = defineProps({
    masterlist: {
      type: [Array, Object],
      default: () => []
    },
    app: {
      type: Object,
      default: () => null
    }
  })

  const emit = defineEmits(['triggerTopRightButton']);

  const showDetails = ref(false)
  const mode = ref<'Add' | 'Edit'>('Add')
  const form = useForm({
    id: null as any,
    app_id: props.app?.id || null,
    group: '',
    name: '',
    route: '',
    icon: '',
    sort_order: 0,
    is_active: true,
    parent_id: null as any,
  })
  const drawerTitle = computed(() => (mode.value === 'Add' ? 'Add Menu' : 'Edit Menu'))

  const menulist = computed(() => {
    let data = []
    if (Array.isArray(props.masterlist)) {
      data = props.masterlist
    } else if (props.masterlist && typeof props.masterlist === 'object') {
      data = props.masterlist.data || []
    }

    if (!Array.isArray(data)) return []

    return data.map(item => ({
      id: item.id || '',
      name: item.name || '',
      route: item.route || '',
      icon: item.icon || '',
      sort_order: item.sort_order || 0,
      is_active: item.is_active || false,
      app_id: item.app_id || null,
      parent_id: item.parent_id || null,
      app_name: item.app?.name || (props.app?.name || ''),
      parent_name: item.parent?.name || 'Root'
    })).filter(Boolean)
  })

  const localMenus = ref<any[]>([])
  watch(menulist, (ml) => { localMenus.value = Array.isArray(ml) ? ml : [] }, { immediate: true })
  const tableRows = computed(() => localMenus.value)
  const openLink = async (row) => {
    mode.value = 'Edit'
    try {
      const resp = await axios.get(`/admin/menu/${row.id}`)
      const d = resp.data || {}
      form.id = d.id || row.id
      form.app_id = d.app_id || props.app?.id || row.app_id || null
      form.group = d.group || ''
      form.name = d.name || row.name || ''
      form.route = d.route || row.route || ''
      form.icon = d.icon || row.icon || ''
      form.sort_order = d.sort_order || row.sort_order || 0
      form.is_active = !!(d.is_active ?? row.is_active)
      form.parent_id = d.parent_id ?? row.parent_id ?? null
    } catch (e) {
      form.id = row.id
      form.app_id = props.app?.id || row.app_id || null
      form.name = row.name || ''
      form.route = row.route || ''
      form.icon = row.icon || ''
      form.sort_order = row.sort_order || 0
      form.is_active = !!row.is_active
      form.parent_id = row.parent_id || null
    }
    showDetails.value = true
  }

  const userHeader = [
    { label: 'Name', fieldName: 'name', type: 'link' },
    { label: 'Route', fieldName: 'route' },
    { label: 'Application', fieldName: 'app_name' },
    { label: 'Parent', fieldName: 'parent_name' },
    { label: 'Order', fieldName: 'sort_order' },
    { label: 'Active', fieldName: 'is_active' }
  ]

  const onAddNewMenu = () => {
    mode.value = 'Add'
    form.id = null
    form.app_id = props.app?.id || null
    form.name = ''
    form.route = ''
    form.icon = ''
    form.sort_order = 0
    form.is_active = true
    form.parent_id = null
    showDetails.value = true
  }

  const parentMenuOptions = computed(() => {
    const parents = (localMenus.value || []).filter(m => (m.parent_id ?? 0) === 0)
    return parents.map(p => ({ value: p.id, label: p.name }))
  })

  const reloadMenus = async () => {
    if (!props.app?.id) return
    const res = await axios.get(`/admin/menus/app/${props.app.id}`)
    const data = Array.isArray(res.data) ? res.data : []
    localMenus.value = data.map(item => ({
      id: item.id || '',
      name: item.name || '',
      route: item.route || '',
      icon: item.icon || '',
      sort_order: item.sort_order || 0,
      is_active: item.is_active || false,
      app_id: item.app_id || null,
      parent_id: item.parent_id || null,
      app_name: props.app?.name || '',
      parent_name: 'Root'
    }))
  }

  const saveMenu = () => {
    if (mode.value === 'Add') {
      form.post('/admin/menu', {
        preserveScroll: true,
        onSuccess: async () => { showDetails.value = false; await reloadMenus() },
        onError: () => {}
      })
    } else {
      form.put(`/admin/menu/${form.id}`, {
        preserveScroll: true,
        onSuccess: async () => { showDetails.value = false; await reloadMenus() },
        onError: () => {}
      })
    }
  }
  </script>

  <style scoped>
  .menu-list-container { position: relative; display: flex; height: 100vh; }
  .main-content { flex: 1; padding-right: 1rem; overflow-y: auto; }
  .slide-panel { position: fixed; top: 0; right: 0; height: 100vh; z-index: 1000; overflow-y: auto; box-shadow: -2px 0 8px rgba(0,0,0,0.1); }
  </style>
