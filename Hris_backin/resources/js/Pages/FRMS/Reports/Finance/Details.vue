<template>
  <div>
    <SectionTitleLineWithButton :icon="'mdiReceiptText'" title="Disbursement Details" main>
        <div>
                  <BaseButton class="mr-2" @click="$emit('triggerTopRightButton','lists')" :icon="'mdiViewList'" label="Request Lists" color="contrast" rounded-full small />
        <BaseButton @click="downloadPDF" :icon="'mdiFile'" label="PDF" color="danger" rounded-full small />
        </div>

    </SectionTitleLineWithButton>
    <div id="exportPDF">
    <CardBox class="mb-6">
      <div class="pdf-header hidden-on-screen mb-6">
        <div class="flex justify-between items-center mb-4">
          <div class="text-left flex items-center">
              <div>
                <h2 class="text-2xl font-bold leading-tight" style="color: #003399; font-family: Arial, sans-serif;">
                  Fund Request and
                </h2>
                <h3 class="text-lg font-normal leading-tight" style="color: #4a6b9c; font-family: Arial, sans-serif;">
                  Liquidation System
                </h3>
              </div>
          </div>
          <img src="/asset/logo.png" alt="Logo" class="h-20" />
        </div>
        <div class="text-center">
           <h1 class="text-2xl font-bold uppercase tracking-wide decoration-double underline">Disbursement Report</h1>
        </div>
      </div>
      <div v-if="loading" class="p-6">Loading...</div>
      <div v-else>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
          <div>
            <div class="text-xs text-gray-500 uppercase">FRM No</div>
            <div class="text-sm">{{ data.form.frm_no }}</div>
          </div>
          <div>
            <div class="text-xs text-gray-500 uppercase">Purpose</div>
            <div class="text-sm">{{ data.form.purpose }}</div>
          </div>
          <div>
            <div class="text-xs text-gray-500 uppercase">Request Date</div>
            <div class="text-sm">{{ formatDate(data.form.request_date) }}</div>
          </div>
          <div>
            <div class="text-xs text-gray-500 uppercase">Site</div>
            <div class="text-sm">{{ data.form.branch_name }}</div>
          </div>
          <div>
            <div class="text-xs text-gray-500 uppercase">Expected Liquidation</div>
            <div class="text-sm">{{ formatDate(data.form.expectedliquidation_date) }}</div>
          </div>
          <div>
            <div class="text-xs text-gray-500 uppercase">Status</div>
            <div class="text-sm">{{ getStatusText(data.form.status_request) }}</div>
          </div>
          <div>
            <div class="text-xs text-gray-500 uppercase">Employee</div>
            <div class="text-sm">{{ data.form.employee_name }}</div>
          </div>
          <div v-if="data.disbursement">
            <div class="text-xs text-gray-500 uppercase">Disbursement No</div>
            <div class="text-sm">{{ data.disbursement.disbursement_no }}</div>
          </div>
          <div v-if="data.disbursement">
            <div class="text-xs text-gray-500 uppercase">Disbursement Date</div>
            <div class="text-sm">{{ formatDate(data.disbursement.actual_liquidation_date) }}</div>
          </div>
          <div v-if="data.disbursement">
            <div class="text-xs text-gray-500 uppercase">Disbursement Status</div>
            <div class="text-sm">{{ getStatusText(data.disbursement.status) }}</div>
          </div>
        </div>

        <div class="mt-4">
          <div class="text-xs text-gray-500 uppercase mb-2">Items</div>
          <div class="overflow-x-auto border rounded">
            <table class="min-w-full text-sm">
              <thead class="bg-info">
                <tr>
                  <th class="px-2 py-1 text-left">No.</th>
                  <th class="px-2 py-1 text-left">Account Code</th>
                  <th class="px-2 py-1 text-left">Frequency</th>
                  <th class="px-2 py-1 text-left">Description</th>
                  <th class="px-2 py-1 text-right">Qty</th>
                  <th class="px-2 py-1 text-right">Unit Price</th>
                  <th class="px-2 py-1 text-right">Amount</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(it, idx) in data.items" :key="idx" class="border-t even:bg-gray-50 dark:even:bg-slate-800">
                  <td class="px-2 py-1">{{ idx + 1 }}</td>
                  <td class="px-2 py-1">{{ it.account_code_title }}</td>
                  <td class="px-2 py-1">{{ it.frequency_label }}</td>
                  <td class="px-2 py-1">{{ it.description }}</td>
                  <td class="px-2 py-1 text-right">{{ it.qty }}</td>
                  <td class="px-2 py-1 text-right">{{ formatCurrency(it.unit_price) }}</td>
                  <td class="px-2 py-1 text-right">{{ formatCurrency(it.amount) }}</td>
                </tr>
              </tbody>
              <tfoot>
                <tr class="border-t">
                  <td colspan="6" class="px-2 py-1 text-right font-semibold">Total Amount:</td>
                  <td class="px-2 py-1 text-right font-bold">₱{{ formatCurrency(data.items.reduce((s, i) => s + (parseFloat(i.amount)||0), 0)) }}</td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>


      </div>
    </CardBox>
    </div>
  </div>
