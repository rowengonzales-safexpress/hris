<template>
  <div class="w-full">
    <!-- Header Skeleton (reusable for both card and side menu) -->
    <div v-if="type === 'table'" class="flex mb-4 gap-4 items-center">
      <div class="h-8 w-40 bg-gray-200 dark:bg-gray-700 rounded animate-pulse"></div>
      <div class="flex-grow"></div>
      <div class="h-10 w-32 bg-gray-200 dark:bg-gray-700 rounded animate-pulse"></div>
    </div>

    <!-- Card Loading Skeleton -->
    <div v-else-if="type === 'card'" class="grid gap-4" :class="gridClass">
      <div v-for="i in itemCount" :key="'card-skeleton-' + i" class="border dark:border-gray-700 rounded-lg p-4 bg-white dark:bg-gray-800">
        <div class="h-6 w-full bg-gray-200 dark:bg-gray-700 rounded animate-pulse mb-3"></div>
        <div class="h-4 w-4/5 bg-gray-200 dark:bg-gray-700 rounded animate-pulse mb-2"></div>
        <div class="h-4 w-3/5 bg-gray-200 dark:bg-gray-700 rounded animate-pulse mb-4"></div>
        <div class="flex justify-between">
          <div class="h-6 w-1/3 bg-gray-200 dark:bg-gray-700 rounded animate-pulse"></div>
          <div class="h-6 w-1/5 bg-gray-200 dark:bg-gray-700 rounded animate-pulse"></div>
        </div>
      </div>
    </div>

    <!-- Side Menu Loading Skeleton -->
    <div v-else-if="type === 'side-menu'" class="flex flex-col gap-2">
      <div v-for="i in itemCount" :key="'menu-skeleton-' + i" class="flex items-center gap-3 p-3">
        <div class="h-6 w-6 rounded-full bg-gray-200 dark:bg-gray-700 animate-pulse"></div>
        <div class="h-4 w-3/4 bg-gray-200 dark:bg-gray-700 rounded animate-pulse"></div>
      </div>
    </div>

    <!-- Default Table Row Loading Skeleton -->
    <div v-else class="flex flex-col gap-2">
      <div v-for="i in itemCount" :key="'row-skeleton-' + i" class="flex gap-4 p-3 border-b dark:border-gray-700">
        <div class="h-6 w-full bg-gray-200 dark:bg-gray-700 rounded animate-pulse"></div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'SkeletonLoader',
  props: {
    type: {
      type: String,
      default: 'table', // 'table', 'card', or 'side-menu'
      validator: (value) => ['table', 'card', 'side-menu'].includes(value),
    },
    itemCount: {
      type: Number,
      default: 5,
    },
    columns: {
      type: Number,
      default: 3,
    },
  },
  computed: {
    gridClass() {
      return {
        'grid-cols-1': this.columns === 1,
        'grid-cols-2': this.columns === 2,
        'grid-cols-3': this.columns === 3,
        'grid-cols-4': this.columns === 4,
      }
    },
  },
}
</script>

<style scoped>
/* No custom styles needed, using Tailwind utility classes */
</style>
