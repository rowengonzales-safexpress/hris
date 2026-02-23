<script setup>
import { ref, onMounted, computed, getCurrentInstance } from 'vue'
import { router } from '@inertiajs/vue3'
import SectionTitleLineWithButton from '@/Components/SectionTitleLineWithButton.vue'
import BaseButton from '@/Components/BaseButton.vue'
import CardBox from '@/Components/CardBox.vue'
import FormField from '@/Components/FormField.vue'
import FormControl from '@/Components/FormControl.vue'
import BaseButtons from '@/Components/BaseButtons.vue'


const props = defineProps({
  form: {
    type: Object,
    required: true
  },
  documents: {
    type: Array,
    default: () => []
  }
})

const emit = defineEmits(['triggerTopRightButton'])

// Access global MDI icons
const instance = getCurrentInstance()
const mdi = instance?.appContext.config.globalProperties.$mdi

const isDragOver = ref(false)
const isUploading = ref(false)
const isProcessing = ref(false)
const uploadProgress = ref(0)
const selectedFiles = ref([])
const documentList = ref(props.documents || [])
const isLoading = ref(false)
const description = ref('')
const lightboxImage = ref(null)
const showLightbox = ref(false)

// File validation
const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']
const maxFileSize = 10 * 1024 * 1024 // 10MB

const formatFileSize = (bytes) => {
  if (bytes === 0) return '0 Bytes'
  const k = 1024
  const sizes = ['Bytes', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
}

const validateFile = (file) => {
  if (!allowedTypes.includes(file.type)) {
    return 'Invalid file type. Only images, PDF, and Word documents are allowed.'
  }
  if (file.size > maxFileSize) {
    return 'File size exceeds 10MB limit.'
  }
  return null
}

// Image resizing function
const resizeImage = (file, maxWidth = 1920, maxHeight = 1080, quality = 0.8) => {
  return new Promise((resolve) => {
    const canvas = document.createElement('canvas')
    const ctx = canvas.getContext('2d')
    const img = new Image()

    img.onload = () => {
      // Calculate new dimensions
      let { width, height } = img

      if (width > height) {
        if (width > maxWidth) {
          height = (height * maxWidth) / width
          width = maxWidth
        }
      } else {
        if (height > maxHeight) {
          width = (width * maxHeight) / height
          height = maxHeight
        }
      }

      canvas.width = width
      canvas.height = height

      // Draw and compress
      ctx.drawImage(img, 0, 0, width, height)

      canvas.toBlob(resolve, 'image/jpeg', quality)
    }

    img.src = URL.createObjectURL(file)
  })
}

const handleDragOver = (e) => {
  e.preventDefault()
  isDragOver.value = true
}

const handleDragLeave = (e) => {
  e.preventDefault()
  isDragOver.value = false
}

const handleDrop = async (e) => {
  e.preventDefault()
  isDragOver.value = false

  const files = Array.from(e.dataTransfer.files)
  await processFiles(files)
}

const handleFileSelect = async (e) => {
  const files = Array.from(e.target.files)
  await processFiles(files)
  // Reset input
  e.target.value = ''
}

const processFiles = async (files) => {
  isProcessing.value = true

  try {
    const validFiles = []

    for (const file of files) {
      const error = validateFile(file)
      if (error) {
        alert(`${file.name}: ${error}`)
        continue
      }

      // Check if file already selected
      const exists = selectedFiles.value.some(f => f.name === file.name && f.size === file.size)
      if (exists) {
        continue
      }

      let processedFile = file
      let preview = null

      // Resize image if it's an image file
      if (file.type.startsWith('image/')) {
        try {
          const resizedBlob = await resizeImage(file)
          processedFile = new File([resizedBlob], file.name, {
            type: 'image/jpeg',
            lastModified: Date.now()
          })
          preview = URL.createObjectURL(processedFile)
        } catch (error) {
          console.error('Error resizing image:', error)
          preview = URL.createObjectURL(file)
        }
      }

      validFiles.push({
        file: processedFile,
        originalFile: file,
        name: file.name,
        size: processedFile.size,
        originalSize: file.size,
        type: processedFile.type,
        preview: preview,
        isResized: file.type.startsWith('image/') && processedFile.size < file.size
      })
    }

    selectedFiles.value.push(...validFiles)
  } finally {
    isProcessing.value = false
  }
}

const removeFile = (index) => {
  const file = selectedFiles.value[index]
  if (file.preview) {
    URL.revokeObjectURL(file.preview)
  }
  selectedFiles.value.splice(index, 1)
}

const saveDocuments = async () => {
  if (selectedFiles.value.length === 0) {
    alert('Please select files to save')
    return
  }

  if (!props.form || !props.form.id) {
    alert('Form data is not available. Please refresh the page.')
    return
  }

  isUploading.value = true
  uploadProgress.value = 0

  try {
    const formData = new FormData()

    selectedFiles.value.forEach((fileObj, index) => {
      formData.append(`files[${index}]`, fileObj.file)
    })

    if (description.value) {
      formData.append('description', description.value)
    }

    const response = await fetch(`/frms/documents/${props.form.id}/upload`, {
      method: 'POST',
      body: formData,
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
      }
    })

    const result = await response.json()

    if (result.success) {
      // Add new documents to the list
      documentList.value.unshift(...result.documents)

      // Clear selected files
      selectedFiles.value.forEach(fileObj => {
        if (fileObj.preview) {
          URL.revokeObjectURL(fileObj.preview)
        }
      })
      selectedFiles.value = []
      description.value = ''

      alert('Documents saved successfully!')
    } else {
      alert('Save failed: ' + result.message)
    }
  } catch (error) {
    console.error('Save error:', error)
    alert('Save failed. Please try again.')
  } finally {
    isUploading.value = false
    uploadProgress.value = 0
  }
}

