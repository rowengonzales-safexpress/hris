<script setup>
import { Link, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

const page = usePage()

const autoCrumbs = computed(() => {
  const url = String(page.url || '/')
  const path = url.replace(/^\//, '').replace(/\/$/, '')
  const segments = path ? path.split('/') : []

  const titleMap = {
    frls: 'Frls',
    admin: 'Admin',
    tracking: 'Tracking',
    hris: 'Hris',
    dashboard: 'Dashboard'
  }

  const out = [{ title: 'Application', url: '/admin/application' }]

  let acc = ''
  segments.forEach((seg, idx) => {
    acc += '/' + seg
    const title = titleMap[seg] || seg.replace(/-/g, ' ').replace(/\b\w/g, s => s.toUpperCase())
    const isLast = idx === segments.length - 1
    out.push({ title, url: isLast ? null : acc })
  })

  return out
})

const breadcrumbs = computed(() => {
  const fromProps = page.props?.navigation?.breadcrumbs
  return Array.isArray(fromProps) && fromProps.length > 0 ? fromProps : autoCrumbs.value
})
</script>

<template>
  <nav aria-label="Breadcrumb" class="flex px-6 xl:max-w-6xl xl:mx-auto">
    <ol class="inline-flex items-center space-x-1 md:space-x-2 rtl:space-x-reverse">
      <li v-for="(item, index) in breadcrumbs" :key="index" class="inline-flex items-center">
        <Link v-if="index !== breadcrumbs.length - 1 && item.url" :href="item.url" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white">
          <svg v-if="index === 0" class="w-3 h-3 me-2.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
            <path d="m19.707 9.293-2-2-7-7a1 1 0 0 0-1.414 0l-7 7-2 2a1 1 0 0 0 1.414 1.414L2 10.414V18a2 2 0 0 0 2 2h3a1 1 0 0 0 1-1v-4a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v4a1 1 0 0 0 1 1h3a2 2 0 0 0 2-2v-7.586l.293.293a1 1 0 0 0 1.414-1.414Z"/>
          </svg>
          <svg v-else class="rtl:rotate-180 w-3 h-3 text-gray-400 mx-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10">
            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 9 4-4-4-4"/>
          </svg>
          {{ item.title }}
        </Link>
        <template v-else>
          <div class="flex items-center">
            <svg v-if="index === 0" class="w-3 h-3 me-2.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
              <path d="m19.707 9.293-2-2-7-7a1 1 0 0 0-1.414 0l-7 7-2 2a1 1 0 0 0 1.414 1.414L2 10.414V18a2 2 0 0 0 2 2h3a1 1 0 0 0 1-1v-4a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v4a1 1 0 0 0 1 1h3a2 2 0 0 0 2-2v-7.586l.293.293a1 1 0 0 0 1.414-1.414Z"/>
            </svg>
            <svg v-else class="rtl:rotate-180 w-3 h-3 text-gray-400 mx-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10">
              <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 9 4-4-4-4"/>
            </svg>
            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ item.title }}</span>
          </div>
        </template>
      </li>
    </ol>
  </nav>
</template>
