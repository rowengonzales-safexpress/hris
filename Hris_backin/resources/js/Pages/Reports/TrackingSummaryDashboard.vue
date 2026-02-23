<script setup>
import TrackingLayout from '@/Layouts/TrackingLayout.vue'
import { Head } from '@inertiajs/vue3'

const props = defineProps({
  summary: {
    type: Object,
    default: () => ({})
  }
})
</script>

<template>
  <Head title="Tracking Summary Dashboard" />
  <TrackingLayout>
    <div class="space-y-6">
      <h1 class="text-2xl font-bold">Tracking Summary Dashboard</h1>
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white rounded-xl shadow p-6">
          <p class="text-sm text-gray-600">Total Shipments</p>
          <p class="text-3xl font-bold">{{ summary.total_shipments ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-xl shadow p-6">
          <p class="text-sm text-gray-600">Active Shipments</p>
          <p class="text-3xl font-bold">{{ summary.active_shipments ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-xl shadow p-6">
          <p class="text-sm text-gray-600">Drivers On Trip</p>
          <p class="text-3xl font-bold">{{ summary.drivers_on_trip ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-xl shadow p-6">
          <p class="text-sm text-gray-600">Vehicles In Use</p>
          <p class="text-3xl font-bold">{{ summary.vehicles_in_use ?? 0 }}</p>
        </div>
      </div>
    </div>
  </TrackingLayout>
</template>