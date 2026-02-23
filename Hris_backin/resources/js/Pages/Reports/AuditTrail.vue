<script setup>
import TrackingLayout from '@/Layouts/TrackingLayout.vue'
import { Head } from '@inertiajs/vue3'

const props = defineProps({
  audit: {
    type: Array,
    default: () => []
  }
})
</script>

<template>
  <Head title="Audit Trail" />
  <TrackingLayout>
    <div class="space-y-6">
      <h1 class="text-2xl font-bold">Audit Trail</h1>
      <div class="bg-white rounded-xl shadow p-4 overflow-auto">
        <table class="min-w-full text-sm">
          <thead>
            <tr class="text-left border-b">
              <th class="p-2">Header ID</th>
              <th class="p-2">Created By</th>
              <th class="p-2">Updated By</th>
              <th class="p-2">Created At</th>
              <th class="p-2">Updated At</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(a, i) in props.audit" :key="i" class="border-b">
              <td class="p-2">{{ a.trackingheader_id }}</td>
              <td class="p-2">{{ a.created_by }}</td>
              <td class="p-2">{{ a.updated_by }}</td>
              <td class="p-2">{{ a.created_at }}</td>
              <td class="p-2">{{ a.updated_at }}</td>
            </tr>
            <tr v-if="!props.audit || props.audit.length === 0">
              <td class="p-4 text-center text-gray-500" colspan="5">No audit records found</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </TrackingLayout>
</template>