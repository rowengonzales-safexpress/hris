<script setup>
import { useForm, router, usePage } from '@inertiajs/vue3'
import { watch, ref, computed, onMounted } from 'vue'
import CardBox from '@/Components/CardBox.vue'
import FormField from '@/Components/FormField.vue'
import FormControl from '@/Components/FormControl.vue'
import BaseButton from '@/Components/BaseButton.vue'
import SectionTitleLineWithButton from '@/Components/SectionTitleLineWithButton.vue'
import SwalConfirm from '@/Components/SwalConfirm.vue'
import axios from 'axios'
import FormWizard from '@/Components/FormWizard.vue'
import TabContent from '@/Components/TabContent.vue'
import CoreTable from '@/Components/CoreTable.vue'
import service from '@/Components/Toast/service'
import Modal from '@/Components/Modal.vue'

const props = defineProps({
  action: String,
  formdata: Object,
  forms: Array
})

const emit = defineEmits(['triggerTopRightButton'])

const page = usePage()
const currentUser = page.props.auth?.user

const form = useForm({
  _token: page.props.csrf_token,
  id: props.formdata?.id || null,
  form_id: props.formdata?.form_id || null,
  location: props.formdata?.location || '',
  description: props.formdata?.description || '',
  expected_liquidation: props.formdata?.expected_liquidation || '',
  ref_num: props.formdata?.ref_num || '',
  debit: props.formdata?.debit || '',
  transaction_type: props.formdata?.transaction_type || '',
  amount: props.formdata?.amount || '',
  original_receipts: props.formdata?.original_receipts || '',
  frequency: props.formdata?.frequency || ''
})

// Transaction Type dropdown data
const transactionTypes = ref([])
const selectedTransactionType = ref(null)

// Frequency dropdown data
const frequencies = ref([])
const selectedFrequencyType = ref(null)

// Searchable Form dropdown
const formOptions = computed(() => (props.forms ?? []).map(f => ({
  id: f.id,
  label: `${f.frm_no}`
})))
const filteredFormOptions = computed(() => formOptions.value)
const selectedForm = ref(null)

// Reactive fields for user and location based on selected form
const selectedUser = ref('')
const selectedLocation = ref('')
const selectedDescription = ref('')
const selectedExpectedLiquidation = ref('')
const selectedTotalAmount = ref('')
const vatOptions = ref([])
const isLoadingVatOptions = ref(false)

const breakdownTableHeader = [
  { label: 'Account Code', fieldName: 'account_code_title' },
{ label: 'Frequency', fieldName: 'frequency' },
  { label: 'Description', fieldName: 'description' },
  { label: 'Qty', fieldName: 'qty' },
  { label: 'Unit Price', fieldName: 'unit_price' },
  { label: 'Amount', fieldName: 'amount' }
]

const breakdownRows = ref([])

const liquidationTableHeader = [
     { label: 'Description', fieldName: 'particulars' },
  { label: 'Actual Ammount', fieldName: 'actual_amount' },
  { label: 'Variance', fieldName: 'variance' },
  { label: 'OR.', fieldName: 'or_no' },
  { label: 'Vat/Non-Vat', fieldName: 'vat_non_vat' },
  { label: 'Expense Amount', fieldName: 'expense_amount' },
  { label: 'Vatable', fieldName: 'vatcode'},
  { label: 'Vat', fieldName: 'input_vat' },
  { label: 'Tin', fieldName: 'tin' },
   { label: 'Address', fieldName: 'address' },
   { label: 'Attach Document', fieldName: 'support_document_html', type: 'html' },
     { label: 'Reason', fieldName: 'reason' },
  { label: 'Action', fieldName: 'action', type: 'slot' }
]
const liquidationRows = ref([])
let toast = service()
const formErrors = ref([])

const isLiquidationModalOpen = ref(false)
const selectedLiquidationDescription = ref(null)
const selectedTaxType = ref(null)
const selectedBreakdownAmount = ref('')

