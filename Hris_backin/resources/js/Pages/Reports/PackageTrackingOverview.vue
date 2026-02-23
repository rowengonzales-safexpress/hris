<script setup>
import TrackingLayout from '@/Layouts/TrackingLayout.vue'
import { Head } from '@inertiajs/vue3'

const props = defineProps({
  packages: {
    type: Array,
    default: () => []
  }
})
</script>

<template>
  <Head title="Package Tracking Overview" />
  <TrackingLayout>
    <div class="space-y-6">
      <h1 class="text-2xl font-bold">Package Tracking Overview</h1>
      <div class="bg-white rounded-xl shadow p-4 overflow-auto">
        <table class="min-w-full text-sm">
          <thead>
            <tr class="text-left border-b">
              <th class="p-2">Tracking #</th>
              <th class="p-2">Packages</th>
              <th class="p-2">Weight</th>
              <th class="p-2">Volume</th>
              <th class="p-2">Location</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(p, i) in props.packages" :key="i" class="border-b">
              <td class="p-2">{{ p.tracking_number }}</td>
              <td class="p-2">{{ p.package_count }}</td>
              <td class="p-2">{{ p.total_weight }}</td>
              <td class="p-2">{{ p.total_volume }}</td>
              <td class="p-2">{{ p.current_location }}</td>
            </tr>
            <tr v-if="!props.packages || props.packages.length === 0">
              <td class="p-4 text-center text-gray-500" colspan="5">No packages found</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </TrackingLayout>
</template>