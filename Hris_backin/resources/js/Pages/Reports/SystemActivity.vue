<script setup>
import TrackingLayout from '@/Layouts/TrackingLayout.vue'
import { Head } from '@inertiajs/vue3'

const props = defineProps({
  activities: {
    type: Array,
    default: () => []
  }
})
</script>

<template>
  <Head title="System Activity" />
  <TrackingLayout>
    <div class="space-y-6">
      <h1 class="text-2xl font-bold">System Activity</h1>
      <div class="bg-white rounded-xl shadow p-4 overflow-auto">
        <table class="min-w-full text-sm">
          <thead>
            <tr class="text-left border-b">
              <th class="p-2">Tracking #</th>
              <th class="p-2">Created At</th>
              <th class="p-2">Updated At</th>
              <th class="p-2">Created By</th>
              <th class="p-2">Updated By</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(a, i) in props.activities" :key="i" class="border-b">
              <td class="p-2">{{ a.tracking_number }}</td>
              <td class="p-2">{{ a.created_at }}</td>
              <td class="p-2">{{ a.updated_at }}</td>
              <td class="p-2">{{ a.created_by }}</td>
              <td class="p-2">{{ a.updated_by }}</td>
            </tr>
            <tr v-if="!props.activities || props.activities.length === 0">
              <td class="p-4 text-center text-gray-500" colspan="5">No activities found</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </TrackingLayout>
</template>