const editIndex = ref(null)
const liquidationDescriptionOptions = computed(() => {
  const used = new Set((liquidationRows.value ?? []).map(r => r.particulars))
  if (editIndex.value !== null && liquidationRows.value[editIndex.value]) {
    used.delete(liquidationRows.value[editIndex.value].particulars)
  }
  return (breakdownRows.value ?? [])
    .filter(item => (item.description || '') && !used.has(item.description))
    .map(item => ({ id: item.id, label: item.description }))
})
const liquidationForm = ref({
  date: new Date().toISOString().slice(0, 10),
  actual_amount: '',
  variance: '',
  reason: '',
  or_no: '',
  tin: '',
  vat_non_vat: '',
  vatcode: '',
  expense_amount: '',
  input_vat: '',
  address: '',
  support_document: null,
  support_document_preview_url: null
})
const fileInput = ref(null)
const isDragging = ref(false)
const onSupportDocChange = (e) => {
  const f = e?.target?.files?.[0] || null
  if (liquidationForm.value.support_document_preview_url) {
    URL.revokeObjectURL(liquidationForm.value.support_document_preview_url)
  }
  if (f && f.type && f.type.startsWith('image/')) {
    liquidationForm.value.support_document = f
    liquidationForm.value.support_document_preview_url = URL.createObjectURL(f)
  } else {
    liquidationForm.value.support_document = null
    liquidationForm.value.support_document_preview_url = null
  }
}
const onSupportDocDrop = (e) => {
  const f = e?.dataTransfer?.files?.[0] || null
  isDragging.value = false
  if (liquidationForm.value.support_document_preview_url) {
    URL.revokeObjectURL(liquidationForm.value.support_document_preview_url)
  }
  if (f && f.type && f.type.startsWith('image/')) {
    liquidationForm.value.support_document = f
    liquidationForm.value.support_document_preview_url = URL.createObjectURL(f)
  }
}
const openFilePicker = () => {
  fileInput.value?.click()
}
const clearSupportDoc = () => {
  if (liquidationForm.value.support_document_preview_url) {
    URL.revokeObjectURL(liquidationForm.value.support_document_preview_url)
  }
  liquidationForm.value.support_document = null
  liquidationForm.value.support_document_preview_url = null
  if (fileInput.value) fileInput.value.value = ''
}
const liquidationRowsDisplay = computed(() => {
  return (liquidationRows.value || []).map(r => {
    const html = r.support_document_preview_url
      ? `<div class="flex items-center gap-2"><img src="${r.support_document_preview_url}" alt="" class="h-10 w-10 object-cover rounded"/></div>`
      : (r.support_document_name || '')
    return { ...r, support_document_html: html }
  })
})
const canFinish = computed(() => (liquidationRows.value || []).length > 0)
const recomputeVariance = () => {
  const alloc = parseFloat(selectedBreakdownAmount.value || 0)
  const actual = parseFloat(liquidationForm.value.actual_amount || 0)
  const v = alloc - actual
  liquidationForm.value.variance = isNaN(v) ? '' : v.toFixed(2)
}
const updateBreakdownAmount = (val) => {
  let item = null
  if (val && typeof val === 'object') {
    item = (breakdownRows.value || []).find(b => b.id == val.id) || (breakdownRows.value || []).find(b => b.description === val.label)
  } else if (val) {
    item = (breakdownRows.value || []).find(b => b.id == val)
    if (!item) item = (breakdownRows.value || []).find(b => b.description === val)
  }
  selectedBreakdownAmount.value = item?.amount ?? ''
  recomputeVariance()
}
const addLiquidationItem = () => {
  const val = selectedLiquidationDescription.value
  let item = null
  if (val && typeof val === 'object') {
    item = (breakdownRows.value || []).find(b => b.id == val.id)
  } else if (val) {
    item = (breakdownRows.value || []).find(b => b.id == val)
  }

  const description = item ? item.description : (val?.label || val || '')
  const frmslist_id = item ? item.id : (val?.id || null)
  const selectedVat = liquidationForm.value.vat_non_vat
  const selectedVatValue = (selectedVat && typeof selectedVat === 'object') ? selectedVat.value : selectedVat
  const vatLabel =
    ((vatOptions.value || []).find(v => String(v.value) === String(selectedVatValue))?.label) ||
    (typeof selectedVat === 'string' ? selectedVat : '')

  const row = {
    id: editIndex.value !== null ? (liquidationRows.value[editIndex.value]?.id ?? null) : null,
    actual_amount: parseFloat(liquidationForm.value.actual_amount || 0),
    particulars: description,
    expense_amount: parseFloat(liquidationForm.value.expense_amount || 0),
    variance: liquidationForm.value.variance || '',
    reason: liquidationForm.value.reason || '',
    or_no: liquidationForm.value.or_no || '',
    vat_non_vat: selectedVatValue || '',
    vatcode: vatLabel,
    input_vat: parseFloat(liquidationForm.value.input_vat || 0),
    tin: liquidationForm.value.tin || '',
    address: liquidationForm.value.address || '',
    support_document_name: liquidationForm.value.support_document?.name || '',
    support_document: liquidationForm.value.support_document || null,
    support_document_preview_url: liquidationForm.value.support_document_preview_url || null,
    frmslist_id: frmslist_id,
    description: description,
    amount: parseFloat(liquidationForm.value.actual_amount || 0),
    ref_num: form.ref_num || ''
  }

  if (editIndex.value !== null) {
    liquidationRows.value.splice(editIndex.value, 1, row)
    editIndex.value = null
    isLiquidationModalOpen.value = false
  } else {
    liquidationRows.value = [...liquidationRows.value, row]
    selectedLiquidationDescription.value = null
    selectedTaxType.value = null
    liquidationForm.value = {
      date: new Date().toISOString().slice(0, 10),
      actual_amount: '',
      variance: '',
      reason: '',
      or_no: '',
      tin: '',
      vat_non_vat: '',
      vatcode: '',
      expense_amount: '',
      input_vat: '',
      address: '',
      support_document: null,
      support_document_preview_url: null,
      ref_num: form.ref_num || ''
    }
  }
}