const downloadDocument = (doc) => {
  try {
    // Log download attempt for debugging
    console.log('Attempting to download document:', {
      id: doc.id,
      filename: doc.original_filename,
      url: `/frms/documents/download/${doc.id}`
    })

    window.open(`/frms/documents/download/${doc.id}`, '_blank')
  } catch (error) {
    console.error('Download error:', error)
    alert('Download failed. Please try again.')
  }
}

const deleteDocument = async (doc, index) => {
  if (!confirm('Are you sure you want to delete this document?')) {
    return
  }

  try {
    const response = await fetch(`/frms/documents/${doc.id}`, {
      method: 'DELETE',
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        'Content-Type': 'application/json'
      }
    })

    const result = await response.json()

    if (result.success) {
      documentList.value.splice(index, 1)
      alert('Document deleted successfully!')
    } else {
      alert('Delete failed: ' + result.message)
    }
  } catch (error) {
    console.error('Delete error:', error)
    alert('Delete failed. Please try again.')
  }
}

const goBack = () => {
  router.visit('/frms/form')
}

onMounted(() => {
  //Load documents if not provided
  if (!props.documents || props.documents.length === 0) {
    loadDocuments()
  }
})

const loadDocuments = async () => {
  if (!props.form?.id) return

  isLoading.value = true
  try {
    const response = await fetch(route('frms.documents.api', { frm_id: props.form.id }))
    const result = await response.json()

    if (result.success) {
      documentList.value = result.documents
    }
  } catch (error) {
    console.error('Error loading documents:', error)
  } finally {
    isLoading.value = false
  }
}

