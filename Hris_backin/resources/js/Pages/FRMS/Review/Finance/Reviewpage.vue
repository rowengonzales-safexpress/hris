<template>
  <FRMSLayout>
    <SectionTitleLineWithButton :icon="'mdiFileEye'" title="Review Details" main>
      <BaseButtons>
        <BaseButton :icon="'mdiViewList'" label="Back to List" color="contrast" rounded-full small @click="goBack" />
      </BaseButtons>
    </SectionTitleLineWithButton>

    <CardBox>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div>
          <div class="text-sm font-semibold">Site/Location</div>
          <div class="p-2">{{ form.branch?.branch_name ?? '' }}</div>
        </div>
        <div>
          <div class="text-sm font-semibold">Fund Request Reference No.</div>
          <div class=" p-2">{{ form.frm_no }}</div>
        </div>
        <div>
          <div class="text-sm font-semibold">Disbursement Reference No.</div>
          <div class=" p-2">{{ summary.disbursement_no || '' }}</div>
        </div>
      </div>

      <div class="mt-6">
        <div class="text-sm font-semibold">Liquidation Ref No.</div>
        <div class="p-2">{{ summary.liquidation_ref || '' }}</div>
      </div>
      <div class="mt-4">
        <div class="text-xs text-gray-500 uppercase mb-2">Items</div>
        <div class="overflow-x-auto border rounded">
          <table class="min-w-full text-sm">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 py-2 text-left">Account Code</th>
                <th class="px-4 py-2 text-left">Frequency</th>
                <th class="px-4 py-2 text-left">Description</th>
                <th class="px-4 py-2 text-right">Qty</th>
                <th class="px-4 py-2 text-right">Unit Price</th>
                <th class="px-4 py-2 text-right">Amount</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(it, idx) in data.items" :key="idx" class="border-t">
                <td class="px-4 py-2">{{ it.account_code_title }}</td>
                <td class="px-4 py-2">{{ it.frequency_label }}</td>
                <td class="px-4 py-2">{{ it.description }}</td>
                <td class="px-4 py-2 text-right">{{ it.qty }}</td>
                <td class="px-4 py-2 text-right">{{ formatCurrency(it.unit_price) }}</td>
                <td class="px-4 py-2 text-right">{{ formatCurrency(it.amount) }}</td>
              </tr>
            </tbody>
            <tfoot>
              <tr class="border-t">
                <td colspan="5" class="px-4 py-2 text-right font-semibold">Total Amount:</td>
                <td class="px-4 py-2 text-right font-bold">₱{{ formatCurrency((data.items||[]).reduce((s, i) => s + (parseFloat(i.amount)||0), 0)) }}</td>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>
      <div class="mt-6">
        <div class="text-xs text-gray-500 uppercase mb-2">Liquidation Details</div>
        <div class="overflow-x-auto border rounded">
          <table class="min-w-full text-sm">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 py-2 text-left">Date</th>
                <th class="px-4 py-2 text-left">OR No</th>
                <th class="px-4 py-2 text-left">Description</th>
                <th class="px-4 py-2 text-right">Expense Amount</th>
                <th class="px-4 py-2 text-right">Input VAT</th>
                <th class="px-4 py-2 text-left">Status</th>
                <th class="px-4 py-2 text-left">Receipt</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="li in data.liquidation" :key="li.id" class="border-t">
                <td class="px-4 py-2">{{ formatDate(li.date) }}</td>
                <td class="px-4 py-2">{{ li.or_no || '—' }}</td>
                <td class="px-4 py-2">{{ li.description || '—' }}</td>
                <td class="px-4 py-2 text-right">₱{{ formatCurrency(li.expense_amount) }}</td>
                <td class="px-4 py-2 text-right">{{ formatCurrency(li.input_vat) }}</td>
                <td class="px-4 py-2">{{ li.vat_non_vat || '—' }}</td>
                <td class="px-4 py-2">
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
      <div class="mt-8 space-y-3">
        <div>
          <span class="font-semibold">Disbursed Fund Request:</span>
          <span class="ml-2">₱{{ formatAmount(summary.requested_total) }}</span>
        </div>
        <div>
          <span class="font-semibold">Liquidated Expenses:</span>
          <span class="ml-2">₱{{ formatAmount(summary.liquidated_total) }}</span>
        </div>
        <div v-if="reimbursementBalance > 0">   
          <span class="font-semibold">Balance for Reimbursement:</span>
          <span class="ml-2">₱{{ formatAmount(reimbursementBalance) }}</span>
        </div>

      </div>



      <div class=" mt-8 grid grid-cols-1 md:grid-cols-3 gap-6">
        <div>
         <div><span class="font-semibold">Approved by:</span> (Finance Staff)</div>
          <div><span class="font-semibold">Approved Date and Time:</span> N/A</div>
        </div>
        <div>
           <div><span class="font-semibold">Approved by:</span> (Finance Manager)</div>
          <div><span class="font-semibold">Approved Date and Time:</span> N/A</div>
        </div>
        <div>
           <div><span class="font-semibold">Approved by:</span> (CFO)</div>
          <div><span class="font-semibold">Approved Date and Time:</span> N/A</div>
        </div>
      </div>


    </CardBox>



    <Modal :show="showBreakdown" title="Liquidation Breakdown" @close="showBreakdown=false">
      <CoreTable :table-rows="breakdownItems" :table-header="breakdownHeader" table-name="liquidation-breakdown" />
      <div class="mt-4">Total: ₱{{ formatAmount(breakdownTotal) }}</div>
    </Modal>
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
  </FRMSLayout>
