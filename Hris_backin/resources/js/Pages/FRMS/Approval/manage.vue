<script setup>
import { computed, ref, onMounted, watch } from 'vue'
import { useForm, router, usePage } from '@inertiajs/vue3'
import CardBox from '@/Components/CardBox.vue'
import FormField from '@/Components/FormField.vue'
import FormControl from '@/Components/FormControl.vue'
import BaseButton from '@/Components/BaseButton.vue'
import BaseButtons from '@/Components/BaseButtons.vue'
import SectionTitleLineWithButton from '@/Components/SectionTitleLineWithButton.vue'
import SwalConfirm from '@/Components/SwalConfirm.vue'
import FormWizard from '@/Components/FormWizard.vue'
import TabContent from '@/Components/TabContent.vue'
import Modal from '@/Components/Modal.vue'
import service from "@/Components/Toast/service";
import CoreTable from '@/Components/CoreTable.vue'
import BaseIcon from '@/Components/BaseIcon.vue'
import axios from 'axios'

let toast = service();

const props = defineProps({
  action: String,
  formdata: Object,
  approvalType: {
    type: String,
    default: 'disbursement'
  }
})
const page = usePage()
const emit = defineEmits(['triggerTopRightButton'])

// Reactive data
const openReferences = ref([])
const selectedReference = ref(null)
const formDetails = ref(null)
const isLoadingReferences = ref(false)
const isLoadingFormDetails = ref(false)
const inputVatCode = ref('')
const inputVatAmount = ref(0)
const showModal = ref(false)
const editingIndex = ref(null)
const vatOptions = ref([])
const isLoadingVatOptions = ref(false)
const frequencies = ref([])

const documentList = ref([])
const isLoadingDocuments = ref(false)
const imageDocuments = ref([])

// New item form data
const newItem = ref({
  date: '',
  or_no: '',
  plate_no: '',
  account_code_title: '',
  particulars: '',
  supplier_name: '',
  tin: '',
  address: '',
  location_client: '',
  gross_amount: 0,
  vat_non_vat: '',
  expense_amount: 0,
  input_vat: 0,
  code: '',
  accountable_person: '',
  validated_by_accounting: false,
  manual_journal_no: ''
})

const form = useForm({
  _token: page.props.csrf_token,
  id: props.formdata?.id || null,
  branch_id: props.formdata?.branch_id || null,
  ref_num: props.formdata?.ref_num || '',
  form_id: null,
  items: props.formdata?.items || []
})

const approvalForm = ref(null)
const approvalItems = ref([])
const disbursementInfo = ref(null)

// Load open references on component mount
onMounted(async () => {
  try {
    await loadFrequencies()
    await frmsAccountCode()
  } catch (e) {
    console.error('Error in onMounted:', e)
  }
  if (props.formdata?.id) {
    if (props.approvalType === 'liquidation') {
      await loadLiquidationDetails(props.formdata.id)
    } else {
      await loadApprovalDetails(props.formdata.id)
    }
  }
})

const loadApprovalDetails = async (id) => {
  try {
    const response = await fetch(route('frls.approval.details', { id }))
    const data = await response.json()
    if (data.success) {
      approvalForm.value = data.form
      approvalItems.value = Array.isArray(data.items) ? data.items : []
    }
  } catch (error) {
    console.error('Error loading approval details:', error)
  }
}

const loadLiquidationDetails = async (formId) => {
  try {
    const response = await fetch(route('frls.approval.liquidation-details', { formId }))
    const data = await response.json()
    if (data.success) {
      approvalForm.value = data.form
      disbursementInfo.value = data.disbursement || null
      approvalItems.value = Array.isArray(data.items) ? data.items : []

      // Debug log
      console.log('Loaded disbursement info:', disbursementInfo.value)
    }
  } catch (error) {
    console.error('Error loading liquidation details:', error)
  }
}

const loadFrequencies = async () => {
  try {
    const response = await fetch('/frls/transaction-code/frls-frequincy', {
      headers: {
        'Accept': 'application/json'
      }
    })
    const data = await response.json()
    if (data.success && Array.isArray(data.data)) {
      frequencies.value = data.data.map(type => ({
        id: type.id,
        label: type.description
      }))
    }
  } catch (error) {
    console.error('Error fetching frequency types:', error)
  }
}