// Helper functions for file handling
const getFileIcon = (doc) => {
  const mimeType = doc.mime_type?.toLowerCase()
  const extension = doc.original_filename?.split('.').pop()?.toLowerCase()

  if (mimeType?.includes('image/') || ['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(extension)) {
    return mdi.mdiFileImage
  } else if (mimeType?.includes('pdf') || extension === 'pdf') {
    return mdi.mdiFilePdfBox
  } else if (mimeType?.includes('word') || ['doc', 'docx'].includes(extension)) {
    return mdi.mdiFileWord
  } else if (mimeType?.includes('excel') || mimeType?.includes('spreadsheet') || ['xls', 'xlsx'].includes(extension)) {
    return mdi.mdiFileExcel
  }
  return mdi.mdiFileDocument
}

const getFileIconColor = (doc) => {
  const extension = doc.file_extension?.toLowerCase()
  const mimeType = doc.mime_type?.toLowerCase()

  if (mimeType?.includes('image/') || ['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(extension)) {
    return 'text-green-600 bg-green-100'
  } else if (mimeType?.includes('pdf') || extension === 'pdf') {
    return 'text-red-600 bg-red-100'
  } else if (mimeType?.includes('word') || ['doc', 'docx'].includes(extension)) {
    return 'text-blue-600 bg-blue-100'
  } else if (mimeType?.includes('excel') || mimeType?.includes('spreadsheet') || ['xls', 'xlsx'].includes(extension)) {
    return 'text-green-600 bg-green-100'
  }
  return 'text-gray-600 bg-gray-100'
}

const isImage = (doc) => {
  const extension = doc.file_extension?.toLowerCase()
  const mimeType = doc.mime_type?.toLowerCase()
  return mimeType?.includes('image/') || ['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(extension)
}

const viewImage = (doc) => {
  if (isImage(doc)) {
    lightboxImage.value = doc
    showLightbox.value = true
  }
}

const closeLightbox = () => {
  showLightbox.value = false
  lightboxImage.value = null
}

// Load documents when component mounts
onMounted(() => {
  if (props.form?.id) {
    loadDocuments()
  }
})
</script>

<template>
  <div class="min-h-screen">
    <SectionTitleLineWithButton
  :icon="mdi.mdiFileDocument"
  :title="`Document: ${form?.frm_no || ''}`"
  main
>

     <BaseButton @click="emit('triggerTopRightButton', 'lists')" :icon="mdi.mdiViewList" label="Request Lists" color="contrast" rounded-full small />
    </SectionTitleLineWithButton>



    <!-- Upload Area -->
    <CardBox class="mb-6">
      <h3 class="text-lg font-semibold mb-4">Upload Documents</h3>

      <!-- Drag and Drop Area -->
      <div
        @dragover="handleDragOver"
        @dragleave="handleDragLeave"
        @drop="handleDrop"
        :class="[
          'border-2 border-dashed rounded-lg p-8 text-center transition-all duration-200',
          isDragOver ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20 scale-105' : 'border-gray-400 dark:border-gray-600 hover:border-blue-400 dark:hover:border-blue-500',
          isProcessing ? 'pointer-events-none opacity-75' : ''
        ]"
      >
        <div class="flex flex-col items-center">
          <!-- Loading State -->
          <div v-if="isProcessing" class="flex flex-col items-center">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mb-4"></div>
            <p class="text-lg font-medium text-blue-600 mb-2">Processing files...</p>
            <p class="text-sm text-gray-500 dark:text-gray-400">Please wait while we optimize your images</p>
          </div>

          <!-- Normal State -->
          <div v-else class="flex flex-col items-center">
            <svg class="w-12 h-12 text-gray-400 dark:text-gray-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
            </svg>
            <p class="text-lg font-medium text-gray-700 dark:text-gray-300 mb-2">
              Drag and drop files here, or
              <label class="text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 cursor-pointer underline">
                browse
                <input
                  type="file"
                  multiple
                  accept=".jpg,.jpeg,.png,.pdf,.doc,.docx"
                  @change="handleFileSelect"
                  class="hidden"
                />
              </label>
            </p>
            <p class="text-sm text-gray-500 dark:text-gray-400">
              Supports: Images (JPG, PNG), PDF, Word documents (Max 10MB each)
            </p>
            <p class="text-xs text-blue-500 dark:text-blue-400 mt-1">
              Images will be automatically optimized for better quality and smaller size
            </p>
          </div>
        </div>
      </div>

      <!-- Selected Files -->
      <div v-if="selectedFiles.length > 0" class="mt-6">
        <h4 class="font-medium mb-3 dark:text-gray-200">Selected Files ({{ selectedFiles.length }})</h4>
        <div class="space-y-3">
          <div
            v-for="(fileObj, index) in selectedFiles"
            :key="index"
            class="flex items-center justify-between p-4 bg-white dark:bg-gray-800 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg hover:border-blue-400 dark:hover:border-blue-500 transition-colors"
          >
            <div class="flex items-center space-x-4">
              <div class="relative">
                <img
                  v-if="fileObj.preview"
                  :src="fileObj.preview"
                  alt="Preview"
                  class="w-16 h-16 object-cover rounded-lg border-2 border-gray-200 dark:border-gray-600"
                />
                <div v-else class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-lg flex items-center justify-center border-2 border-gray-200 dark:border-gray-600">
                  <svg class="w-8 h-8 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                  </svg>
                </div>
                <div v-if="fileObj.isResized" class="absolute -top-1 -right-1 bg-green-500 text-white text-xs px-1 py-0.5 rounded-full">
                  ✓
                </div>
              </div>
              <div>
                <p class="font-medium text-sm dark:text-gray-200">{{ fileObj.name }}</p>
                <div class="flex items-center space-x-2 text-xs text-gray-500 dark:text-gray-400">
                  <span>{{ formatFileSize(fileObj.size) }}</span>
                  <span v-if="fileObj.isResized" class="text-green-600 dark:text-green-400">
                    (Optimized from {{ formatFileSize(fileObj.originalSize) }})
                  </span>
                </div>
              </div>
            </div>
            <BaseButton
               @click="removeFile(index)"
               :icon="mdi.mdiDelete"
               color="danger"
               small
               rounded-full
             />
          </div>
        </div>

        <!-- Description -->
        <FormField label="Description (Optional)" class="mt-4">
          <FormControl
            v-model="description"
            type="textarea"
            placeholder="Add a description for these documents..."
          />
        </FormField>

        <!-- Save Button -->
        <BaseButtons class="mt-4">
          <BaseButton
            @click="saveDocuments"
             :disabled="isUploading"
             :icon="mdi.mdiContentSave"
             :label="isUploading ? 'Saving...' : 'Save Documents'"
            color="info"
            class="px-6 py-2"
          />
        </BaseButtons>
      </div>
    </CardBox>

    <!-- Documents List -->
    <CardBox>
      <h3 class="text-lg font-semibold mb-4">Uploaded Documents</h3>

      <!-- Loading Skeleton -->
      <div v-if="isLoading" class="space-y-4">
        <div v-for="n in 3" :key="n" class="animate-pulse">
          <div class="flex items-center space-x-4 p-4 border rounded-lg">
            <div class="w-12 h-12 bg-gray-300 rounded"></div>
            <div class="flex-1 space-y-2">
              <div class="h-4 bg-gray-300 rounded w-3/4"></div>
              <div class="h-3 bg-gray-300 rounded w-1/2"></div>
            </div>
            <div class="flex space-x-2">
              <div class="w-8 h-8 bg-gray-300 rounded"></div>
              <div class="w-8 h-8 bg-gray-300 rounded"></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Documents -->
      <div v-else-if="documentList.length > 0" class="space-y-4">
        <div
          v-for="(document, index) in documentList"
          :key="document.id"
          class="flex items-center justify-between p-4 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 bg-white dark:bg-gray-900 transition-colors"
        >
          <div class="flex items-center space-x-4">
            <div :class="`w-12 h-12 rounded flex items-center justify-center ${getFileIconColor(document)}`">
              <svg class="w-6 h-6" :path="getFileIcon(document)" viewBox="0 0 24 24">
                <path fill="currentColor" :d="getFileIcon(document)" />
              </svg>
            </div>
            <div>
              <p class="font-medium dark:text-gray-200">{{ document.document_name }}</p>
              <p class="text-sm text-gray-500 dark:text-gray-400">
                {{ document.original_filename }} • {{ document.file_size_human }}
              </p>
              <p class="text-xs text-gray-400 dark:text-gray-500">
                Uploaded: {{ document.uploaded_at }}
              </p>
            </div>
          </div>

          <div class="flex space-x-2">
            <BaseButton
              v-if="isImage(document)"
              @click="viewImage(document)"
              :icon="mdi.mdiEye"
              color="success"
              small
              outline
              title="View Image"
              class="dark:text-green-400 dark:border-green-400 dark:hover:bg-green-400 dark:hover:text-white"
            />
            <BaseButton
              @click="downloadDocument(document)"
              :icon="mdi.mdiDownload"
              color="info"
              small
              outline
              title="Download"
              class="dark:text-blue-400 dark:border-blue-400 dark:hover:bg-blue-400 dark:hover:text-white"
            />
            <BaseButton
              @click="deleteDocument(document, index)"
              :icon="mdi.mdiDelete"
              color="danger"
              small
              outline
              title="Delete"
              class="dark:text-red-400 dark:border-red-400 dark:hover:bg-red-400 dark:hover:text-white"
            />
          </div>
        </div>
      </div>

      <!-- Empty State -->
        <div v-else class="text-center py-8 text-gray-500 dark:text-gray-400">
         <svg class="w-16 h-16 mx-auto mb-4 text-gray-300 dark:text-gray-600" :path="mdi.mdiFileDocument" viewBox="0 0 24 24">
           <path fill="currentColor" :d="mdi.mdiFileDocument" />
         </svg>
        <p>No documents uploaded yet</p>
        <p class="text-sm">Upload your first document using the area above</p>
      </div>
    </CardBox>

    <!-- Lightbox Modal -->
    <div
      v-if="showLightbox && lightboxImage"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-75"
      @click="closeLightbox"
    >
      <div class="relative max-w-4xl max-h-full p-4">
        <!-- Close button -->
        <button
          @click="closeLightbox"
          class="absolute top-2 right-2 z-10 w-8 h-8 bg-white rounded-full flex items-center justify-center text-gray-600 hover:text-gray-800 hover:bg-gray-100 transition-colors"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </button>

        <!-- Image -->
        <img
          :src="lightboxImage.file_url"
          :alt="lightboxImage.document_name"
          class="max-w-full max-h-full object-contain rounded-lg shadow-2xl"
          @click.stop
        />

        <!-- Image info -->
        <div class="absolute bottom-0 left-0 right-0 bg-black bg-opacity-50 text-white p-4 rounded-b-lg">
          <h3 class="font-semibold">{{ lightboxImage.document_name }}</h3>
          <p class="text-sm opacity-75">{{ lightboxImage.original_filename }} • {{ lightboxImage.file_size_human }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.animate-pulse {
  animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

@keyframes pulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: .5;
  }
}
</style>
