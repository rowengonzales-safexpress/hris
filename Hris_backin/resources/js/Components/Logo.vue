<template>
  <div class="tms-logo">
    <div class="logo-title">Admin</div>
    <div class="logo-subtitle">Safexpress System Management</div>
  </div>
</template>

<script setup lang="ts">
// No props needed for the text-based logo
</script>

<style scoped>
.tms-logo {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  font-family: 'Inter', sans-serif;
}

.logo-title {
  font-size: 24px;
  font-weight: 700;
  color: var(--va-primary);
  line-height: 1.2;
  margin-bottom: 2px;
}

.logo-subtitle {
  font-size: 12px;
  font-weight: 400;
  color: var(--va-secondary);
  text-transform: lowercase;
  line-height: 1;
}
</style>