const frequencyLabelById = (value) => {
  if (value && typeof value === 'object') {
    return value.description ?? value.label ?? ''
  }
  const match = frequencies.value.find(f => f.id == value)
  return match?.label || (value ?? '')
}

const displayApprovalItems = computed(() => {
  return (approvalItems.value || []).map(item => ({
    ...item,
    frequency_description: frequencyLabelById(item.frequency ?? item.frequency_description),
    unit_price_display: `₱${formatCurrency(item.unit_price)}`,
    amount_display: `₱${formatCurrency(item.amount)}`
  }))
})

const displayLiquidationItems = computed(() => {
  return (approvalItems.value || []).map(item => ({
    ...item,
    amount_display: `₱${formatCurrency(item.amount)}`,
    expense_amount_display: `₱${formatCurrency(item.expense_amount)}`,
    input_vat_display: `₱${formatCurrency(item.input_vat)}`,
    documents_count: Array.isArray(item.documents) ? item.documents.length : 0,
  }))
})

const requestedItemsHeader = [
  { label: 'Account Code Title', fieldName: 'account_code_title' },
  { label: 'Description', fieldName: 'description' },
  { label: 'Frequency', fieldName: 'frequency_description' },
  { label: 'Qty', fieldName: 'qty' },
  { label: 'Unit Price', fieldName: 'unit_price_display' },
  { label: 'Amount', fieldName: 'amount_display' },
  { label: 'Remarks', fieldName: 'remarks' }
]

const liquidationItemsHeader = [
  { label: 'Ref No', fieldName: 'ref_num' },
  { label: 'OR No', fieldName: 'or_no' },
  { label: 'Description', fieldName: 'description' },
  { label: 'Variance', fieldName: 'variance' },
  { label: 'Reason', fieldName: 'reason' },
  { label: 'VAT', fieldName: 'vat_non_vat' },
  { label: 'Amount', fieldName: 'amount_display' },
  { label: 'Expense Amount', fieldName: 'expense_amount_display' },
  { label: 'Input VAT', fieldName: 'input_vat_display' },
  { label: 'Docs', fieldName: 'documents_count' },
]

const totalRequestedAmount = computed(() => {
  return (approvalItems.value || []).reduce((sum, item) => {
    return sum + (parseFloat(item.amount) || 0)
  }, 0)
})

const totalLiquidationAmount = computed(() => {
  return (approvalItems.value || []).reduce((sum, item) => {
    return sum + (parseFloat(item.expense_amount) || 0)
  }, 0)
})

const loadOpenReferences = async () => {
  isLoadingReferences.value = true
  try {
    const response = await fetch(route('frls.liquidation-expenses.open-references'))
    const data = await response.json()
    openReferences.value = data.map(ref => ({
      value: ref.ref_num,
      label: `${ref.ref_num}`,
      form_id: ref.form_id,
      amount: ref.amount
    }))
  } catch (error) {
    console.error('Error loading open references:', error)
    toast.error('Failed to load open references')
  } finally {
    isLoadingReferences.value = false
  }
}

const loadVatOptions = async () => {
  isLoadingVatOptions.value = true
  try {
    const response = await fetch(route('frls.trancsaction-codes.frls-vat'))
    const data = await response.json()
    if (data.success) {
      vatOptions.value = data.data.map(vat => ({
        value: vat.trans_value,
        label: vat.description,
      }))
    }
  } catch (error) {
    console.error('Error loading VAT options:', error)
    toast.error('Failed to load VAT options')
    vatOptions.value = []
  } finally {
    isLoadingVatOptions.value = false
  }
}

const loadFormDetails = async (formId) => {
  if (!formId) return

  isLoadingFormDetails.value = true
  try {
    const response = await fetch(route('frls.liquidation-expenses.form-details', { formId }))
    const data = await response.json()
    formDetails.value = data
  } catch (error) {
    console.error('Error loading Liquidation Expenses details:', error)
    toast.error('Failed to load Liquidation Expenses details')
  } finally {
    isLoadingFormDetails.value = false
  }
}

