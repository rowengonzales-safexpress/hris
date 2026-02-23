<template>
  <SectionTitleLineWithButton :icon="'mdiChartLine'" title="Finance Disbursement Report" main>
    <BaseButtons>
      <BaseButton :icon="'mdiRefresh'" title="Refresh Data" color="whiteDark" @click="refreshData" />
    </BaseButtons>
  </SectionTitleLineWithButton>

  <CardBox class="flex-1 p-6" has-table>
    <CoreTable
      v-if="disbursementData.length > 0"
      :table-rows="disbursementData"
      :table-header="tableHeader"
      table-name="Finance Disbursement Report"
      :searchable-fields="['frm_no', 'fullname', 'branch_name', 'purpose']"
      :is-paginated="true"
      @openLink="showDetails"
    >
      <template #row-action="scope">
        <span :class="{
          'text-yellow-500': scope.slotProp.status === 'P',
          'text-blue-500': scope.slotProp.status === 'O',
          'text-red-500': scope.slotProp.status === 'C',
          'text-green-500': scope.slotProp.status === 'A'
        }">
          {{
            scope.slotProp.status === 'P' ? 'Pending' :
            scope.slotProp.status === 'O' ? 'Open' :
            scope.slotProp.status === 'C' ? 'Close' :
            scope.slotProp.status === 'A' ? 'Approve' :
            scope.slotProp.status
          }}
        </span>
      </template>
    </CoreTable>

    <div v-else class="text-gray-500 text-center py-8">
      No disbursement records found.
    </div>
  </CardBox>
</template>

<script>
import CoreTable from '../../../../Components/CoreTable.vue';
import SectionTitleLineWithButton from '../../../../Components/SectionTitleLineWithButton.vue';
import CardBox from '../../../../Components/CardBox.vue';
import BaseButton from '../../../../Components/BaseButton.vue';
import BaseButtons from '../../../../Components/BaseButtons.vue';
import { router } from '@inertiajs/vue3';

export default {
  name: 'FinanceList',
  components: { CoreTable, SectionTitleLineWithButton, CardBox, BaseButton, BaseButtons },
  props: {
    disbursementData: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({}) },
    summary: { type: Object, default: () => ({}) },
    sites: { type: Array, default: () => [] },
  },
  data() {
    return {
      tableHeader: [
        { label: 'Ref.No', fieldName: 'frm_no', type: 'link' },
        { label: 'Dibet', fieldName: 'amount' },
        { label: 'Location', fieldName: 'branch_name' },
        { label: 'Accountable Person', fieldName: 'fullname' },
        { label: 'Transaction Type', fieldName: 'transaction_type' },
        { label: 'Description', fieldName: 'purpose' },
        { label: 'Frequency', fieldName: 'frequency' },
        { label: 'Expected Liquidation', fieldName: 'expected_liquidation_date', type: 'date' },
        { label: 'Actual Liquidation', fieldName: 'actual_liquidation_date', type: 'date' },
        { label: 'Original Receipts', fieldName: 'original_receipts' },
        { label: 'Amount', fieldName: 'amount' },
        { label: 'Defference', fieldName: 'defference' },
        { label: 'Status', fieldName: 'status', type: 'slot' },
      ],
    };
  },
  methods: {
    showDetails(row) {
      this.$emit('triggerTopRightButton', 'Details', row);
    },
    refreshData() {
      router.reload();
    },
  },
};
</script>

<style scoped>
</style>
