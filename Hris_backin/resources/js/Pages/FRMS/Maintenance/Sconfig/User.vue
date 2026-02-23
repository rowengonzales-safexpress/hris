<script setup>
import { ref, onMounted, computed } from 'vue'
import { useForm, router, usePage } from '@inertiajs/vue3'
import service from '@/Components/Toast/service'
import CardBox from '@/Components/CardBox.vue'
import CoreTable from '@/Components/CoreTable.vue'
import AsideDrawer from '@/Components/AsideDrawer.vue'
import FormField from '@/Components/FormField.vue'
import FormControl from '@/Components/FormControl.vue'
import BaseButtons from '@/Components/BaseButtons.vue'
import BaseButton from '@/Components/BaseButton.vue'

import axios from 'axios'
import SwalConfirm from '@/Components/SwalConfirm.vue'
import Checkbox from '@/Components/Checkbox.vue'
import BaseIcon from '@/Components/BaseIcon.vue'

const rows = ref([])
const roles = ref([])
const branches = ref([])
const openDrawer = ref(false)
const isLoading = ref(false)
const page = usePage()
const form = useForm({
  _token: page.props.csrf_token,
  id: null,
  name: '',
  first_name: '',
  last_name: '',
  email: '',
  user_type: 'User',
  member_role: null,
  sitehead_user_id: null,
  branch_id: '',
  status: 'A',
  password: '',
  password_confirmation: ''
}).transform((data) => ({
  ...data,
  member_role: data.member_role?.id !== undefined ? data.member_role.id : data.member_role,
  user_type: data.user_type?.id !== undefined ? data.user_type.id : data.user_type,
  sitehead_user_id: data.sitehead_user_id?.id !== undefined ? data.sitehead_user_id.id : data.sitehead_user_id,
  branch_id: data.branch_id?.id !== undefined ? data.branch_id.id : data.branch_id,
}))
const formErrors = ref([])
let toast = service()
const isConfirmOpen = ref(false)
const action = ref('')
const drawerTitle = computed(() => action.value === 'Add' ? 'Add User' : action.value === 'Edit' ? 'Edit User' : 'User')
const showPasswordField = ref(false)

const roleOptions = computed(() => {
  const list = roles.value.map(role => ({ id: role.id, label: role.name }))
  return [{ id: null, label: 'Select Role' }, ...list]
})

const userTypeOptions = computed(() => [
  { id: 'User', label: 'User' },
  { id: 'Admin', label: 'Admin' }
])

const immediateHeadOptions = computed(() => {
  const list = rows.value.map(user => ({ id: user.id, label: user.name }))
  return [{ id: null, label: 'Select Immediate Head' }, ...list]
})

const branchOptions = computed(() => {
  const list = branches.value.map(branch => ({ id: branch.id, label: branch.branch_name }))
  return [{ id: null, label: 'Select Branch' }, ...list]
})