// Load documents for the selected form
const loadDocuments = async (formId) => {
  if (!formId) {
    documentList.value = []
    imageDocuments.value = []
    return
  }

  isLoadingDocuments.value = true
  try {
    console.log('Loading documents for form ID:', formId)
    const response = await fetch(route('frls.documents.api', { frm_id: formId }), {
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      }
    })

    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`)
    }

    const data = await response.json()
    console.log('Documents API response:', data)

    if (data.success && Array.isArray(data.documents)) {
      documentList.value = data.documents.map(doc => ({
        ...doc,
        file_size: doc.file_size || 0,
        file_size_human: doc.file_size_human || ((doc.file_size || 0) / 1024).toFixed(1) + ' KB'
      }))
    } else {
      console.warn('Unexpected API response structure:', data)
      documentList.value = []
    }

    imageDocuments.value = documentList.value.filter(doc => isImage(doc.original_filename))

  } catch (error) {
    console.error('Error loading documents:', error)
    toast.error('Failed to load documents')
    documentList.value = []
    imageDocuments.value = []
  } finally {
    isLoadingDocuments.value = false
  }
}

// Check if file is an image
const isImage = (fileName) => {
  if (!fileName) return false
  const imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'svg']
  const extension = fileName.split('.').pop().toLowerCase()
  return imageExtensions.includes(extension)
}

// Get file icon based on file type
const getFileIcon = (fileName) => {
  const extension = fileName.split('.').pop().toLowerCase()

  switch (extension) {
    case 'pdf':
      return 'mdiFilePdfBox'
    case 'doc':
    case 'docx':
      return 'mdiFileWordBox'
    case 'xls':
    case 'xlsx':
      return 'mdiFileExcelBox'
    case 'ppt':
    case 'pptx':
      return 'mdiFilePowerpointBox'
    case 'txt':
      return 'mdiFileDocumentOutline'
    case 'zip':
    case 'rar':
    case '7z':
      return 'mdiFileArchive'
    default:
      return 'mdiFileOutline'
  }
}

// Get file icon color
const getFileIconColor = (fileName) => {
  const extension = fileName.split('.').pop().toLowerCase()

  switch (extension) {
    case 'pdf':
      return 'text-red-500'
    case 'doc':
    case 'docx':
      return 'text-blue-500'
    case 'xls':
    case 'xlsx':
      return 'text-green-500'
    case 'ppt':
    case 'pptx':
      return 'text-orange-500'
    case 'txt':
      return 'text-gray-500'
    case 'zip':
    case 'rar':
    case '7z':
      return 'text-purple-500'
    default:
      return 'text-gray-400'
  }
}

// View image in lightbox
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
const viewImage = (document) => {
  if (!isImage(document.original_filename)) return
  openLightbox(document.file_url)
}

// Download document
const downloadDocument = (document) => {
  window.open(route('frls.documents.download', { id: document.id }), '_blank')
}

// Method to handle VAT type change
const onVatTypeChange = () => {
  const selectedVatType = newItem.value.vat_non_vat?.value ?? newItem.value.vat_non_vat
  calculateExpenseAmount()
}

// Watch for reference selection changes
watch(selectedReference, async (newRef) => {
  if (newRef) {
    form.ref_num = newRef.label
    form.form_id = newRef.form_id
    console.log('Loading details for form ID:', newRef.form_id)

    await Promise.all([
      loadFormDetails(newRef.form_id),
      loadDocuments(newRef.form_id)
    ])
  } else {
    form.ref_num = ''
    form.form_id = null
    formDetails.value = null
    documentList.value = []
    imageDocuments.value = []
  }
}, { immediate: true })

watch(() => props.formdata, async (newvalue, oldvalue) => {
  if (newvalue != oldvalue && newvalue) {
    for (let key in newvalue) {
      if (newvalue.hasOwnProperty(key)) {
        form[key] = newvalue[key]
      }
    }
    if (newvalue.id) {
      if (props.approvalType === 'liquidation') {
        await loadLiquidationDetails(newvalue.id)
      } else {
        await loadApprovalDetails(newvalue.id)
      }
    }
  }
})

// Computed property for total expenses
const totalExpenses = computed(() => {
  return form.items.reduce((sum, item) => sum + (parseFloat(item.expense_amount) || 0), 0)
})

// Modal functions
const closeModal = () => {
  showModal.value = false
  editingIndex.value = null
  resetNewItem()
}

const resetNewItem = () => {
  newItem.value = {
    date: '',
    or_no: '',
    plate_no: '',
    account_code_title: '',
    particulars: '',
    supplier_name: '',
    tin: '',
    address: '',
    location_client: '',
    gross_amount: 0,
    vat_non_vat: '',
    expense_amount: 0,
    input_vat: 0,
    code: '',
    accountable_person: '',
    validated_by_accounting: false,
    manual_journal_no: ''
  }
}

const accountCodes = ref([])
const selectedAccountCodeType = ref(null)

const frmsAccountCode = async () => {
  try {
    const response = await axios.get('/frls/transaction-code/frls-account-code')
    if (response.data.success) {
      accountCodes.value = response.data.data.map(type => ({
        id: type.identitycode + ' - ' + type.description,
        label: type.identitycode + ' - ' + type.description
      }))

      if (form.account_code_title) {
        const match = accountCodes.value.find(t => t.id == form.account_code_title)
        if (match) {
          selectedAccountCodeType.value = match
        }
      }
    }
  } catch (error) {
    console.error('Error fetching transaction types:', error)
  }
}

const addExpenseToTable = () => {
  if (!newItem.value.supplier_name || !newItem.value.expense_amount) {
    toast.error('Please fill in required fields: Supplier and Expense Amount')
    return
  }

  if (editingIndex.value !== null) {
    form.items[editingIndex.value] = { ...newItem.value }
    toast.success('Expense updated successfully')
  } else {
    form.items.push({ ...newItem.value })
    toast.success('Expense added successfully')
  }

  closeModal()
}

const finishExpenses = () => {
  if (form.items.length === 0) {
    toast.error('Please add at least one expense before finishing')
    return
  }

  router.post(route('frls.liquidation-expenses.store'), {
    ref_num: form.ref_num,
    form_id: form.form_id,
    items: form.items
  }, {
    preserveScroll: true,
    onSuccess: () => {
      emit('triggerTopRightButton', 'lists')
      toast.success('Expenses saved successfully!')
    },
    onError: (errors) => {
      console.error('Save errors:', errors)
      toast.error('Failed to save expenses. Please try again.')
    }
  })
}

// Calculate expense amount based on VAT type
const calculateExpenseAmount = () => {
  try {
    const grossAmount = parseFloat(newItem.value.gross_amount) || 0
    const selectedVatType = newItem.value.vat_non_vat?.value ?? newItem.value.vat_non_vat

    if (grossAmount <= 0) {
      newItem.value.expense_amount = '0.00'
      newItem.value.input_vat = '0.00'
      return
    }

    if (selectedVatType === undefined || selectedVatType === null || selectedVatType === '') {
      newItem.value.expense_amount = grossAmount.toFixed(2)
      newItem.value.input_vat = '0.00'
      return
    }

    let expenseAmount

    if (selectedVatType > 0) {
      expenseAmount = grossAmount / selectedVatType
    } else {
      expenseAmount = grossAmount
    }

    newItem.value.expense_amount = Math.max(0, expenseAmount).toFixed(2)

    const calculatedExpense = parseFloat(newItem.value.expense_amount) || 0
    newItem.value.input_vat = Math.max(0, grossAmount - calculatedExpense).toFixed(2)

  } catch (error) {
    console.error('Error calculating expense amount:', error)
    newItem.value.expense_amount = '0.00'
    newItem.value.input_vat = '0.00'
  }
}

// Watchers
watch(() => newItem.value.gross_amount, (newGrossAmount) => {
  if ((newGrossAmount || newGrossAmount === 0) && newItem.value.vat_non_vat) {
    calculateExpenseAmount()
  }
})

watch(() => newItem.value.vat_non_vat, (newVatType) => {
  if (newVatType && (newItem.value.gross_amount || newItem.value.gross_amount === 0)) {
    calculateExpenseAmount()
  }
}, { deep: true })

watch(() => selectedAccountCodeType.value, (newAccountCode) => {
  if (newAccountCode && newAccountCode.id) {
    const codeMatch = newAccountCode.id.match(/^(\d{3})/)
    if (codeMatch) {
      newItem.value.code = codeMatch[1]
      newItem.value.account_code_title = newAccountCode.id
    } else {
      newItem.value.code = ''
    }
  } else {
    newItem.value.code = ''
    newItem.value.account_code_title = ''
  }
})

const formatCurrency = (amount) => {
  if (!amount) return '0.00'
  return parseFloat(amount).toFixed(2)
}

const formatDate = (dateString) => {
  if (!dateString) return ''
  const date = new Date(dateString)
  return date.toLocaleDateString('en-GB')
}

const editItem = (index) => {
  newItem.value = { ...form.items[index] }
  editingIndex.value = index
  showModal.value = true
  toast.info('Item loaded for editing')
}

const removeItem = (index) => {
  form.items.splice(index, 1)
  toast.success('Item removed successfully')
}

// Rejection functionality
const rejectRemarks = ref('')
const isRejectModalOpen = ref(false)
const isRejectConfirmOpen = ref(false)
const isConfirmOpen = ref(false)
const remarksMax = 250
const rejectRemaining = computed(() => {
  const len = (rejectRemarks.value || '').length
  return Math.max(0, remarksMax - len)
})
watch(rejectRemarks, (val) => {
  if (typeof val === 'string' && val.length > remarksMax) {
    rejectRemarks.value = val.slice(0, remarksMax)
  }
})

const submit = () => { isConfirmOpen.value = true }

const openRejectModal = () => {
  rejectRemarks.value = ''
  isRejectModalOpen.value = true
}

const closeRejectModal = () => {
  isRejectModalOpen.value = false
}

const submitReject = () => {
  if (!(rejectRemarks.value || '').trim()) {
    toast.error('Please enter remarks for rejection')
    return
  }
  isRejectModalOpen.value = false
  isRejectConfirmOpen.value = true
}

// Fixed reject function
const onConfirmReject = async () => {
  try {
    if (!(rejectRemarks.value || '').trim()) {
      toast.error('Please enter remarks for rejection')
      isRejectConfirmOpen.value = false
      return
    }
    let payload = {
      remarks: rejectRemarks.value
    }

    // Correct logic for determining what to reject
    if (props.approvalType === 'disbursement') {
      // For disbursement approval, we're rejecting the FORM
      payload.documentId = props.formdata?.id
      payload.aliase = 'form'
    } else if (props.approvalType === 'liquidation') {
      // For liquidation approval, we're rejecting the DISBURSEMENT
      if (!disbursementInfo.value?.id) {
        toast.error('Disbursement information missing')
        return
      }
      payload.documentId = disbursementInfo.value.id
      payload.aliase = 'disbursement'
    } else {
      toast.error('Invalid approval type')
      return
    }

    if (!payload.documentId) {
      toast.error('Missing ID for rejection')
      return
    }

    console.log('Sending reject payload:', payload)

    await router.post(route('frls.approval.reject'), payload, {
      preserveScroll: true,
      onSuccess: () => {
        toast.success('Request rejected successfully')
        emit('triggerTopRightButton', 'lists')
      },
      onError: (errors) => {
        console.error('Reject error:', errors)
        toast.error('Failed to reject request')
      }
    })
  } catch (error) {
    console.error('Reject error:', error)
    toast.error('Failed to reject request')
  }
}

const formErrors = ref([])
const onConfirmSubmit = async () => {
  try {
    form.id = props.formdata?.id
    form.branch_id = props.formdata?.branch_id
    if (!form.id || !form.branch_id) {
      toast.error('Missing form or branch information')
      return
    }

    await new Promise((resolve, reject) => {
      form.post(route('frls.approval.finish'), {
        preserveScroll: true,
        onSuccess: () => resolve(),
        onError: (errors) => {
          formErrors.value = Object.values(errors || {})
          console.error('Update status error:', errors)
          reject(errors)
        }
      })
    })

    toast.success('Request Fund Approved successfully!')
    emit('triggerTopRightButton', 'lists')
  } catch (error) {
    toast.error('Failed to update status. Please try again.')
  }
}

const onCancelSubmit = () => {
  isConfirmOpen.value = false
}
</script>

<template>
  <SectionTitleLineWithButton
    :icon="'mdiClipboardText'"
    :title="(props.action === 'Add' ? 'Add' : 'Manage') + ' Approval - ' + (props.approvalType === 'liquidation' ? 'Liquidation' : 'Disbursement')"
    main>
    <div class="flex gap-2">
      <BaseButton
        @click="emit('triggerTopRightButton', 'lists')"
        :icon="'mdiViewList'"
        label="Back to List"
        color="contrast"
        rounded-full
        small
      />
    </div>
  </SectionTitleLineWithButton>



  <CardBox>
    <FormWizard
      @on-complete="submit"
      @on-reject="openRejectModal"
      :show-finish-dropdown="true"
      color="#094899"
      step-size="xs"
    >
      <TabContent title="Form Details" icon="mdiFileDocument">
        <div class="space-y-6">
          <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
              <div>
                <span class="font-medium text-gray-700 dark:text-gray-300">Form Number:</span>
                <span class="ml-2 text-gray-900 dark:text-gray-100">{{ approvalForm?.frm_no }}</span>
              </div>
              <div>
                <span class="font-medium text-gray-700 dark:text-gray-300">Request Date:</span>
                <span class="ml-2 text-gray-900 dark:text-gray-100">{{ approvalForm?.request_date }}</span>
              </div>
              <div>
                <span class="font-medium text-gray-700 dark:text-gray-300">Expected Liquidation Date:</span>
                <span class="ml-2 text-gray-900 dark:text-gray-100">{{ approvalForm?.expectedliquidation_date }}</span>
              </div>
              <div>
                <span class="font-medium text-gray-700 dark:text-gray-300">Purpose:</span>
                <span class="ml-2 text-gray-900 dark:text-gray-100">{{ approvalForm?.purpose }}</span>
              </div>
              <div>
                <span class="font-medium text-gray-700 dark:text-gray-300">Created By:</span>
                <span class="ml-2 text-gray-900 dark:text-gray-100">{{ approvalForm?.user_name }}</span>
              </div>
              <div>
                <span class="font-medium text-gray-700 dark:text-gray-300">Branch:</span>
                <span class="ml-2 text-gray-900 dark:text-gray-100">{{ approvalForm?.branch_name }}</span>
              </div>
              <div>
                <span class="font-medium text-gray-700 dark:text-gray-300">Status:</span>
                <span class="ml-2 text-gray-900 dark:text-gray-100">{{ approvalForm?.status_request }}</span>
              </div>
            </div>
            <div class="mt-6">
              <div class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Quotation</div>
              <div v-if="approvalForm?.quotation" class="flex items-center gap-4">
                <img
                  v-if="isImage(approvalForm.quotation)"
                  :src="'/storage/' + approvalForm.quotation"
                  alt="Quotation"
                  class="h-32 w-auto object-contain rounded border border-gray-200 cursor-pointer"
                  @click="openLightbox('/storage/' + approvalForm.quotation)"
                />
                <div v-else class="flex items-center gap-2">
                  <BaseIcon :path="getFileIcon(approvalForm.quotation)" class="text-2xl" />
                  <a
                    :href="'/storage/' + approvalForm.quotation"
                    target="_blank"
                    class="text-blue-600 hover:underline text-sm"
                  >
                    View Attachment
                  </a>
                </div>
              </div>
              <div v-else class="text-sm text-gray-500 dark:text-gray-400">No quotation uploaded.</div>
            </div>
          </div>
        </div>
      </TabContent>

      <TabContent title="Requested Items" icon="mdiFormatListBulleted">
        <div class="space-y-6">
          <template v-if="props.approvalType === 'disbursement'">
            <div v-if="displayApprovalItems.length > 0" class="overflow-x-auto">
              <CoreTable
                :table-rows="displayApprovalItems"
                :table-header="requestedItemsHeader"
                table-name="approval-requested-items"
                :is-paginated="false"
              />
              <div class="flex justify-end mt-3">
                <div class="text-sm font-semibold text-gray-700 dark:text-gray-300">
                  Total Amount: ₱{{ formatCurrency(totalRequestedAmount) }}
                </div>
              </div>
            </div>
            <div v-else class="text-center py-8 text-gray-500 dark:text-gray-400">
              <p>No items found.</p>
            </div>
          </template>

          <template v-else>
            <div v-if="displayLiquidationItems.length > 0" class="overflow-x-auto mt-4">
              <CoreTable
                :table-rows="displayLiquidationItems"
                :table-header="liquidationItemsHeader"
                table-name="approval-liquidation-items"
                :is-paginated="false"
              />
              <div class="mt-6">
                <div v-for="item in approvalItems" :key="item.id" class="mb-6">
                  <div class="text-sm font-semibold text-gray-700 dark:text-gray-300">
                    Documents for Ref No {{ item.ref_num || item.id }}
                    <span v-if="!(item.documents && item.documents.length)" class="font-normal text-gray-500 dark:text-gray-400">None</span>
                  </div>
                  <div v-if="item.documents && item.documents.length" class="mt-2 grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 gap-3">
                    <div v-for="doc in item.documents" :key="doc.id" class="group relative border rounded-md p-2 bg-white dark:bg-gray-900 dark:border-gray-700">
                      <img
                        v-if="isImage(doc.original_filename)"
                        :src="doc.file_url"
                        :alt="doc.original_filename"
                        class="w-full h-24 object-cover rounded cursor-pointer"
                        @click="viewImage(doc)"
                      />
                      <div v-else class="flex items-center gap-2">
                        <BaseIcon
                          :path="getFileIcon(doc.original_filename)"
                          class="text-2xl"
                          :class="getFileIconColor(doc.original_filename)"
                        />
                        <a
                          :href="route('frls.documents.download', { id: doc.id })"
                          class="text-sm text-blue-600 hover:underline truncate"
                          :title="doc.original_filename"
                          target="_blank"
                        >
                          {{ doc.original_filename }}
                        </a>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="flex justify-end mt-3">
                <div class="text-sm font-semibold text-gray-700 dark:text-gray-300">
                  Total Expense: ₱{{ formatCurrency(totalLiquidationAmount) }}
                </div>
              </div>
            </div>
            <div v-else class="text-center py-8 text-gray-500 dark:text-gray-400">
              <p>No liquidation items found.</p>
            </div>
          </template>
        </div>
      </TabContent>
    </FormWizard>

    <SwalConfirm
      v-model="isConfirmOpen"
      type="warning"
      title="Are you sure?"
      text="Once submitted, you cannot undo this action!"
      confirmText="Submit"
      cancelText="Cancel"
      @confirm="onConfirmSubmit"
      @cancel="onCancelSubmit"
    />

    <div v-if="lightboxOpen" class="fixed inset-0 z-50 bg-black/80 select-none" @click.self="closeLightbox">
      <button
        type="button"
        class="absolute top-4 right-4 bg-white/20 hover:bg-white/30 text-white rounded-full h-10 w-10 flex items-center justify-center text-xl"
        @click="closeLightbox"
      >
        ✕
      </button>
      <div class="w-full h-full flex items-center justify-center" @wheel.prevent="onLightboxWheel">
        <img
          :src="lightboxImage"
          alt=""
          class="object-contain max-w-none"
          :style="{
            transform: `translate(${translateX}px, ${translateY}px) scale(${zoom})`,
            cursor: dragging ? 'grabbing' : 'grab'
          }"
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

    <Modal :show="isRejectModalOpen" @close="closeRejectModal">
      <div class="p-6">
         <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
             Reject Request
         </h2>
        <FormField :label="'Remarks'" :help="`Available characters: ${rejectRemaining}/250`">
          <FormControl
            v-model="rejectRemarks"
            type="textarea"
            placeholder="Enter remarks..."
            rows="4"
            :max="remarksMax"
          />
        </FormField>
         <div class="mt-6 flex justify-end gap-2">
           <BaseButton label="Cancel" color="contrast" @click="closeRejectModal" />
           <BaseButton label="Reject" color="danger" :disabled="!(rejectRemarks || '').trim()" @click="submitReject" />
         </div>
      </div>
    </Modal>

    <SwalConfirm
      v-model="isRejectConfirmOpen"
      type="warning"
      title="Confirm Rejection"
      text="Are you sure you want to reject this request?"
      confirmText="Yes, Reject"
      cancelText="Cancel"
      @confirm="onConfirmReject"
      @cancel="() => isRejectConfirmOpen = false"
    />
  </CardBox>
</template>