const openEditLiquidation = (row) => {
  let idx = -1
  if (row?.id != null) {
    idx = (liquidationRows.value || []).findIndex(r => r.id == row.id)
  }
  if (idx === -1) {
    idx = (liquidationRows.value || []).findIndex(r =>
      (r.particulars || r.description || '') === (row.particulars || row.description || '') &&
      (r.or_no || '') === (row.or_no || '') &&
      parseFloat(r.actual_amount ?? 0) === parseFloat(row.actual_amount ?? row.amount ?? 0)
    )
  }
  const current = idx !== -1 ? liquidationRows.value[idx] : row
  editIndex.value = idx !== -1 ? idx : 0
  liquidationForm.value = {
    date: current.date || new Date().toISOString().slice(0, 10),
    actual_amount: current.actual_amount ?? '',
    expense_amount: current.expense_amount ?? '',
    vatcode: current.vatcode ?? '',
    input_vat: current.input_vat ?? '',
    variance: current.variance ?? '',
    reason: current.reason ?? '',
    or_no: current.or_no ?? '',
    tin: current.tin ?? '',
    address: current.address ?? '',
    support_document: null,
    support_document_preview_url: current.support_document_preview_url ?? null,
    ref_num: current.ref_num || ''
  }
  const match = (breakdownRows.value || []).find(b => b.description === (current.particulars || current.description))
  selectedLiquidationDescription.value = match ? match.id : { id: null, label: current.particulars }
  const vatValue = current.vat_non_vat
  const vatLabel = current.vatcode || (typeof current.vat_non_vat === 'string' ? current.vat_non_vat : '')
  const vatOption =
    (vatOptions.value || []).find(v => String(v.value) === String(vatValue)) ||
    (vatOptions.value || []).find(v => v.label === vatLabel)
  liquidationForm.value.vat_non_vat = vatOption ? vatOption.value : (vatValue ?? '')
  updateBreakdownAmount(selectedLiquidationDescription.value)
  isLiquidationModalOpen.value = true
}

const removeLiquidationItem = (row) => {
  const idx = liquidationRows.value.indexOf(row)
  if (idx === -1) return
  if (liquidationRows.value[idx]?.support_document_preview_url) {
    URL.revokeObjectURL(liquidationRows.value[idx].support_document_preview_url)
  }
  liquidationRows.value.splice(idx, 1)
  if (editIndex.value === idx) {
    editIndex.value = null
    isLiquidationModalOpen.value = false
  }
}

