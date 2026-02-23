<script setup>
import { useForm, router, usePage } from '@inertiajs/vue3'
import { watch, ref, onMounted, computed, onUnmounted } from 'vue'
import CardBox from '@/Components/CardBox.vue'
import FormField from '@/Components/FormField.vue'
import FormControl from '@/Components/FormControl.vue'
import BaseButton from '@/Components/BaseButton.vue'
import BaseIcon from '@/Components/BaseIcon.vue'
import BaseButtons from '@/Components/BaseButtons.vue'
import SectionTitleLineWithButton from '@/Components/SectionTitleLineWithButton.vue'
import SwalConfirm from '@/Components/SwalConfirm.vue'
import FormWizard from '@/Components/FormWizard.vue'
import TabContent from '@/Components/TabContent.vue'
import service from "@/Components/Toast/service";

let toast = service();

const props = defineProps({
  action: String,
  formdata: Object
})

const emit = defineEmits(['triggerTopRightButton'])

// Get current user from shared Inertia props
const page = usePage()
const currentUser = page.props.auth?.user

// Initialize form with sensible defaults for add route
const form = useForm({
  _token: page.props.csrf_token,
  id: props.formdata?.id || null,
  user_id: props.formdata?.user_id ?? currentUser?.id ?? null,
  branch_id: props.formdata?.branch_id ?? currentUser?.branch_id ?? null,
  request_date: props.formdata?.request_date || '',
  expectedliquidation_date: props.formdata?.expectedliquidation_date ? props.formdata.expectedliquidation_date.toString().slice(0, 10) : '',
  purpose: props.formdata?.purpose || null,
  quotation: null,
  documents: props.formdata?.documents || [],
  // Default to 'FA' (For Approved) on Add; use existing value on Manage
  status_request: props.formdata?.status_request || 'FA',
  items: props.formdata?.items ? JSON.parse(JSON.stringify(props.formdata.items)) : [
    { account_code_title: '', frequency: '', description: '', qty: 0, unit_price: 0, amount: 0, remarks: '' }
  ]
})
const canFinish = computed(() => {
  const items = form.items || []
  if (items.length === 0) return false
  return items.every(i => {
    const acct = i.account_code_title
    const freq = i.frequency
    const hasAcct = !!(acct && (typeof acct === 'object' ? (acct.id || acct.label) : acct))
    const hasFreq = !!(freq && (typeof freq === 'object' ? freq.id : freq))
    const descOk = !!(i.description && i.description.toString().trim())
    const qtyOk = (parseFloat(i.qty) || 0) > 0
    const unitOk = (parseFloat(i.unit_price) || 0) > 0
    const amtOk = (parseFloat(i.amount) || 0) > 0
    return hasAcct && hasFreq && descOk && qtyOk && unitOk && amtOk
  })
})
watch(() => props.formdata, (newvalue, oldvalue) => {
  if (newvalue != oldvalue && newvalue) {
    for (let key in newvalue) {
      if (newvalue.hasOwnProperty(key)) {
        if (key === 'items') {
             form[key] = JSON.parse(JSON.stringify(newvalue[key]));
        } else if (key === 'expectedliquidation_date') {
             form[key] = newvalue[key] ? newvalue[key].toString().slice(0, 10) : '';
        } else if (key === 'request_date') {
             form[key] = newvalue[key] ? newvalue[key].toString().slice(0, 10) : '';
        } else {
             form[key] = newvalue[key];
        }
      }
    }
  }
})
// Confirm modal state and handlers
const isConfirmOpen = ref(false)
const formErrors = ref([])
const currentQuotation = ref(props.formdata?.quotation || null)
const previewUrl = ref(null)

const getFileExtension = (filename) => {
    return filename.slice((filename.lastIndexOf(".") - 1 >>> 0) + 2);
}