</template>

<script setup>
import FRMSLayout from '@/Layouts/FRMSLayout.vue'
import SectionTitleLineWithButton from '@/Components/SectionTitleLineWithButton.vue'
import BaseButtons from '@/Components/BaseButtons.vue'
import BaseButton from '@/Components/BaseButton.vue'
import CardBox from '@/Components/CardBox.vue'

  import CoreTable from '@/Components/CoreTable.vue'
  import { ref, computed, watch } from 'vue'
  import { router } from '@inertiajs/vue3'
  import Modal from '@/Components/Modal.vue'
  import axios from 'axios'

const props = defineProps({
  form: { type: Object, default: () => ({}) },
  summary: { type: Object, default: () => ({}) }
})

const statusText = computed(() => {
  const s = props.form?.status_request
  if (s === 'FA') return 'For Approval'
  if (s === 'FD') return 'For Disbursement'
  if (s === 'FL') return 'For Liquidation'
  if (s === 'A') return 'Approved'
  if (s === 'C') return 'Closed'
  if (s === 'X') return 'Canceled'
  return s || ''
})

const employeeName = computed(() => {
  const u = props.form?.user
  return u ? `${u.first_name ?? ''} ${u.last_name ?? ''}`.trim() : ''
})

const items = computed(() => Array.isArray(props.form?.items) ? props.form.items : [])
const itemsDisplay = computed(() => items.value.map(i => ({
  account_code_title: i.account_code_title,
  description: i.description,
  frequency: i.frequency?.description ?? '',
  qty: i.qty,
  unit_price_text: `₱${formatAmount(i.unit_price)}`,
  amount_text: `₱${formatAmount(i.amount)}`,
})))

const itemHeader = [
  { label: 'Account Code', fieldName: 'account_code_title' },
  { label: 'Description', fieldName: 'description' },
  { label: 'Frequency', fieldName: 'frequency' },
  { label: 'Qty', fieldName: 'qty' },
  { label: 'Unit Price', fieldName: 'unit_price_text', columnRowValueClass: 'font-semibold' },
  { label: 'Amount', fieldName: 'amount_text', columnRowValueClass: 'font-bold text-purple-700' }
]