const isDeleteConfirmOpen = ref(false)
const rowToDelete = ref(null)
const confirmRemove = (row) => {
  rowToDelete.value = row
  isDeleteConfirmOpen.value = true
}
const onConfirmDelete = () => {
  if (rowToDelete.value) {
    removeLiquidationItem(rowToDelete.value)
  }
  isDeleteConfirmOpen.value = false
  rowToDelete.value = null
}
const onCancelDelete = () => {
  isDeleteConfirmOpen.value = false
  rowToDelete.value = null
}

const frmtype = async () => {
  try {
    const response = await axios.get('/frls/transaction-codes/frls-types')
    if (response.data.success) {
      transactionTypes.value = response.data.data.map(type => ({
        id: type.id,
        label: type.description
      }))

      // Set selected transaction type if editing
      if (form.transaction_type) {
        const match = transactionTypes.value.find(t => t.id == form.transaction_type)
        if (match) {
          selectedTransactionType.value = match
        }
      }
    }
  } catch (error) {
    console.error('Error fetching transaction types:', error)
  }
}

const frmfrequincy = async () => {
  try {
    const response = await axios.get('/frls/transaction-code/frls-frequincy')
    if (response.data.success) {
      frequencies.value = response.data.data.map(type => ({
        id: type.id,
        label: type.description
      }))

      // Set selected frequency type if editing
      if (form.frequency) {
        const match = frequencies.value.find(t => t.id == form.frequency)
        if (match) {
          selectedFrequencyType.value = match
        }
      }
    }
  } catch (error) {
    console.error('Error fetching frequency types:', error)
  }
}

const loadBreakdown = async (id) => {
  if (!id) {
    breakdownRows.value = []
    selectedTotalAmount.value = ''
    return
  }
  try {
    const { data } = await axios.get('/frls/finance-disbursement/breakdown/' + id)
    console.log('Breakdown data:', data)

    // Map the response data to match the table structure
    breakdownRows.value = (data?.items ?? []).map(item => ({
      id: item.id,
      account_code_title: item.account_code_title || '',
      description: item.description || '',
      frequency: item.frequency || '',
      qty: item.qty || 0,
      unit_price: item.unit_price || 0,
      amount: item.amount || 0
    }))

    selectedTotalAmount.value = data?.total_amount ?? ''
    // Also update the form amount if empty
    if (!form.amount && data?.total_amount) {
      form.amount = data.total_amount
    }

  } catch (e) {
    console.error('Error loading breakdown:', e)
    breakdownRows.value = []
    selectedTotalAmount.value = ''
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
    // Fallback to static options
    vatOptions.value = [
    ]
  } finally {
    isLoadingVatOptions.value = false
  }
}

const loadExistingLiquidations = async (formId) => {
  liquidationRows.value = []
  if (!formId) return
  try {
    const { data } = await axios.get(`/frls/approval/liquidation-details/${formId}`)
    const items = data?.items ?? []
    liquidationRows.value = items.map(it => {
      const doc = Array.isArray(it.documents) && it.documents.length > 0 ? it.documents[0] : null
      const html = doc ? `<a href="${doc.file_url}" target="_blank" class="text-blue-600 underline">${doc.original_filename}</a>` : ''
      const isImage = doc?.mime_type?.startsWith('image/')
      const mappedVatLabel = (vatOptions.value || []).find(v => v.value == it.vatcode)?.label
      return {
        id: it.id ?? null,
        actual_amount: parseFloat(it.amount ?? 0),
        particulars: it.description ?? '',
        expense_amount: parseFloat(it.expense_amount ?? 0),
        variance: it.variance ?? '',
        reason: it.reason ?? '',
        or_no: it.or_no ?? '',
        vat_non_vat: it.vat_non_vat ?? '',
        vatcode: mappedVatLabel ?? it.vatcode ?? '',
        input_vat: parseFloat(it.input_vat ?? 0),
        tin: it.tin ?? '',
        address: it.address ?? '',
        support_document_name: doc ? doc.original_filename : '',
        support_document: null,
        support_document_preview_url: isImage ? doc.file_url : null,
        support_document_html: html,
        frmslist_id: it.frmslist_id ?? null,
        description: it.description ?? '',
        amount: parseFloat(it.amount ?? 0),
        ref_num: it.ref_num ?? ''
      }
    })
  } catch (e) {
    console.error('Error loading liquidation details:', e)
    liquidationRows.value = []
  }
}

