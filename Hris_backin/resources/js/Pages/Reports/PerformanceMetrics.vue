<script setup>
import TrackingLayout from '@/Layouts/TrackingLayout.vue'
import { Head } from '@inertiajs/vue3'

const props = defineProps({
  metrics: {
    type: Array,
    default: () => []
  }
})
</script>

<template>
  <Head title="Performance Metrics" />
  <TrackingLayout>
    <div class="space-y-6">
      <h1 class="text-2xl font-bold">Performance Metrics</h1>
      <div class="bg-white rounded-xl shadow p-4 overflow-auto">
        <table class="min-w-full text-sm">
          <thead>
            <tr class="text-left border-b">
              <th class="p-2">Vehicle</th>
              <th class="p-2">Avg Fuel / km</th>
              <th class="p-2">Distance (km)</th>
              <th class="p-2">Trips</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(m, i) in props.metrics" :key="i" class="border-b">
              <td class="p-2">{{ m.vehicle_name }}</td>
              <td class="p-2">{{ m.avg_fuel_consumption_per_km }}</td>
              <td class="p-2">{{ m.total_distance_km }}</td>
              <td class="p-2">{{ m.total_trips }}</td>
            </tr>
            <tr v-if="!props.metrics || props.metrics.length === 0">
              <td class="p-4 text-center text-gray-500" colspan="4">No metrics found</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </TrackingLayout>
</template>