const formatDate = (d) => {
  if (!d) return ''
  return new Date(d).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: '2-digit' })
}

const goBack = () => {
  router.visit(route('frms.review.index'))
}

const formatAmount = (val) => {
  const n = Number(val || 0)
  return n.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

  const formatCurrency = (val) => {
    if (!val) return '0.00'
    return parseFloat(val).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
  }

const getStatusText = (status) => {
  const t = { P:'Pending', O:'Open', A:'Approved', C:'Closed', X:'Canceled', R:'Rejected', FA:'For Approval', FD:'For Disbursement', FL:'For Liquidation' }
  return t[status] || 'Unknown'
}

  const showBreakdown = ref(false)
  const breakdownItems = ref([])
  const breakdownTotal = ref(0)
  const breakdownHeader = [
  { label: 'Description', fieldName: 'description' },
  { label: 'OR No.', fieldName: 'or_no' },
  { label: 'Amount', fieldName: 'amount' },
  { label: 'Expense Amount', fieldName: 'expense_amount' },
  { label: 'Variance', fieldName: 'variance' },
]

const openLiquidationRef = async () => {
  if (!props.form?.id) return
  try {
    const res = await fetch(route('frms.finance-disbursement.breakdown', { formId: props.form.id }))
    const data = await res.json()
    breakdownItems.value = Array.isArray(data.items) ? data.items : []
    breakdownTotal.value = Number(data.total_amount || 0)
    showBreakdown.value = true
  } catch (e) {
    showBreakdown.value = false
  }
}

  const uploadDocuments = () => {
    if (!props.form?.id) return
    router.visit(route('frms.documents.index', { frm_id: props.form.id }))
  }

  const loading = ref(false)
  const data = ref({ form: {}, items: [], liquidation: [], disbursement: null })
  const reimbursementBalance = computed(() => Math.abs(Number(props.summary?.variance_total || 0)))

const lightboxOpen = ref(false)
const lightboxImage = ref('')
const openLightbox = (url) => { lightboxImage.value = url; lightboxOpen.value = true }
const closeLightbox = () => { lightboxOpen.value = false; lightboxImage.value = ''; zoom.value = 1; translateX.value = 0; translateY.value = 0; dragging.value = false }
const zoom = ref(1)
const minZoom = 0.5
const maxZoom = 4
const onLightboxWheel = (e) => {
  const step = e.deltaY < 0 ? 0.1 : -0.1
  const next = zoom.value + step
  zoom.value = Math.min(maxZoom, Math.max(minZoom, next))
}
const translateX = ref(0)
const translateY = ref(0)
const dragging = ref(false)
const dragOriginX = ref(0)
const dragOriginY = ref(0)
const onDragStart = (e) => { dragging.value = true; dragOriginX.value = e.clientX - translateX.value; dragOriginY.value = e.clientY - translateY.value }
const onDragMove = (e) => { if (!dragging.value) return; translateX.value = e.clientX - dragOriginX.value; translateY.value = e.clientY - dragOriginY.value }
const onDragEnd = () => { dragging.value = false }
const onTouchStart = (e) => { const t = e.touches[0]; dragging.value = true; dragOriginX.value = t.clientX - translateX.value; dragOriginY.value = t.clientY - translateY.value }
const onTouchMove = (e) => { const t = e.touches[0]; if (!dragging.value) return; translateX.value = t.clientX - dragOriginX.value; translateY.value = t.clientY - dragOriginY.value }

watch(() => props.form, async (val) => {
  if (!val) return
  loading.value = true
  try {
    const id = val.id
    let url = `/frls/review/finance-detail/${id}`
    try { if (typeof route === 'function') { url = route('frls.review.finance-detail', id) } } catch(e) {}
    const res = await axios.get(url)
    if (res.data && res.data.success) {
      data.value = res.data
    }
  } catch(e) {
  } finally {
    loading.value = false
  }
}, { immediate: true })
</script>
