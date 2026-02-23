<script setup>
import Lists from "./List.vue";
const props = defineProps({ masterlist: Array });
</script>

<template>
    <Lists :masterlist="masterlist" />
</template>
