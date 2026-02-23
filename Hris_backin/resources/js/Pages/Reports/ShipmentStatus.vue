<script setup>
import TrackingLayout from '@/Layouts/TrackingLayout.vue'
import { Head } from '@inertiajs/vue3'

const props = defineProps({
  shipments: {
    type: Array,
    default: () => []
  }
})
</script>

<template>
  <Head title="Shipment Status Report" />
  <TrackingLayout>
    <div class="space-y-6">
      <h1 class="text-2xl font-bold">Shipment Status Report</h1>
      <div class="bg-white rounded-xl shadow p-4 overflow-auto">
        <table class="min-w-full text-sm">
          <thead>
            <tr class="text-left border-b">
              <th class="p-2">Tracking #</th>
              <th class="p-2">Reference #</th>
              <th class="p-2">Status ID</th>
              <th class="p-2">ETA</th>
              <th class="p-2">Actual Delivery</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(s, i) in props.shipments" :key="i" class="border-b">
              <td class="p-2">{{ s.tracking_number }}</td>
              <td class="p-2">{{ s.reference_number }}</td>
              <td class="p-2">{{ s.current_status_id }}</td>
              <td class="p-2">{{ s.estimated_delivery_date }}</td>
              <td class="p-2">{{ s.actual_delivery_date }}</td>
            </tr>
            <tr v-if="!props.shipments || props.shipments.length === 0">
              <td class="p-4 text-center text-gray-500" colspan="5">No shipments found</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </TrackingLayout>
</template>