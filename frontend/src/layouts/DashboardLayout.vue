<template>
  <div class="page">
    <AppSidebar />
    <div class="page-wrapper">
      <AppHeader />
      <div class="page-body">
        <div class="container-xl">
          <RouterView />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { RouterView } from 'vue-router';
import AppSidebar from '@/components/AppSidebar.vue';
import AppHeader from '@/components/AppHeader.vue';
</script>

<style scoped>
/* Scoped styles for the dashboard layout */
</style>
