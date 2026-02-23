<script setup>
import { ref } from 'vue'
import FRMSLayout from '@/Layouts/FRMSLayout.vue'
import { Head } from '@inertiajs/vue3'
import Account from './Account.vue'
import Frequency from './Frequency.vue'
import Vat from './Vat.vue'
import Warehouse from './Warehouse.vue'
import User from './User.vue'
import Tab from '@/Components/Tab.vue'

const activetab = ref('account-title')
</script>

<template>
  <Head title="System Configuration" />
  <FRMSLayout>
    <ul class="flex flex-wrap -mb-px text-sm font-medium text-center text-gray-600 dark:text-gray-400">
      <li class="mr-2">
        <Tab
          label="Account Title"
          :icon="'mdiFileCog'"
          name="account-title"
          :active-tab="activetab"
          @click="activetab = 'account-title'"
        />
      </li>
      <li class="mr-2">
        <Tab
          label="Frequency"
          :icon="'mdiClockOutline'"
          name="frequency"
          :active-tab="activetab"
          @click="activetab = 'frequency'"
        />
      </li>
      <li class="mr-2">
        <Tab
          label="VAT"
          :icon="'mdiPercent'"
          name="vat"
          :active-tab="activetab"
          @click="activetab = 'vat'"
        />
      </li>
      <li class="mr-2">
        <Tab
          label="Warehouse"
          :icon="'mdiWarehouse'"
          name="warehouse"
          :active-tab="activetab"
          @click="activetab = 'warehouse'"
        />
      </li>
      <li class="mr-2">
        <Tab
          label="User"
          :icon="'mdiAccount'"
          name="user"
          :active-tab="activetab"
          @click="activetab = 'user'"
        />
      </li>
    </ul>

    <div class="mt-4">
      <div v-show="activetab === 'account-title'">
        <Account />
      </div>
      <div v-show="activetab === 'frequency'">
        <Frequency />
      </div>
      <div v-show="activetab === 'vat'">
        <Vat />
      </div>
      <div v-show="activetab === 'warehouse'">
        <Warehouse />
      </div>
      <div v-show="activetab === 'user'">
        <User />
      </div>
    </div>
  </FRMSLayout>

</template>