const isImage = (file) => {
    if (file instanceof File) {
        return file.type.startsWith('image/');
    }
    if (typeof file === 'string') {
        const ext = getFileExtension(file).toLowerCase();
        return ['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(ext);
    }
    return false;
}

const getFileIcon = (file) => {
    let ext = '';
    if (file instanceof File) {
        const name = file.name;
        ext = getFileExtension(name).toLowerCase();
    } else if (typeof file === 'string') {
        ext = getFileExtension(file).toLowerCase();
    }

    switch (ext) {
        case 'pdf': return 'mdiFilePdfBox';
        case 'doc':
        case 'docx': return 'mdiFileWordBox';
        case 'xls':
        case 'xlsx': return 'mdiFileExcelBox';
        default: return 'mdiFileDocument';
    }
}

const submit = async()  => {
  if (!canFinish.value) {
    toast.error('Please complete all required (*) fields in the items list')
    return
  }
  isConfirmOpen.value = true
}

const addItem = () => {
  form.items.push({ account_code_title: '', frequency: '', description: '', qty: 0, unit_price: 0, amount: 0 })
}

const removeItem = (index) => {
  if (form.items.length > 1) {
    form.items.splice(index, 1)
  }
}


const onConfirmSubmit = () => {
  // Ensure required fields are present before submit
  if (!form.user_id) form.user_id = currentUser?.id ?? null
  if (!form.branch_id) form.branch_id = currentUser?.branch_id ?? null
  if (!form.status_request) form.status_request = 'FA'

  form.items = form.items.map(i => ({
    ...i,
    account_code_title: typeof i.account_code_title === 'object' ? (i.account_code_title.id ?? i.account_code_title.label ?? '') : i.account_code_title,
    frequency: typeof i.frequency === 'object' ? (i.frequency.id ?? '') : i.frequency,
    qty: typeof i.qty === 'string' ? parseFloat(i.qty) || 0 : i.qty || 0,
    unit_price: typeof i.unit_price === 'string' ? parseFloat(i.unit_price) || 0 : i.unit_price || 0,
    amount: typeof i.amount === 'string' ? parseFloat(i.amount) || 0 : i.amount || 0
  }))

  form.post(route('frls.form.store'), {
    preserveScroll: true,
    onSuccess: () => {
      emit('triggerTopRightButton', 'lists')
      toast.success(`Fund request saved successfully!`);
    },
    onError: (errors) => {
      formErrors.value = Object.values(errors || {})
      toast.error('Failed to save fund request')
    }
  })
}
const onCancelSubmit = () => {
  // no-op for now; modal closes itself
}

const fileInput = ref(null)

const triggerFileInput = () => {
  fileInput.value.click()
}

const handleFileChange = (event) => {
  const file = event.target.files[0]
  if (file) {
      if (file.size > 10 * 1024 * 1024) {
          toast.error("File size exceeds 10MB limit");
          return;
      }
      if (previewUrl.value) URL.revokeObjectURL(previewUrl.value);
      form.quotation = file
      if (isImage(file)) {
          previewUrl.value = URL.createObjectURL(file);
      } else {
          previewUrl.value = null;
      }
  }
}

const handleDrop = (event) => {
  const file = event.dataTransfer.files[0]
  if (file) {
      if (file.size > 10 * 1024 * 1024) {
          toast.error("File size exceeds 10MB limit");
          return;
      }
      const allowedTypes = ['application/pdf', 'image/jpeg', 'image/png', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];
      if (!allowedTypes.includes(file.type)) {
          toast.error("Invalid file type (PDF, Images, Word, Excel only)");
          return;
      }
      if (previewUrl.value) URL.revokeObjectURL(previewUrl.value);
      form.quotation = file
      if (isImage(file)) {
          previewUrl.value = URL.createObjectURL(file);
      } else {
          previewUrl.value = null;
      }
  }
}

const removeFile = () => {
  form.quotation = null
  if (previewUrl.value) {
      URL.revokeObjectURL(previewUrl.value);
      previewUrl.value = null;
  }
  if (fileInput.value) {
      fileInput.value.value = ''
  }
}

onUnmounted(() => {
    if (previewUrl.value) URL.revokeObjectURL(previewUrl.value);
})

const frequencies = ref([])
const accountCodes = ref([])

const frmfrequincy = async () => {
  try {
    const response = await axios.get('/frls/transaction-code/frls-frequincy')
    if (response.data.success) {
      frequencies.value = response.data.data.map(type => ({
        id: type.id,
        label: type.description
      }))
    }
  } catch (error) {
    console.error('Error fetching frequency types:', error)
  }
}

const frmsAccountCode = async () => {
  try {
    const response = await axios.get('/frls/transaction-code/frls-account-code')
    if (response.data.success) {
      accountCodes.value = response.data.data.map(type => ({
        id: type.description,
        label: type.description
      }))
    }
  } catch (error) {
    console.error('Error fetching transaction types:', error)
  }
}

onMounted(async () => {
  await frmfrequincy()
  await frmsAccountCode()

      if (props.action === 'Manage' && props.formdata?.id) {
          try {
              const response = await axios.get(route('frls.form.show', props.formdata.id))
              if (response.data.success && response.data.data) {
                  const freshData = response.data.data
                  if (freshData.items && freshData.items.length > 0) {
                       form.items = JSON.parse(JSON.stringify(freshData.items));
                  } else {
                       if (!form.items || form.items.length === 0) {
                           form.items = [{ account_code_title: '', frequency: '', description: '', qty: 0, unit_price: 0, amount: 0, remarks: '' }];
                       }
                  }
                  // Update other fields if necessary to ensure consistency
                  if (freshData.purpose) form.purpose = freshData.purpose;
                  if (freshData.request_date) form.request_date = freshData.request_date.toString().slice(0, 10);
                  if (freshData.expectedliquidation_date) form.expectedliquidation_date = freshData.expectedliquidation_date.toString().slice(0, 10);
                  if (freshData.quotation) currentQuotation.value = freshData.quotation;
                  if (freshData.documents) form.documents = freshData.documents;
              }
          } catch (error) {
              console.error("Failed to fetch fresh data", error);
          }
      }

      if (!props.formdata?.id) {
          const now = new Date();
          const year = now.getFullYear();
          const month = String(now.getMonth() + 1).padStart(2, '0');
          const day = String(now.getDate()).padStart(2, '0');
          form.request_date = `${year}-${month}-${day}`;

          const tomorrow = new Date(now);
          tomorrow.setDate(tomorrow.getDate() + 1);
          const tYear = tomorrow.getFullYear();
          const tMonth = String(tomorrow.getMonth() + 1).padStart(2, '0');
          const tDay = String(tomorrow.getDate()).padStart(2, '0');
          form.expectedliquidation_date = `${tYear}-${tMonth}-${tDay}`;
      }
    })

const updateAmount = (item) => {
  const q = parseFloat(item.qty) || 0
  const u = parseFloat(item.unit_price) || 0
  item.amount = q * u
}

const checkNextDisabled = (i) => {
  return i === 0 && !(form.purpose && form.purpose.toString().trim())
}

const formatDate = (date) => {
  if (!date) return 'N/A'
  return new Date(date).toLocaleDateString()
}

const getStatusText = (status) => {
  const map = { 'FA': 'For Approval', 'A': 'Approved', 'R': 'Rejected' }
  return map[status] || status
}

const getAccountTitle = (it) => {
  const val = it.account_code_title
  return (typeof val === 'object' && val) ? (val.label || val.id || '') : val
}

const getFrequencyLabel = (it) => {
  const val = it.frequency
  return (typeof val === 'object' && val) ? (val.label || val.id || '') : val
}

const formatCurrency = (amount) => {
  return (parseFloat(amount) || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

const minDate = computed(() => {
  const now = new Date();
  const offset = now.getTimezoneOffset() * 60000;
  return new Date(now.getTime() - offset).toISOString().split('T')[0];
})
</script>

<template>
  <SectionTitleLineWithButton :icon="'mdiFileDocumentPlus'" :title="(props.action === 'Add' ? 'Add' : 'Edit') + ' Fund Request'" main>
    <BaseButton @click="emit('triggerTopRightButton', 'lists')" :icon="'mdiViewList'" label="Request Lists" color="contrast" rounded-full small />
  </SectionTitleLineWithButton>

  <CardBox>

     <FormWizard
                @on-complete="submit"
                color="#094899"
                step-size="xs"
                :finish-disabled="!canFinish"
                :next-disabled="(i) => i === 0 && !(form.purpose && form.purpose.toString().trim())"
            >
                <TabContent title="Fund Request Information" icon="mdiFileDocumentPlus">
    <div>
      <FormField label="Request Date" help="Required. Request Date">
        <FormControl name="request_date" v-model="form.request_date" type="date" disabled />
      </FormField>

      <FormField label="Expected Liquidation Date">
        <FormControl name="expectedliquidation_date" v-model="form.expectedliquidation_date" type="date" :min="minDate" />
      </FormField>
      <FormField label="Purpose">
        <FormControl name="purpose" v-model="form.purpose" type="text" />
      </FormField>

      <FormField label="Quotation (Optional)">
          <div
            class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-blue-500 transition-colors cursor-pointer"
            @dragover.prevent
            @drop.prevent="handleDrop"
            @click="triggerFileInput"
          >
            <input
              type="file"
              ref="fileInput"
              class="hidden"
              accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx"
              @change="handleFileChange"
            />
            <div v-if="form.quotation" class="flex flex-col items-center justify-center gap-2">
                 <div v-if="previewUrl" class="mb-2">
                     <img :src="previewUrl" class="h-32 w-auto object-contain rounded border border-gray-200" />
                 </div>
                 <div v-else class="mb-2">
                     <BaseIcon :path="getFileIcon(form.quotation)" size="48" class="text-gray-500" />
                 </div>
                 <div class="flex items-center gap-2">
                     <span class="text-sm text-gray-600">{{ form.quotation.name || 'File selected' }}</span>
                     <BaseButton icon="mdiClose" color="danger" small rounded-full @click.stop="removeFile" />
                 </div>
            </div>
            <div v-else-if="currentQuotation" class="flex flex-col items-center justify-center gap-2">
                 <div v-if="isImage(currentQuotation)" class="mb-2">
                     <img :src="'/storage/' + currentQuotation" class="h-32 w-auto object-contain rounded border border-gray-200" />
                 </div>
                 <div v-else class="mb-2">
                     <BaseIcon :path="getFileIcon(currentQuotation)" size="48" class="text-gray-500" />
                 </div>
                 <div class="flex items-center gap-2">
                      <span class="text-sm text-gray-600">Current: <a :href="'/storage/' + currentQuotation" target="_blank" class="text-blue-600 hover:underline" @click.stop>View File</a></span>
                      <span class="text-xs text-gray-400 mx-2">|</span>
                      <span class="text-sm text-gray-500">Click to replace</span>
                 </div>
            </div>
            <div v-else>
                 <BaseIcon path="mdiCloudUpload" size="36" class="text-gray-400 mx-auto mb-2" />
                 <p class="text-sm text-gray-500">Drag and drop a file here, or click to upload</p>
                 <p class="text-xs text-gray-400 mt-1">PDF, Images, Word, Excel (Max 10MB)</p>
            </div>
          </div>
      </FormField>
    </div>
    </TabContent>
    <TabContent title="List of Items" icon="mdiFormatListBulleted">
    <div>
      <div class="fixed bottom-6 right-6 md:bottom-8 md:right-8 z-50 flex items-center gap-3">
        <BaseButton @click="addItem" class="lg:w-12 lg:h-12" :icon="'mdiPlus'" color="info" rounded-full small />

      </div>

      <div class="space-y-4">
        <div v-for="(item, idx) in form.items" :key="idx" class="relative rounded-2xl border border-gray-200 p-4 shadow-sm transition-shadow focus-within:border-blue-600 focus-within:shadow-md focus-within:ring-2 focus-within:ring-blue-500">

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <FormField label="Account Title*">
              <FormControl :options="accountCodes" v-model="item.account_code_title" />
            </FormField>
            <FormField label="Frequency*">
              <FormControl :options="frequencies" v-model="item.frequency" />
            </FormField>
            <FormField label="Description*">
              <FormControl v-model="item.description" placeholder="Description" />
            </FormField>
            <FormField label="Qty*">
              <FormControl v-model.number="item.qty" type="number" step="0.01" placeholder="0" @input="updateAmount(item)" />
            </FormField>
            <FormField label="Unit Price*">
              <FormControl v-model.number="item.unit_price" type="number" step="0.01" placeholder="0" @input="updateAmount(item)" />
            </FormField>
            <FormField label="Amount*">
              <FormControl v-model.number="item.amount" type="number" step="0.01" placeholder="0" :disabled="true" />
            </FormField>
             <FormField label="Remarks">
              <FormControl v-model="item.remarks" type="text" placeholder="Remarks" />
            </FormField>
             <BaseButton @click="removeItem(idx)" :icon="'mdiDelete'" color="danger" rounded-full class=" lg:w-12 lg:h-12 absolute bottom-3 right-2 mt-5" />
          </div>
        </div>
      </div>
    </div>
    </TabContent>
    <TabContent title="Review" icon="mdiEye">
        <div class="mb-6">
            <h3 class="text-lg font-bold mb-4">Review Fund Request</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div>
                    <div class="text-xs text-gray-500 uppercase">Purpose</div>
                    <div class="text-sm">{{ form.purpose }}</div>
                </div>
                <div>
                    <div class="text-xs text-gray-500 uppercase">Request Date</div>
                    <div class="text-sm">{{ formatDate(form.request_date) }}</div>
                </div>
                <div>
                    <div class="text-xs text-gray-500 uppercase">Expected Liquidation</div>
                    <div class="text-sm">{{ formatDate(form.expectedliquidation_date) }}</div>
                </div>
                 <div>
                    <div class="text-xs text-gray-500 uppercase">Status</div>
                    <div class="text-sm">{{ getStatusText(form.status_request) }}</div>
                </div>
            </div>

            <div class="mb-6" v-if="form.documents && form.documents.length > 0">
                <div class="text-xs text-gray-500 uppercase mb-2">Attached Documents</div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div v-for="doc in form.documents" :key="doc.id" class="flex items-center gap-3 p-3 border rounded-lg bg-gray-50">
                        <BaseIcon :path="getFileIcon(doc.original_filename)" size="24" class="text-gray-500" />
                        <div class="flex-1 min-w-0">
                            <div class="text-sm font-medium text-gray-900 truncate" :title="doc.original_filename">
                                {{ doc.original_filename }}
                            </div>
                            <div class="text-xs text-gray-500">
                                {{ (doc.file_size / 1024).toFixed(2) }} KB
                            </div>
                        </div>
                         <a :href="'/storage/' + doc.file_path" target="_blank" class="text-blue-600 hover:underline text-sm whitespace-nowrap">View</a>
                    </div>
                </div>
            </div>

             <div class="mt-4">
                <div class="text-xs text-gray-500 uppercase mb-2">Items</div>
                <div class="overflow-x-auto border rounded">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-100">
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
                            <tr v-for="(it, idx) in form.items" :key="idx" class="border-t even:bg-gray-50">
                                <td class="px-2 py-1">{{ idx + 1 }}</td>
                                <td class="px-2 py-1">{{ getAccountTitle(it) }}</td>
                                <td class="px-2 py-1">{{ getFrequencyLabel(it) }}</td>
                                <td class="px-2 py-1">{{ it.description }}</td>
                                <td class="px-2 py-1 text-right">{{ it.qty }}</td>
                                <td class="px-2 py-1 text-right">{{ formatCurrency(it.unit_price) }}</td>
                                <td class="px-2 py-1 text-right">{{ formatCurrency(it.amount) }}</td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr class="border-t">
                                <td colspan="6" class="px-2 py-1 text-right font-semibold">Total Amount:</td>
                                <td class="px-2 py-1 text-right font-bold">₱{{ formatCurrency(form.items.reduce((s, i) => s + (parseFloat(i.amount)||0), 0)) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </TabContent>
     </FormWizard>

    <!-- Custom Swal-like confirm modal -->
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

  </CardBox>
</template>