// Initialize selectedForm based on existing form_id
const initializeFormData = async () => {
  if (form.form_id) {
    const match = (props.forms ?? []).find(f => f.id === form.form_id)
    if (match) {
      selectedForm.value = {
        id: match.id,
        label: `${match.frm_no}`
      }
      selectedUser.value = `${match.user?.first_name || ''} ${match.user?.last_name || ''}`.trim()
      selectedLocation.value = match.branch?.branch_name || ''
      form.location = selectedLocation.value

      // Set description from frms_list items
      if (match.items && match.items.length > 0) {
        const descriptions = match.items.map(item => item.description).filter(desc => desc).join(', ')
        selectedDescription.value = `Fund Request for ${descriptions}`
        form.description = descriptions
      }

      // Set expected liquidation date
      selectedExpectedLiquidation.value = match?.expectedliquidation_date || ''
      form.expected_liquidation = selectedExpectedLiquidation.value

      // Set total amount from form
     // await loadBreakdown(match.id)
    }
  }
}

// Calculate expense amount based on VAT type
const calculateExpenseAmount = () => {
  try {
    const actual_amount = parseFloat(liquidationForm.value.actual_amount) || 0
    const selectedVat = liquidationForm.value.vat_non_vat
    const selectedVatType = selectedVat?.value ?? selectedVat
    const selectedVatLabel =
      (selectedVat && typeof selectedVat === 'object' && selectedVat.label)
        ? selectedVat.label
        : ((vatOptions.value || []).find(v => v.value == selectedVatType)?.label || (typeof selectedVat === 'string' ? selectedVat : ''))

    liquidationForm.value.vatcode = selectedVatLabel

    if (actual_amount <= 0) {
      liquidationForm.value.expense_amount = '0.00'
    //   liquidationForm.value.input_vat = '0.00'
      return
    }

    if (selectedVatType === undefined || selectedVatType === null || selectedVatType === '') {
      liquidationForm.value.expense_amount = actual_amount.toFixed(2)
    //   liquidationForm.value.input_vat = '0.00'
      return
    }

    let expenseAmount

    if (selectedVatType > 0) {
      // For VAT Register: Expense Amount = Gross Amount / (1 + VAT rate)
      // Assuming transValue represents (1 + VAT rate), e.g., 1.12 for 12% VAT
      expenseAmount = actual_amount / selectedVatType
    } else {
      // For Non-VAT or when trans_value is 0: Expense Amount = Gross Amount
      expenseAmount = actual_amount
    }

    // Set expense amount with proper formatting
    liquidationForm.value.expense_amount = Math.max(0, expenseAmount).toFixed(2)

    // Calculate Input VAT (Gross Amount - Expense Amount)
    const calculatedExpense = parseFloat(liquidationForm.value.expense_amount) || 0
    liquidationForm.value.input_vat = Math.max(0, actual_amount - calculatedExpense).toFixed(2)

  } catch (error) {

    liquidationForm.value.expense_amount = '0.00'
    liquidationForm.value.input_vat = '0.00'
  }
}

// Method to handle VAT type change with basic alert
const onVatTypeChange = () => {
  const selectedVatType = liquidationForm.value.vat_non_vat?.value ?? liquidationForm.value.vat_non_vat
  calculateExpenseAmount()
}

const totalExpenses = computed(() => {
  return (liquidationRows.value || []).reduce((sum, item) => sum + (parseFloat(item.expense_amount) || 0), 0)
})

// Fetch transaction types on component mount
onMounted(async () => {
  await frmtype()
  await frmfrequincy()
  await initializeFormData()
  await loadBreakdown(form.form_id || form.id)
  await loadVatOptions()
  await loadExistingLiquidations(form.form_id || form.id)
})

// Improved watchers with better validation
watch(() => liquidationForm.value.actual_amount, (newGrossAmount) => {
  if ((newGrossAmount || newGrossAmount === 0) && liquidationForm.value.vat_non_vat) {
    calculateExpenseAmount()
  }
})

watch(() => liquidationForm.value.vat_non_vat, (newVatType) => {
  if (newVatType && (liquidationForm.value.actual_amount || liquidationForm.value.actual_amount === 0)) {
    calculateExpenseAmount()
  }
}, { deep: true })

