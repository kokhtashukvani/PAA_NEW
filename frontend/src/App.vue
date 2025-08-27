<template>
  <div id="app">
    <RouterView />
  </div>
</template>

<script setup>
import { onMounted } from 'vue';
import { RouterView } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

const authStore = useAuthStore();

// When the component is mounted, try to fetch the user.
// This is crucial for session persistence. If a token exists in localStorage,
// this action will validate it and fetch the user's data.
onMounted(() => {
  authStore.fetchUser();
});
</script>

<style>
/* You can add global styles here if needed, but for now, we rely on Tabler */
</style>