const validations = computed(() => {
  const pwd = form.password || ''
  return {
    length: pwd.length >= 8,
    upper: /[A-Z]/.test(pwd),
    lower: /[a-z]/.test(pwd),
    number: /[0-9]/.test(pwd),
    special: /[!@#$%^&*]/.test(pwd)
  }
})

const strengthScore = computed(() => {
  const v = validations.value
  let score = 0
  if (v.length) score++
  if (v.upper) score++
  if (v.lower) score++
  if (v.number) score++
  if (v.special) score++
  return score
})

const strengthLabel = computed(() => {
  const score = strengthScore.value
  if (score <= 1) return 'Very Weak'
  if (score === 2) return 'Weak'
  if (score === 3) return 'Medium'
  if (score === 4) return 'Strong'
  return 'Very Strong'
})

const strengthColor = computed(() => {
  const score = strengthScore.value
  if (score <= 1) return 'text-red-500'
  if (score === 2) return 'text-orange-500'
  if (score === 3) return 'text-yellow-500'
  if (score === 4) return 'text-blue-500'
  return 'text-green-600'
})

const strengthBarColor = computed(() => {
  const score = strengthScore.value
  if (score <= 1) return 'bg-red-500'
  if (score === 2) return 'bg-orange-500'
  if (score === 3) return 'bg-yellow-500'
  if (score === 4) return 'bg-blue-500'
  return 'bg-green-600'
})

const strengthWidth = computed(() => {
  return `${(strengthScore.value / 5) * 100}%`
})

const passwordMatch = computed(() => {
  return form.password && form.password_confirmation && form.password === form.password_confirmation
})

const headers = [
  { label: 'Username', fieldName: 'name', type: 'link' },
  { label: 'First Name', fieldName: 'first_name' },
  { label: 'Last Name', fieldName: 'last_name' },
  { label: 'Email', fieldName: 'email' },
  { label: 'Type', fieldName: 'user_type' },
  { label: 'Status', fieldName: 'status', type: 'activeinactive' },
]

const loadData = async () => {
  isLoading.value = true
  try {
    const [userResp, roleResp, branchResp] = await Promise.all([
      axios.get('/frls/user/list'),
      axios.get('/frls/role/list'),
      axios.get('/frls/core-branch/list')
    ])
    rows.value = userResp?.data?.data ?? []
    roles.value = roleResp?.data?.data ?? []
    branches.value = branchResp?.data?.data ?? []
  } catch (error) {
    console.error('Error loading data:', error)
  } finally {
    isLoading.value = false
  }
}

const openLink = async (row) => {
  action.value = 'Edit'
  isLoading.value = true
  try {
    const resp = await axios.get(`/frls/user/${row.id}`)
    const data = resp?.data || row
    form.id = data.id ?? row.id
    form.name = data.name ?? row.name ?? ''
    form.first_name = data.first_name ?? row.first_name ?? ''
    form.last_name = data.last_name ?? row.last_name ?? ''
    form.email = data.email ?? row.email ?? ''

    const userType = data.user_type ?? row.user_type ?? 'User'
    form.user_type = userTypeOptions.value.find(t => t.id === userType) || userTypeOptions.value[0]

    const roleId = data.role?.id ?? data.member_role ?? row.member_role ?? null
    form.member_role = roleOptions.value.find(r => r.id === roleId) || roleOptions.value[0]

    form.status = data.status ?? 'A'

    const headId = data.sitehead_user_id ?? row.sitehead_user_id ?? null
    form.sitehead_user_id = immediateHeadOptions.value.find(u => u.id == headId) || immediateHeadOptions.value[0]

    const branchId = data.branch_id ?? row.branch_id ?? null
    form.branch_id = branchOptions.value.find(b => b.id == branchId) || branchOptions.value[0]
    form.password = ''
    form.password_confirmation = ''
    showPasswordField.value = false
    openDrawer.value = true
  } finally {
    isLoading.value = false
  }
}

const newItem = () => {
  action.value = 'Add'
  form.id = null
  form.name = ''
  form.first_name = ''
  form.last_name = ''
  form.email = ''
  form.user_type = userTypeOptions.value[0]
  form.member_role = roleOptions.value[0]
  form.status = 'A'
  form.sitehead_user_id = immediateHeadOptions.value[0]
  form.branch_id = branchOptions.value[0]
  form.password = ''
  form.password_confirmation = ''
  showPasswordField.value = true
  openDrawer.value = true
}

const save = async () => {
  isLoading.value = true
  try {
    if (action.value === 'Edit' && form.id) {
      await new Promise((resolve, reject) => {
        form.put(`/frls/user/${form.id}`, {
          preserveScroll: true,
          onSuccess: () => {
            toast.success('User updated')
            resolve()
          },
          onError: (errors) => {
            formErrors.value = Object.values(errors || {})
            const firstError = formErrors.value.length > 0 ? formErrors.value[0] : 'Failed to update user'
            toast.error(firstError)
            reject(errors)
          }
        })
      })
    } else {
      await new Promise((resolve, reject) => {
        form.post('/frls/user', {
          preserveScroll: true,
          onSuccess: () => {
            toast.success('User created')
            resolve()
          },
          onError: (errors) => {
            formErrors.value = Object.values(errors || {})
            const firstError = formErrors.value.length > 0 ? formErrors.value[0] : 'Failed to create user'
            toast.error(firstError)
            reject(errors)
          }
        })
      })
    }
    await loadData()
    openDrawer.value = false
  } finally {
    isLoading.value = false
  }
}

const remove = async () => {
  if (!form.id) {
    openDrawer.value = false
    return
  }
  isLoading.value = true
  try {
    await new Promise((resolve, reject) => {
      router.delete(`/frls/user/${form.id}`, {
        preserveScroll: true,
        onSuccess: () => {
          toast.success('User deleted')
          resolve()
        },
        onError: (errors) => {
          formErrors.value = Object.values(errors || {})
          toast.error('Failed to delete user')
          reject(errors)
        }
      })
    })
    await loadData()
    openDrawer.value = false
  } finally {
    isLoading.value = false
  }
}

onMounted(loadData)
</script>

<template>
  <CardBox class="flex-1 p-6" has-table>
    <CoreTable
      :table-rows="rows"
      :table-header="headers"
      table-name="Users"
      searchable-fields="name,email,first_name,last_name"
      :is-paginated="true"
      :loading="isLoading"
      @openLink="openLink"
    >
      <template #table-action>
        <BaseButtons>
          <BaseButton :icon="'mdiFileDocumentPlus'" :disabled="isLoading" color="info" rounded-full small label="Add" @click="newItem" />
        </BaseButtons>
      </template>
    </CoreTable>
  </CardBox>
    <AsideDrawer :title="drawerTitle" :is-open="openDrawer" @closeDrawer="openDrawer = false" class="shadow-lg shadow-blue-500/50">
      <div v-if="formErrors.length" class="mb-3 text-red-600 text-sm">
        <div v-for="(e,i) in formErrors" :key="i">{{ e }}</div>
      </div>
      <FormField label="Username">
        <FormControl v-model="form.name" />
      </FormField>
      <FormField label="First Name">
        <FormControl v-model="form.first_name" />
      </FormField>
      <FormField label="Last Name">
        <FormControl v-model="form.last_name" />
      </FormField>
      <FormField label="Email">
        <FormControl v-model="form.email" type="email" />
      </FormField>
      <FormField label="Immediate Head">
          <FormControl v-model="form.sitehead_user_id" :options="immediateHeadOptions" />
      </FormField>
      <FormField label="User Type">
          <FormControl v-model="form.user_type" :options="userTypeOptions" />
      </FormField>
      <FormField label="Branch">
          <FormControl v-model="form.branch_id" :options="branchOptions" />
      </FormField>
      <FormField label="Role">
          <FormControl v-model="form.member_role" :options="roleOptions" />
      </FormField>



      <div v-if="action === 'Edit'" class="mb-4">
        <div class="flex justify-between items-center mb-2">

           <BaseButton
             type="button"
             small
             color="info"
             outline
             :label="showPasswordField ? 'Cancel' : 'Change Password'"
             @click="showPasswordField = !showPasswordField"
           />
        </div>
        <p v-if="!showPasswordField" class="text-gray-500 text-sm italic">Leave blank to keep current password</p>
      </div>

      <div v-if="showPasswordField">

        <div  class="mb-4 p-4 bg-blue-900/10 rounded-lg border border-blue-500/30">
        <div class="flex items-start">
          <BaseIcon path="mdiInformation" class="text-blue-500 mr-2 mt-0.5" w="w-5" h="h-5" />
          <div class="text-sm text-gray-600 dark:text-gray-300">
            <p class="font-semibold mb-1">Password Requirements</p>
            <ul class="list-disc pl-4 space-y-0.5 text-xs">
              <li>Must be at least 8 characters long</li>
              <li>Must meet at least 3 of the following criteria:</li>
              <ul class="list-disc pl-4 mt-0.5">
                  <li>At least one uppercase letter (A-Z)</li>
                  <li>At least one lowercase letter (a-z)</li>
                  <li>At least one number (0-9)</li>
                  <li>At least one special character (!@#$%^&*)</li>
              </ul>
            </ul>
          </div>
        </div>
      </div>

          <FormField label="Password" :required="action === 'Add'">
            <FormControl v-model="form.password" type="password" :icon="'mdiKey'" />
          </FormField>

          <div v-if="(action === 'Add' || form.password) && form.password" class="mb-6 -mt-4 px-1">
            <div class="flex justify-between items-center mb-1">
               <span class="text-xs text-gray-500 dark:text-gray-400">Password strength: <span :class="['font-bold', strengthColor]">{{ strengthLabel }}</span></span>
            </div>
            <div class="h-1.5 w-full bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden mb-2">
              <div class="h-full transition-all duration-300" :class="strengthBarColor" :style="{ width: strengthWidth }"></div>
            </div>

            <div class="grid grid-cols-1 gap-1">
                <div class="flex items-center text-xs" :class="validations.length ? 'text-green-500' : 'text-red-500'">
                   <BaseIcon :path="validations.length ? 'mdiCheck' : 'mdiClose'" w="w-3.5" h="h-3.5" class="mr-1" />
                   At least 8 characters
                </div>
                <div class="flex items-center text-xs" :class="validations.upper ? 'text-green-500' : 'text-red-500'">
                   <BaseIcon :path="validations.upper ? 'mdiCheck' : 'mdiClose'" w="w-3.5" h="h-3.5" class="mr-1" />
                   Uppercase letter (A-Z)
                </div>
                 <div class="flex items-center text-xs" :class="validations.lower ? 'text-green-500' : 'text-red-500'">
                   <BaseIcon :path="validations.lower ? 'mdiCheck' : 'mdiClose'" w="w-3.5" h="h-3.5" class="mr-1" />
                   Lowercase letter (a-z)
                </div>
                 <div class="flex items-center text-xs" :class="validations.number ? 'text-green-500' : 'text-red-500'">
                   <BaseIcon :path="validations.number ? 'mdiCheck' : 'mdiClose'" w="w-3.5" h="h-3.5" class="mr-1" />
                   Number (0-9)
                </div>
                 <div class="flex items-center text-xs" :class="validations.special ? 'text-green-500' : 'text-red-500'">
                   <BaseIcon :path="validations.special ? 'mdiCheck' : 'mdiClose'" w="w-3.5" h="h-3.5" class="mr-1" />
                   Special character (!@#$%^&*)
                </div>
            </div>
          </div>

          <FormField label="Confirm Password" :required="action === 'Add'">
            <div>
              <FormControl v-model="form.password_confirmation" type="password" :icon="'mdiKey'" />
              <div v-if="form.password_confirmation" class="flex items-center text-xs mt-1" :class="passwordMatch ? 'text-green-500' : 'text-red-500'">
                   <BaseIcon :path="passwordMatch ? 'mdiCheck' : 'mdiClose'" w="w-3.5" h="h-3.5" class="mr-1" />
                   {{ passwordMatch ? 'Passwords match' : 'Passwords do not match' }}
              </div>
            </div>
          </FormField>
      </div>
      <FormField label="Status">
         <div class="flex items-center">
             <Checkbox :checked="form.status === 'A'" @update:checked="val => form.status = val ? 'A' : 'I'" />
             <span class="ml-2">{{ form.status === 'A' ? 'Active' : 'Inactive' }}</span>
         </div>
      </FormField>
      <BaseButtons>
        <BaseButton :icon="'mdiContentSave'" :disabled="isLoading" color="info" rounded-full small label="Save" @click="save" />
        <BaseButton v-if="action === 'Edit'" :icon="'mdiDelete'" color="danger" rounded-full small label="Delete" @click="isConfirmOpen = true" />
        <BaseButton v-else :icon="'mdiClose'" color="error" rounded-full small label="Cancel" @click="openDrawer = false" />
      </BaseButtons>
      <SwalConfirm
        v-model="isConfirmOpen"
        type="warning"
        title="Delete Record"
        text="Are you sure you want to delete this record?"
        @confirm="remove"
      />
    </AsideDrawer>
</template>