// Watch for transaction type selection changes
watch(selectedTransactionType, (val) => {
  form.transaction_type = val?.id || ''
})

// Watch for frequency type selection changes
watch(selectedFrequencyType, (val) => {
  form.frequency = val?.id || ''
})

watch(() => props.formdata, async (newvalue) => {
  if (newvalue) {
    for (let key in newvalue) {
      if (newvalue.hasOwnProperty(key)) {
        form[key] = newvalue[key]
      }
    }

    if (newvalue.id) {
      form.form_id = newvalue.id
      selectedForm.value = { id: newvalue.id, label: `${newvalue.frm_no}` }
      await loadBreakdown(newvalue.id)
    }

    // Update selected transaction type when formdata changes
    if (newvalue.transaction_type && transactionTypes.value.length > 0) {
      const match = transactionTypes.value.find(t => t.id == newvalue.transaction_type)
      if (match) {
        selectedTransactionType.value = match
      }
    }

    // Update selected frequency type when formdata changes
    if (newvalue.frequency && frequencies.value.length > 0) {
      const match = frequencies.value.find(t => t.id == newvalue.frequency)
      if (match) {
        selectedFrequencyType.value = match
      }
    }
  }
})

watch(selectedLiquidationDescription, (val) => {
  updateBreakdownAmount(val)

})

watch(() => liquidationForm.value.actual_amount, () => {
  recomputeVariance()
})

watch(selectedForm, async (val) => {
  form.form_id = val?.id || null

  // Update user and location when form is selected
  if (val?.id) {
    const match = (props.forms ?? []).find(f => f.id === val.id)
    if (match) {
      selectedUser.value = `${match.user?.first_name || ''} ${match.user?.last_name || ''}`.trim()
      selectedLocation.value = match.branch?.branch_name || ''
      form.location = selectedLocation.value

      // Set description from frms_list items
      if (match.items && match.items.length > 0) {
        const descriptions = match.items.map(item => item.description).filter(desc => desc).join(', ')
        selectedDescription.value = `Fund Request for ${descriptions}`
        form.description = descriptions
      } else {
        selectedDescription.value = ''
        form.description = ''
      }

      // Set expected liquidation date
      selectedExpectedLiquidation.value = match?.expectedliquidation_date || ''
      form.expected_liquidation = selectedExpectedLiquidation.value

      await loadBreakdown(match.id)
      await loadExistingLiquidations(match.id)
    }
  } else {
    selectedUser.value = ''
    selectedLocation.value = ''
    selectedDescription.value = ''
    selectedExpectedLiquidation.value = ''
    form.location = ''
    form.description = ''
    form.expected_liquidation = ''
    selectedTotalAmount.value = ''
    breakdownRows.value = []
  }
})

const submit = async () => {
  if ((liquidationRows.value || []).length === 0) {
    toast.error('Please add at least one liquidation item')
    return
  }
  isConfirmOpen.value = true
}

const isConfirmOpen = ref(false)

