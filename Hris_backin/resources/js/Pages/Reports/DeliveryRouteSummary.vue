<script setup>
import TrackingLayout from '@/Layouts/TrackingLayout.vue'
import { Head } from '@inertiajs/vue3'

const props = defineProps({
  routes: {
    type: Array,
    default: () => []
  }
})
</script>

<template>
  <Head title="Delivery Route Summary" />
  <TrackingLayout>
    <div class="space-y-6">
      <h1 class="text-2xl font-bold">Delivery Route Summary</h1>
      <div class="bg-white rounded-xl shadow p-4 overflow-auto">
        <table class="min-w-full text-sm">
          <thead>
            <tr class="text-left border-b">
              <th class="p-2">Header ID</th>
              <th class="p-2">Store In</th>
              <th class="p-2">Store Out</th>
              <th class="p-2">Delivery Status</th>
              <th class="p-2">Receiver</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(r, i) in props.routes" :key="i" class="border-b">
              <td class="p-2">{{ r.trackingheader_id }}</td>
              <td class="p-2">{{ r.store_time_in }}</td>
              <td class="p-2">{{ r.store_time_out }}</td>
              <td class="p-2">{{ r.delivery_status }}</td>
              <td class="p-2">{{ r.receiver_name }}</td>
            </tr>
            <tr v-if="!props.routes || props.routes.length === 0">
              <td class="p-4 text-center text-gray-500" colspan="5">No routes found</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </TrackingLayout>
</template>