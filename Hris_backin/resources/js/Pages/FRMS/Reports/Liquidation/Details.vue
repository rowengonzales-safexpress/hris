<template>
  <div>
    <SectionTitleLineWithButton :icon="'mdiReceiptText'" title="Fund Request Details" main >
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
           <h1 class="text-2xl font-bold uppercase tracking-wide decoration-double underline">Liquidation Report</h1>
        </div>
      </div>
      <div v-if="loading" class="p-6">Loading...</div>
      <div v-else>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
          <div>
            <div class="text-xs text-gray-500 uppercase">Request No</div>
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

        <div class="mt-6">
          <div class="text-xs text-gray-500 uppercase mb-2">Liquidation Details</div>
          <div class="overflow-x-auto border rounded">
            <table class="min-w-full text-sm">
              <thead class="bg-info">
                <tr>
                  <th class="px-2 py-1 text-left">Date</th>
                  <th class="px-2 py-1 text-left">OR No</th>
                  <th class="px-2 py-1 text-left">Description</th>
                  <th class="px-2 py-1 text-right">Expense Amount</th>
                  <th class="px-2 py-1 text-right">Input VAT</th>
                  <th class="px-2 py-1 text-left">Status</th>
                  <th class="px-2 py-1 text-left">Receipt</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="li in data.liquidation" :key="li.id" class="border-t even:bg-gray-50 dark:even:bg-slate-800">
                  <td class="px-2 py-1">{{ formatDate(li.date) }}</td>
                  <td class="px-2 py-1">{{ li.or_no || '—' }}</td>
                  <td class="px-2 py-1">{{ li.description || '—' }}</td>
                  <td class="px-2 py-1 text-right">₱{{ formatCurrency(li.expense_amount) }}</td>
                  <td class="px-2 py-1 text-right">{{ formatCurrency(li.input_vat) }}</td>
                  <td class="px-2 py-1">{{ li.vat_non_vat || '—' }}</td>
                  <td class="px-2 py-1">
                    <div v-if="li.thumbnail_url" class="h-16 w-16 overflow-hidden rounded cursor-pointer" @click="openLightbox(li.thumbnail_url)">
                      <img :src="li.thumbnail_url" alt="" class="h-16 w-16 object-cover" />
                    </div>
                    <span v-else>—</span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <div v-if="lightboxOpen" class="fixed inset-0 z-50 bg-black/80 select-none" @click.self="closeLightbox">
        <button type="button" class="absolute top-4 right-4 bg-white/20 hover:bg-white/30 text-white rounded-full h-10 w-10 flex items-center justify-center text-xl" @click="closeLightbox">✕</button>
        <div class="w-full h-full flex items-center justify-center" @wheel.prevent="onLightboxWheel">
          <img
            :src="lightboxImage"
            alt=""
            class="object-contain max-w-none"
            :style="{ transform: `translate(${translateX}px, ${translateY}px) scale(${zoom})`, cursor: dragging ? 'grabbing' : 'grab' }"
            @mousedown="onDragStart"
            @mousemove="onDragMove"
            @mouseup="onDragEnd"
            @mouseleave="onDragEnd"
            @touchstart.prevent="onTouchStart"
            @touchmove.prevent="onTouchMove"
            @touchend="onDragEnd"
          />
        </div>
      </div>
      <div class="mt-8 space-y-3">
        <div>
          <span class="font-semibold">Disbursed Fund Request:</span>
          <span class="ml-2">₱{{ formatCurrency(summary.requested_total) }}</span>
        </div>
        <div>
          <span class="font-semibold">Liquidated Expenses:</span>
          <span class="ml-2">₱{{ formatCurrency(summary.liquidated_total) }}</span>
        </div>
        <div v-if="summary.variance !== 0">
          <span class="font-semibold">Variance:</span>
          <span class="ml-2">₱{{ formatCurrency(summary.variance) }}</span>
        </div>
      </div>



      <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-6">
        <template v-if="data.approval_details && data.approval_details.length > 0">
          <div v-for="(approval, index) in data.approval_details" :key="index">
            <div><span class="font-semibold">{{ approval.approver_name }}</span></div>
            <div><span class="font-semibold">Approved by:</span> ({{ approval.role_description }})</div>
            <div><span class="font-semibold">Approved Date and Time:</span> {{ formatDateTime(approval.approved_date) }}</div>
          </div>
        </template>
        <template v-else>

        </template>
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
  name: 'LiquidationDetailsPage',
  components: { SectionTitleLineWithButton, CardBox, BaseButton },
  props: {
    formdata: { type: Object, default: () => ({}) }
  },
  data() {
    return {
      loading: false,
      data: { form: {}, items: [], liquidation: [], disbursement: null },
      summary: { requested_total: 0, liquidated_total: 0, variance: 0 },
      lightboxOpen: false,
      lightboxImage: '',
      zoom: 1,
      translateX: 0,
      translateY: 0,
      dragging: false,
      dragOriginX: 0,
      dragOriginY: 0,
    }
  },
  watch: {
    formdata: {
      immediate: true,
      async handler(val) {
        if (!val || !val.form_id) return;
        this.loading = true;
        try {
          let url = `/frls/reports/liquidation-detail/${val.form_id}`
          try {
            if (typeof route === 'function') {
              url = route('frls.reports.liquidation-detail', val.form_id)
            }
          } catch(e) {}
          const res = await axios.get(url);
          if (res.data && res.data.success) {
            this.data = res.data;
            if (res.data.summary) {
              this.summary = res.data.summary;
            }
          }
        } catch (e) {
          console.error('Failed to load details', e);
        } finally {
          this.loading = false;
        }
      }
    }
  },
  methods: {
    formatCurrency(amount) {
      if (!amount) return '0.00';
      return parseFloat(amount).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    },
    formatDate(dateString) {
      if (!dateString) return 'N/A';
      return new Date(dateString).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: '2-digit' });
    },
    formatDateTime(dateString) {
      if (!dateString) return 'N/A';
      return new Date(dateString).toLocaleString('en-US', { year: 'numeric', month: 'short', day: '2-digit', hour: '2-digit', minute: '2-digit' });
    },
    getStatusText(status) {
      const statusTexts = { P:'Pending', O:'Open', A:'Approved', C:'Closed', X:'Canceled', R:'Rejected', FA:'For Approval', FD:'For Disbursement', FL:'For Liquidation' };
      return statusTexts[status] || 'Unknown';
    },
    openLightbox(url) { this.lightboxImage = url; this.lightboxOpen = true },
    closeLightbox() { this.lightboxOpen = false; this.lightboxImage=''; this.zoom=1; this.translateX=0; this.translateY=0; this.dragging=false },
    onLightboxWheel(e) { const step = e.deltaY < 0 ? 0.1 : -0.1; const next = this.zoom + step; this.zoom = Math.min(4, Math.max(0.5, next)) },
    onDragStart(e) { this.dragging = true; this.dragOriginX = e.clientX - this.translateX; this.dragOriginY = e.clientY - this.translateY },
    onDragMove(e) { if (!this.dragging) return; this.translateX = e.clientX - this.dragOriginX; this.translateY = e.clientY - this.dragOriginY },
    onDragEnd() { this.dragging = false },
    onTouchStart(e) { const t = e.touches[0]; this.dragging = true; this.dragOriginX = t.clientX - this.translateX; this.dragOriginY = t.clientY - this.translateY },
    onTouchMove(e) { const t = e.touches[0]; if (!this.dragging) return; this.translateX = t.clientX - this.dragOriginX; this.translateY = t.clientY - this.dragOriginY },

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
}
</script>

<style scoped>
.lightbox-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.8); z-index: 50; }
.hidden-on-screen {
  display: none;
}
</style>