const onConfirmSubmit = () => {
  const formId = form.form_id || selectedForm.value?.id || props.formdata?.id || null
  if (!formId) {
    toast.error('Please select a form before submitting')
    return
  }

  // Create FormData safely - FIXED THE ISSUE HERE
  let formDataPayload
  try {
    formDataPayload = new FormData()
  } catch (error) {

    toast.error('Browser does not support file uploads')
    return
  }

  // Append basic form data
  formDataPayload.append('id', form.id || '')
  formDataPayload.append('ref_num', form.ref_num || '')
  formDataPayload.append('debit', form.debit || '')
  formDataPayload.append('location', form.location || '')
  formDataPayload.append('form_id', String(formId))
  formDataPayload.append('transaction_type', form.transaction_type || '')
  formDataPayload.append('description', form.description || '')
  formDataPayload.append('frequency', form.frequency || '')
  formDataPayload.append('expected_liquidation', form.expected_liquidation || '')
  formDataPayload.append('original_receipts', form.original_receipts || '')
  formDataPayload.append('amount', form.amount || '')

  // Append liquidation items
  ;(liquidationRows.value || []).forEach((item, idx) => {
    if (item.id != null) {
      formDataPayload.append(`items[${idx}][id]`, String(item.id))
    }
    formDataPayload.append(`items[${idx}][frmslist_id]`, item.frmslist_id ?? '')
    formDataPayload.append(`items[${idx}][description]`, item.description ?? '')
    formDataPayload.append(`items[${idx}][or_no]`, item.or_no ?? '')
    formDataPayload.append(`items[${idx}][amount]`, item.amount ?? '')
    formDataPayload.append(`items[${idx}][variance]`, item.variance ?? '')
    formDataPayload.append(`items[${idx}][reason]`, item.reason ?? '')
    formDataPayload.append(`items[${idx}][tin]`, item.tin ?? '')
    formDataPayload.append(`items[${idx}][address]`, item.address ?? '')
    formDataPayload.append(`items[${idx}][vat_non_vat]`, item.vat_non_vat ?? '')
    formDataPayload.append(`items[${idx}][vatcode]`, item.vatcode ?? '')
    formDataPayload.append(`items[${idx}][expense_amount]`, item.expense_amount ?? '')
    formDataPayload.append(`items[${idx}][input_vat]`, item.input_vat ?? '')

    // Only append file if it exists and is a File object
    if (item.support_document instanceof File) {
      formDataPayload.append(`items[${idx}][support_document]`, item.support_document)
    }
  })

  form.transform(() => formDataPayload)
  form.post(route('frls.finance-disbursement.store'), {
    preserveScroll: true,
    forceFormData: true,
    onSuccess: () => {
      emit('triggerTopRightButton', 'lists')
    },
    onError: (errors) => {
      formErrors.value = Object.values(errors || {})

      toast.error('Failed to submit form')
    }
  })
}

const onCancelSubmit = () => {
  isConfirmOpen.value = false
}
</script>