</template>

<script>
import SectionTitleLineWithButton from '../../../../Components/SectionTitleLineWithButton.vue';
import CardBox from '../../../../Components/CardBox.vue';
import BaseButton from '../../../../Components/BaseButton.vue';
import axios from 'axios';
import html2pdf from "html2pdf.js";

export default {
  name: 'FinanceDetailsPage',
  components: { SectionTitleLineWithButton, CardBox, BaseButton },
  props: { formdata: { type: Object, default: () => ({}) } },
  data() {
    return { loading: false, data: { form: {}, items: [], liquidation: [], disbursement: null } };
  },
  watch: {
    formdata: {
      immediate: true,
      async handler(val) {
        if (!val) return;
        this.loading = true;
        try {
          const id = val.frms_id ?? val.id;
          let url = `/frls/reports/finance-detail/${id}`;
          try { if (typeof route === 'function') { url = route('frls.reports.finance-detail', id); } } catch(e) {}
          const res = await axios.get(url);
          if (res.data && res.data.success) {
            this.data = res.data;
          }
        } catch(e) {
        } finally {
          this.loading = false;
        }
      }
    }
  },
  methods: {
    formatCurrency(amount) { if (!amount) return '0.00'; return parseFloat(amount).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); },
    formatDate(dateString) { if (!dateString) return 'N/A'; return new Date(dateString).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: '2-digit' }); },
    getStatusText(status) { const t = { P:'Pending', O:'Open', A:'Approved', C:'Closed', X:'Canceled', R:'Rejected', FA:'For Approval', FD:'For Disbursement', FL:'For Liquidation' }; return t[status] || 'Unknown'; },
    closeDrawer() { this.$emit('triggerTopRightButton','lists'); },

    downloadPDF() {
      const element = document.getElementById("exportPDF"); // Specify the element to convert to PDF

      // Temporarily show the header for PDF generation
      const header = element.querySelector('.pdf-header');
      if (header) header.classList.remove('hidden-on-screen');

      const filename = `${this.data.form.frm_no || 'FRM'}_${this.data.form.branch_name || 'Branch'}.pdf`;

      const opt = {
        margin: [10, 10, 10, 10],
        filename: filename,
        image: { type: 'jpeg', quality: 0.98 },
        html2canvas: { scale: 2 },
        jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
      };

      html2pdf().from(element).set(opt).toPdf().get('pdf').then((pdf) => {
        const totalPages = pdf.internal.getNumberOfPages();
        for (let i = 1; i <= totalPages; i++) {
          pdf.setPage(i);
          pdf.setFontSize(10);
          pdf.text(`Page ${i} of ${totalPages}`, pdf.internal.pageSize.getWidth() - 30, pdf.internal.pageSize.getHeight() - 10);
        }
      }).output('bloburl').then((bloburl) => {
        window.open(bloburl, '_blank');
         // Re-hide the header after PDF generation
         if (header) header.classList.add('hidden-on-screen');
      });
    }
  }
};
</script>

<style scoped>
.hidden-on-screen {
  display: none;
}
</style>
