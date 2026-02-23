<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
  status: Number,
});

const errorTitle = computed(() => {
  return {
    401: 'UNAUTHORIZED',
    403: 'FORBIDDEN',
    404: 'NOT FOUND',
    500: 'SERVER ERROR',
    503: 'SERVICE UNAVAILABLE',
  }[props.status] || 'ERROR';
});

const title = computed(() => {
  return {
    401: 'You are not authorized',
    403: 'You are not authorized',
    404: 'Page not found',
    500: 'Something went wrong',
    503: 'Service unavailable',
  }[props.status] || 'An unexpected error occurred';
});

const description = computed(() => {
  return {
    401: 'You tried to access a page you did not have prior authorization for.',
    403: 'You tried to access a page you did not have prior authorization for.',
    404: 'The page you are looking for might have been removed, had its name changed, or is temporarily unavailable.',
    500: 'Please try again later or contact the administrator.',
    503: 'We are currently performing maintenance. Please check back soon.',
  }[props.status] || 'Please contact your administrator if the problem persists.';
});

const illustrationSrc = computed(() => {
    // Using a placeholder image that resembles the style.
    // Ideally, replace this with the local asset path of the provided image.
    // For now, returning a 403 forbidden vector style image URL or a placeholder.
    if (props.status === 403 || props.status === 401) {
        return 'https://img.freepik.com/free-vector/403-error-forbidden-concept-illustration_114360-1914.jpg?w=740&t=st=1703000000~exp=1703000600~hmac=...';
        // Using a reliable generic 403 SVG placeholder since we can't upload the user's specific image
        // A better approach for the user is to use a "Lock" SVG if the image isn't available.
    }
    return null;
});
</script>

<template>
  <Head :title="errorTitle" />
  <div class="min-h-screen flex flex-col items-center justify-center bg-white text-gray-800 px-4">

    <!-- Main Content Wrapper -->
    <div class="flex flex-col md:flex-row items-center justify-center max-w-5xl w-full mb-12">

        <!-- Illustration Section -->
        <div class="flex-1 flex justify-center md:justify-end md:pr-12 mb-8 md:mb-0">
            <div class="relative w-72 h-72 md:w-96 md:h-96">
                <!-- Blue Blob Background Effect -->
                <div class="absolute inset-0 bg-blue-100 rounded-full transform scale-90 translate-y-4"></div>

                <!-- Illustration -->
                <div class="relative z-10 w-full h-full flex items-center justify-center">
                    <!-- If status is 403/401, show a lock/guard SVG representation -->
                    <svg v-if="status === 403 || status === 401" viewBox="0 0 200 200" class="w-full h-full" xmlns="http://www.w3.org/2000/svg">
                        <!-- Simplified Guard/Lock Illustration matching the vibe -->
                        <rect x="60" y="80" width="80" height="90" rx="5" fill="#e2e8f0" stroke="#334155" stroke-width="2"/>
                        <circle cx="100" cy="120" r="8" fill="#fff" stroke="#334155" stroke-width="2"/>
                        <path d="M100 128 v20" stroke="#334155" stroke-width="2"/>
                        <path d="M70 80 V50 a30 30 0 0 1 60 0 v30" fill="none" stroke="#334155" stroke-width="2"/>
                        <!-- Guard Figure (Abstract) -->
                        <path d="M140 60 h40 v100 h-20" fill="none" stroke="#2563eb" stroke-width="0"/>
                        <!-- Since drawing a full guard in SVG manually is complex, using a Lock + Barrier symbol -->
                        <rect x="40" y="160" width="120" height="10" fill="#1e293b"/>
                        <path d="M50 160 L40 200" stroke="#1e293b" stroke-width="2"/>
                        <path d="M150 160 L160 200" stroke="#1e293b" stroke-width="2"/>
                        <path d="M40 160 l120 0" stroke="#fbbf24" stroke-width="0"/>
                        <!-- Striped Barrier -->
                        <rect x="30" y="150" width="140" height="15" fill="#3b82f6"/>
                        <path d="M40 150 l-10 15 M60 150 l-10 15 M80 150 l-10 15 M100 150 l-10 15 M120 150 l-10 15 M140 150 l-10 15 M160 150 l-10 15" stroke="#1e293b" stroke-width="2"/>
                    </svg>
                     <svg v-else-if="status === 404" viewBox="0 0 24 24" class="w-48 h-48 text-indigo-400" fill="none" stroke="currentColor" stroke-width="1">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                     <svg v-else viewBox="0 0 24 24" class="w-48 h-48 text-red-400" fill="none" stroke="currentColor" stroke-width="1">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Text Section -->
        <div class="flex-1 text-center md:text-left">
            <h3 class="text-xl md:text-2xl font-semibold text-gray-500 uppercase tracking-widest mb-0">ERROR</h3>
            <h1 class="text-8xl md:text-9xl font-black text-gray-800 leading-none mb-2">{{ status }}</h1>
            <h2 class="text-3xl md:text-4xl font-bold text-gray-700 uppercase tracking-widest">{{ errorTitle }}</h2>
        </div>
    </div>

    <!-- Message Section -->
    <div class="max-w-xl mx-auto text-center">
        <h3 class="text-2xl font-bold text-gray-800 mb-3">{{ title }}</h3>
        <p class="text-gray-500 mb-8 text-lg">{{ description }}</p>

        <!-- Buttons -->
        <div class="flex flex-col sm:flex-row justify-center gap-4">
             <Link
                href="/"
                class="inline-flex items-center justify-center px-8 py-3 border border-transparent text-base font-bold rounded-full text-white bg-info hover:bg-indigo-700 transition-all duration-200 shadow-lg hover:shadow-xl"
            >
                GO TO HOMEPAGE
            </Link>
             <button
                @click="$event.view.history.back()"
                class="inline-flex items-center justify-center px-8 py-3 border border-gray-300 text-base font-bold rounded-full text-gray-700 bg-white hover:bg-gray-50 transition-all duration-200 shadow-md hover:shadow-lg"
            >
                GO BACK
            </button>
        </div>
    </div>

  </div>
</template>

<style scoped>
/* Optional: Add custom fonts if needed to match exact typography */
</style>

<style scoped>
/* Add any additional custom styles here */
</style>
