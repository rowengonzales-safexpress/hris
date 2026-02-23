<template>
 <footer class="bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 p-4 text-sm text-gray-600 dark:text-gray-400">
                <div class="float-right hidden sm:inline">
                    <b>Version</b> 1.0.0
                </div>
                <strong>Copyright &copy; {{ currentYear }}  <a href="#" class="text-blue-600 hover:text-blue-800">SafExpress</a>.</strong> All rights reserved.
            </footer>
</template>
<script setup>
import { ref } from 'vue'
const currentYear = ref(new Date().getFullYear())
</script>
