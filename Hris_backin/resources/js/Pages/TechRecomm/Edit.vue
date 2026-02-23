<template>
  <TechRecommLayout title="Edit Recommendation">
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
        Edit Tech Recommendation
      </h2>
    </template>

    <div class="py-4">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
            <form @submit.prevent="submit">
              <div class="mb-4">
                <InputLabel for="title" value="Recommendation Title" />
                <TextInput
                  id="title"
                  type="text"
                  class="mt-1 block w-full"
                  v-model="form.title"
                  required
                  autofocus
                />
                <InputError class="mt-2" :message="form.errors.title" />
              </div>

              <div class="mb-4">
                <InputLabel for="short_description" value="Short Description" />
                <TextInput
                  id="short_description"
                  type="text"
                  class="mt-1 block w-full"
                  v-model="form.short_description"
                  required
                />
                <InputError class="mt-2" :message="form.errors.short_description" />
              </div>

              <div class="mb-4">
                <InputLabel for="description" value="Detailed Description" />
                <Textarea
                  id="description"
                  class="mt-1 block w-full"
                  v-model="form.description"
                  rows="4"
                  required
                />
                <InputError class="mt-2" :message="form.errors.description" />
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="mb-4">
                  <InputLabel for="category" value="Category" />
                  <select
                    id="category"
                    class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                    v-model="form.category"
                    required
                  >
                    <option value="">Select Category</option>
                    <option value="Hardware">Hardware</option>
                    <option value="Software">Software</option>
                    <option value="Network">Network</option>
                    <option value="Security">Security</option>
                    <option value="Cloud">Cloud</option>
                    <option value="Other">Other</option>
                  </select>
                  <InputError class="mt-2" :message="form.errors.category" />
                </div>

                <div class="mb-4">
                  <InputLabel for="priority" value="Priority" />
                  <select
                    id="priority"
                    class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                    v-model="form.priority"
                    required
                  >
                    <option value="Low">Low</option>
                    <option value="Medium">Medium</option>
                    <option value="High">High</option>
                    <option value="Critical">Critical</option>
                  </select>
                  <InputError class="mt-2" :message="form.errors.priority" />
                </div>
              </div>

              <div class="mb-4">
                <InputLabel for="benefits" value="Benefits" />
                <Textarea
                  id="benefits"
                  class="mt-1 block w-full"
                  v-model="form.benefits"
                  rows="3"
                  required
                />
                <InputError class="mt-2" :message="form.errors.benefits" />
              </div>

              <div class="mb-4">
                <InputLabel for="cost_estimate" value="Cost Estimate (if known)" />
                <TextInput
                  id="cost_estimate"
                  type="text"
                  class="mt-1 block w-full"
                  v-model="form.cost_estimate"
                />
                <InputError class="mt-2" :message="form.errors.cost_estimate" />
              </div>

              <div class="mb-4">
                <InputLabel for="status" value="Status" />
                <select
                  id="status"
                  class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                  v-model="form.status"
                  required
                >
                  <option value="Pending">Pending</option>
                  <option value="Under Review">Under Review</option>
                  <option value="Approved">Approved</option>
                  <option value="Rejected">Rejected</option>
                </select>
                <InputError class="mt-2" :message="form.errors.status" />
              </div>

              <div class="flex items-center justify-end mt-4">
                <Link
                  :href="route('tech-recomm')"
                  class="inline-flex items-center px-4 py-2 bg-gray-300 dark:bg-gray-700 border border-transparent rounded-md font-semibold text-xs text-gray-800 dark:text-gray-300 uppercase tracking-widest hover:bg-gray-400 dark:hover:bg-gray-600 active:bg-gray-500 dark:active:bg-gray-600 focus:outline-none focus:border-gray-500 focus:ring focus:ring-gray-300 dark:focus:ring-gray-700 disabled:opacity-25 transition ease-in-out duration-150 mr-2"
                >
                  Cancel
                </Link>
                <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                  Update Recommendation
                </PrimaryButton>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </TechRecommLayout>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import TechRecommLayout from '@/Layouts/TechRecommLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';


const props = defineProps({
  recommendation: {
    type: Object,
    required: true,
  },
});

const form = useForm({
  title: props.recommendation.title,
  short_description: props.recommendation.short_description,
  description: props.recommendation.description,
  category: props.recommendation.category,
  priority: props.recommendation.priority,
  benefits: props.recommendation.benefits,
  cost_estimate: props.recommendation.cost_estimate,
  status: props.recommendation.status,
});

const submit = () => {
  form.put(route('tech-recomm.update', props.recommendation.id));
};
</script>