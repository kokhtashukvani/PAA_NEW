<template>
  <header class="navbar navbar-expand-md d-print-none">
    <div class="container-xl">
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbar-menu">
        <span class="navbar-toggler-icon"></span>
      </button>
      <h1 class="navbar-brand navbar-brand-autodark d-none-navbar-horizontal pe-0 pe-md-3">
        <a href=".">
          <img src="@/assets/logo.svg" width="110" height="32" alt="Tabler" class="navbar-brand-image">
        </a>
      </h1>
      <div class="navbar-nav flex-row order-md-last">
        <div class="nav-item dropdown">
          <a href="#" class="nav-link d-flex lh-1 text-reset p-0" data-bs-toggle="dropdown" aria-label="Open user menu">
            <span class="avatar avatar-sm">{{ userInitials }}</span>
            <div class="d-none d-xl-block ps-2">
              <div>{{ authStore.user?.username }}</div>
              <div class="mt-1 small text-muted">{{ authStore.user?.role }}</div>
            </div>
          </a>
          <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
            <a href="#" class="dropdown-item" @click.prevent="handleLogout">خروج</a>
          </div>
        </div>
      </div>
    </div>
  </header>
</template>

<script setup>
import { computed } from 'vue';
import { useAuthStore } from '@/stores/auth';

const authStore = useAuthStore();

const handleLogout = () => {
  authStore.logout();
};

const userInitials = computed(() => {
  const username = authStore.user?.username;
  if (!username) return '';
  return username.charAt(0).toUpperCase();
});
</script>

<style scoped>
.avatar {
    background-color: #3d5afe;
    color: white;
    font-weight: bold;
}
</style>