<template>
  <SectionTitleLineWithButton
    :icon="'mdiFileDocumentPlus'"
    :title="(props.action === 'Add' ? 'Add' : 'Manage') + ' Liquidation Expenses'"
    main
  >
    <BaseButton
      @click="emit('triggerTopRightButton', 'lists')"
      :icon="'mdiViewList'"
      label="Disbursement Lists"
      color="contrast"
      rounded-full
      small
    />
  </SectionTitleLineWithButton>

  <CardBox>
    <FormWizard
      @on-complete="submit"
      color="#094899"
      step-size="xs"
      :finish-disabled="!canFinish"
    >
      <TabContent title="Breakdown" icon="mdiFormatListBulleted">

        <CoreTable
          :table-rows="breakdownRows"
          :table-header="breakdownTableHeader"
          table-name="FRMS Breakdown"
          searchable-fields="description,account_code_title"
          :is-paginated="false"
          :rows-to-display-on-open="999"
        />
         <div class="mb-4">
          <div class="text-sm text-gray-600 font-semibold">Total amount: {{ selectedTotalAmount }}</div>
        </div>
      </TabContent>

      <TabContent title="Liquidation list" icon="mdiReceiptText">

      <div class="fixed bottom-6 right-6 md:bottom-8 md:right-8 z-50 flex items-center gap-3">
        <BaseButton @click="isLiquidationModalOpen = true" class="lg:w-12 lg:h-12" :icon="'mdiPlus'" color="info" rounded-full small />

      </div>
        <CoreTable
          :table-rows="liquidationRowsDisplay"
          :table-header="liquidationTableHeader"
          table-name="Liquidation Items"
          :is-paginated="false"
          :rows-to-display-on-open="999"
        >
          <template #row-action="{ slotProp }">
            <div class="flex gap-2">
              <BaseButton :icon="'mdiPencil'" color="whiteDark" title="Edit" @click="openEditLiquidation(slotProp)" />
              <BaseButton :icon="'mdiDelete'" color="danger" title="Remove" @click="confirmRemove(slotProp)" />
            </div>
          </template>
        </CoreTable>
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

    <SwalConfirm
      v-model="isDeleteConfirmOpen"
      type="warning"
      title="Remove item?"
      text="This liquidation item will be permanently removed."
      confirmText="Yes, Remove"
      cancelText="Cancel"
      @confirm="onConfirmDelete"
      @cancel="onCancelDelete"
    />

    <Modal :show="isLiquidationModalOpen" max-width="xl" @close="isLiquidationModalOpen = false">
      <div class="p-6">
        <div class="text-lg font-semibold mb-4">{{ editIndex !== null ? 'Edit Liquidation Item' : 'Add Liquidation Item' }}</div>
        <div class="grid grid-cols-12 gap-4">

          <div class="col-span-12 md:col-span-6">
            <FormField label="Description">
              <FormControl :options="liquidationDescriptionOptions" v-model="selectedLiquidationDescription" />
            </FormField>
          </div>
          <div class="col-span-12 md:col-span-6">
            <FormField label="Actual Amount"  :help="'Amount requested: ' + selectedBreakdownAmount">
              <FormControl v-model="liquidationForm.actual_amount" type="number" step="0.01" />
            </FormField>
          </div>

          <div class="col-span-12 md:col-span-6">
            <FormField label="Variance">
              <FormControl v-model="liquidationForm.variance" type="number" step="0.01" />
            </FormField>
          </div>

          <div class="col-span-12 md:col-span-6">
            <FormField label="OR No.">
              <FormControl v-model="liquidationForm.or_no" />
            </FormField>
          </div>
          <div class="col-span-12 md:col-span-6">
            <FormField label="Reference Number">
              <FormControl v-model="liquidationForm.ref_num" />
            </FormField>
          </div>
          <div class="col-span-12 md:col-span-6">
            <FormField label="VAT/Non-VAT">
              <FormControl
                v-model="liquidationForm.vat_non_vat"
                :options="vatOptions"
                :loading="isLoadingVatOptions"
                placeholder="Select VAT type..."
                @change="onVatTypeChange()"
              />
            </FormField>
          </div>
          <div class="col-span-12 md:col-span-6">
            <FormField label="Expense Amount *">
              <FormControl
                v-model.number="liquidationForm.expense_amount"
                type="number"
                step="0.01"
                placeholder="0.00"
                min="0"
                required
                readonly
              />
            </FormField>
          </div>
          <div class="col-span-12 md:col-span-6">
            <FormField label="Input VAT">
              <FormControl
                v-model.number="liquidationForm.input_vat"
                type="number"
                step="0.01"
                placeholder="0.00"
                min="0"
                readonly
              />
            </FormField>
          </div>
          <div class="col-span-12 md:col-span-6">
            <FormField label="TIN">
              <FormControl v-model="liquidationForm.tin" />
            </FormField>
          </div>
          <div class="col-span-12 md:col-span-6">
            <FormField label="Address">
              <FormControl v-model="liquidationForm.address" />
            </FormField>
          </div>
          <div class="col-span-12">
            <FormField label="Reason">
              <FormControl v-model="liquidationForm.reason" />
            </FormField>
          </div>
          <div class="col-span-12">
            <FormField label="Support Document">
              <input ref="fileInput" type="file" accept="image/*" class="hidden" @change="onSupportDocChange" />
              <div
                class="border-2 border-dashed rounded-lg p-4 flex items-center justify-center cursor-pointer"
                :class="isDragging ? 'border-blue-500 bg-blue-50' : 'border-gray-300'"
                @click="openFilePicker"
                @dragenter.prevent="isDragging = true"
                @dragover.prevent
                @dragleave.prevent="isDragging = false"
                @drop.prevent="onSupportDocDrop"
              >
                <div v-if="liquidationForm.support_document_preview_url" class="relative">
                  <img :src="liquidationForm.support_document_preview_url" alt="" class="h-28 w-28 object-cover rounded" />
                  <button type="button" class="absolute top-1 right-1 bg-red-500 text-white rounded-full h-6 w-6 flex items-center justify-center text-xs"
                          @click.stop="clearSupportDoc">✕</button>
                </div>
                <div v-else class="text-gray-500">
                  Drag & drop image here or click to browse
                </div>
              </div>
            </FormField>
          </div>
        </div>
        <div class="mt-4 flex justify-end gap-2">
          <BaseButton color="contrast" label="Cancel" @click="isLiquidationModalOpen = false" />
          <BaseButton color="info" :label="editIndex !== null ? 'Edit' : 'Add'" @click="addLiquidationItem" />
        </div>
      </div>
    </Modal>
  </CardBox>
</template>
