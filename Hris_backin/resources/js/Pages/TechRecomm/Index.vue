<template>
  <TechRecommLayout title="Tech Recommendation">
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
        Tech Recommendation
      </h2>
    </template>

    <div class="py-4">
      <div class="flex justify-between items-center mb-6">
        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Recommendations Overview</h3>
        <Link
          :href="route('tech-recomm.create')"
          class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150"
        >
          Create New Recommendation
        </Link>
      </div>

      <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
        <div class="p-6 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
          <div class="flex flex-col">
            <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
              <div class="py-2 align-middle inline-block min-w-full sm:px-6 lg:px-8">
                <div class="shadow overflow-hidden border-b border-gray-200 dark:border-gray-700 sm:rounded-lg">
                  <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                      <tr>
                        <th
                          scope="col"
                          class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider"
                        >
                          Title
                        </th>
                        <th
                          scope="col"
                          class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider"
                        >
                          Category
                        </th>
                        <th
                          scope="col"
                          class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider"
                        >
                          Submitted By
                        </th>
                        <th
                          scope="col"
                          class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider"
                        >
                          Status
                        </th>
                        <th
                          scope="col"
                          class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider"
                        >
                          Date
                        </th>
                        <th scope="col" class="relative px-6 py-3">
                          <span class="sr-only">Actions</span>
                        </th>
                      </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                      <tr v-if="recommendations.length === 0">
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                          No recommendations found. Create a new recommendation to get started.
                        </td>
                      </tr>
                      <tr v-for="recommendation in recommendations" :key="recommendation.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                        <td class="px-6 py-4 whitespace-nowrap">
                          <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                            {{ recommendation.title }}
                          </div>
                          <div class="text-sm text-gray-500 dark:text-gray-400">
                            {{ recommendation.short_description }}
                          </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                          <div class="text-sm text-gray-900 dark:text-gray-100">{{ recommendation.category }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                          <div class="text-sm text-gray-900 dark:text-gray-100">{{ recommendation.submitted_by }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                          <span
                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                            :class="{
                              'bg-green-100 text-green-800': recommendation.status === 'Approved',
                              'bg-yellow-100 text-yellow-800': recommendation.status === 'Pending',
                              'bg-red-100 text-red-800': recommendation.status === 'Rejected',
                              'bg-blue-100 text-blue-800': recommendation.status === 'Under Review',
                            }"
                          >
                            {{ recommendation.status }}
                          </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                          <div class="text-sm text-gray-900 dark:text-gray-100">{{ recommendation.formatted_created_at }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                          <Link
                            :href="route('tech-recomm.show', recommendation.id)"
                            class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 mr-4"
                          >
                            View
                          </Link>
                          <Link
                            :href="route('tech-recomm.edit', recommendation.id)"
                            class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 mr-4"
                          >
                            Edit
                          </Link>
                          <button
                            @click="confirmRecommendationDeletion(recommendation)"
                            class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"
                          >
                            Delete
                          </button>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Recommendation Summary Cards -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <div class="text-lg font-semibold text-gray-900 dark:text-gray-100">Total</div>
            <div class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ stats.total }}</div>
          </div>
        </div>
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <div class="text-lg font-semibold text-gray-900 dark:text-gray-100">Approved</div>
            <div class="text-3xl font-bold text-green-600 dark:text-green-400">{{ stats.approved }}</div>
          </div>
        </div>
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <div class="text-lg font-semibold text-gray-900 dark:text-gray-100">Pending</div>
            <div class="text-3xl font-bold text-yellow-600 dark:text-yellow-400">{{ stats.pending }}</div>
          </div>
        </div>
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <div class="text-lg font-semibold text-gray-900 dark:text-gray-100">Rejected</div>
            <div class="text-3xl font-bold text-red-600 dark:text-red-400">{{ stats.rejected }}</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <Modal :show="confirmingDeletion" @close="closeModal">
      <div class="p-6">
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
          Are you sure you want to delete this recommendation?
        </h2>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
          Once this recommendation is deleted, all of its resources and data will be permanently deleted.
        </p>
        <div class="mt-6 flex justify-end">
          <SecondaryButton @click="closeModal"> Cancel </SecondaryButton>
          <DangerButton
            class="ml-3"
            :class="{ 'opacity-25': processing }"
            :disabled="processing"
            @click="deleteRecommendation"
          >
            Delete Recommendation
          </DangerButton>
        </div>
      </div>
    </Modal>
  </TechRecommLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import TechRecommLayout from '@/Layouts/TechRecommLayout.vue';
import Modal from '@/Components/Modal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';

const props = defineProps({
  recommendations: {
    type: Array,
    default: () => [],
  },
});

const confirmingDeletion = ref(false);
const processing = ref(false);
const recommendationToDelete = ref(null);

const stats = computed(() => {
  return {
    total: props.recommendations.length,
    approved: props.recommendations.filter(rec => rec.status === 'Approved').length,
    pending: props.recommendations.filter(rec => rec.status === 'Pending').length,
    rejected: props.recommendations.filter(rec => rec.status === 'Rejected').length,
  };
});

const confirmRecommendationDeletion = (recommendation) => {
  recommendationToDelete.value = recommendation;
  confirmingDeletion.value = true;
};

const closeModal = () => {
  confirmingDeletion.value = false;
  recommendationToDelete.value = null;
};

const deleteRecommendation = () => {
  processing.value = true;
  router.delete(route('tech-recomm.destroy', recommendationToDelete.value.id), {
    onSuccess: () => closeModal(),
    onFinish: () => {
      processing.value = false;
    },
  });
};
</script>