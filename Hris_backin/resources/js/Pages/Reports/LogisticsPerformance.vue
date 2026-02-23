<script setup>
import TrackingLayout from '@/Layouts/TrackingLayout.vue'
import { Head } from '@inertiajs/vue3'

const props = defineProps({
  drivers: {
    type: Array,
    default: () => []
  },
  vehicles: {
    type: Array,
    default: () => []
  }
})
</script>

<template>
  <Head title="Logistics Performance" />
  <TrackingLayout>
    <div class="space-y-6">
      <h1 class="text-2xl font-bold">Logistics Performance</h1>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl shadow p-4 overflow-auto">
          <h2 class="font-semibold mb-3">Drivers</h2>
          <table class="min-w-full text-sm">
            <thead>
              <tr class="text-left border-b">
                <th class="p-2">Name</th>
                <th class="p-2">Total Deliveries</th>
                <th class="p-2">Successful</th>
                <th class="p-2">Avg Rating</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(d, i) in props.drivers" :key="i" class="border-b">
                <td class="p-2">{{ d.full_name }}</td>
                <td class="p-2">{{ d.total_deliveries }}</td>
                <td class="p-2">{{ d.successful_deliveries }}</td>
                <td class="p-2">{{ d.average_rating }}</td>
              </tr>
              <tr v-if="!props.drivers || props.drivers.length === 0">
                <td class="p-4 text-center text-gray-500" colspan="4">No driver data</td>
              </tr>
            </tbody>
          </table>
        </div>
        <div class="bg-white rounded-xl shadow p-4 overflow-auto">
          <h2 class="font-semibold mb-3">Vehicles</h2>
          <table class="min-w-full text-sm">
            <thead>
              <tr class="text-left border-b">
                <th class="p-2">Vehicle</th>
                <th class="p-2">Trips</th>
                <th class="p-2">Distance (km)</th>
                <th class="p-2">Fuel (L)</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(v, i) in props.vehicles" :key="i" class="border-b">
                <td class="p-2">{{ v.vehicle_name }}</td>
                <td class="p-2">{{ v.total_trips }}</td>
                <td class="p-2">{{ v.total_distance_km }}</td>
                <td class="p-2">{{ v.total_fuel_consumed_liters }}</td>
              </tr>
              <tr v-if="!props.vehicles || props.vehicles.length === 0">
                <td class="p-4 text-center text-gray-500" colspan="4">No vehicle data</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </TrackingLayout>
</template>